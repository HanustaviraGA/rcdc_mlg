<?php

namespace Tests\Feature;

use App\Services\Lecturers\LecturerSnapshots;
use App\Services\Publications\PublicationDashboard;
use App\Services\Publications\PublicationPerformanceDashboard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LecturerSnapshotDashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('SQLite required.');
        }
        $this->travelTo(now()->setDate(2026, 10, 22));
        Schema::create('database_dosen', function (Blueprint $table) {
            $table->string('kode_dosen')->primary();
        });
        Schema::create('database_dosen_new', function (Blueprint $table) {
            $table->string('kode_dosen')->primary();
            $table->string('campus')->nullable();
            $table->string('nama_dosen')->nullable();
            $table->string('nama_gugus_binaan')->nullable();
            $table->string('jja')->nullable();
            $table->string('tipe_faculty')->nullable();
        });
        Schema::create('publication_imports', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedTinyInteger('period');
            $table->string('filename')->nullable();
            $table->json('summary')->nullable();
            $table->timestamps();
        });
        Schema::create('publication_kpi_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('publication_import_id');
            $table->string('kode_dosen');
            $table->string('name');
            $table->string('program');
            $table->string('academic_rank')->nullable();
            $table->string('faculty_type')->nullable();
            $table->double('rtto_non_scopus')->nullable();
            $table->double('rtto_scopus')->nullable();
            $table->unsignedTinyInteger('rtto_score')->nullable();
        });
        Schema::create('rectorate_dosen', function (Blueprint $table) {
            $table->id();
            foreach (['kode_dosen', 'request_code', 'author', 'fm_author', 'jja', 'prodi_kpi', 'dept', 'kampus',
                'submitted', 'status', 'jenis', 'tipe_publikasi', 'title', 'first_author', 'source_title',
                'quartile_jurnal', 'year', 'month', 'period'] as $column) {
                $table->string($column)->nullable();
            }
            $table->double('bobot_asli')->nullable();
            $table->timestamps();
        });
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
            $table->string('nama_program')->nullable();
            $table->string('jja')->nullable();
            $table->string('tipe_faculty')->nullable();
            $table->string('campus')->nullable();
        });

        DB::table('database_dosen')->insert([['kode_dosen' => 'D1'], ['kode_dosen' => 'D2']]);
        DB::table('database_dosen_new')->insert([
            ['kode_dosen' => 'D1', 'campus' => 'JAKARTA', 'nama_dosen' => 'Current D1', 'jja' => 'Guru Besar', 'tipe_faculty' => 'Professional'],
            ['kode_dosen' => 'D2', 'campus' => 'MALANG', 'nama_dosen' => 'Current D2', 'jja' => 'Guru Besar', 'tipe_faculty' => 'Professional'],
        ]);
        DB::table('lecturer_imports')->insert(['id' => 1, 'year' => 2025, 'month' => 8]);
        DB::table('lecturer_snapshot_rows')->insert([
            'lecturer_import_id' => 1, 'kode_dosen' => 'D1', 'nama_dosen' => 'Historical D1',
            'nama_gugus_binaan' => 'Computer Science', 'jja' => 'Asisten Ahli',
            'tipe_faculty' => 'Functional', 'campus' => 'MALANG',
        ]);
    }

    protected function tearDown(): void
    {
        $this->travelBack();
        parent::tearDown();
    }

    public function test_period_roster_controls_historical_profile_and_campus_without_current_fallback(): void
    {
        $this->insertPaper('D1', 2025, 8, 'D1-HISTORICAL');
        // D2 has a current Malang profile but no 2025 snapshot. It must not
        // enter the historical participant population through current data.
        $this->insertPaper('D2', 2025, 8, 'D2-NO-SNAPSHOT');

        $data = $this->dashboard()->data();
        $snapshot = collect($data['snapshots'])->firstWhere('year', 2025);
        $faculty = collect($snapshot['fm']['cards'])->flatMap(fn ($card) => $card['faculty'])->keyBy('code');

        $this->assertArrayHasKey('D1', $faculty->all());
        $this->assertArrayNotHasKey('D2', $faculty->all());
        $this->assertSame('Asisten Ahli', $faculty['D1']['rank']);
        $this->assertSame('Functional', $faculty['D1']['faculty']);
        $this->assertSame('MALANG', $faculty['D1']['campus']);
        $this->assertSame('2026', $data['defaultYear']);
        $this->assertContains('2026', $data['years']);
    }

    public function test_missing_period_snapshot_never_supplies_current_profile_fields(): void
    {
        $this->insertPaper('D1', 2024, 8, 'EXPLICIT-CAMPUS');
        DB::table('rectorate_dosen')->update(['kampus' => 'MALANG', 'fm_author' => null, 'prodi_kpi' => null, 'dept' => null]);
        $data = $this->dashboard()->data();
        $fm = $data['snapshots'][0]['fm'];
        $member = $fm['cards'][0]['faculty'][0];

        $this->assertSame(1, $fm['facultyTotal']);
        $this->assertSame('D1', $member['name']);
        $this->assertSame('Lainnya', $member['program']);
        $this->assertNull($member['rank']);
        $this->assertNull($member['faculty']);
        $this->assertSame('MALANG', $member['campus']);

        // Even a current-year report needs a roster snapshot at or before
        // its month; it must not read the live profile or a future batch.
        DB::table('rectorate_dosen')->update(['year' => 2026, 'month' => 1, 'kampus' => null]);
        DB::table('lecturer_imports')->insert(['id' => 2, 'year' => 2026, 'month' => 10]);
        $this->assertSame([], $this->dashboard()->data()['snapshots']);
    }

    public function test_period_roster_does_not_require_membership_in_current_master_or_expand_population(): void
    {
        DB::table('database_dosen')->where('kode_dosen', 'D1')->delete();
        DB::table('lecturer_snapshot_rows')->insert([
            'lecturer_import_id' => 1, 'kode_dosen' => 'ROSTER-ONLY', 'nama_dosen' => 'Not a participant', 'campus' => 'MALANG',
        ]);
        $this->insertPaper('D1', 2025, 8, 'PARTICIPANT');
        DB::table('rectorate_dosen')->update(['kampus' => '', 'fm_author' => '', 'jja' => '', 'prodi_kpi' => '', 'dept' => '']);
        $fm = $this->dashboard()->data()['snapshots'][0]['fm'];
        $member = $fm['cards'][0]['faculty'][0];

        $this->assertSame(1, $fm['facultyTotal']);
        $this->assertSame('D1', $member['code']);
        $this->assertSame('Historical D1', $member['name']);
        $this->assertSame('CS', $member['program']);
        $this->assertSame('Asisten Ahli', $member['rank']);
        $this->assertSame('MALANG', $member['campus']);
        $this->assertSame(1, $fm['cards'][0]['titles']);
        $this->assertSame(1.0, $fm['cards'][0]['weight']);
        $this->assertSame(1.0, $fm['cards'][0]['actual']);
        $this->assertCount(1, $fm['cards'][0]['publications']);
    }

    public function test_paper_values_override_snapshot_profile_fields(): void
    {
        $this->insertPaper('D1', 2025, 8, 'OFFICIAL-PAPER');
        DB::table('rectorate_dosen')->update(['fm_author' => 'Paper author', 'jja' => 'Lektor', 'prodi_kpi' => 'DI', 'kampus' => 'Malang']);
        $member = $this->dashboard()->data()['snapshots'][0]['fm']['cards'][0]['faculty'][0];

        $this->assertSame('Paper author', $member['name']);
        $this->assertSame('DI', $member['program']);
        $this->assertSame('Lektor', $member['rank']);
        $this->assertSame('Malang', $member['campus']);
    }

    public function test_current_server_year_remains_an_option_without_any_imports(): void
    {
        $this->travelTo(now()->setDate(2030, 1, 1));
        $data = $this->dashboard()->data();

        $this->assertSame('2030', $data['defaultYear']);
        $this->assertContains('2030', $data['years']);
        $this->assertSame([], $data['snapshots']);
    }

    public function test_legacy_installations_keep_current_master_campus_fallback(): void
    {
        Schema::drop('lecturer_snapshot_rows');
        Schema::drop('lecturer_imports');
        $this->insertPaper('D2', 2025, 8, 'LEGACY');
        $fm = $this->dashboard()->data()['snapshots'][0]['fm'];

        $this->assertSame(1, $fm['facultyTotal']);
        $this->assertSame('D2', $fm['cards'][0]['faculty'][0]['code']);
        $this->assertSame('MALANG', $fm['cards'][0]['faculty'][0]['campus']);
    }

    public function test_kpi_only_batch_is_a_snapshot_and_uses_official_values(): void
    {
        DB::table('publication_imports')->insert([
            'id' => 9, 'year' => 2025, 'month' => 9, 'period' => 3,
            'filename' => 'kpi-only.xlsx',
            'summary' => json_encode(['report' => ['realization' => ['CS' => ['realization' => 7]]]]),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('publication_kpi_entries')->insert([
            'publication_import_id' => 9, 'kode_dosen' => 'D1', 'name' => 'KPI Only', 'program' => 'CS',
            'academic_rank' => 'Lektor', 'faculty_type' => 'Professional', 'rtto_scopus' => 3,
            'rtto_non_scopus' => 1, 'rtto_score' => 5,
        ]);
        // A newer KPI-only period must not be lost to an older paper period
        // in the same month, nor inherit those papers.
        $this->insertPaper('D1', 2025, 9, 'OLDER-PAPER');
        DB::table('rectorate_dosen')->update(['period' => 2]);

        $data = $this->dashboard()->data();
        $snapshot = collect($data['snapshots'])->first(fn ($row) => $row['year'] === 2025 && $row['month'] === 9);
        $this->assertNotNull($snapshot);
        $this->assertSame(7.0, $snapshot['fm']['cards'][0]['actual']);
        $this->assertSame('KPI Only', $snapshot['fm']['cards'][0]['faculty'][0]['name']);
        $this->assertSame('Lektor', $snapshot['fm']['cards'][0]['faculty'][0]['rank']);
        $this->assertSame('Professional', $snapshot['fm']['cards'][0]['faculty'][0]['faculty']);
        $this->assertSame('MALANG', $snapshot['fm']['cards'][0]['faculty'][0]['campus']);
        $this->assertSame(5, $snapshot['fm']['cards'][0]['faculty'][0]['score']);
        $this->assertSame(3.0, $snapshot['fm']['cards'][0]['faculty'][0]['scopus']);
        $this->assertSame([], $snapshot['fm']['cards'][0]['publications']);
    }

    private function dashboard(): PublicationPerformanceDashboard
    {
        $source = $this->createMock(PublicationDashboard::class);
        $source->method('data')->willReturn([
            'faculty' => [[
                'code' => 'D1', 'name' => 'Current D1', 'program' => 'CS', 'rank' => 'GB',
                'faculty_detail' => 'Professional',
            ]],
            'years' => ['2025'],
        ]);

        return new PublicationPerformanceDashboard($source, app(LecturerSnapshots::class));
    }

    private function insertPaper(string $code, int $year, int $month, string $request): void
    {
        DB::table('rectorate_dosen')->insert([
            'kode_dosen' => $code, 'request_code' => $request, 'fm_author' => $code.' Author',
            'prodi_kpi' => 'CS', 'dept' => 'CS', 'kampus' => null, 'submitted' => 'Scopus FM',
            'status' => 'Accepted', 'jenis' => 'Jurnal', 'tipe_publikasi' => 'Scopus',
            'title' => $request, 'first_author' => 'Y', 'year' => $year, 'month' => $month,
            'period' => 3, 'bobot_asli' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
