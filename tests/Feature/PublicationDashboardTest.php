<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Publications\PublicationDashboard;
use App\Services\Publications\PublicationImporter;
use App\Services\Publications\PublicationScore;
use App\Services\Publications\RawPublicationReader;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use ZipArchive;

class PublicationDashboardTest extends TestCase
{
    private array $files = [];

    private const HEADER = ['RequestCode', 'Author', 'FM Author', 'Kode Dosen', 'JJA', 'Pendidikan', 'Type', 'S/F', 'Dept', 'Kampus', 'First Author', 'Skema', 'Bobot', 'Submitted', 'St2026', 'Jenis', 'Tipe Publikasi', 'Title ', 'Scopus Year', 'Tanggal Pelaporan', 'Source title', 'Quartile Jurnal', 'Notes', 'Prodi KPI'];

    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Run with php -d extension=pdo_sqlite vendor/bin/phpunit.');
        }
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Schema::create('database_dosen', function (Blueprint $table) {
            $table->string('kode_dosen')->primary();
            foreach (['nama_dosen', 'pendidikan_dosen', 'jurusan_dosen', 'jja_dosen', 'ft_dosen'] as $field) {
                $table->string($field)->nullable();
            }
        });
        Schema::create('rectorate_dosen', function (Blueprint $table) {
            $table->string('id_rectorate')->primary();
            foreach (['request_code', 'author', 'fm_author', 'kode_dosen', 'jja', 'pendidikan', 'type', 's_f', 'dept', 'kampus', 'first_author', 'sumber_paper', 'submitted', 'status', 'jenis', 'tipe_publikasi', 'title', 'scopus_year', 'source_title', 'publisher', 'quartile_jurnal', 'year', 'period', 'month'] as $field) {
                $table->string($field)->nullable();
            }
            $table->double('bobot')->nullable();
            $table->double('bobot_asli')->nullable();
            $table->timestamps();
        });
        (require database_path('migrations/2026_09_08_000000_add_publication_import_tracking.php'))->up();
        (require database_path('migrations/2026_09_08_000100_create_publication_planning_tables.php'))->up();
        DB::table('database_dosen')->insert([
            ['kode_dosen' => 'D001', 'nama_dosen' => 'Test Lecturer', 'pendidikan_dosen' => 'S2', 'jurusan_dosen' => 'CS', 'jja_dosen' => 'L200', 'ft_dosen' => 'Functional'],
            ['kode_dosen' => 'D002', 'nama_dosen' => 'No Report', 'pendidikan_dosen' => 'S2', 'jurusan_dosen' => 'CS', 'jja_dosen' => 'L200', 'ft_dosen' => 'Functional'],
        ]);
    }

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        parent::tearDown();
    }

    public function test_raw_sheet_uses_header_names_and_filters_both_categories(): void
    {
        $header = array_reverse(self::HEADER);
        $rows = [$this->row(), $this->row(['Submitted' => 'Non Scopus FM', 'RequestCode' => 'R2', 'Tipe Publikasi' => 'Nscopus']),
            $this->row(['Kampus' => 'JAKARTA']), $this->row(['Submitted' => 'Scopus Mahasiswa'])];
        $file = $this->workbook(['Summary' => [self::HEADER, array_values($this->row())], 'Raw' => [$header, ...array_map(fn ($r) => array_values(array_reverse($r, true)), $rows)]]);
        $parsed = app(RawPublicationReader::class)->read($file, 2026);
        $this->assertCount(2, $parsed['records']);
        $this->assertSame(4, $parsed['summary']['read']);
        $this->assertSame(1, $parsed['summary']['excluded_campus']);
        $this->assertSame(1, $parsed['summary']['excluded_submitted']);
        $record = $parsed['records'][0];
        $this->assertSame('Correct source', $record['source_title']);
        $this->assertSame('Q1', $record['quartile_jurnal']);
        $this->assertSame(3.0, $record['bobot']);
        $this->assertSame(0.5, $record['bobot_asli']);
        $this->assertSame('2026-01-01', $record['tanggal_pelaporan']);
        $this->assertNull($record['publisher']);
        $this->assertSame(2, $record['source_row']);
    }

    public function test_zero_weight_and_excel_serial_date_are_preserved(): void
    {
        $file = $this->rawFile([$this->row(['Bobot' => '0', 'Tipe Publikasi' => 'Nscopus', 'Submitted' => 'Non Scopus FM', 'Tanggal Pelaporan' => '46023'])]);
        $parsed = app(RawPublicationReader::class)->read($file, 2026);
        $this->assertSame(0.0, $parsed['records'][0]['bobot_asli']);
        $this->assertSame('2026-01-01', $parsed['records'][0]['tanggal_pelaporan']);
    }

    public function test_missing_raw_sheet_does_not_replace_existing_snapshot(): void
    {
        $importer = app(PublicationImporter::class);
        $importer->import($this->rawFile([$this->row()]), 'valid.xlsx', 2026, 8, 3);
        try {
            $importer->import($this->workbook(['Summary' => [self::HEADER, array_values($this->row())]]), 'wrong.xlsx', 2026, 8, 3);
            $this->fail('Missing Raw must be rejected.');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('Sheet Raw', $e->getMessage());
        }
        $this->assertSame(1, DB::table('rectorate_dosen')->count());
        $this->assertSame('valid.xlsx', DB::table('publication_imports')->value('filename'));
    }

    public function test_empty_filter_does_not_erase_snapshot(): void
    {
        $importer = app(PublicationImporter::class);
        $importer->import($this->rawFile([$this->row()]), 'valid.xlsx', 2026, 8, 3);
        try {
            $importer->import($this->rawFile([$this->row(['Submitted' => 'Scopus Mahasiswa'])]), 'students.xlsx', 2026, 8, 3);
            $this->fail('Empty selection must fail.');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('Tidak ada baris', $e->getMessage());
        }
        $this->assertSame(1, DB::table('rectorate_dosen')->count());
    }

    public function test_reimport_is_idempotent_and_other_snapshots_survive(): void
    {
        $importer = app(PublicationImporter::class);
        $file = $this->rawFile([$this->row(), $this->row()]);
        $first = $importer->import($file, 'first.xlsx', 2026, 7, 3);
        $importer->import($file, 'second.xlsx', 2026, 8, 3);
        $again = $importer->import($file, 'again.xlsx', 2026, 7, 3);
        $this->assertSame($first['import_id'], $again['import_id']);
        $this->assertSame(1, $again['duplicates']);
        $this->assertSame(2, DB::table('rectorate_dosen')->count());
        $this->assertSame(2, DB::table('publication_imports')->count());
    }

    public function test_conflicting_duplicate_and_incorrect_status_year_are_rejected(): void
    {
        $reader = app(RawPublicationReader::class);
        try {
            $reader->read($this->rawFile([$this->row(), $this->row(['Title ' => 'Conflicting title'])]), 2026);
            $this->fail('Conflicting duplicate must fail.');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('duplikat', $e->getMessage());
        }
        $this->expectException(ValidationException::class);
        $reader->read($this->rawFile([$this->row()]), 2025);
    }

    public function test_dashboard_uses_latest_snapshot_per_year_and_leaves_absent_scores_null(): void
    {
        $importer = app(PublicationImporter::class);
        $importer->import($this->rawFile([$this->row(['RequestCode' => 'OLD'])]), 'july.xlsx', 2026, 7, 3);
        $importer->import($this->rawFile([$this->row(['RequestCode' => 'NEW'])]), 'august.xlsx', 2026, 8, 3);
        $dashboard = app(PublicationDashboard::class)->data();
        $this->assertSame(['2026'], $dashboard['years']);
        $this->assertCount(1, $dashboard['publications']);
        $this->assertSame('NEW', $dashboard['publications'][0]['request_code']);
        $faculty = collect($dashboard['faculty'])->keyBy('code');
        $this->assertSame(4, $faculty['D001']['annual'][2026]['score']);
        $this->assertNull($faculty['D002']['annual'][2026]['score']);
        $this->assertNull($faculty['D001']['annual'][2026]['cluster']);
    }

    public function test_supplied_publication_collection_does_not_query_other_years(): void
    {
        $score = app(PublicationScore::class);
        DB::enableQueryLog();
        $result = $score->calculate('D001', $score->rule('Functional', 'L', 'S2'), collect([(object) [
            'tipe_publikasi' => 'Scopus', 'jenis' => 'seminar', 'bobot' => 1, 'bobot_asli' => 0.5,
        ]]));
        $this->assertSame(4, $result);
        $this->assertCount(0, DB::getQueryLog());
        $this->assertNull($score->calculate('D001', null, collect()));
        $this->assertSame('LK', $score->rank('Lektor Kepala (700)'));
        $this->assertSame('S3', $score->education('Doktor (S3)'));
        $this->assertNull($score->education('Universitas Bina Nusantara'));
    }

    public function test_dashboard_escapes_script_payload_and_does_not_use_html_sample_facts(): void
    {
        app(PublicationImporter::class)->import($this->rawFile([$this->row(['Title ' => '</script><script>alert(1)</script>'])]), 'safe.xlsx', 2026, 8, 3);
        $this->get(route('publication-dashboard'))->assertOk()
            ->assertSee('Publication Performance Intelligence Dashboard')
            ->assertDontSee('</script><script>alert(1)</script>', false)
            ->assertDontSee('Dashboard visualisasi Analisa Publikasi FM &amp; Mahasiswa.pdf', false);
    }

    public function test_public_dashboard_uses_home_navigation_without_admin_actions_or_login(): void
    {
        $this->assertSame('/kpi-publikasi', route('publication-dashboard', [], false));
        $this->get('/kpi-publikasi')->assertOk()
            ->assertSee('id="header"', false)
            ->assertSee('id="footer"', false)
            ->assertSee('landing/assets/css/main.css', false)
            ->assertSee('href="'.route('research-gallery.index').'"', false)
            ->assertDontSee('Import Excel')
            ->assertDontSee('Import hibah')
            ->assertDontSee('/dashboard/matrixkpidosen')
            ->assertDontSee('change_perms');
        $this->assertGuest();

        $this->get('/dashboard/kpi-publikasi?year=2026')->assertStatus(301)
            ->assertRedirect(route('publication-dashboard', ['year' => 2026]));
    }

    public function test_signed_in_users_keep_access_to_management_links_on_public_dashboard(): void
    {
        $this->actingAs((new User)->forceFill(['id' => 1, 'name' => 'Admin']));
        $this->get(route('publication-dashboard'))->assertOk()
            ->assertSee('Import Excel')->assertSee('Import hibah')->assertSee('/dashboard/matrixkpidosen');
    }

    public function test_planning_records_are_loaded_only_for_the_requested_year(): void
    {
        app(PublicationImporter::class)->import($this->rawFile([$this->row()]), 'data.xlsx', 2026, 8, 3);
        DB::table('kpi_fm_profiles')->insert(['kode_dosen' => 'D001', 'year' => 2026, 'cluster' => 'B', 'mentor_label' => 'MENTOR']);
        DB::table('kpi_research_priorities')->insert([
            ['kode_dosen' => 'D001', 'year' => 2027, 'topic' => 'Verified plan', 'sdgs' => '["SDG 9"]'],
            ['kode_dosen' => 'D001', 'year' => 2026, 'topic' => 'Earlier plan', 'sdgs' => '[]'],
        ]);
        $dashboard = app(PublicationDashboard::class)->data();
        $faculty = collect($dashboard['faculty'])->keyBy('code');
        $this->assertSame('B', $faculty['D001']['annual'][2026]['cluster']);
        $this->assertCount(1, $dashboard['priorities']);
        $this->assertSame('Verified plan', $dashboard['priorities'][0]['topic']);
    }

    public function test_import_transaction_restores_snapshot_if_database_insert_fails(): void
    {
        $importer = app(PublicationImporter::class);
        $importer->import($this->rawFile([$this->row()]), 'original.xlsx', 2026, 8, 3);
        DB::unprepared("CREATE TRIGGER reject_publication BEFORE INSERT ON rectorate_dosen BEGIN SELECT RAISE(ABORT, 'test failure'); END");
        try {
            $importer->import($this->rawFile([$this->row(['RequestCode' => 'NEW'])]), 'replacement.xlsx', 2026, 8, 3);
            $this->fail('Test trigger must reject the replacement.');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertStringContainsString('test failure', $e->getMessage());
        }
        $this->assertSame('R1', DB::table('rectorate_dosen')->value('request_code'));
        $this->assertSame('original.xlsx', DB::table('publication_imports')->value('filename'));
    }

    public function test_dry_run_and_invalid_quarter_do_not_write_data(): void
    {
        $importer = app(PublicationImporter::class);
        $file = $this->rawFile([$this->row()]);
        $summary = $importer->import($file, 'dry.xlsx', 2026, 8, 3, true);
        $this->assertSame(1, $summary['selected']);
        $this->assertSame(0, DB::table('rectorate_dosen')->count());
        $this->assertSame(0, DB::table('publication_imports')->count());
        $this->expectException(ValidationException::class);
        $importer->import($file, 'invalid.xlsx', 2026, 8, 2);
    }

    public function test_profiles_normalize_program_aliases_and_verify_legacy_campus(): void
    {
        Schema::create('database_dosen_new', function (Blueprint $table) {
            $table->string('kode_dosen');
            $table->string('nama_gugus_binaan');
            $table->string('campus');
        });
        DB::table('database_dosen_new')->insert(['kode_dosen' => 'D001', 'nama_gugus_binaan' => 'Computer Science', 'campus' => 'Binus Malang']);
        app(PublicationImporter::class)->import($this->rawFile([$this->row(), $this->row(['Kode Dosen' => 'D002'])]), 'legacy.xlsx', 2026, 8, 3);
        DB::table('rectorate_dosen')->update(['kampus' => null]);
        $dashboard = app(PublicationDashboard::class)->data();
        $this->assertSame(1, $dashboard['snapshots'][2026]['legacy_campus']);
        $this->assertSame(1, $dashboard['snapshots'][2026]['unverified_campus']);
        $this->assertSame(['Computer Science'], collect($dashboard['faculty'])->pluck('program')->unique()->values()->all());
    }

    private function row(array $overrides = []): array
    {
        return array_replace(array_combine(self::HEADER, ['R1', 'Test Author', 'Test Lecturer', 'D001', 'Lektor (200)', 'Pascasarjana (S2)', 'Faculty Member', 'SOCS', 'Computer Science', 'MALANG', 'Y', '2A', '0,5', 'Scopus FM', '3Accepted', 'Jurnal', 'Scopus', 'Test title', '2026', '1-Jan-26', 'Correct source', 'Q1', '', 'Computer Science Malang']), $overrides);
    }

    private function rawFile(array $rows): string
    {
        return $this->workbook(['Raw' => [self::HEADER, ...array_map('array_values', $rows)]]);
    }

    private function workbook(array $sheets): string
    {
        $file = tempnam(sys_get_temp_dir(), 'publication-test-');
        $this->files[] = $file;
        $zip = new ZipArchive;
        $zip->open($file, ZipArchive::OVERWRITE);
        $ns = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
        $relationships = 'http://schemas.openxmlformats.org/package/2006/relationships';
        $document = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
        $zip->addFromString('_rels/.rels', '<Relationships xmlns="'.$relationships.'"><Relationship Id="rId1" Type="'.$document.'/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $workbook = '<workbook xmlns="'.$ns.'" xmlns:r="'.$document.'"><sheets>';
        $rels = '<Relationships xmlns="'.$relationships.'">';
        $i = 0;
        foreach ($sheets as $name => $rows) {
            $i++;
            $workbook .= '<sheet name="'.htmlspecialchars($name, ENT_XML1).'" sheetId="'.$i.'" r:id="rId'.$i.'"/>';
            $rels .= '<Relationship Id="rId'.$i.'" Type="'.$document.'/worksheet" Target="worksheets/sheet'.$i.'.xml"/>';
            $xml = '<worksheet xmlns="'.$ns.'"><sheetData>';
            foreach ($rows as $r => $cells) {
                $xml .= '<row r="'.($r + 1).'">';
                foreach ($cells as $c => $value) {
                    $column = '';
                    for ($n = $c + 1; $n > 0; $n = intdiv($n - 1, 26)) {
                        $column = chr(65 + ($n - 1) % 26).$column;
                    }
                    $xml .= '<c r="'.$column.($r + 1).'" t="inlineStr"><is><t>'.htmlspecialchars((string) $value, ENT_XML1).'</t></is></c>';
                }
                $xml .= '</row>';
            }
            $zip->addFromString('xl/worksheets/sheet'.$i.'.xml', $xml.'</sheetData></worksheet>');
        }
        $zip->addFromString('xl/workbook.xml', $workbook.'</sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', $rels.'</Relationships>');
        $zip->close();

        return $file;
    }
}
