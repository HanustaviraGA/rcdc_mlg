<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ColumnMapping\ColumnMapperService;
use App\Services\CommunityDevelopment\CommunityDevelopmentDashboard;
use App\Services\CommunityDevelopment\CommunityDevelopmentWorkbook;
use App\Services\Publications\PublicationPerformanceDashboard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\Support\CreatesWorkbook;
use Tests\TestCase;

class CommunityDevelopmentImportTest extends TestCase
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
            $table->string('nama_dosen');
            $table->string('nama_gugus_binaan')->nullable();
        });
        DB::table('database_dosen_new')->insert([
            ['kode_dosen' => 'D1', 'nama_dosen' => 'Name from Data Center', 'nama_gugus_binaan' => 'Entrepreneurship'],
            ['kode_dosen' => 'D2', 'nama_dosen' => 'Computer Science lecturer', 'nama_gugus_binaan' => 'Computer Science'],
            ['kode_dosen' => 'D3', 'nama_dosen' => 'English lecturer', 'nama_gugus_binaan' => 'English Literature'],
        ]);
        (require database_path('migrations/2026_10_23_000000_create_community_development_imports.php'))->up();
    }

    protected function tearDown(): void
    {
        $this->removeWorkbooks();
        $this->travelBack();
        parent::tearDown();
    }

    private function row(array $changes = []): array
    {
        return array_replace(['WRONG PRODI FROM WORKBOOK', 'D1', 'WRONG NAME FROM WORKBOOK', 'FM', 'AA', 'Functional', 99, 1, 5, 'Sudah PkM'], $changes);
    }

    private function workbook(?array $lecturers = null, ?array $ratios = null): string
    {
        return $this->makeWorkbook([
            'Keterlibatan' => array_merge([['Jurusan Binaan', 'Kode Dosen', 'Nama Dosen', 'Nama Tipe Dosen', 'JJA', 'Faculty Type', 'Jumlah kegiatan pkm dari semester ini', 'Jumlah kegiatan yang dilaporkan ke binusmaya', 'Skor KPI comdev FM', 'Keterangan : Minimal 1 Kegiatan PkM']], $lecturers ?? [$this->row(), $this->row([1 => 'D2', 7 => 0, 8 => 0, 9 => 'Belum PkM'])]),
            'Rasio' => array_merge([['PRODI', 'JUMLAH FM', 'JUMLAH KEGIATAN', 'RASIO']], $ratios ?? [['BINUS @MALANG', 2, 10, '5.0'], ['EBC', 1, 8, '8.0'], ['CS', 1, 2, '2.0']]),
        ]);
    }

    private function payload(string $path, string $year = '2025/2026', string $semester = 'Ganjil'): array
    {
        $preview = app(CommunityDevelopmentWorkbook::class)->preview($path);

        return ['pkm' => new UploadedFile($path, 'comdev.xlsx', null, null, true), 'academic_year' => $year, 'period' => $semester,
            'column_mapping' => json_encode(array_map(fn ($sheet) => $sheet['suggested_mapping'], $preview['sheets'])),
            'header_rows' => json_encode(array_map(fn ($sheet) => $sheet['header_row'], $preview['sheets']))];
    }

    private function import(string $path, string $year = '2025/2026', string $semester = 'Ganjil', string $filename = 'comdev.xlsx'): array
    {
        $data = $this->payload($path, $year, $semester);

        return app(CommunityDevelopmentWorkbook::class)->import($path, $year, $semester, json_decode($data['column_mapping'], true), json_decode($data['header_rows'], true), $filename);
    }

    public function test_preview_and_upload_require_authentication(): void
    {
        $data = $this->payload($this->workbook());
        $this->postJson(route('importpkm.preview'), $data)->assertUnauthorized();
        $this->postJson(route('importpkm.upload'), $data)->assertUnauthorized();
        $this->assertSame(0, DB::table('community_development_imports')->count());
    }

    public function test_preview_maps_column_h_and_ignores_profile_columns(): void
    {
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $this->postJson(route('importpkm.preview'), $this->payload($this->workbook()))->assertOk()
            ->assertJsonPath('sheets.rasio.suggested_mapping.program', 0)
            ->assertJsonPath('sheets.keterlibatan.suggested_mapping.kode_dosen', 1)
            ->assertJsonPath('sheets.keterlibatan.suggested_mapping.reported_activities', 7)
            ->assertJsonPath('sheets.keterlibatan.suggested_mapping.score', 8)
            ->assertJsonMissingPath('sheets.keterlibatan.mapping_config.required.nama_dosen');
        $this->assertSame(0, DB::table('community_development_imports')->count());
    }

    public function test_upload_persists_both_sheets_and_uses_only_code_for_dosen_profiles(): void
    {
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $before = DB::table('database_dosen_new')->get();
        $this->postJson(route('importpkm.upload'), $this->payload($this->workbook()))->assertOk()
            ->assertJsonPath('summary.period_label', '2025/2026 Ganjil')->assertJsonPath('summary.matched', 2)
            ->assertJsonPath('summary.programs', 2)->assertJsonPath('summary.replaced', false);
        $this->assertSame(2025, DB::table('community_development_imports')->sole()->year_start);
        $this->assertSame(1, DB::table('community_development_lecturers')->where('kode_dosen', 'D1')->sole()->reported_activities);
        $this->assertSame(0, DB::table('community_development_lecturers')->where('kode_dosen', 'D2')->sole()->reported_activities);
        $this->assertFalse(Schema::hasColumn('community_development_lecturers', 'nama_dosen'));
        $this->assertEquals($before, DB::table('database_dosen_new')->get());
        $data = app(CommunityDevelopmentDashboard::class)->data();
        $this->assertFalse($data['isDemo']);
        $this->assertSame(['faculty' => 2, 'activities' => 10, 'ratio' => 5.0], $data['totals']);
        $ebc = collect($data['programs'])->firstWhere('key', 'ebc');
        $this->assertSame(8, $ebc['activities']);
        $this->assertSame(1, $ebc['lecturers'][0]['activities']);
        $this->assertSame('Name from Data Center', $ebc['lecturers'][0]['name']);
        $this->assertSame('EBC', $ebc['code']);
    }

    public function test_reupload_replaces_only_the_same_academic_year_and_semester(): void
    {
        $original = $this->import($this->workbook());
        $other = $this->import($this->workbook(), semester: 'Genap');
        $future = $this->import($this->workbook(), '2026/2027');
        $replacement = $this->import($this->workbook([$this->row([1 => 'D2', 7 => 4, 8 => 6])]), filename: 'replacement.xlsx');
        $this->assertTrue($replacement['replaced']);
        $this->assertSame($original['import_id'], $replacement['import_id']);
        $this->assertSame(3, DB::table('community_development_imports')->count());
        $this->assertSame(['D2'], DB::table('community_development_lecturers')->where('import_id', $original['import_id'])->pluck('kode_dosen')->all());
        foreach ([$other, $future] as $period) {
            $this->assertSame(2, DB::table('community_development_lecturers')->where('import_id', $period['import_id'])->count());
        }
        $this->assertSame('2026/2027 Ganjil', app(CommunityDevelopmentDashboard::class)->data()['periodLabel']);
        $this->assertSame('2025/2026 Genap', app(CommunityDevelopmentDashboard::class)->data($other['import_id'])['periodLabel']);
    }

    public function test_periods_must_be_consecutive_academic_years_and_known_semesters(): void
    {
        $this->actingAs((new User)->forceFill(['id' => 1]));
        foreach (['2025', '2025/2027', '2026/2025', '2025-2026'] as $year) {
            $this->postJson(route('importpkm.upload'), $this->payload($this->workbook(), $year))->assertUnprocessable()->assertJsonValidationErrors('academic_year');
        }
        $this->postJson(route('importpkm.upload'), $this->payload($this->workbook(), semester: 'Quarter 1'))->assertUnprocessable()->assertJsonValidationErrors('period');
        $this->assertSame(0, DB::table('community_development_imports')->count());
    }

    public function test_required_and_duplicate_column_mappings_are_rejected_even_with_force(): void
    {
        $this->actingAs((new User)->forceFill(['id' => 1]));
        config(['imports.force.pkm' => true]);
        foreach ([null, 1, 999, -1] as $index) {
            $payload = $this->payload($this->workbook());
            $mapping = json_decode($payload['column_mapping'], true);
            $mapping['keterlibatan']['reported_activities'] = $index;
            $payload['column_mapping'] = json_encode($mapping);
            $this->postJson(route('importpkm.upload'), $payload)->assertUnprocessable()->assertJsonValidationErrors('column_mapping.keterlibatan');
        }
        $this->assertSame(0, DB::table('community_development_imports')->count());
    }

    public function test_confirmed_mapping_is_authoritative_when_headers_and_columns_move(): void
    {
        $path = $this->makeWorkbook([
            'Rasio' => [['Title line'], ['My activities', 'My program', 'My faculty'], [3, 'EBC', 1]],
            'Keterlibatan' => [['Title line'], ['ID', 'Reported', 'Other activities', 'Grade'], ['D1', 2, 999, 6]],
        ]);
        $mapping = ['rasio' => ['program' => 1, 'faculty' => 2, 'activities' => 0], 'keterlibatan' => ['kode_dosen' => 0, 'reported_activities' => 1, 'score' => 3]];
        $summary = app(CommunityDevelopmentWorkbook::class)->import($path, '2025/2026', 'Ganjil', $mapping, ['rasio' => 2, 'keterlibatan' => 2], 'moved.xlsx');
        $this->assertSame(1, $summary['selected']);
        $this->assertSame(2, DB::table('community_development_lecturers')->sole()->reported_activities);
        $this->assertNull(DB::table('community_development_lecturers')->sole()->status);
        $this->assertSame(3, app(CommunityDevelopmentDashboard::class)->data()['totals']['activities']);
    }

    public function test_required_column_configuration_comes_only_from_column_mapper(): void
    {
        $mapper = new class extends ColumnMapperService
        {
            public function getMappingConfig(string $importType, ?int $year = null): array
            {
                $config = parent::getMappingConfig($importType, $year);
                if ($importType === 'pkm_involvement') {
                    $config['required']['score'] = $config['optional']['score'];
                    unset($config['optional']['score']);
                }

                return $config;
            }
        };
        $this->app->instance(ColumnMapperService::class, $mapper);
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $payload = $this->payload($this->workbook());
        $mapping = json_decode($payload['column_mapping'], true);
        unset($mapping['keterlibatan']['score']);
        $payload['column_mapping'] = json_encode($mapping);
        $this->postJson(route('importpkm.upload'), $payload)->assertUnprocessable()->assertJsonValidationErrors('column_mapping.keterlibatan');
    }

    public function test_strict_validation_failure_preserves_the_previous_period(): void
    {
        $this->import($this->workbook(), filename: 'original.xlsx');
        $before = DB::table('community_development_lecturers')->get();
        foreach (['bad', '-1', '1.5', ''] as $invalid) {
            try {
                $this->import($this->workbook([$this->row([7 => $invalid])]), filename: 'bad.xlsx');
                $this->fail('Invalid required activity counts must fail.');
            } catch (ValidationException $exception) {
                $this->assertStringContainsString('Sheet Keterlibatan', implode(' ', $exception->errors()['pkm']));
                $this->assertEquals($before, DB::table('community_development_lecturers')->get());
                $this->assertSame('original.xlsx', DB::table('community_development_imports')->sole()->filename);
            }
        }
    }

    public function test_force_nulls_invalid_values_and_skips_missing_or_conflicting_codes(): void
    {
        config(['imports.force.pkm' => true]);
        $summary = $this->import($this->workbook([$this->row([7 => 'bad', 8 => 8]), $this->row([1 => '']), $this->row([7 => 4])]));
        $this->assertTrue($summary['force_import']);
        $this->assertSame(2, $summary['skipped_rows']);
        $this->assertSame(1, $summary['selected']);
        $this->assertNotEmpty($summary['warnings']);
        $row = DB::table('community_development_lecturers')->sole();
        $this->assertNull($row->reported_activities);
        $this->assertNull($row->score);
        $data = app(CommunityDevelopmentDashboard::class)->data();
        $this->assertNull($data['programs'][0]['lecturers'][0]['activities']);
    }

    public function test_an_empty_or_missing_sheet_never_erases_previous_records(): void
    {
        $this->import($this->workbook());
        $before = DB::table('community_development_lecturers')->get();
        config(['imports.force.pkm' => true]);
        foreach ([$this->workbook([]), $this->makeWorkbook(['Rasio' => [['PRODI', 'JUMLAH FM', 'JUMLAH KEGIATAN'], ['EBC', 1, 3]]])] as $path) {
            try {
                $this->import($path);
                $this->fail('Both sheets need usable data.');
            } catch (ValidationException) {
                $this->assertEquals($before, DB::table('community_development_lecturers')->get());
            }
        }
    }

    public function test_database_failure_rolls_back_both_sheets_and_batch_metadata(): void
    {
        $this->import($this->workbook(), filename: 'original.xlsx');
        $before = DB::table('community_development_programs')->get();
        DB::unprepared("CREATE TRIGGER reject_comdev BEFORE INSERT ON community_development_lecturers BEGIN SELECT RAISE(ABORT, 'database failure'); END");
        try {
            $this->import($this->workbook([$this->row([7 => 4])]), filename: 'replacement.xlsx');
            $this->fail('Failed writes must roll back.');
        } catch (\Illuminate\Database\QueryException) {
            $this->assertEquals($before, DB::table('community_development_programs')->get());
            $this->assertSame('original.xlsx', DB::table('community_development_imports')->sole()->filename);
            $this->assertSame(2, DB::table('community_development_lecturers')->count());
        }
    }

    public function test_unmatched_codes_are_retained_and_join_automatically_after_dosen_upload(): void
    {
        $summary = $this->import($this->workbook([$this->row([1 => 'NEW']), $this->row([1 => 'D3'])], [['DLLC', 2, 5, 2.5]]));
        $this->assertSame(['NEW'], $summary['unmatched_codes']);
        $data = app(CommunityDevelopmentDashboard::class)->data();
        $this->assertSame('English lecturer', $data['programs'][0]['lecturers'][0]['name']);
        $this->assertSame('NEW', $data['unassignedLecturers'][0]['code']);
        DB::table('database_dosen_new')->insert(['kode_dosen' => 'NEW', 'nama_dosen' => 'New Data Center profile', 'nama_gugus_binaan' => 'Chinese Literature']);
        $data = app(CommunityDevelopmentDashboard::class)->data();
        $this->assertSame([], $data['unassignedLecturers']);
        $this->assertCount(2, $data['programs'][0]['lecturers']);
        $this->assertSame('New Data Center profile', collect($data['programs'][0]['lecturers'])->firstWhere('code', 'NEW')['name']);
    }

    public function test_public_dashboard_uses_uploaded_periods_and_separately_labels_dummy_partners(): void
    {
        $this->import($this->workbook(), filename: 'real-report.xlsx');
        $this->mock(PublicationPerformanceDashboard::class)->shouldReceive('data')->andReturn(['defaultYear' => 2026, 'years' => ['2026'], 'snapshots' => [], 'programs' => []]);
        $this->get('/kpi-publikasi')->assertOk()->assertSee('2025/2026 Ganjil')->assertSee('real-report.xlsx')
            ->assertSee('id="communityPeriod"', false)->assertSee('Data mitra dan area masih berupa contoh.')
            ->assertDontSee('Data contoh untuk pratinjau.')->assertDontSee('WRONG NAME FROM WORKBOOK')
            ->assertViewHas('communityDevelopment', fn ($data) => ! $data['isDemo'] && $data['programs'][0]['lecturers'][0]['activities'] === 1);
        $this->get('/kpi-publikasi?community_period=999')->assertOk()->assertSee('Belum ada data Community Development untuk periode ini.');
    }

    public function test_module_offers_academic_year_options_and_mapping_for_both_sheets(): void
    {
        $response = $this->get(route('importpkm.index'))->assertOk();
        $html = base64_decode($response->json('page'));
        $this->assertStringContainsString('2025/2026', $html);
        $this->assertStringContainsString('Pemetaan sheet Rasio', $html);
        $this->assertStringContainsString('Pemetaan sheet Keterlibatan', $html);
        $this->assertStringContainsString('value="Ganjil"', $html);
        $this->assertStringContainsString('value="Genap"', $html);
    }
}
