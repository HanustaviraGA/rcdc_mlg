<?php

namespace Tests\Feature;

use App\Services\Lecturers\LecturerWorkbook;
use App\Services\LrcDashboard\LrcDashboard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
                'jenis_registrasi' => 'NUPTK', 'nomor_nidn_nupn' => '123', 'agama' => 'Islam', 'nama_gugus_binaan' => 'Computer Science'],
            'D2' => ['pendidikan' => 'S2', 'jja' => 'Asisten Ahli', 'usia' => '30', 'agama' => 'Kristen'],
            'D3' => ['pendidikan' => 'S2', 'jja' => 'L200', 'usia' => '30', 'agama' => 'Islam'],
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
        $this->assertSame([[], ['D1'], ['D3'], ['D2'], []], $codes('jja'));
        $this->assertSame([['D2', 'D3'], ['D1'], [], [], [], []], $codes('usia'));
        $this->assertSame([['D1'], ['D2', 'D3', 'D4']], $codes('serdos'));
        $this->assertSame([['D1'], ['D2', 'D3', 'D4']], $codes('nuptk'));
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
}
