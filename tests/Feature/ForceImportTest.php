<?php

namespace Tests\Feature;

use App\Models\DataDosen;
use App\Models\User;
use App\Services\Imports\ImportPolicy;
use App\Services\Imports\PkmWorkbook;
use App\Services\Lecturers\LecturerEducationWorkbook;
use App\Services\Lecturers\LecturerWorkbook;
use App\Services\Publications\PublicationImporter;
use App\Services\Publications\StudentPublicationImporter;
use App\Services\Research\RectorateResearchImporter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\Support\CreatesWorkbook;
use Tests\TestCase;

class ForceImportTest extends TestCase
{
    use CreatesWorkbook;

    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('SQLite required.');
        }
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->travelTo(now()->setDate(2026, 10, 23));
        Schema::create('database_dosen_new', function (Blueprint $table) {
            $table->string('kode_dosen')->primary();
            foreach (array_diff(array_keys(LecturerWorkbook::HEADERS), ['kode_dosen']) as $field) {
                $table->text($field)->nullable();
            }
            $table->timestamps();
        });
        (require database_path('migrations/2026_09_25_000000_add_visibility_and_soft_deletes_to_database_dosen_new.php'))->up();
        DataDosen::create(['kode_dosen' => 'D1', 'nama_dosen' => 'Original', 'pendidikan' => 'S2']);
        (require database_path('migrations/2026_10_22_000000_create_lecturer_snapshots.php'))->up();
        (require database_path('migrations/2026_10_22_000100_add_education_status_to_lecturers.php'))->up();
        Schema::create('rectorate_dosen', function (Blueprint $table) {
            $table->string('id_rectorate')->primary();
            foreach (['request_code', 'author', 'fm_author', 'kode_dosen', 'jja', 'pendidikan', 'type', 's_f', 'dept',
                'kampus', 'first_author', 'sumber_paper', 'submitted', 'status', 'jenis', 'tipe_publikasi', 'title',
                'scopus_year', 'source_title', 'quartile_jurnal', 'publisher', 'year', 'month', 'period'] as $field) {
                $table->text($field)->nullable();
            }
            $table->double('bobot_asli')->nullable();
            $table->double('bobot')->nullable();
            $table->timestamps();
        });
        foreach (['2026_09_08_000000_add_publication_import_tracking.php', '2026_09_23_000000_create_publication_kpi_entries_table.php',
            '2026_09_24_000100_create_student_publication_imports.php', '2026_09_08_000200_add_rectorate_research_imports.php',
            '2026_09_24_000000_add_monthly_research_snapshots.php'] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        Schema::create('pkm_dosen', function (Blueprint $table) {
            $table->string('id_pkm')->primary();
            foreach (['periode', 'kode_dosen', 'judul_pkm', 'jenis_pkm', 'peserta', 'skema_pendanaan', 'nama_mahasiswa', 'link_evidence', 'year', 'period'] as $field) {
                $table->text($field)->nullable();
            }
            $table->timestamps();
        });
        Schema::create('ce_pkm_dosen', function (Blueprint $table) {
            $table->string('id_ce')->primary();
            foreach (['kode_dosen', 'jumlah_kegiatan', 'jumlah_laporan', 'skor_sementara', 'keterangan', 'year', 'period'] as $field) {
                $table->text($field)->nullable();
            }
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        $this->removeWorkbooks();
        $this->travelBack();
        parent::tearDown();
    }

    private function mappedWorkbook(array $values, array $otherRows = [], string $sheet = 'MALANG', array $extraSheets = []): array
    {
        $path = $this->makeWorkbook([$sheet => [array_keys($values), array_values($values), ...array_map('array_values', $otherRows)]] + $extraSheets);

        return [$path, array_flip(array_keys($values))];
    }

    private function fmValues(array $changes = []): array
    {
        return array_replace(['kode_dosen' => 'D1', 'kampus' => 'MALANG', 'submitted' => 'Scopus FM',
            'bobot_asli' => '1', 'title' => 'Paper', 'jenis' => 'Jurnal', 'tipe_publikasi' => 'Scopus', 'status' => 'Accepted'], $changes);
    }

    public function test_flags_are_independent_and_false_values_are_not_treated_as_true(): void
    {
        foreach (['rectorate_fm', 'rectorate_mhs', 'rectorate_hibah', 'dosen_data_center', 'dosen_status_pendidikan', 'pkm'] as $feature) {
            $this->assertFalse((new ImportPolicy($feature))->force);
        }
        config(['imports.force.rectorate_fm' => 'false', 'imports.force.rectorate_mhs' => true]);
        $this->assertFalse((new ImportPolicy('rectorate_fm'))->force);
        $this->assertTrue((new ImportPolicy('rectorate_mhs'))->force);
        $this->assertFalse((new ImportPolicy('unknown'))->force);
    }

    public function test_fm_force_allows_blank_cells_and_invalid_weight_but_skips_unidentified_rows(): void
    {
        config(['imports.force.rectorate_fm' => true]);
        [$path, $mapping] = $this->mappedWorkbook($this->fmValues(['title' => '', 'bobot_asli' => 'bad']), [
            $this->fmValues(['kode_dosen' => '']), $this->fmValues(['title' => 'Valid paper']),
        ]);
        $summary = app(PublicationImporter::class)->import($path, 'force.xlsx', 2026, 10, 4, columnMapping: $mapping);
        $this->assertTrue($summary['force_import']);
        $this->assertSame(1, $summary['skipped_rows']);
        $this->assertSame(2, $summary['selected']);
        $this->assertNotEmpty($summary['warnings']);
        $this->assertSame(1, DB::table('rectorate_dosen')->whereNull('title')->whereNull('bobot_asli')->count());
    }

    public function test_request_parameter_cannot_enable_force_and_bad_mapping_cannot_be_bypassed(): void
    {
        [$path, $mapping] = $this->mappedWorkbook($this->fmValues(['title' => '']));
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $payload = ['year' => 2026, 'month' => 10, 'fm_file' => new UploadedFile($path, 'force.xlsx', null, null, true),
            'column_mapping' => ['fm_file' => json_encode($mapping)], 'force_import' => true];
        $this->postJson(route('importrectorate.upload'), $payload)->assertUnprocessable();
        config(['imports.force.rectorate_fm' => true]);
        unset($mapping['title']);
        $payload['column_mapping']['fm_file'] = json_encode($mapping);
        $this->postJson(route('importrectorate.upload'), $payload)->assertUnprocessable();
        $this->assertSame(0, DB::table('publication_imports')->count());
    }

    public function test_fm_force_can_ignore_invalid_kpi_headers_without_affecting_mhs_policy(): void
    {
        config(['imports.force.rectorate_fm' => true]);
        [$path, $mapping] = $this->mappedWorkbook($this->fmValues(), extraSheets: ['KPI' => [['Wrong header'], ['Bad row']]]);
        $summary = app(PublicationImporter::class)->import($path, 'kpi.xlsx', 2026, 10, 4, columnMapping: $mapping);
        $this->assertFalse($summary['kpi']['available']);
        $this->assertNotEmpty($summary['warnings']);
        $this->assertFalse((new ImportPolicy('rectorate_mhs'))->force);
    }

    public function test_fm_force_clears_invalid_kpi_numbers_and_skips_duplicate_codes(): void
    {
        config(['imports.force.rectorate_fm' => true]);
        [$path, $mapping] = $this->mappedWorkbook($this->fmValues(), extraSheets: ['KPI' => [
            ['Kode Dosen', 'Nama Dosen', 'Jurusan Binaan', 'JJA', 'Faculty Type', 'Non Scopus RTTO', 'Scopus RTTO', 'Score KPI RTTO'],
            ['D1', 'Lecturer', 'Computer Science', 'Lektor', 'Functional', 'bad', '2', '9'],
            ['D1', 'Duplicate', 'Computer Science', 'Lektor', 'Functional', '1', '2', '3'],
            ['', 'Missing identity', 'Computer Science', 'Lektor', 'Functional', '1', '2', '3'],
        ]]);
        $summary = app(PublicationImporter::class)->import($path, 'kpi.xlsx', 2026, 10, 4, columnMapping: $mapping);
        $this->assertSame(2, $summary['kpi']['skipped_rows']);
        $this->assertSame(1, $summary['kpi']['lecturers']);
        $entry = DB::table('publication_kpi_entries')->sole();
        $this->assertNull($entry->rtto_non_scopus);
        $this->assertNull($entry->rtto_score);
        $this->assertSame(2.0, (float) $entry->rtto_scopus);
    }

    public function test_student_force_uses_derived_list_when_supplementary_sheets_are_invalid(): void
    {
        config(['imports.force.rectorate_mhs' => true]);
        [$path, $mapping] = $this->mappedWorkbook(['name' => '', 'campus' => 'MALANG', 'submitted' => 'Scopus Mahasiswa',
            'type' => 'Scopus', 'title' => '', 'program' => 'Computer Science'], extraSheets: [
                'LIST' => [['Wrong header']], 'TITLE' => [['Wrong header']],
            ]);
        $summary = app(StudentPublicationImporter::class)->import($path, 'students.xlsx', 2026, 10, columnMapping: $mapping);
        $this->assertTrue($summary['force_import']);
        $this->assertSame(1, $summary['selected']);
        $this->assertSame(1, $summary['list_count']);
        $this->assertNotEmpty($summary['warnings']);
        $this->assertSame(1, DB::table('student_publication_imports')->count());
    }

    public function test_hibah_force_preserves_nullable_values_but_skips_invalid_years(): void
    {
        config(['imports.force.rectorate_hibah' => true]);
        $values = ['tahun_anggaran' => '2026', 'kd_prop' => 'P1', 'kode_dosen_nim' => 'D1',
            'nama' => '', 'peran' => 'Ketua', 'judul' => '', 'lokasi_kampus' => 'BINUS @Malang'];
        [$path, $mapping] = $this->mappedWorkbook($values, [array_replace($values, ['tahun_anggaran' => 'bad'])], 'Detail');
        $summary = app(RectorateResearchImporter::class)->import($path, 'grants.xlsx', year: 2026, month: 10, columnMapping: $mapping);
        $this->assertTrue($summary['force_import']);
        $this->assertSame(1, $summary['skipped_rows']);
        $this->assertNull(DB::table('rectorate_research')->sole()->judul);
    }

    public function test_dosen_force_requires_full_mapping_and_imports_blank_names_with_warnings(): void
    {
        config(['imports.force.dosen_data_center' => true]);
        $values = ['kode_dosen' => 'D1', 'nama_dosen' => '', 'nama_gugus_binaan' => 'Computer Science',
            'jja' => 'Lektor', 'tipe_faculty' => 'Functional', 'status' => 'Aktif', 'pendidikan' => 'S2'];
        [$path, $mapping] = $this->mappedWorkbook($values, [array_replace($values, ['kode_dosen' => ''])], 'Malang');
        $summary = app(LecturerWorkbook::class)->import($path, 2026, 10, $mapping, 1);
        $this->assertTrue($summary['force_import']);
        $this->assertSame(1, $summary['skipped_rows']);
        $this->assertNull(DataDosen::find('D1')->nama_dosen);
        unset($mapping['pendidikan']);
        $this->expectException(ValidationException::class);
        app(LecturerWorkbook::class)->import($path, 2026, 10, $mapping, 1);
    }

    public function test_education_force_skips_missing_codes_and_conflicting_duplicates(): void
    {
        config(['imports.force.dosen_status_pendidikan' => true]);
        [$path, $mapping] = $this->mappedWorkbook(['kode_dosen' => 'D1', 'status_pendidikan' => 'On Going S3'], [
            ['kode_dosen' => '', 'status_pendidikan' => 'S3'], ['kode_dosen' => 'D1', 'status_pendidikan' => 'S3'],
        ], LecturerEducationWorkbook::SHEET);
        $summary = app(LecturerEducationWorkbook::class)->import($path, 2026, 10, $mapping, 1);
        $this->assertTrue($summary['force_import']);
        $this->assertSame(2, $summary['skipped_rows']);
        $this->assertSame('On Going S3', DataDosen::find('D1')->status_pendidikan);
        $this->assertSame('S2', DataDosen::find('D1')->pendidikan);
    }

    public function test_pkm_force_import_is_transactional_and_reports_bypassed_values(): void
    {
        config(['imports.force.pkm' => true]);
        $path = $this->makeWorkbook([
            'List' => [range('A', 'J'), ['', '2026', '', 'D1', '', '', '', '', '', ''], ['', '2026', '', '', '', '', '', '', '', '']],
            'CE' => [range('A', 'G'), ['', '', 'D1', 'bad', '2', '1', 'Note']],
        ]);
        $summary = app(PkmWorkbook::class)->import($path, 2026, 'Ganjil');
        $this->assertTrue($summary['force_import']);
        $this->assertSame(2, $summary['selected']);
        $this->assertSame(1, $summary['skipped_rows']);
        $this->assertNull(DB::table('pkm_dosen')->sole()->judul_pkm);
        $this->assertNull(DB::table('ce_pkm_dosen')->sole()->jumlah_kegiatan);
        $this->assertNotEmpty($summary['warnings']);
    }

    public function test_pkm_endpoint_reads_its_own_force_flag_and_requires_template_columns(): void
    {
        config(['imports.force.pkm' => true]);
        $path = $this->makeWorkbook(['List' => [range('A', 'J'), ['', '2026', '', 'D1', '', '', '', '', '', '']]]);
        $payload = ['pkm' => new UploadedFile($path, 'pkm.xlsx', null, null, true), 'year' => 2026, 'period' => 'Ganjil'];
        $this->postJson(route('importpkm.create'), $payload)->assertOk()->assertJsonPath('summary.force_import', true)->assertJsonPath('summary.selected', 1);
        config(['imports.force.pkm' => false]);
        $this->postJson(route('importpkm.create'), $payload)->assertUnprocessable();
        config(['imports.force.pkm' => true]);
        $bad = $this->makeWorkbook(['List' => [['Only one header'], ['Some data']]]);
        $payload['pkm'] = new UploadedFile($bad, 'pkm.xlsx', null, null, true);
        $this->postJson(route('importpkm.create'), $payload)->assertUnprocessable();
        $this->assertSame(1, DB::table('pkm_dosen')->count());
    }

    public function test_force_with_no_importable_rows_keeps_existing_period_intact(): void
    {
        [$path, $mapping] = $this->mappedWorkbook($this->fmValues());
        app(PublicationImporter::class)->import($path, 'original.xlsx', 2026, 10, 4, columnMapping: $mapping);
        $before = DB::table('rectorate_dosen')->get();
        config(['imports.force.rectorate_fm' => true]);
        [$invalid, $mapping] = $this->mappedWorkbook($this->fmValues(['kode_dosen' => '']));
        try {
            app(PublicationImporter::class)->import($invalid, 'empty.xlsx', 2026, 10, 4, columnMapping: $mapping);
            $this->fail('An empty replacement must not erase the existing snapshot.');
        } catch (ValidationException) {
            $this->assertEquals($before, DB::table('rectorate_dosen')->get());
            $this->assertSame('original.xlsx', DB::table('publication_imports')->sole()->filename);
        }
    }

    public function test_database_errors_are_never_bypassed_by_force(): void
    {
        [$path, $mapping] = $this->mappedWorkbook($this->fmValues());
        app(PublicationImporter::class)->import($path, 'original.xlsx', 2026, 10, 4, columnMapping: $mapping);
        $before = DB::table('rectorate_dosen')->get();
        config(['imports.force.rectorate_fm' => true]);
        DB::unprepared("CREATE TRIGGER reject_force_insert BEFORE INSERT ON rectorate_dosen BEGIN SELECT RAISE(ABORT, 'database failure'); END");
        try {
            app(PublicationImporter::class)->import($path, 'replacement.xlsx', 2026, 10, 4, columnMapping: $mapping);
            $this->fail('A database failure must still roll back the complete replacement.');
        } catch (\Illuminate\Database\QueryException) {
            $this->assertEquals($before, DB::table('rectorate_dosen')->get());
            $this->assertSame('original.xlsx', DB::table('publication_imports')->sole()->filename);
        }
    }
}
