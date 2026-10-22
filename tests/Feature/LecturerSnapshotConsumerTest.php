<?php

namespace Tests\Feature;

use App\Services\Lecturers\LecturerSnapshots;
use App\Services\Publications\ImportedLecturerKpi;
use App\Services\Reports\MonthlyPublicationReport;
use App\Services\Research\RectorateResearchImporter;
use App\Services\Research\RectorateResearchReader;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class LecturerSnapshotConsumerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('SQLite required.');
        }

        $this->travelTo(now()->setDate(2026, 10, 22));
        Schema::create('database_dosen_new', function (Blueprint $table) {
            $table->string('kode_dosen')->primary();
            $table->string('nama_dosen')->nullable();
            $table->softDeletes();
        });
        DB::table('database_dosen_new')->insert([
            ['kode_dosen' => 'D1', 'nama_dosen' => 'Modern D1'],
            ['kode_dosen' => 'D2', 'nama_dosen' => 'Modern D2'],
        ]);
        Schema::create('lecturer_imports', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
        });
        Schema::create('lecturer_snapshot_rows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lecturer_import_id');
            $table->string('kode_dosen');
            $table->string('nama_dosen')->nullable();
            $table->string('nama_gugus_binaan')->nullable();
            $table->string('jja')->nullable();
            $table->string('tipe_faculty')->nullable();
            $table->string('pendidikan')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
        DB::table('lecturer_imports')->insert([
            ['id' => 1, 'year' => 2025, 'month' => 1],
            ['id' => 2, 'year' => 2025, 'month' => 8],
        ]);
        DB::table('lecturer_snapshot_rows')->insert([
            ['lecturer_import_id' => 1, 'kode_dosen' => 'D1', 'nama_dosen' => 'January D1', 'nama_gugus_binaan' => 'January Program', 'jja' => 'January Rank', 'tipe_faculty' => 'Functional', 'pendidikan' => 'S2'],
            ['lecturer_import_id' => 2, 'kode_dosen' => 'D1', 'nama_dosen' => 'August D1', 'nama_gugus_binaan' => 'August Program', 'jja' => 'August Rank', 'tipe_faculty' => 'Professional', 'pendidikan' => 'S3'],
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        $this->travelBack();
        parent::tearDown();
    }

    public function test_snapshot_api_uses_latest_same_year_batch_without_future_or_current_master_leak(): void
    {
        $snapshots = app(LecturerSnapshots::class);

        $this->assertSame('January D1', $snapshots->rows(2025, 7)['D1']->nama_dosen);
        $this->assertSame('August D1', $snapshots->rows(2025, 9)['D1']->nama_dosen);
        $this->assertTrue($snapshots->rows(2024)->isEmpty());
        $this->assertFalse($snapshots->rows(2025, 7)->has('D2'));
    }

    public function test_publication_consumers_use_historical_roster_and_preserve_kpi_participants(): void
    {
        Schema::create('publication_imports', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('month');
            $table->integer('period');
            $table->string('filename');
            $table->json('summary');
        });
        Schema::create('student_publication_imports', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('month');
            $table->json('publication_list')->nullable();
            $table->json('summary')->nullable();
        });
        Schema::create('rectorate_dosen', function (Blueprint $table) {
            $table->id();
            $table->integer('publication_import_id')->nullable();
            $table->string('kode_dosen');
            $table->string('request_code')->nullable();
            $table->string('fm_author')->nullable();
            $table->string('prodi_kpi')->nullable();
            $table->string('dept')->nullable();
            $table->string('jja')->nullable();
            $table->string('tipe_publikasi');
            $table->string('submitted');
            $table->string('first_author')->nullable();
            $table->double('bobot_asli');
            $table->string('kampus')->nullable();
        });
        Schema::create('publication_kpi_entries', function (Blueprint $table) {
            $table->id();
            $table->integer('publication_import_id');
            $table->string('kode_dosen');
            $table->string('name')->nullable();
            $table->string('program')->nullable();
            $table->string('academic_rank')->nullable();
            $table->string('faculty_type')->nullable();
            $table->double('rtto_scopus')->nullable();
            $table->double('rtto_non_scopus')->nullable();
            $table->integer('rtto_score')->nullable();
        });

        DB::table('publication_imports')->insert([
            ['id' => 1, 'year' => 2025, 'month' => 8, 'period' => 3, 'filename' => 'august.xlsx', 'summary' => '{}'],
            ['id' => 2, 'year' => 2025, 'month' => 9, 'period' => 3, 'filename' => 'september.xlsx', 'summary' => '{}'],
        ]);
        DB::table('rectorate_dosen')->insert([
            ['publication_import_id' => 1, 'kode_dosen' => 'D1', 'request_code' => 'P1', 'fm_author' => null, 'prodi_kpi' => null, 'dept' => null, 'jja' => null, 'tipe_publikasi' => 'Scopus', 'submitted' => 'Scopus FM', 'first_author' => 'Y', 'bobot_asli' => 1, 'kampus' => 'MALANG'],
            ['publication_import_id' => 2, 'kode_dosen' => 'D1', 'request_code' => 'P2', 'fm_author' => null, 'prodi_kpi' => null, 'dept' => null, 'jja' => null, 'tipe_publikasi' => 'Scopus', 'submitted' => 'Scopus FM', 'first_author' => 'Y', 'bobot_asli' => 2, 'kampus' => 'MALANG'],
        ]);
        DB::table('publication_kpi_entries')->insert([
            ['publication_import_id' => 2, 'kode_dosen' => 'D1', 'name' => 'Official D1', 'program' => 'Official Program', 'academic_rank' => 'Official Rank', 'faculty_type' => 'Functional', 'rtto_scopus' => 4, 'rtto_non_scopus' => 0, 'rtto_score' => 6],
        ]);

        $report = app(MonthlyPublicationReport::class)->generate(2025, 8);
        $reportRow = collect($report['tables'])->keyBy('id')['fm_August Program']['rows'][0];
        $this->assertSame('August D1', $reportRow[1]);
        $this->assertSame('August Program', $reportRow[2]);
        $this->assertSame('August Rank', $reportRow[3]);

        $rows = app(ImportedLecturerKpi::class)->rows();
        $historical = $rows->firstWhere('month', 8);
        $official = $rows->firstWhere('month', 9);
        $this->assertSame('August D1', $historical['name']);
        $this->assertSame('August Program', $historical['program_name']);
        $this->assertSame('August Rank', $historical['rank']);
        $this->assertSame('Official D1', $official['name']);
        $this->assertSame('Official Program', $official['program_name']);
        $this->assertSame('Official Rank', $official['rank']);
        $this->assertSame('RTTO', $official['score_source']);
        $this->assertCount(2, $rows);
        $this->assertFalse($rows->contains('code', 'D2'));
    }

    public function test_research_import_dry_run_resolves_codes_from_the_requested_period_roster(): void
    {
        $reader = Mockery::mock(RectorateResearchReader::class);
        $reader->shouldReceive('read')->twice()->andReturn([
            'summary' => ['sheet' => 'MALANG'],
            'records' => [
                ['kode_dosen_nim' => 'D1', 'kategori_fm_eksternal_mahasiswa' => 'FM'],
                ['kode_dosen_nim' => 'D2', 'kategori_fm_eksternal_mahasiswa' => 'FM'],
            ],
        ]);
        app()->instance(RectorateResearchReader::class, $reader);

        $summary = app(RectorateResearchImporter::class)->import('unused.xlsx', 'research.xlsx', true, 2025, 7);

        $this->assertSame(['D2'], $summary['unmatched_fm_codes']);
        $this->assertSame(['D2'], app(RectorateResearchImporter::class)->import('unused.xlsx', 'research.xlsx', true, 2025, 7)['unmatched_fm_codes']);
    }
}
