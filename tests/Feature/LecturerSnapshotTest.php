<?php

namespace Tests\Feature;

use App\Models\DataDosen;
use App\Models\User;
use App\Services\Lecturers\LecturerSnapshots;
use App\Services\Lecturers\LecturerWorkbook;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\Support\CreatesWorkbook;
use Tests\TestCase;

class LecturerSnapshotTest extends TestCase
{
    use CreatesWorkbook;

    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('SQLite required.');
        }
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->travelTo(now()->setDate(2026, 10, 22));
        Schema::create('database_dosen_new', function (Blueprint $table) {
            $table->string('kode_dosen')->primary();
            foreach (array_diff(array_keys(LecturerWorkbook::HEADERS), ['kode_dosen']) as $field) {
                $table->text($field)->nullable();
            }
            $table->timestamps();
        });
        (require database_path('migrations/2026_09_25_000000_add_visibility_and_soft_deletes_to_database_dosen_new.php'))->up();
        DataDosen::create(['kode_dosen' => 'D1', 'nama_dosen' => 'Current 2026', 'jja' => 'Lektor']);
        (require database_path('migrations/2026_10_22_000000_create_lecturer_snapshots.php'))->up();
    }

    protected function tearDown(): void
    {
        $this->removeWorkbooks();
        $this->travelBack();
        parent::tearDown();
    }

    public function test_preview_accepts_custom_headers_without_validating_import_first(): void
    {
        $path = $this->makeWorkbook(['FM Malang' => [['Employee ID', 'Full Name', 'Rank'], ['D1', 'Historical', 'Asisten Ahli']]]);
        $preview = app(LecturerWorkbook::class)->preview($path);
        $this->assertSame(['Employee ID', 'Full Name', 'Rank'], $preview['headers']);
        $this->assertSame(1, $preview['header_row']);
        $this->assertSame('Historical', $preview['preview_data'][0][1]);
        $this->assertArrayHasKey('nama_dosen', $preview['mapping_config']['required']);
        $this->assertSame(1, DB::table('lecturer_imports')->count());
    }

    public function test_manual_mapping_is_used_and_historical_import_does_not_touch_current_master(): void
    {
        $path = $this->makeWorkbook(['Malang' => [['ID', 'Full Name', 'Rank'], ['d1', 'Old 2025', 'Asisten Ahli']]]);
        app(LecturerWorkbook::class)->import($path, 2025, 8, ['kode_dosen' => 0, 'nama_dosen' => 1, 'jja' => 2], 1);
        $this->assertSame('Current 2026', DataDosen::find('D1')->nama_dosen);
        $rows = app(LecturerSnapshots::class)->rows(2025, 8);
        $this->assertSame('Old 2025', $rows['D1']->nama_dosen);
        $this->assertSame('Asisten Ahli', $rows['D1']->jja);
        $this->assertTrue(app(LecturerSnapshots::class)->rows(2024)->isEmpty());
        $this->assertTrue(app(LecturerSnapshots::class)->rows(2025, 7)->isEmpty());
    }

    public function test_reimport_replaces_only_selected_month_and_uses_no_future_values(): void
    {
        $service = app(LecturerWorkbook::class);
        $service->import($this->makeWorkbook(['Malang' => [['Kode Dosen', 'Nama Dosen', 'JJA'], ['D1', 'September', 'Lektor Kepala']]]), 2025, 9);
        $service->import($this->makeWorkbook(['Malang' => [['Kode Dosen', 'Nama Dosen'], ['D1', 'August'], ['D2', 'Old only']]]), 2025, 8);
        $service->import($this->makeWorkbook(['Malang' => [['Kode Dosen', 'Nama Dosen'], ['D1', 'August revised']]]), 2025, 8);
        $resolver = app(LecturerSnapshots::class);
        $this->assertCount(1, $resolver->rows(2025, 8));
        $this->assertNull($resolver->rows(2025, 8)['D1']->jja);
        $this->assertSame('September', $resolver->rows(2025)['D1']->nama_dosen);
        $this->assertSame('Current 2026', $resolver->rows(2026)['D1']->nama_dosen);
    }

    public function test_required_only_mapping_ignores_absent_null_and_empty_optional_selections(): void
    {
        $service = app(LecturerWorkbook::class);
        $service->import($this->makeWorkbook(['Malang' => [
            ['Kode Dosen', 'Nama Dosen', 'JJA'], ['D1', 'Prior snapshot', 'Profesor'],
        ]]), 2025, 1);
        $path = $this->makeWorkbook(['Malang' => [
            ['ID', 'Person', 'Rank'], ['D1', 'Required only', 'Must not be imported'],
        ]]);
        $optional = array_diff(array_keys(LecturerWorkbook::HEADERS), ['kode_dosen', 'nama_dosen']);
        foreach ([[], array_fill_keys($optional, null), array_fill_keys($optional, '')] as $unselected) {
            $mapping = ['kode_dosen' => 0, 'nama_dosen' => 1] + $unselected;
            $parsed = $service->read($path, $mapping, 1);
            $this->assertSame([['kode_dosen' => 'D1', 'nama_dosen' => 'Required only']], $parsed['records']);
            $summary = $service->import($path, 2025, 2, $mapping, 1);
            $this->assertSame(2, $summary['mapped_columns']);
            $this->assertContains('JJA', $summary['preserved_columns']);
            $row = app(LecturerSnapshots::class)->rows(2025, 2)['D1'];
            $this->assertSame('Required only', $row->nama_dosen);
            $this->assertSame('Profesor', $row->jja);
            $this->assertNull($row->email_1);
        }
        $this->assertSame('Current 2026', DataDosen::find('D1')->nama_dosen);
    }

    public function test_mapped_optional_columns_honor_values_and_allow_blank_cells(): void
    {
        $service = app(LecturerWorkbook::class);
        $service->import($this->makeWorkbook(['Malang' => [
            ['Kode Dosen', 'Nama Dosen', 'JJA'], ['D1', 'Prior snapshot', 'Profesor'],
        ]]), 2025, 1);
        $path = $this->makeWorkbook(['Malang' => [
            ['ID', 'Person', 'Rank', 'Email'], ['D1', 'Mapped optional', '', 'new@example.test'],
        ]]);
        $mapping = ['kode_dosen' => 0, 'nama_dosen' => 1, 'jja' => 2, 'email_1' => 3];
        $summary = $service->import($path, 2025, 2, $mapping, 1);
        $this->assertSame(4, $summary['mapped_columns']);
        $this->assertNotContains('JJA', $summary['preserved_columns']);
        $row = app(LecturerSnapshots::class)->rows(2025, 2)['D1'];
        $this->assertSame('Mapped optional', $row->nama_dosen);
        $this->assertNull($row->jja);
        $this->assertSame('new@example.test', $row->email_1);
    }

    public function test_missing_null_and_empty_required_mappings_report_required_field_errors(): void
    {
        $path = $this->makeWorkbook(['Malang' => [['ID', 'Person'], ['D1', 'Test']]]);
        foreach (['kode_dosen', 'nama_dosen'] as $field) {
            foreach (['absent', null, ''] as $selection) {
                $mapping = ['kode_dosen' => 0, 'nama_dosen' => 1, 'jja' => null];
                if ($selection === 'absent') {
                    unset($mapping[$field]);
                } else {
                    $mapping[$field] = $selection;
                }
                try {
                    app(LecturerWorkbook::class)->import($path, 2025, 1, $mapping, 1);
                    $this->fail('Required mapping must not be ignored.');
                } catch (ValidationException $e) {
                    $this->assertSame(['Kolom '.LecturerWorkbook::HEADERS[$field].' wajib dipetakan.'], $e->errors()['column_mapping.'.$field]);
                }
            }
        }
        $this->assertSame(1, DB::table('lecturer_imports')->count());
    }

    public function test_invalid_mapping_reports_fields_and_does_not_write_snapshot(): void
    {
        $path = $this->makeWorkbook(['Malang' => [['ID', 'Full Name'], ['D1', 'Test']]]);
        try {
            app(LecturerWorkbook::class)->import($path, 2025, 1, ['kode_dosen' => 99], 1);
            $this->fail('Invalid mapping should fail.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('column_mapping.kode_dosen', $e->errors());
            $this->assertArrayHasKey('column_mapping.nama_dosen', $e->errors());
        }
        $this->assertSame(1, DB::table('lecturer_imports')->count());
    }

    public function test_default_admin_table_is_current_year_and_historical_filter_is_isolated(): void
    {
        app(LecturerWorkbook::class)->import($this->makeWorkbook(['Malang' => [['Kode Dosen', 'Nama Dosen'], ['D1', 'Previous year']]]), 2025, 1);
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $this->postJson(route('dosen.init_table'))->assertOk()->assertJsonPath('data.0.nama_dosen', 'Current 2026');
        $this->postJson(route('dosen.init_table'), ['year' => 2025])->assertOk()->assertJsonPath('data.0.nama_dosen', 'Previous year');
        $this->postJson(route('dosen.init_table'), ['year' => 2024])->assertOk()->assertJsonPath('recordsTotal', 0);
    }

    public function test_latest_current_year_snapshot_controls_roster_and_older_upload_cannot_overwrite_it(): void
    {
        $service = app(LecturerWorkbook::class);
        $service->import($this->makeWorkbook(['Malang' => [
            ['Kode Dosen', 'Nama Dosen', 'JJA'], ['D2', 'November current', 'Lektor Kepala'],
        ]]), 2026, 11);
        $service->import($this->makeWorkbook(['Malang' => [
            ['Kode Dosen', 'Nama Dosen'], ['D1', 'Earlier October'],
        ]]), 2026, 10);

        $this->assertSame(['D2'], app(LecturerSnapshots::class)->rows(2026)->keys()->all());
        $this->assertSame(['D2'], DataDosen::visibleOnWebsite()->pluck('kode_dosen')->all());
        $this->assertSame('November current', DataDosen::find('D2')->nama_dosen);
        $this->assertSame('Current 2026', DataDosen::find('D1')->nama_dosen);
        $this->assertSame('Earlier October', app(LecturerSnapshots::class)->rows(2026, 10)['D1']->nama_dosen);
        $this->assertSame(2, DataDosen::withTrashed()->count());
    }

    public function test_optional_unmapped_fields_are_preserved_from_prior_snapshot_and_live_matches_snapshot(): void
    {
        $service = app(LecturerWorkbook::class);
        $service->import($this->makeWorkbook(['Malang' => [
            ['Kode Dosen', 'Nama Dosen', 'JJA'], ['D1', 'November', 'Profesor'],
        ]]), 2026, 11);
        $service->import($this->makeWorkbook(['Malang' => [
            ['Kode Dosen', 'Nama Dosen'], ['D1', 'December'],
        ]]), 2026, 12);
        $this->assertSame('Profesor', app(LecturerSnapshots::class)->rows(2026, 12)['D1']->jja);
        $this->assertSame('Profesor', DataDosen::find('D1')->jja);
        $service->import($this->makeWorkbook(['Malang' => [
            ['Kode Dosen', 'Nama Dosen'], ['D1', 'First historical'],
        ]]), 2025, 1);
        $this->assertNull(app(LecturerSnapshots::class)->rows(2025, 1)['D1']->jja);
    }

    public function test_archiving_live_profile_does_not_erase_historical_reporting_evidence(): void
    {
        app(LecturerWorkbook::class)->import($this->makeWorkbook(['Malang' => [
            ['Kode Dosen', 'Nama Dosen'], ['D1', 'Historical evidence'],
        ]]), 2025, 6);
        DataDosen::find('D1')->delete();
        $this->assertSame('Historical evidence', app(LecturerSnapshots::class)->rows(2025, 6)['D1']->nama_dosen);
        $this->assertTrue(DataDosen::visibleOnWebsite()->get()->isEmpty());
    }

    public function test_preview_and_import_choose_same_sheet_and_keep_blank_column_positions(): void
    {
        $path = $this->makeWorkbook([
            'Data All' => [['Kode Dosen', 'Nama Dosen'], ['OTHER', 'Not Malang']],
            'Malang' => [['Report title'], ['Kode Dosen', '', 'Person'], ['D3', '', 'Mapped third column']],
        ]);
        $preview = app(LecturerWorkbook::class)->preview($path);
        $this->assertSame('Malang', $preview['sheet']);
        $this->assertSame(2, $preview['header_row']);
        $this->assertSame(0, $preview['suggested_mapping']['kode_dosen']);
        $this->assertSame('', $preview['headers'][1]);
        app(LecturerWorkbook::class)->import($path, 2025, 6, ['kode_dosen' => 0, 'nama_dosen' => 2], $preview['header_row']);
        $this->assertSame('Mapped third column', app(LecturerSnapshots::class)->rows(2025, 6)['D3']->nama_dosen);
    }

    public function test_new_calendar_year_does_not_reuse_previous_year_roster_as_current(): void
    {
        $this->travelTo(now()->setDate(2027, 1, 10));
        $this->assertTrue(app(LecturerSnapshots::class)->rows(2027)->isEmpty());
        $this->assertTrue(DataDosen::visibleOnWebsite()->get()->isEmpty());
        $this->assertSame('Current 2026', app(LecturerSnapshots::class)->rows(2026)['D1']->nama_dosen);
        $this->assertSame([2027, 2026], app(LecturerSnapshots::class)->years()->all());
    }

    public function test_snapshot_import_preserves_global_hidden_and_archived_profiles(): void
    {
        DataDosen::find('D1')->forceFill(['is_hidden' => true])->save();
        DataDosen::create(['kode_dosen' => 'D2', 'nama_dosen' => 'Archived'])->delete();
        app(LecturerWorkbook::class)->import($this->makeWorkbook(['Malang' => [
            ['Kode Dosen', 'Nama Dosen'], ['D1', 'Updated hidden'], ['D2', 'Updated archived'], ['D3', 'Visible new'],
        ]]), 2026, 11);
        $this->assertTrue(DataDosen::find('D1')->is_hidden);
        $this->assertTrue(DataDosen::withTrashed()->find('D2')->trashed());
        $this->assertSame('Updated archived', DataDosen::withTrashed()->find('D2')->nama_dosen);
        $this->assertSame(['D3'], DataDosen::visibleOnWebsite()->pluck('kode_dosen')->all());
        $this->assertCount(3, app(LecturerSnapshots::class)->rows(2026, 11));
    }

    public function test_public_home_and_admin_counts_use_only_current_year_roster(): void
    {
        app(LecturerWorkbook::class)->import($this->makeWorkbook(['Malang' => [
            ['Kode Dosen', 'Nama Dosen', 'Nama Gugus Binaan', 'JJA'], ['D2', 'Current roster member', 'CS', 'Profesor'],
        ]]), 2026, 11);
        app(LecturerWorkbook::class)->import($this->makeWorkbook(['Malang' => [
            ['Kode Dosen', 'Nama Dosen'], ['D3', 'Historical only'],
        ]]), 2025, 8);
        Schema::create('umkm_partnership', function (Blueprint $table) {
            $table->integer('tahun_bergabung')->nullable();
            $table->string('cluster')->nullable();
        });
        $response = $this->get(route('home'))->assertOk();
        $this->assertSame(['D2'], $response->viewData('dosen')->pluck('kode_dosen')->all());
        $this->assertSame([1], $response->viewData('prodiCounts'));
        $this->assertSame(['PROFESOR'], $response->viewData('jjaLabels'));
        $response->assertDontSee('Historical only')->assertDontSee('Current 2026');
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $this->postJson(route('dosen.init_table'))->assertOk()->assertJsonPath('recordsTotal', 1)
            ->assertJsonPath('data.0.kode_dosen', 'D2');
    }

    public function test_snapshot_validation_failure_keeps_existing_period_and_current_master_unchanged(): void
    {
        $service = app(LecturerWorkbook::class);
        $service->import($this->makeWorkbook(['Malang' => [
            ['Kode Dosen', 'Nama Dosen'], ['D1', 'Valid historical'],
        ]]), 2025, 8);
        try {
            $service->import($this->makeWorkbook(['Malang' => [
                ['Code', 'Person'], ['D1', 'Changed'], ['D1', 'Conflicting'],
            ]]), 2025, 8, ['kode_dosen' => 0, 'nama_dosen' => 1], 1);
            $this->fail('Conflicting codes should fail before writing.');
        } catch (ValidationException) {
            $this->assertSame('Valid historical', app(LecturerSnapshots::class)->rows(2025, 8)['D1']->nama_dosen);
            $this->assertSame('Current 2026', DataDosen::find('D1')->nama_dosen);
            $this->assertSame(2, DB::table('lecturer_imports')->count());
        }
    }

    public function test_admin_page_renders_mapping_controls_and_historical_program_options(): void
    {
        app(LecturerWorkbook::class)->import($this->makeWorkbook(['Malang' => [
            ['Kode Dosen', 'Nama Dosen', 'Nama Gugus Binaan'], ['D1', 'Previous year', 'Historical program'],
        ]]), 2025, 1);
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $response = $this->getJson(route('dosen.index'))->assertOk();
        $html = base64_decode($response->json('page'));
        $this->assertStringContainsString('Historical program', $html);
        $this->assertStringContainsString('id="dosenFilterButton"', $html);
        $this->assertMatchesRegularExpression('/id="modalDosenFilter"[\s\S]*id="dosenFilterYearInput"[\s\S]*id="dosenFilterMonthInput"[\s\S]*id="dosenFilterProdiInput"/', $html);
        $this->assertStringContainsString('onsubmit="applyDosenFilters(event)"', $html);
        $this->assertStringContainsString('id="dosenMappingConfirm"', $html);
        $this->assertStringContainsString('id="dosenImportYear"', $html);
        $this->assertStringContainsString('id="dosenImportMonth"', $html);
        $this->assertStringContainsString('value="2026"', $html);
    }

    public function test_guest_cannot_preview_or_import_and_malformed_mapping_is_rejected(): void
    {
        $this->postJson(route('dosen.preview'))->assertUnauthorized();
        $this->postJson(route('dosen.read'))->assertUnauthorized();
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $path = $this->makeWorkbook(['Malang' => [['ID', 'Name'], ['D1', 'New']]]);
        foreach ([
            'null', '{broken', '[]', '{"kode_dosen":"0","nama_dosen":1}',
            '{"kode_dosen":0,"nama_dosen":0}', '{"kode_dosen":0,"nama_dosen":1,"unknown":0}',
            '{"kode_dosen":0,"nama_dosen":1,"unknown":null}', '{"kode_dosen":0,"nama_dosen":1,"unknown":""}',
            '{"kode_dosen":0,"nama_dosen":1,"jja":"0"}', '{"kode_dosen":0,"nama_dosen":1,"jja":-1}',
            '{"kode_dosen":0,"nama_dosen":1,"jja":99}', '{"kode_dosen":0,"nama_dosen":1,"jja":true}',
            '{"kode_dosen":0,"nama_dosen":1,"jja":1.5}', '{"kode_dosen":0,"nama_dosen":1,"jja":[]}',
            '{"kode_dosen":0,"nama_dosen":1,"jja":0}',
        ] as $mapping) {
            $file = new UploadedFile($path, 'dosen.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
            $this->postJson(route('dosen.read'), ['dosen' => $file, 'year' => 2025, 'month' => 1,
                'header_row' => 1, 'column_mapping' => $mapping])->assertUnprocessable();
        }
        $this->assertSame(1, DB::table('lecturer_imports')->count());
        $this->assertSame('Current 2026', DataDosen::find('D1')->nama_dosen);
    }

    public function test_upload_endpoint_accepts_unselected_optional_mappings_and_mapped_blank_cells(): void
    {
        $path = $this->makeWorkbook(['Malang' => [
            ['ID', 'Full Name', 'Rank', 'Email'], ['D1', 'Optional endpoint', '', 'mapped@example.test'],
        ]]);
        $file = new UploadedFile($path, 'lecturers.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $this->postJson(route('dosen.read'), ['dosen' => $file, 'year' => 2025, 'month' => 3, 'header_row' => 1,
            'column_mapping' => json_encode(['kode_dosen' => 0, 'nama_dosen' => 1, 'jja' => 2, 'email_1' => 3, 'note' => null, 'campus' => ''])])
            ->assertOk()->assertJsonPath('summary.mapped_columns', 4);
        $row = app(LecturerSnapshots::class)->rows(2025, 3)['D1'];
        $this->assertSame('Optional endpoint', $row->nama_dosen);
        $this->assertNull($row->jja);
        $this->assertSame('mapped@example.test', $row->email_1);
    }

    public function test_upload_endpoint_passes_mapping_and_period_to_reader(): void
    {
        $path = $this->makeWorkbook(['Malang' => [['ID', 'Full Name'], ['D1', 'Mapped endpoint']]]);
        $file = new UploadedFile($path, 'lecturers.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $this->postJson(route('dosen.preview'), ['dosen' => $file])->assertOk()->assertJsonPath('headers.0', 'ID');
        $this->postJson(route('dosen.read'), ['dosen' => $file, 'year' => 2025, 'month' => 3, 'header_row' => 1,
            'column_mapping' => json_encode(['kode_dosen' => 0, 'nama_dosen' => 1])])
            ->assertOk()->assertJsonPath('summary.year', 2025)->assertJsonPath('summary.updated_current_master', false);
        $this->assertSame('Mapped endpoint', app(LecturerSnapshots::class)->rows(2025, 3)['D1']->nama_dosen);
    }
}
