<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CommunityDevelopment\CommunityDevelopmentDashboard;
use App\Services\CommunityDevelopment\CommunityPartnerWorkbook;
use App\Services\CommunityDevelopment\PartnerWorkbookReader;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\Support\CreatesWorkbook;
use Tests\TestCase;
use ZipArchive;

class CommunityPartnerImportTest extends TestCase
{
    use CreatesWorkbook;

    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('SQLite required.');
        }
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        (require database_path('migrations/2026_10_27_000000_create_community_partner_imports.php'))->up();
    }

    protected function tearDown(): void
    {
        $this->removeWorkbooks();
        parent::tearDown();
    }

    private function workbook(bool $binary = false, ?array $rows = null): string
    {
        $sheets = ['Mitra' => array_merge([['DATA UMKM'], ['Nama Usaha', 'Area', 'CLUSTER', 'SKALA', 'Tahun Bergabung', 'Nama Lengkap Pemilik UMKM', 'Alamat Sesuai KTP']],
            $rows ?? [['Usaha A', 'Kabupaten Malang', 'f&b', 'mikro', 2020, 'PRIVATE OWNER', 'PRIVATE ADDRESS'],
                ['Yayasan B', 'Kota Malang', 'NON UMKM', '', '', 'PRIVATE OWNER', 'PRIVATE ADDRESS'],
                ['Usaha C', 'Kediri', 'Kriya', 'Kecil', 2021, '', '']])];

        return $binary ? $this->makeXlsbWorkbook($sheets) : $this->makeWorkbook($sheets);
    }

    private function payload(string $path, int $year = 2025, string $extension = 'xlsx'): array
    {
        $preview = app(CommunityPartnerWorkbook::class)->preview($path);

        return ['partners' => new UploadedFile($path, 'mitra.'.$extension, test: true), 'data_year' => $year,
            'sheet' => $preview['sheet'], 'header_row' => $preview['header_row'], 'column_mapping' => json_encode($preview['suggested_mapping'])];
    }

    private function import(string $path, int $year = 2025): array
    {
        $preview = app(CommunityPartnerWorkbook::class)->preview($path);

        return app(CommunityPartnerWorkbook::class)->import($path, $year, $preview['sheet'], $preview['header_row'], $preview['suggested_mapping'], 'mitra.xlsx');
    }

    public function test_both_endpoints_require_authentication(): void
    {
        foreach (['preview', 'upload'] as $action) {
            $this->postJson(route('importpkm.partners.'.$action))->assertUnauthorized();
        }
        $this->assertDatabaseCount('community_partner_imports', 0);
    }

    public function test_xlsx_preview_detects_headers_and_supports_sheet_and_header_selection(): void
    {
        $this->actingAs(new User);
        $path = $this->makeWorkbook(['Cover' => [], 'Mitra' => [['Judul'], ['Nama Usaha', 'Cluster', 'Skala', 'Area'], ['A', 'Kriya', 'Mikro', 'Kediri']]]);
        $this->postJson(route('importpkm.partners.preview'), ['partners' => new UploadedFile($path, 'mitra.xlsx', test: true)])
            ->assertOk()->assertJsonPath('sheets.1', 'Mitra')->assertJsonPath('headers', []);
        $this->postJson(route('importpkm.partners.preview'), ['partners' => new UploadedFile($path, 'mitra.xlsx', test: true), 'sheet' => 'Mitra'])
            ->assertOk()->assertJsonPath('header_row', 2)->assertJsonPath('suggested_mapping.name', 0)->assertJsonPath('preview_data.0.0', 'A');
        $this->postJson(route('importpkm.partners.preview'), ['partners' => new UploadedFile($path, 'mitra.xlsx', test: true), 'sheet' => 'Mitra', 'header_row' => 999])
            ->assertUnprocessable()->assertJsonValidationErrors('header_row');
        $preview = app(CommunityPartnerWorkbook::class)->preview($this->workbook(), 'Mitra', 2);
        $this->assertSame(['name' => 0, 'cluster' => 2, 'scale' => 3, 'area' => 1, 'joined_year' => 4], $preview['suggested_mapping']);
    }

    public function test_xlsx_and_xlsb_uploads_produce_the_same_dashboard_figures(): void
    {
        $this->actingAs(new User);
        foreach (['xlsx', 'xlsb'] as $extension) {
            $payload = $this->payload($this->workbook($extension === 'xlsb'), extension: $extension);
            $this->postJson(route('importpkm.partners.upload'), $payload)->assertOk()
                ->assertJsonPath('summary.selected', 3)->assertJsonPath('summary.replaced', $extension === 'xlsb');
            $data = app(CommunityDevelopmentDashboard::class)->data();
            $this->assertSame(2025, $data['selectedPartnerYear']);
            $this->assertSame([1, 1, 1], array_column($data['areas'], 'count'));
            $this->assertSame(['Mikro', 'Lainnya', 'Kecil'], $data['scales']);
            $this->assertSame(['F&B', 'NON UMKM', 'Kriya'], $data['clusters']);
            $this->assertDatabaseCount('community_partners', 3);
        }
        $this->assertDatabaseCount('community_partner_imports', 1);
        $this->assertSame(2020, DB::table('community_partners')->where('name', 'Usaha A')->value('joined_year'));
    }

    public function test_import_uses_confirmed_mapping_and_rejects_invalid_mappings_before_replacement(): void
    {
        $this->actingAs(new User);
        $this->import($this->workbook());
        $payload = $this->payload($this->workbook());
        $payload['column_mapping'] = json_encode(['name' => 0, 'area' => 1, 'cluster' => 2, 'scale' => 3]);
        $this->postJson(route('importpkm.partners.upload'), $payload)->assertOk();
        $this->assertNull(DB::table('community_partners')->first()->joined_year);
        $before = DB::table('community_partners')->get();
        foreach ([['name' => 0, 'area' => 1, 'cluster' => 2], ['name' => 0, 'area' => 0, 'cluster' => 2, 'scale' => 3],
            ['name' => 77, 'area' => 1, 'cluster' => 2, 'scale' => 3], ['name' => 'no', 'area' => 1, 'cluster' => 2, 'scale' => 3]] as $mapping) {
            $payload['column_mapping'] = json_encode($mapping);
            $this->postJson(route('importpkm.partners.upload'), $payload)->assertUnprocessable()->assertJsonValidationErrors('column_mapping');
            $this->assertEquals($before, DB::table('community_partners')->get());
        }
    }

    public function test_year_snapshots_are_independent_and_public_filters_keep_both_periods(): void
    {
        (require database_path('migrations/2026_10_23_000000_create_community_development_imports.php'))->up();
        $id = DB::table('community_development_imports')->insertGetId(['year_start' => 2025, 'semester' => 'Genap', 'filename' => 'pkm.xlsx', 'column_mapping' => '{}', 'header_rows' => '{}', 'created_at' => now(), 'updated_at' => now()]);
        $this->import($this->workbook(), 2025);
        $this->import($this->workbook(rows: [['New A', 'Kota Malang', 'Batik', 'Menengah', '', '', '']]), 2026);
        $this->assertDatabaseCount('community_partner_imports', 2);
        $this->assertCount(1, app(CommunityDevelopmentDashboard::class)->data()['partners']);
        $this->get('/dashboard-comdev?partner_year=2025&community_period='.$id)->assertOk()
            ->assertSee('name="partner_year" value="2025"', false)->assertSee('name="community_period" value="'.$id.'"', false)
            ->assertViewHas('communityDevelopment', fn ($data) => count($data['partners']) === 3 && $data['selectedPeriod'] === $id)
            ->assertDontSee('PRIVATE OWNER')->assertDontSee('PRIVATE ADDRESS');
        $this->get('/dashboard-comdev?partner_year=2024')->assertOk()->assertSee('Belum ada data mitra binaan untuk tahun 2024')
            ->assertViewHas('communityDevelopment', fn ($data) => $data['partners'] === [] && array_sum(array_column($data['areas'], 'count')) === 0);
    }

    public function test_invalid_rows_and_database_failures_leave_the_previous_snapshot_intact(): void
    {
        $this->import($this->workbook());
        $before = DB::table('community_partners')->get();
        try {
            $this->import($this->workbook(rows: [['', 'Kab. Malang', 'Kriya', 'Mikro', '', '', '']]));
            $this->fail('Invalid row should fail.');
        } catch (ValidationException) {
            $this->assertEquals($before, DB::table('community_partners')->get());
        }
        DB::unprepared("CREATE TRIGGER reject_partner BEFORE INSERT ON community_partners BEGIN SELECT RAISE(ABORT, 'database failure'); END");
        try {
            $this->import($this->workbook(rows: [['New A', 'Kota Malang', 'Batik', 'Menengah', '', '', '']]));
            $this->fail('Insert must fail.');
        } catch (\Illuminate\Database\QueryException) {
            $this->assertEquals($before, DB::table('community_partners')->get());
            $this->assertSame(3, DB::table('community_partner_imports')->sole()->partner_count);
        }
    }

    public function test_force_import_reports_skipped_rows_and_exact_duplicates(): void
    {
        config(['imports.force.pkm' => true]);
        $a = ['A', 'Kab. Malang', 'Kriya', 'Mikro', '', '', ''];
        $summary = $this->import($this->workbook(rows: [$a, $a, ['', 'Kediri', 'Kriya', '', '', '', ''], ['A', 'Kota Malang', 'Kriya', '', '', '', '']]));
        $this->assertSame(2, $summary['selected']);
        $this->assertSame(1, $summary['duplicates']);
        $this->assertSame(1, $summary['skipped_rows']);
        $this->assertNotEmpty($summary['warnings']);
        $this->assertSame('Belum diketahui', DB::table('community_partners')->where('area', 'Kota Malang')->value('scale'));
    }

    public function test_invalid_files_and_upload_parameters_return_validation_errors(): void
    {
        $this->actingAs(new User);
        $file = UploadedFile::fake()->createWithContent('broken.xlsb', 'not an excel workbook');
        $this->postJson(route('importpkm.partners.preview'), ['partners' => $file])->assertUnprocessable()->assertJsonValidationErrors('partners');
        $this->postJson(route('importpkm.partners.preview'), ['partners' => UploadedFile::fake()->createWithContent('data.php', 'bad')])->assertUnprocessable();
        $payload = $this->payload($this->workbook());
        $payload['data_year'] = 1999;
        $this->postJson(route('importpkm.partners.upload'), $payload)->assertUnprocessable()->assertJsonValidationErrors('data_year');
        $payload['data_year'] = 2025;
        $payload['column_mapping'] = 'null';
        $this->postJson(route('importpkm.partners.upload'), $payload)->assertUnprocessable()->assertJsonValidationErrors('column_mapping');
        $this->assertDatabaseCount('community_partner_imports', 0);
    }

    public function test_binary_cell_types_sparse_columns_and_cached_formula_results_are_read(): void
    {
        $wide = fn ($value) => pack('V', mb_strlen($value)).mb_convert_encoding($value, 'UTF-16LE', 'UTF-8');
        $path = $this->makeXlsbWorkbook(['Δ data' => [[
            0 => ['type' => 6, 'payload' => $wide('Usaha 日本')],
            2 => ['type' => 2, 'payload' => pack('V', (2020 << 2) | 2)],
            3 => ['type' => 2, 'payload' => pack('V', ((-500 << 2) & 0xFFFFFFFF) | 3)],
            4 => ['type' => 9, 'payload' => pack('e', 2025.0)],
            5 => ['type' => 8, 'payload' => $wide('Mikro')],
            6 => ['type' => 4, 'payload' => "\1"],
            7 => ['type' => 62, 'payload' => "\0".$wide('Rich text')],
        ]]]);
        $rows = app(PartnerWorkbookReader::class)->read($path)['rows'];
        $this->assertSame(['Usaha 日本', '', '2020', '-5', '2025', 'Mikro', '1', 'Rich text'], $rows[1]);
        $zip = new ZipArchive;
        $zip->open($path);
        $zip->addFromString('xl/worksheets/sheet1.bin', "\0\x7fshort");
        $zip->close();
        $this->expectException(ValidationException::class);
        app(PartnerWorkbookReader::class)->read($path);
    }

    public function test_migration_cascades_partner_rows_when_a_snapshot_is_deleted(): void
    {
        $this->import($this->workbook());
        DB::table('community_partner_imports')->delete();
        $this->assertDatabaseCount('community_partners', 0);
    }

    public function test_import_page_has_a_separate_partner_form_with_mapping_and_xlsb_support(): void
    {
        $html = base64_decode($this->get(route('importpkm.index'))->assertOk()->json('page'));
        foreach (['partnerImportForm', 'partnerSheet', 'partnerHeaderRow', 'partnerMappingConfirm', '.xlsx,.xlsb', route('community-dashboard')] as $text) {
            $this->assertStringContainsString($text, $html);
        }
    }
}
