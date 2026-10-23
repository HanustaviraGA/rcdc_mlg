<?php

namespace Tests\Feature;

use App\Models\DataDosen;
use App\Models\User;
use App\Services\Lecturers\LecturerEducationWorkbook;
use App\Services\Lecturers\LecturerWorkbook;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\Support\CreatesWorkbook;
use Tests\TestCase;

class LecturerEducationImportTest extends TestCase
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
        DataDosen::create(['kode_dosen' => 'D1', 'nama_dosen' => 'First lecturer', 'pendidikan' => 'S2', 'jja' => 'Lektor']);
        DataDosen::create(['kode_dosen' => 'D2', 'nama_dosen' => 'Second lecturer', 'pendidikan' => 'S3']);
        (require database_path('migrations/2026_10_22_000000_create_lecturer_snapshots.php'))->up();
        (require database_path('migrations/2026_10_22_000100_add_education_status_to_lecturers.php'))->up();
    }

    protected function tearDown(): void
    {
        $this->removeWorkbooks();
        $this->travelBack();
        parent::tearDown();
    }

    private function workbook(array $entries = [['D1', 'On Going S3'], ['D2', 'S3']]): string
    {
        $rows = [['Notes: Diploma IV (D4) is equivalent to Undergraduate (S1)'],
            ['Kode Dosen', 'Fakultas Internal', 'Nama Program', 'Nama Dosen', 'Tipe', 'Nama Tipe Dosen Detail', 'Status', 'Pendidikan']];
        foreach ($entries as [$code, $status]) {
            $rows[] = [$code, 'SOCS', 'Program in source file', 'Name in source file', 'FM', 'Faculty Member', 'AKTIF', $status];
        }

        return $this->makeWorkbook(['Malang' => [['Wrong sheet']], LecturerEducationWorkbook::SHEET => $rows]);
    }

    private function upload(string $path): UploadedFile
    {
        return new UploadedFile($path, 'education.xlsx', null, null, true);
    }

    private function import(string $path, int $year = 2026, int $month = 10): array
    {
        return app(LecturerEducationWorkbook::class)->import($path, $year, $month, ['kode_dosen' => 0, 'status_pendidikan' => 7], 2);
    }

    public function test_preview_detects_second_row_header_and_column_h_in_the_selected_sheet(): void
    {
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $this->postJson(route('dosen.education.preview'), ['dosen' => $this->upload($this->workbook())])
            ->assertOk()->assertJsonPath('sheet', LecturerEducationWorkbook::SHEET)->assertJsonPath('header_row', 2)
            ->assertJsonPath('suggested_mapping.kode_dosen', 0)->assertJsonPath('suggested_mapping.status_pendidikan', 7)
            ->assertJsonPath('preview_data.0.7', 'On Going S3')
            ->assertJsonPath('mapping_config.required.status_pendidikan', 'Status Pendidikan');
        $this->assertNull(DataDosen::find('D1')->status_pendidikan);
    }

    public function test_import_updates_only_education_status_for_matched_codes_in_selected_period(): void
    {
        DataDosen::find('D1')->forceFill(['is_hidden' => true])->save();
        DataDosen::find('D2')->delete();
        $masterBefore = DB::table('database_dosen_new')->orderBy('kode_dosen')->get()->keyBy('kode_dosen');
        $rowsBefore = DB::table('lecturer_snapshot_rows')->orderBy('id')->get()->keyBy('kode_dosen');
        $batchBefore = DB::table('lecturer_imports')->get();
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $this->postJson(route('dosen.education.import'), [
            'dosen' => $this->upload($this->workbook([[' d1 ', 'On Going S3'], ['D999', 'S3']])),
            'year' => 2026, 'month' => 10, 'header_row' => 2,
            'column_mapping' => json_encode(['kode_dosen' => 0, 'status_pendidikan' => 7]),
        ])->assertOk()->assertJsonPath('summary.updated', 1)->assertJsonPath('summary.unmatched_codes', ['D999']);

        $this->assertSame('On Going S3', DataDosen::find('D1')->status_pendidikan);
        $this->assertSame('S2', DataDosen::find('D1')->pendidikan);
        $this->assertTrue(DataDosen::find('D1')->is_hidden);
        $this->assertTrue(DataDosen::withTrashed()->find('D2')->trashed());
        $this->assertSame(2, DataDosen::withTrashed()->count());
        foreach (DB::table('database_dosen_new')->get() as $row) {
            $this->assertEquals((array) $masterBefore[$row->kode_dosen], array_replace((array) $row, ['status_pendidikan' => null]));
        }
        foreach (DB::table('lecturer_snapshot_rows')->get() as $row) {
            $this->assertEquals((array) $rowsBefore[$row->kode_dosen], array_replace((array) $row, ['status_pendidikan' => null]));
        }
        $this->assertEquals($batchBefore, DB::table('lecturer_imports')->get());
    }

    public function test_mapping_is_honored_and_education_status_can_be_replaced_or_cleared(): void
    {
        $path = $this->makeWorkbook([LecturerEducationWorkbook::SHEET => [
            ['Current status', 'Code', 'Pendidikan'], ['On Going S3', 'D1', 'Wrong value'],
        ]]);
        $service = app(LecturerEducationWorkbook::class);
        $service->import($path, 2026, 10, ['status_pendidikan' => 0, 'kode_dosen' => 1], 1);
        $this->assertSame('On Going S3', DataDosen::find('D1')->status_pendidikan);
        $summary = $this->import($this->workbook([['D1', 'S3'], ['D1', 'S3']]));
        $this->assertSame(1, $summary['duplicates']);
        $this->assertSame('S3', DataDosen::find('D1')->status_pendidikan);
        $this->import($this->workbook([['D1', '']]));
        $this->assertNull(DataDosen::find('D1')->status_pendidikan);
        $this->assertSame('S2', DataDosen::find('D1')->pendidikan);
        $summary = $this->import($this->workbook([['D1', '']]));
        $this->assertSame(0, $summary['updated']);
        $this->assertSame(1, $summary['unchanged']);
    }

    public function test_historical_update_keeps_current_master_and_other_periods_unchanged(): void
    {
        foreach ([[2025, 10], [2026, 9]] as [$year, $month]) {
            app(LecturerWorkbook::class)->import($this->makeWorkbook(['Malang' => [
                ['Kode Dosen', 'Nama Dosen', 'Pendidikan'], ['D1', 'Historical lecturer', 'S1'],
            ]]), $year, $month);
            $summary = $this->import($this->workbook([['D1', 'On Going S2']]), $year, $month);
            $this->assertFalse($summary['updated_current_master']);
        }
        $this->assertNull(DataDosen::find('D1')->status_pendidikan);
        $batch = DB::table('lecturer_imports')->where('year', 2026)->where('month', 10)->sole();
        $this->assertNull(DB::table('lecturer_snapshot_rows')->where('lecturer_import_id', $batch->id)->where('kode_dosen', 'D1')->value('status_pendidikan'));
    }

    public function test_data_center_reupload_keeps_separately_imported_status_and_new_period_starts_empty(): void
    {
        $this->import($this->workbook());
        $path = $this->makeWorkbook(['Malang' => [['Kode Dosen', 'Nama Dosen'], ['D1', 'Updated name']]]);
        app(LecturerWorkbook::class)->import($path, 2026, 10);
        $this->assertSame('On Going S3', DataDosen::find('D1')->status_pendidikan);
        $this->assertSame('On Going S3', DB::table('lecturer_snapshot_rows')->where('kode_dosen', 'D1')->value('status_pendidikan'));
        app(LecturerWorkbook::class)->import($path, 2026, 11);
        $this->assertNull(DataDosen::find('D1')->status_pendidikan);
    }

    public function test_invalid_workbooks_and_conflicting_duplicates_keep_all_data_intact(): void
    {
        $before = DB::table('lecturer_snapshot_rows')->get();
        foreach ([
            $this->makeWorkbook(['Malang' => [['Kode Dosen', 'Pendidikan'], ['D1', 'S3']]]),
            $this->workbook([['D1', 'S3'], ['D1', 'On Going S3']]),
            $this->workbook([['D1', 'S3'], ['', 'On Going S3']]),
            $this->workbook([['UNKNOWN', 'S3']]),
        ] as $path) {
            try {
                $this->import($path);
                $this->fail('Invalid workbook must be rejected.');
            } catch (ValidationException) {
                $this->assertEquals($before, DB::table('lecturer_snapshot_rows')->get());
                $this->assertNull(DataDosen::find('D1')->status_pendidikan);
            }
        }
    }

    public function test_missing_period_does_not_create_or_replace_a_roster(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Unggah Data Center');
        $this->import($this->workbook(), 2025, 1);
    }

    public function test_failed_master_write_rolls_back_snapshot_status_changes(): void
    {
        DB::unprepared("CREATE TRIGGER reject_education_update BEFORE UPDATE ON database_dosen_new WHEN NEW.kode_dosen = 'D2' BEGIN SELECT RAISE(ABORT, 'education update failure'); END");
        try {
            $this->import($this->workbook());
            $this->fail('Master update should fail.');
        } catch (\Illuminate\Database\QueryException $exception) {
            $this->assertStringContainsString('education update failure', $exception->getMessage());
        }
        $this->assertSame(0, DB::table('database_dosen_new')->whereNotNull('status_pendidikan')->count());
        $this->assertSame(0, DB::table('lecturer_snapshot_rows')->whereNotNull('status_pendidikan')->count());
    }

    public function test_routes_require_authentication_and_reject_invalid_mappings(): void
    {
        foreach (['dosen.education.preview', 'dosen.education.import'] as $route) {
            $this->postJson(route($route))->assertUnauthorized();
        }
        $this->actingAs((new User)->forceFill(['id' => 1]));
        foreach (['null', '{broken', '[]', '{"kode_dosen":0,"status_pendidikan":0}',
            '{"kode_dosen":0,"status_pendidikan":"7"}', '{"kode_dosen":0,"status_pendidikan":99}',
            '{"kode_dosen":0,"status_pendidikan":7,"pendidikan":1}'] as $mapping) {
            $this->postJson(route('dosen.education.import'), [
                'dosen' => $this->upload($this->workbook()), 'year' => 2026, 'month' => 10,
                'header_row' => 2, 'column_mapping' => $mapping,
            ])->assertUnprocessable();
        }
        $this->assertNull(DataDosen::find('D1')->status_pendidikan);
    }

    public function test_migration_can_be_rolled_back_without_removing_lecturer_data(): void
    {
        (require database_path('migrations/2026_10_22_000100_add_education_status_to_lecturers.php'))->down();
        $this->assertFalse(Schema::hasColumn('database_dosen_new', 'status_pendidikan'));
        $this->assertFalse(Schema::hasColumn('lecturer_snapshot_rows', 'status_pendidikan'));
        $this->assertSame(2, DataDosen::count());
    }
}
