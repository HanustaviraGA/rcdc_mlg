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
        (require base_path('Modules/RoadmapLRC/database/migrations/2026_11_15_000000_create_prodi_fm_roadmaps_table.php'))->up();
        $this->seed(RoadmapLRCDatabaseSeeder::class);
        $page = $this->get(route('lrc-dashboard'))->assertOk()->getContent();

        // Right below the Jenjang pendidikan dosen chart, before the next section.
        $education = strpos($page, 'data-lrc-chart-card="pendidikan"');
        $this->assertTrue($education < strpos($page, 'id="lrcProjectionHeading"') && strpos($page, 'id="lrcProjectionHeading"') < strpos($page, 'id="lrcProfileHeading"'));
        $this->assertStringContainsString('Seluruh prodi (9).', $page);
        preg_match_all('/data-lrc-projection="(\d{4})">.*?data-lrc-projection-s3>([^<]+)<.*?data-lrc-projection-fm>([^<]+)<.*?data-lrc-projection-percent>([^<]+)</s', $page, $boxes, PREG_SET_ORDER);
        $this->assertSame([
            ['2026', '16', '117', '13,7%'], ['2027', '35', '120', '29,2%'], ['2028', '44', '122', '36,1%'], ['2029', '47', '125', '37,6%'],
            ['2030', '56', '129', '43,4%'], ['2031', '66', '132', '50,0%'], ['2032', '99', '134', '73,9%'], ['2033', '112', '137', '81,8%'],
            ['2034', '130', '139', '93,5%'], ['2035', '133', '141', '94,3%'],
        ], array_map(fn ($box) => array_slice($box, 1), $boxes));
        $this->assertStringContainsString('<span class="visually-hidden">16 dari 117 FM bergelar S3, 13,7%</span>', $page);

        // The roadmap is per program: Fakultas, Gugus Binaan and Program narrow it to the programs of their lecturers.
        DB::table('database_dosen_new')->where('kode_dosen', 'D1')->update(['nama_gugus_binaan' => 'Computer Science', 'nama_program' => 'Informatics', 'status' => 'Aktif']);
        DB::table('database_dosen_new')->where('kode_dosen', 'D2')->update(['nama_gugus_binaan' => 'Entrepreneurship', 'status' => 'Aktif']);
        $dashboard = app(LrcDashboard::class);
        foreach ([['fakultas' => 'SOCS'], ['gugusBinaan' => 'Computer Science'], ['program' => 'Informatics']] as $filter) {
            $projection = $dashboard->data($filter)['projection'];
            $this->assertSame(['CS'], $projection['prodi']);
            $this->assertSame(['year' => 2026, 's3' => 3, 'fm' => 21, 'percent' => 14.3], $projection['years'][0]);
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

    public function test_projection_is_empty_without_the_roadmap_table(): void
    {
        $page = $this->get(route('lrc-dashboard'))->assertOk()->getContent();
        $this->assertStringContainsString('Belum ada roadmap untuk prodi pada filter ini.', $page);
        $this->assertStringNotContainsString('lrc-projection-list', $page);
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
