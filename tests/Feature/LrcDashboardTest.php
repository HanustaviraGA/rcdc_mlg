<?php

namespace Tests\Feature;

use App\Services\Lecturers\LecturerWorkbook;
use App\Services\LrcDashboard\LrcDashboard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\RoadmapLRC\Database\Seeders\RoadmapLRCDatabaseSeeder;
use Tests\TestCase;

class LrcDashboardTest extends TestCase
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
            foreach (array_diff(array_keys(LecturerWorkbook::HEADERS), ['kode_dosen']) as $field) {
                $table->text($field)->nullable();
            }
            $table->text('fakultas_internal')->nullable();
            $table->text('status_serdos')->nullable();
            $table->timestamps();
        });
        (require database_path('migrations/2026_09_25_000000_add_visibility_and_soft_deletes_to_database_dosen_new.php'))->up();
        foreach ([
            ['D1', 'SOCS', 'FM', 'Functional Faculty(A)'],
            ['D2', 'BBSUG', 'FMSTR', 'Functional Faculty(C)'],
            ['D3', 'SOD', 'fmstr ', 'Professional Faculty(A)'],
            ['D4', 'SOCS', 'AFM', '-'],
        ] as [$code, $faculty, $type, $facultyType]) {
            DB::table('database_dosen_new')->insert([
                'kode_dosen' => $code, 'nama_dosen' => "Lecturer {$code}", 'fakultas_internal' => $faculty,
                'tipe' => $type, 'tipe_faculty' => $facultyType,
            ]);
        }
        // The snapshot migration turns the master above into the current roster.
        (require database_path('migrations/2026_10_22_000000_create_lecturer_snapshots.php'))->up();
    }

    protected function tearDown(): void
    {
        $this->travelBack();
        parent::tearDown();
    }

    public function test_faculty_composition_groups_by_lecturer_type_then_faculty_type(): void
    {
        $data = app(LrcDashboard::class)->data();

        $this->assertSame(['labels' => ['FM', 'FMSCS', 'FMSTR'], 'data' => [1, 0, 2]], $data['charts']['fakultas']);
        $this->assertSame(['Functional Faculty(C)', 'Professional Faculty(A)'], array_column($data['facultyDetails']['FMSTR']['types'], 'label'));
        $this->assertSame(['D2'], array_column($data['facultyDetails']['FMSTR']['types'][0]['lecturers'], 'code'));
        // The Fakultas filter still uses Fakultas Internal.
        $this->assertSame(['BBSUG', 'SOCS', 'SOD'], $data['filters']['fakultas']);
        $this->assertSame([1, 0, 0], app(LrcDashboard::class)->data(['fakultas' => 'SOCS'])['charts']['fakultas']['data']);
    }

    public function test_every_other_chart_lists_the_lecturers_behind_each_category(): void
    {
        foreach ([
            'D1' => ['pendidikan' => 'S3', 'jja' => 'Lektor Kepala', 'usia' => '45', 'status_serdos' => 'Lulus',
                'jenis_registrasi' => 'NIDN', 'nomor_nidn_nupn' => '123', 'agama' => 'Islam', 'nama_gugus_binaan' => 'Computer Science'],
            'D2' => ['pendidikan' => 'S2', 'jja' => 'Asisten Ahli', 'usia' => '30', 'agama' => 'Kristen',
                'jenis_registrasi' => null, 'nomor_nidn_nupn' => ' 456 '],
            'D3' => ['pendidikan' => 'S2', 'jja' => 'L200', 'usia' => '30', 'agama' => 'Islam',
                'jenis_registrasi' => 'NUPTK', 'nomor_nidn_nupn' => '-'],
            'D4' => ['jenis_registrasi' => 'NUPTK', 'nomor_nidn_nupn' => null],
        ] as $code => $values) {
            DB::table('database_dosen_new')->where('kode_dosen', $code)->update($values);
        }
        $data = app(LrcDashboard::class)->data();
        $codes = fn (string $chart) => array_map(fn ($lecturers) => array_column($lecturers, 'code'), $data['chartDetails'][$chart]);

        $this->assertSame(['pendidikan', 'jja', 'usia', 'serdos', 'nuptk', 'tipeDosen', 'agama'], array_keys($data['chartDetails']));
        foreach ($data['chartDetails'] as $chart => $categories) {
            // Each list matches its bar/slice by index and by count.
            $this->assertSame($data['charts'][$chart]['data'], array_map('count', $categories), $chart);
        }
        $this->assertSame([['D2', 'D3'], ['D1'], []], $codes('pendidikan'));
        $this->assertSame([[], ['D2'], ['D3'], ['D1'], []], $codes('jja'));
        $this->assertSame([['D2', 'D3'], ['D1'], [], [], [], []], $codes('usia'));
        $this->assertSame([['D1'], ['D2', 'D3', 'D4']], $codes('serdos'));
        $this->assertSame([['D1', 'D2'], ['D3', 'D4']], $codes('nuptk'));
        $this->assertSame([['D1', 'D2'], ['D3']], $codes('tipeDosen'));
        $this->assertSame(['Islam', 'Kristen', 'Tidak diketahui'], $data['charts']['agama']['labels']);
        $this->assertSame([['D1', 'D3'], ['D2'], ['D4']], $codes('agama'));
        $this->assertSame(['code' => 'D1', 'name' => 'Lecturer D1', 'gugus' => 'Computer Science', 'program' => null], $data['chartDetails']['pendidikan'][1][0]);

        // Lists follow the dashboard filters.
        $socs = app(LrcDashboard::class)->data(['fakultas' => 'SOCS']);
        $this->assertSame([[], ['D1'], []], array_map(fn ($lecturers) => array_column($lecturers, 'code'), $socs['chartDetails']['pendidikan']));
        $this->assertSame(['Islam', 'Tidak diketahui'], $socs['charts']['agama']['labels']);
        $this->assertSame([['D1'], ['D4']], array_map(fn ($lecturers) => array_column($lecturers, 'code'), $socs['chartDetails']['agama']));
    }

    public function test_page_offers_a_lecturer_list_button_for_every_chart_category(): void
    {
        $response = $this->get(route('lrc-dashboard'))->assertOk();
        foreach (['pendidikan' => 3, 'jja' => 5, 'usia' => 6, 'serdos' => 2, 'nuptk' => 2, 'tipeDosen' => 2, 'agama' => 1] as $chart => $categories) {
            $response->assertSee('data-lrc-chart-card="'.$chart.'"', false);
            $response->assertSee('data-lrc-chart="'.$chart.'" data-lrc-index="'.($categories - 1).'"', false);
            $response->assertDontSee('data-lrc-chart="'.$chart.'" data-lrc-index="'.$categories.'"', false);
        }
        // Empty categories cannot be opened; the Komposisi Fakultas buttons are unchanged.
        $response->assertSee('data-lrc-chart="pendidikan" data-lrc-index="0" disabled', false)->assertSee('data-lrc-faculty="FMSTR"', false);
    }

    public function test_projection_shows_the_planned_share_of_s3_fm_per_year_below_the_education_chart(): void
    {
        $this->roadmapMigrations();
        $this->seed(RoadmapLRCDatabaseSeeder::class);
        $page = $this->get(route('lrc-dashboard'))->assertOk()->getContent();

        // Right below the Jenjang pendidikan dosen chart, before the next section.
        $education = strpos($page, 'data-lrc-chart-card="pendidikan"');
        $this->assertTrue($education < strpos($page, 'id="lrcProjectionHeading"') && strpos($page, 'id="lrcProjectionHeading"') < strpos($page, 'id="lrcProfileHeading"'));
        $this->assertStringContainsString('Seluruh prodi (9).', $page);
        preg_match_all('/data-lrc-projection="(\d{4})"[^>]*>.*?data-lrc-projection-s3>([^<]+)<.*?data-lrc-projection-fm>([^<]+)<.*?data-lrc-projection-percent>([^<]+)</s', $page, $boxes, PREG_SET_ORDER);
        $this->assertSame([
            ['2026', '18', '117', '15,4%'], ['2027', '36', '119', '30,3%'], ['2028', '44', '122', '36,1%'], ['2029', '47', '125', '37,6%'],
            ['2030', '56', '129', '43,4%'], ['2031', '66', '131', '50,4%'], ['2032', '99', '134', '73,9%'], ['2033', '113', '138', '81,9%'],
            ['2034', '130', '141', '92,2%'], ['2035', '133', '143', '93,0%'],
        ], array_map(fn ($box) => array_slice($box, 1), $boxes));
        $this->assertStringContainsString('<span class="visually-hidden">: 18 dari 117 FM bergelar S3, 15,4%. Lihat rincian per prodi.</span>', $page);
        $this->assertStringContainsString('<button type="button" class="lrc-projection-card" data-lrc-projection="2026" aria-haspopup="dialog">', $page);
        $this->assertStringContainsString('id="lrcProjectionDetail"', $page);
        $this->assertMatchesRegularExpression('#<script src="[^"]*/js/lrc-projection\.js\?v=\d+"></script>#', $page);

        // The roadmap is per program: Fakultas, Gugus Binaan and Program narrow it to the programs of their lecturers.
        DB::table('database_dosen_new')->where('kode_dosen', 'D1')->update(['nama_gugus_binaan' => 'Computer Science', 'nama_program' => 'Informatics', 'status' => 'Aktif']);
        DB::table('database_dosen_new')->where('kode_dosen', 'D2')->update(['nama_gugus_binaan' => 'Entrepreneurship', 'status' => 'Aktif']);
        $dashboard = app(LrcDashboard::class);
        foreach ([['fakultas' => 'SOCS'], ['gugusBinaan' => 'Computer Science'], ['program' => 'Informatics']] as $filter) {
            $projection = $dashboard->data($filter)['projection'];
            $this->assertSame(['CS'], $projection['prodi']);
            $this->assertSame(['year' => 2026, 's3' => 4, 'fm' => 21, 'percent' => 19.0], array_slice($projection['years'][0], 0, 4));
        }
        $this->assertSame(['CS'], $dashboard->data(['fakultas' => 'SOCS', 'statusDosen' => 'Aktif'])['projection']['prodi']);
        $this->assertStringContainsString('Prodi CS.', $this->get(route('lrc-dashboard', ['fakultas' => 'SOCS']))->getContent());
        // Status Dosen does not narrow the roadmap.
        $status = $dashboard->data(['statusDosen' => 'Aktif'])['projection'];
        $this->assertSame([false, true, 9], [$status['narrowed'], $status['statusIgnored'], count($status['prodi'])]);
        $this->assertStringContainsString('Roadmap tidak dibedakan per status dosen.', $this->get(route('lrc-dashboard', ['statusDosen' => 'Aktif']))->getContent());
        // A program without a roadmap.
        $sod = $this->get(route('lrc-dashboard', ['fakultas' => 'SOD']))->assertOk()->getContent();
        $this->assertStringContainsString('Belum ada roadmap untuk prodi pada filter ini.', $sod);
        $this->assertStringNotContainsString('data-lrc-projection=', $sod);
    }

    public function test_projection_counts_fm_s3_as_existing_plus_graduates_plus_new_recruits(): void
    {
        $this->projectionFixture([
            ['CS', 'Computer Science', 1, [2026 => [5, 4, 1, 1, 0], 2027 => [6, 3, 3, 0, 1], 2028 => [6, 2, 4, 0, 0]]],
            ['DKV', 'Visual Communication Design', 2, [2026 => [1, 0, 1, 0, 0], 2027 => [1, 0, 1, 0, 0], 2028 => [1, 0, 1, 0, 0]]],
        ]);

        $projection = app(LrcDashboard::class)->data()['projection'];
        $this->assertSame(['CS', 'DKV'], $projection['prodi']);
        $cs = $projection['dataCenter']['CS'];
        $this->assertSame(['Computer Science', 4, 1, 2, 1], [$cs['programStudi'], $cs['fm'], $cs['s2'], $cs['ongoing'], $cs['s3']]);
        $this->assertSame(['code' => 'C3', 'name' => 'Lecturer C3', 'pendidikan' => 'S2', 'statusPendidikan' => 'On Going S3', 'education' => 'On Going S3', 'proyeksiLulusS3' => 2028],
            $cs['lecturers'][2]);
        $this->assertNull($cs['lecturers'][0]['proyeksiLulusS3'], 'an S3 lecturer has nothing left to graduate');
        $program = fn (int $index, string $prodi) => collect($projection['years'][$index]['programs'])->firstWhere('prodi', $prodi);
        $formula = fn (array $entry) => [$entry['existing'], $entry['graduates'], $entry['recruits'], $entry['projectedS3']];

        // CS: 1 S3 now; C2 graduates in 2027 and C3 in 2028; the roadmap recruits one S2 in 2026 and one S3 in 2027.
        $this->assertSame([1, 0, 0, 1], $formula($program(0, 'CS')));
        $this->assertSame([['level' => 'S2', 'year' => 2026, 'count' => 1]], $program(0, 'CS')['newRecruits']);
        $this->assertSame([1, 1, 1, 3], $formula($program(1, 'CS')));
        $this->assertSame([3, 1, 0, 4], $formula($program(2, 'CS')));
        $this->assertSame([['level' => 'S3', 'year' => 2027, 'count' => 1], ['level' => 'S2', 'year' => 2026, 'count' => 1]], $program(2, 'CS')['newRecruits']);
        $this->assertSame(['fm' => 6, 's2' => 2, 's3' => 4, 'nrS2' => 0, 'nrS3' => 0], $program(2, 'CS')['roadmap']);
        $this->assertSame(6, $program(2, 'CS')['projectedFm']);
        // DKV already has more S3 and FM than its roadmap: no recruits, and the projection keeps the Data Center count.
        $this->assertSame([2, 0, 0, 2], $formula($program(2, 'DKV')));
        $this->assertSame([[], 2], [$program(2, 'DKV')['newRecruits'], $program(2, 'DKV')['projectedFm']]);
        // The year sums its programs.
        $year = $projection['years'][1];
        $this->assertSame([2027, 4, 7, 57.1, 3, 1, 1, 5, 2], [$year['year'], $year['s3'], $year['fm'], $year['percent'],
            $year['existing'], $year['graduates'], $year['recruits'], $year['projectedS3'], $year['newRecruits']]);
    }

    public function test_new_fm_recruits_come_from_the_roadmap_and_stay_at_their_level(): void
    {
        // CS has 4 FM in the Data Center and plans no New FM Recruit: its projected S3 only follows its graduates,
        // above the roadmap when C2 graduates in 2027 and below it in 2028 (C4 has no S3 plan).
        $this->projectionFixture([['CS', 'Computer Science', 1, [2026 => [4, 3, 1, 0, 0], 2027 => [4, 3, 1, 0, 0], 2028 => [4, 0, 4, 0, 0]]]]);
        $years = app(LrcDashboard::class)->data()['projection']['years'];

        $this->assertSame([[], [], []], array_map(fn (array $year) => $year['programs'][0]['newRecruits'], $years));
        $this->assertSame([0, 0, 0], array_column($years, 'newRecruits'));
        $this->assertSame([[1, 0, 0, 1], [1, 1, 0, 2], [2, 1, 0, 3]], array_map(fn (array $year) => [$year['existing'], $year['graduates'], $year['recruits'], $year['projectedS3']], $years));
        $this->assertSame([1, 1, 4], array_column($years, 's3'), 'the roadmap S3 stays as planned');
        $this->assertSame([4, 4, 4], array_column($years, 'projectedFm'));

        // A larger FM count is not a recruit by itself: only the NR columns are.
        DB::table('prodi_fm_roadmaps')->where('year', 2027)->update(['jumlah_fm' => 5, 'jumlah_s2' => 4]);
        $program = app(LrcDashboard::class)->data()['projection']['years'][1]['programs'][0];
        $this->assertSame([[], 4, 5], [$program['newRecruits'], $program['projectedFm'], $program['roadmap']['fm']]);

        // Recruits join in their year at their level: S3 recruits count for FM S3, S2 recruits stay S2.
        DB::table('prodi_fm_roadmaps')->where('year', 2027)->update(['jumlah_nr_s2' => 1]);
        DB::table('prodi_fm_roadmaps')->where('year', 2028)->update(['jumlah_fm' => 8, 'jumlah_s2' => 2, 'jumlah_s3' => 6, 'jumlah_nr_s3' => 3]);
        $years = app(LrcDashboard::class)->data()['projection']['years'];
        $program = $years[2]['programs'][0];
        $this->assertSame([['level' => 'S3', 'year' => 2028, 'count' => 3], ['level' => 'S2', 'year' => 2027, 'count' => 1]], $program['newRecruits']);
        $this->assertSame([2, 1, 3, 6, 8], [$program['existing'], $program['graduates'], $program['recruits'], $program['projectedS3'], $program['projectedFm']]);
        $this->assertSame(['fm' => 8, 's2' => 2, 's3' => 6, 'nrS2' => 0, 'nrS3' => 3], $program['roadmap']);
        $this->assertSame([0, 1, 4], array_column($years, 'newRecruits'));
    }

    /**
     * Computer Science lecturers C1 (S3), C2 (On Going S3, graduating 2027), C3 (S2 on its row, On Going S3
     * graduating 2028 by its latest education status) and C4 (S2), Visual Communication Design V1 and V2 (S3),
     * and the given roadmaps: [prodi, program, order, [year => [FM, S2, S3, NR S2, NR S3]]].
     */
    private function projectionFixture(array $roadmaps): void
    {
        foreach (['2026_10_22_000100_add_education_status_to_lecturers', '2026_11_03_000000_create_lecturer_education_statuses',
            '2026_11_12_000000_add_s3_planning_to_lecturer_education'] as $migration) {
            (require database_path("migrations/{$migration}.php"))->up();
        }
        $this->roadmapMigrations();
        $import = DB::table('lecturer_imports')->value('id');
        foreach ([
            ['C1', 'Computer Science', 'S3', null, null],
            ['C2', 'Computer Science', 'On going', 'On Going S3', 2027],
            // S2 on the Data Center row; the latest education status below makes it On Going S3 graduating in 2028.
            ['C3', 'Computer Science', 'S2', null, null],
            ['C4', 'Computer Science', 'S2', null, null],
            ['V1', 'Visual Communication Design', 'S3', null, null],
            ['V2', 'Visual Communication Design', 'S3', null, null],
        ] as [$code, $program, $education, $status, $graduation]) {
            DB::table('database_dosen_new')->insert(['kode_dosen' => $code, 'nama_dosen' => "Lecturer {$code}", 'nama_gugus_binaan' => $program,
                'pendidikan' => $education, 'status_pendidikan' => $status, 'proyeksi_lulus_s3' => $graduation]);
            DB::table('lecturer_snapshot_rows')->insert(['kode_dosen' => $code, 'lecturer_import_id' => $import]);
        }
        DB::table('lecturer_education_statuses')->insert(['kode_dosen' => 'C3', 'year' => 2026, 'month' => 9,
            'status_pendidikan' => 'On Going S3', 'proyeksi_lulus_s3' => 2028]);
        foreach ($roadmaps as [$prodi, $program, $order, $years]) {
            foreach ($years as $year => [$fm, $s2, $s3, $nrS2, $nrS3]) {
                DB::table('prodi_fm_roadmaps')->insert(['prodi' => $prodi, 'program_studi' => $program, 'urutan' => $order, 'year' => $year,
                    'jumlah_fm' => $fm, 'jumlah_s2' => $s2, 'jumlah_s3' => $s3, 'jumlah_nr_s2' => $nrS2, 'jumlah_nr_s3' => $nrS3]);
            }
        }
    }

    private function roadmapMigrations(): void
    {
        foreach (['2026_11_15_000000_create_prodi_fm_roadmaps_table', '2026_11_16_000000_add_new_recruits_to_prodi_fm_roadmaps_table'] as $migration) {
            (require base_path("Modules/RoadmapLRC/database/migrations/{$migration}.php"))->up();
        }
    }

    public function test_projection_is_empty_without_the_roadmap_table(): void
    {
        $page = $this->get(route('lrc-dashboard'))->assertOk()->getContent();
        $this->assertStringContainsString('Belum ada roadmap untuk prodi pada filter ini.', $page);
        $this->assertStringNotContainsString('lrc-projection-list', $page);
        $this->assertStringNotContainsString('id="lrcProjectionDetail"', $page);
    }

    public function test_jja_chart_ascends_from_tp_to_gb_and_details_expose_promotion_planning(): void
    {
        DB::table('database_dosen_new')->insert(['kode_dosen' => 'D5', 'nama_dosen' => 'Lecturer D5', 'jja' => 'GB']);
        DB::table('lecturer_snapshot_rows')->insert(['kode_dosen' => 'D5', 'lecturer_import_id' => DB::table('lecturer_imports')->value('id')]);
        foreach ([
            'D1' => ['TP', 'AA150', '2027-04-01'],
            'D2' => ['AA150', 'Lektor', '2028-01-01'],
            'D3' => ['L300', 'Lektor Kepala', '2029-11-01'],
            'D4' => ['LK400', 'GB', '2030-06-01'],
        ] as $code => [$rank, $roadmap, $date]) {
            DB::table('database_dosen_new')->where('kode_dosen', $code)->update([
                'jja' => $rank, 'roadmap_kenaikan_jja' => $roadmap, 'eligible_kenaikan_jja' => $date,
            ]);
        }
        $data = app(LrcDashboard::class)->data();
        $this->assertSame(['TP (Tenaga Pengajar)', 'AA (Asisten Ahli)', 'L (Lektor)', 'LK (Lektor Kepala)', 'GB (Guru Besar)'], $data['charts']['jja']['labels']);
        $this->assertSame([1, 1, 1, 1, 1], $data['charts']['jja']['data']);
        $this->assertSame(['AA', 'L', 'LK', 'GB', null], array_column($data['rankProgression'], 'next'));
        $this->assertSame([['D1'], ['D2'], ['D3'], ['D4'], ['D5']], array_map(fn ($rows) => array_column($rows, 'code'), $data['chartDetails']['jja']));
        $tp = $data['chartDetails']['jja'][0][0];
        $this->assertSame(['AA150', 'AA', '2027-04-01', 2027], [$tp['roadmapKenaikanJja'], $tp['roadmapJjaRank'], $tp['eligibleKenaikanJja'], $tp['eligibleJjaYear']]);
        $this->assertNull($data['chartDetails']['jja'][4][0]['eligibleJjaYear']);
        $this->assertSame('LK', $data['chartDetails']['jja'][2][0]['roadmapJjaRank']);
    }
}
