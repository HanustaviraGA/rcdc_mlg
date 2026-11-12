<?php

namespace Tests\Feature;

use App\Models\DataDosen;
use App\Models\User;
use App\Services\Lecturers\LecturerEducationStatuses;
use App\Services\Lecturers\LecturerEducationWorkbook;
use App\Services\Lecturers\LecturerWorkbook;
use App\Services\LrcDashboard\LrcDashboard;
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
        (require database_path('migrations/2026_11_03_000000_create_lecturer_education_statuses.php'))->up();
        (require database_path('migrations/2026_11_12_000000_add_s3_planning_to_lecturer_education.php'))->up();
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

    private function planningWorkbook(array $entries): string
    {
        return $this->makeWorkbook([LecturerEducationWorkbook::SHEET => [
            ['Kode Dosen', 'Pendidikan', ' Proyeksi LULUS S3 ', 'Roadmap S3  ', 'Pembiayaan  ', 'Universitas Tujuan  '],
            ...$entries,
        ]]);
    }

    private function importPlanning(string $path, int $month = 10): array
    {
        $service = app(LecturerEducationWorkbook::class);
        $preview = $service->preview($path);

        return $service->import($path, 2026, $month, $preview['suggested_mapping'], $preview['header_row']);
    }

    public function test_s3_planning_is_detected_saved_and_available_on_the_dashboard(): void
    {
        $path = $this->planningWorkbook([
            ['D1', 'S2', '2032', '2028', 'Beasiswa BINUS', 'UGM'],
            ['D2', 'On Going S3', '2027', '-', '-', '-'],
            ['MISSING', 'S2', '2030', '2026', 'Mandiri', 'UM'],
        ]);
        $preview = app(LecturerEducationWorkbook::class)->preview($path);
        $this->assertSame(['kode_dosen' => 0, 'status_pendidikan' => 1, 'proyeksi_lulus_s3' => 2,
            'roadmap_s3' => 3, 'pembiayaan' => 4, 'universitas_tujuan' => 5], $preview['suggested_mapping']);
        $this->assertSame(LecturerEducationWorkbook::OPTIONAL_FIELDS, $preview['mapping_config']['optional']);
        $summary = $this->importPlanning($path);
        $this->assertSame([2, ['MISSING']], [$summary['updated'], $summary['unmatched_codes']]);
        foreach (['database_dosen_new', 'lecturer_snapshot_rows', 'lecturer_education_statuses'] as $table) {
            $this->assertDatabaseHas($table, ['kode_dosen' => 'D1', 'status_pendidikan' => 'S2',
                'roadmap_s3' => 2028, 'proyeksi_lulus_s3' => 2032, 'pembiayaan' => 'Beasiswa BINUS', 'universitas_tujuan' => 'UGM']);
            $this->assertDatabaseHas($table, ['kode_dosen' => 'D2', 'roadmap_s3' => null,
                'proyeksi_lulus_s3' => 2027, 'pembiayaan' => null, 'universitas_tujuan' => null]);
        }
        $education = app(LrcDashboard::class)->data()['chartDetails']['pendidikan'];
        $this->assertSame([2028, 2032], [$education[0][0]['roadmapS3'], $education[0][0]['proyeksiLulusS3']]);
        $this->assertSame(2027, $education[2][0]['proyeksiLulusS3']);
        $this->assertArrayNotHasKey('roadmapS3', $education[2][0]);
        $this->assertSame(0, $this->importPlanning($path)['updated']);
    }

    public function test_planning_survives_data_center_replacement_and_carries_into_a_new_period(): void
    {
        $this->importPlanning($this->planningWorkbook([['D1', 'S2', '2032', '2028', 'Beasiswa BINUS', 'UGM']]));
        $dataCenter = $this->makeWorkbook(['Malang' => [['Kode Dosen', 'Nama Dosen', 'Pendidikan'], ['D1', 'Updated name', 'S2']]]);
        app(LecturerWorkbook::class)->import($dataCenter, 2026, 10);
        $this->assertDatabaseHas('lecturer_snapshot_rows', ['kode_dosen' => 'D1', 'roadmap_s3' => 2028, 'universitas_tujuan' => 'UGM']);
        $this->assertDatabaseHas('database_dosen_new', ['kode_dosen' => 'D1', 'roadmap_s3' => 2028, 'universitas_tujuan' => 'UGM']);
        app(LecturerWorkbook::class)->import($dataCenter, 2026, 11);
        $education = app(LrcDashboard::class)->data()['chartDetails']['pendidikan'][0][0];
        $this->assertSame([2028, 2032], [$education['roadmapS3'], $education['proyeksiLulusS3']]);
        $this->import($this->workbook([['D1', 'On Going S3']]), 2026, 11);
        $details = app(LecturerEducationStatuses::class)->detailsAsOf(2026, 11)->get('D1');
        $this->assertSame([2028, 2032, 'UGM'], [$details['roadmap_s3'], $details['proyeksi_lulus_s3'], $details['universitas_tujuan']]);
        $this->importPlanning($this->planningWorkbook([['D1', 'On Going S3', '2031', '-', '-', '']]), 11);
        $details = app(LecturerEducationStatuses::class)->detailsAsOf(2026, 11)->get('D1');
        $this->assertSame([null, 2031, null, null], [$details['roadmap_s3'], $details['proyeksi_lulus_s3'], $details['pembiayaan'], $details['universitas_tujuan']]);
        $this->assertSame(2028, app(LecturerEducationStatuses::class)->detailsAsOf(2026, 10)->get('D1')['roadmap_s3']);
    }

    public function test_invalid_planning_and_conflicting_duplicate_plans_do_not_replace_saved_data(): void
    {
        $valid = ['D1', 'S2', '2032', '2028', 'Mandiri', 'UGM'];
        $this->importPlanning($this->planningWorkbook([$valid]));
        $before = DB::table('lecturer_education_statuses')->get();
        foreach ([
            [$valid, ['D1', 'S2', '2033', '2028', 'Mandiri', 'UGM']],
            [['D1', 'S2', 'bukan tahun', '2028', 'Mandiri', 'UGM']],
            [['D1', 'S2', '2032', '20.28', 'Mandiri', 'UGM']],
        ] as $entries) {
            try {
                $this->importPlanning($this->planningWorkbook($entries));
                $this->fail('Invalid planning must be rejected.');
            } catch (ValidationException) {
                $this->assertEquals($before, DB::table('lecturer_education_statuses')->get());
                $this->assertSame(2032, (int) DataDosen::find('D1')->proyeksi_lulus_s3);
            }
        }
    }

    public function test_planning_migration_can_be_reversed_without_changing_status_or_roster(): void
    {
        $this->importPlanning($this->planningWorkbook([['D1', 'S2', '2032', '2028', 'Mandiri', 'UGM']]));
        (require database_path('migrations/2026_11_12_000000_add_s3_planning_to_lecturer_education.php'))->down();
        foreach (['database_dosen_new', 'lecturer_snapshot_rows', 'lecturer_education_statuses'] as $table) {
            $this->assertFalse(Schema::hasColumn($table, 'roadmap_s3'));
            $this->assertDatabaseHas($table, ['kode_dosen' => 'D1', 'status_pendidikan' => 'S2']);
        }
        $this->assertSame(2, DataDosen::count());
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

    public function test_preview_and_import_use_the_sheet_chosen_by_the_user(): void
    {
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $path = $this->makeWorkbook(['Cover' => [['Laporan pendidikan']], 'Pendidikan 2026' => [
            ['Title line'], ['Kode Dosen', 'Pendidikan'], ['D1', 'On Going S3'],
        ]]);
        $request = fn (string $route, array $data) => $this->postJson(route($route), ['dosen' => $this->upload($path)] + $data);
        // Without Detail Jenjang Pendidikan the preview shows the first sheet instead of failing.
        $request('dosen.education.preview', [])->assertOk()
            ->assertJsonPath('sheet', 'Cover')->assertJsonPath('sheets', ['Cover', 'Pendidikan 2026']);
        $request('dosen.education.preview', ['sheet' => 'Pendidikan 2026'])->assertOk()
            ->assertJsonPath('header_row', 2)->assertJsonPath('suggested_mapping', ['kode_dosen' => 0, 'status_pendidikan' => 1]);
        $request('dosen.education.preview', ['sheet' => 'Missing'])->assertUnprocessable();

        $import = ['year' => 2026, 'month' => 10, 'header_row' => 2, 'column_mapping' => json_encode(['kode_dosen' => 0, 'status_pendidikan' => 1])];
        $request('dosen.education.import', $import)->assertUnprocessable()->assertJsonValidationErrors('dosen');
        $this->assertNull(DataDosen::find('D1')->status_pendidikan);
        $request('dosen.education.import', $import + ['sheet' => 'Pendidikan 2026'])->assertOk()
            ->assertJsonPath('summary.sheet', 'Pendidikan 2026')->assertJsonPath('summary.updated', 1);
        $this->assertSame('On Going S3', DataDosen::find('D1')->status_pendidikan);
    }

    public function test_one_source_column_can_feed_several_target_fields(): void
    {
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $path = $this->makeWorkbook([LecturerEducationWorkbook::SHEET => [['Kode Dosen'], ['D1']]]);
        $this->postJson(route('dosen.education.import'), [
            'dosen' => $this->upload($path), 'year' => 2026, 'month' => 10, 'header_row' => 1,
            'column_mapping' => json_encode(['kode_dosen' => 0, 'status_pendidikan' => 0]),
        ])->assertOk()->assertJsonPath('summary.updated', 1);
        $this->assertSame('D1', DataDosen::find('D1')->status_pendidikan);
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

    public function test_data_center_reupload_keeps_separately_imported_status(): void
    {
        $this->import($this->workbook());
        $path = $this->makeWorkbook(['Malang' => [['Kode Dosen', 'Nama Dosen'], ['D1', 'Updated name']]]);
        app(LecturerWorkbook::class)->import($path, 2026, 10);
        $this->assertSame('On Going S3', DataDosen::find('D1')->status_pendidikan);
        $this->assertSame('On Going S3', DB::table('lecturer_snapshot_rows')->where('kode_dosen', 'D1')->value('status_pendidikan'));
    }

    public function test_a_new_data_center_period_keeps_every_lecturers_uploaded_status(): void
    {
        Schema::create('identitas_dosen', function (Blueprint $table) {
            $table->string('id_identitas')->primary();
            $table->string('kode_dosen');
        });
        Schema::create('database_attribute_dosen', function (Blueprint $table) {
            $table->string('id_attribute')->primary();
            $table->string('kode_dosen');
            $table->text('attribute_dosen');
            $table->text('attribute_icon');
        });
        $this->import($this->workbook([['D1', 'On Going S3'], ['D2', 'S3']]));
        $this->travelTo(now()->setDate(2026, 11, 3));
        // The Data Center replaces its period and rows as before, without the status.
        app(LecturerWorkbook::class)->import($this->makeWorkbook(['Malang' => [
            ['Kode Dosen', 'Nama Dosen', 'Pendidikan'], ['D1', 'First lecturer', 'S2'], ['D2', 'Second lecturer', 'S3'],
        ]]), 2026, 11);
        $this->assertNull(DataDosen::find('D1')->status_pendidikan);

        $education = app(LrcDashboard::class)->data()['chartDetails']['pendidikan'];
        $this->assertSame(['D2'], array_column($education[1], 'code'));
        $this->assertSame(['D1'], array_column($education[2], 'code'), 'On Going S3 is still shown after the new period');
        $this->actingAs((new User)->forceFill(['id' => 1]));
        foreach ([[], ['year' => 2026, 'month' => 11], ['year' => 2026, 'month' => 10]] as $period) {
            $this->postJson(route('dosen.detail'), ['kode_dosen' => 'D1'] + $period)
                ->assertOk()->assertJsonPath('dosen.status_pendidikan', 'On Going S3');
        }
    }

    public function test_a_status_applies_until_a_later_period_uploads_another(): void
    {
        $statuses = app(LecturerEducationStatuses::class);
        $dataCenter = $this->makeWorkbook(['Malang' => [['Kode Dosen', 'Nama Dosen'], ['D1', 'First lecturer'], ['D2', 'Second lecturer']]]);
        app(LecturerWorkbook::class)->import($dataCenter, 2026, 11);
        $this->import($this->workbook([['D1', 'On Going S3']]));
        $this->assertSame(['D1' => 'On Going S3'], $statuses->asOf(2026, 11)->all());
        $this->assertSame([], $statuses->asOf(2026, 9)->all());

        // A later upload compares with the carried status, then replaces it from its own period on.
        $summary = $this->import($this->workbook([['D1', 'On Going S3'], ['D2', 'S3']]), 2026, 11);
        $this->assertSame([1, 1], [$summary['updated'], $summary['unchanged']]);
        $this->import($this->workbook([['D1', 'S3']]), 2026, 11);
        $this->import($this->workbook([['D1', 'S2']]), 2026, 10);
        $this->assertSame('S2', $statuses->asOf(2026, 10)->get('D1'));
        $this->assertSame(['D1' => 'S3', 'D2' => 'S3'], $statuses->asOf(2026, 11)->all());
        $this->assertSame(['D1' => 'S3', 'D2' => 'S3'], $statuses->asOf(2027, 1)->all(), 'carried into later years');

        // A blank upload clears the status instead of falling back to an older period.
        $this->import($this->workbook([['D1', '']]), 2026, 11);
        $lecturers = DataDosen::query()->orderBy('kode_dosen')->get();
        $statuses->apply($lecturers, 2026, 11);
        $this->assertSame([null, 'S3'], $lecturers->pluck('status_pendidikan')->all());
        $this->assertSame(3, DB::table('lecturer_education_statuses')->count());
    }

    public function test_migration_keeps_statuses_already_uploaded_to_data_center_rows(): void
    {
        (require database_path('migrations/2026_11_03_000000_create_lecturer_education_statuses.php'))->down();
        app(LecturerWorkbook::class)->import($this->makeWorkbook(['Malang' => [['Kode Dosen', 'Nama Dosen'], ['d1', 'First lecturer']]]), 2025, 12);
        $batches = DB::table('lecturer_imports')->pluck('id', 'year');
        DB::table('lecturer_snapshot_rows')->where('lecturer_import_id', $batches[2025])->update(['status_pendidikan' => 'S3']);
        DB::table('lecturer_snapshot_rows')->where('lecturer_import_id', $batches[2026])->where('kode_dosen', 'D1')
            ->update(['status_pendidikan' => 'On Going S3']);

        (require database_path('migrations/2026_11_03_000000_create_lecturer_education_statuses.php'))->up();
        $this->assertEquals([['D1', 2025, 12, 'S3'], ['D1', 2026, 10, 'On Going S3']], DB::table('lecturer_education_statuses')
            ->orderBy('year')->get()->map(fn ($row) => [$row->kode_dosen, $row->year, $row->month, $row->status_pendidikan])->all());
        $this->assertSame([], app(LecturerEducationStatuses::class)->asOf(2025, 11)->all());
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
        $this->assertSame(0, DB::table('lecturer_education_statuses')->count());
    }

    public function test_routes_require_authentication_and_reject_invalid_mappings(): void
    {
        foreach (['dosen.education.preview', 'dosen.education.import'] as $route) {
            $this->postJson(route($route))->assertUnauthorized();
        }
        $this->actingAs((new User)->forceFill(['id' => 1]));
        foreach (['null', '{broken', '[]',
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
        (require database_path('migrations/2026_11_03_000000_create_lecturer_education_statuses.php'))->down();
        $this->assertFalse(Schema::hasTable('lecturer_education_statuses'));
        try {
            $this->import($this->workbook());
            $this->fail('Import needs the education status table.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('Migrasi status pendidikan', $exception->getMessage());
        }
        (require database_path('migrations/2026_10_22_000100_add_education_status_to_lecturers.php'))->down();
        $this->assertFalse(Schema::hasColumn('database_dosen_new', 'status_pendidikan'));
        $this->assertFalse(Schema::hasColumn('lecturer_snapshot_rows', 'status_pendidikan'));
        $this->assertSame(2, DataDosen::count());
    }
}
