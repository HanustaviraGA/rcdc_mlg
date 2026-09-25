<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Publications\ImportedLecturerKpi;
use App\Services\Research\RectorateResearchImporter;
use App\Services\Research\RectorateResearchReader;
use App\Services\Research\RectorateResearchRepository;
use App\Services\Research\ResearchGallery;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use ZipArchive;

class RectorateResearchTest extends TestCase
{
    private array $files = [];

    public function test_module_upload_returns_summaries_and_monthly_status(): void
    {
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $path = $this->workbook(['MALANG' => [$this->row()]]);
        $file = new UploadedFile($path, 'hibah.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
        $response = $this->postJson(route('importrectorate.upload'), ['research_file' => $file, 'year' => 2026, 'month' => 9])
            ->assertOk()->assertJsonPath('summaries.research_file.selected', 1);
        $this->assertStringContainsString('Hibah berhasil diproses', $response->json('summary_html'));
        $this->assertStringContainsString('hibah.xlsx', $response->json('history_html'));
        $this->assertStringContainsString('2026-09', $response->json('history_html'));
        $this->assertSame(1, DB::table('research_imports')->count());
        $this->postJson(route('importrectorate.upload'), ['year' => 2026, 'month' => 9])
            ->assertUnprocessable()->assertJsonValidationErrors('research_file');
    }

    public function test_admin_kpi_includes_grant_only_lecturers_and_keeps_undated_imports_separate(): void
    {
        $importer = app(RectorateResearchImporter::class);
        $file = $this->workbook(['MALANG' => [
            $this->row(['kode_dosen_nim' => 'D999', 'nama' => 'Grant Only', 'prodi_di_kpi' => 'CS']),
            $this->row(['kode_dosen_nim' => 'D002', 'peran' => 'Anggota 1', 'prodi_di_kpi' => 'DI']),
            $this->row(['kode_dosen_nim' => 'MHS', 'kategori_fm_eksternal_mahasiswa' => 'Mahasiswa']),
        ]]);
        $legacy = $importer->import($file, 'legacy.xlsx', year: 2026, month: 7);
        DB::table('research_imports')->where('id', $legacy['import_id'])->update(['year' => null, 'month' => null]);
        $importer->import($file, 'september.xlsx', year: 2026, month: 9);
        $service = app(ImportedLecturerKpi::class);
        $rows = $service->rows();
        $this->assertCount(4, $rows);
        $chair = $rows->where('month', 9)->firstWhere('code', 'D999');
        $this->assertSame(1, $chair['grant_chair']);
        $this->assertSame(0, $chair['grant_member']);
        $this->assertNull($chair['scopus']);
        $this->assertNull($chair['score']);
        $this->assertSame(1, $rows->where('month', 9)->firstWhere('code', 'D002')['grant_member']);
        $this->assertCount(2, $service->filter($rows, ['year' => 2026, 'month' => 9]));
        $this->assertCount(2, $service->filter($rows, ['search' => 'D999']));
        $this->assertCount(2, $rows->whereNull('month'));
        $this->assertFalse($rows->contains('code', 'MHS'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Run PHP with -d extension=pdo_sqlite.');
        }
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        (require database_path('migrations/2026_09_08_000200_add_rectorate_research_imports.php'))->up();
        (require database_path('migrations/2026_09_24_000000_add_monthly_research_snapshots.php'))->up();
        Schema::create('database_dosen', fn (Blueprint $table) => $table->string('kode_dosen')->primary());
        DB::table('database_dosen')->insert([['kode_dosen' => 'D001'], ['kode_dosen' => 'D002']]);
        Schema::create('researchs', function (Blueprint $table) {
            $table->string('ID')->primary();
            foreach (['kode_dosen', 'title', 'budget_year', 'contract_number', 'abstract', 'source_of_fund', 'funding', 'researcher', 'permalink', 'institution'] as $column) {
                $table->text($column)->nullable();
            }
            $table->timestamps();
        });
        DB::table('researchs')->insert(['ID' => 'SYSTEM1', 'kode_dosen' => 'D001', 'title' => 'Existing system research',
            'budget_year' => '2026', 'contract_number' => 'SYS1', 'funding' => '10000000', 'researcher' => '[]',
            'abstract' => 'Existing abstract', 'permalink' => 'https://example.org/research', 'updated_at' => now()]);
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

    public function test_detail_sheet_header_mapping_and_campus_filter(): void
    {
        $file = $this->workbook([
            'Pivot' => [$this->row()],
            'Detail' => [$this->row(), $this->row(['kode_dosen_nim' => 'D002', 'peran' => 'Anggota 1']),
                $this->row(['lokasi_kampus' => 'BINUS @Bandung']), $this->row(['lokasi_kampus' => '-'])],
        ], true);
        $parsed = app(RectorateResearchReader::class)->read($file);
        $this->assertCount(2, $parsed['records']);
        $this->assertSame(4, $parsed['summary']['read']);
        $this->assertSame(2, $parsed['summary']['excluded_campus']);
        $record = $parsed['records'][0];
        $this->assertSame('0.6', $record['bobot_sumber_dana']);
        $this->assertSame('10000000.00', $record['approved_amount']);
        $this->assertSame('2026-01-01', $record['starts_on']);
        $this->assertSame('2026-12-01', $record['ends_on']);
        $this->assertSame([8, 16], json_decode($record['sdg_numbers'], true));
        $this->assertSame(2, $record['source_row']);
        $this->assertSame(1, $parsed['summary']['projects']);
    }

    public function test_upload_preserves_system_data_and_repeated_file_is_idempotent(): void
    {
        $before = DB::table('researchs')->get()->toJson();
        $file = $this->workbook(['Detail' => [$this->row()]]);
        $importer = app(RectorateResearchImporter::class);
        $first = $importer->import($file, 'first.xlsx');
        $second = $importer->import($file, 'same-file-renamed.xlsx');
        $this->assertSame($before, DB::table('researchs')->get()->toJson());
        $this->assertSame(1, DB::table('rectorate_research')->count());
        $this->assertSame(1, DB::table('research_imports')->count());
        $this->assertSame($first['import_id'], $second['import_id']);
        $this->assertTrue($second['already_imported']);
    }

    public function test_latest_upload_per_year_keeps_older_years_and_audit_history(): void
    {
        $importer = app(RectorateResearchImporter::class);
        $importer->import($this->workbook(['Detail' => [$this->row(), $this->row(['tahun_anggaran' => '2024', 'judul' => 'Older year'])]]), 'first.xlsx', year: 2026, month: 8);
        $importer->import($this->workbook(['Detail' => [$this->row(['judul' => 'Corrected title'])]]), 'next.xlsx', year: 2026, month: 9);
        $active = app(RectorateResearchRepository::class)->activeRows();
        $this->assertSame(3, DB::table('rectorate_research')->count());
        $this->assertCount(2, $active);
        $this->assertEqualsCanonicalizing(['Older year', 'Corrected title'], $active->pluck('judul')->all());
    }

    public function test_missing_detail_and_empty_campus_selection_do_not_change_data(): void
    {
        $importer = app(RectorateResearchImporter::class);
        $importer->import($this->workbook(['Detail' => [$this->row()]]), 'valid.xlsx');
        foreach ([['Pivot' => [$this->row()]], ['Detail' => [$this->row(['lokasi_kampus' => 'Binus @Malang Utara'])]]] as $sheets) {
            try {
                $importer->import($this->workbook($sheets), 'invalid.xlsx');
                $this->fail('Invalid sheet or campus must be rejected.');
            } catch (ValidationException) {
                $this->assertSame(1, DB::table('rectorate_research')->count());
                $this->assertSame(1, DB::table('research_imports')->count());
            }
        }
    }

    public function test_identical_rows_deduplicate_and_conflicting_rows_fail(): void
    {
        $reader = app(RectorateResearchReader::class);
        $parsed = $reader->read($this->workbook(['Detail' => [$this->row(), $this->row(['no' => '2'])]]));
        $this->assertSame(1, $parsed['summary']['duplicates']);
        $this->assertSame(1, $parsed['summary']['selected']);
        $this->expectException(ValidationException::class);
        $reader->read($this->workbook(['Detail' => [$this->row(), $this->row(['judul' => 'Different'])]]));
    }

    public function test_missing_values_remain_null_and_localized_numbers_are_normalized(): void
    {
        $reader = app(RectorateResearchReader::class);
        $this->assertSame('10000000.00', $reader->amount('10.000.000'));
        $this->assertSame('10000000.25', $reader->amount('10.000.000,25'));
        $this->assertSame('1333333.33', $reader->amount('1333333.3333333333'));
        $this->assertSame('0.00', $reader->amount('0'));
        $parsed = $reader->read($this->workbook(['Detail' => [$this->row(['dana_disetujui' => '-', 'tanggal_mulai' => '31/02/2026', 'sdgs' => ''])]]));
        $record = $parsed['records'][0];
        $this->assertNull($record['approved_amount']);
        $this->assertNull($record['starts_on']);
        $this->assertSame('31/02/2026', $record['tanggal_mulai']);
        $this->assertSame(1, $parsed['summary']['invalid_values']['tanggal_mulai']);
    }

    public function test_database_failure_rolls_back_the_entire_new_upload(): void
    {
        DB::unprepared("CREATE TRIGGER reject_research BEFORE INSERT ON rectorate_research BEGIN SELECT RAISE(ABORT, 'test insert failure'); END");
        try {
            app(RectorateResearchImporter::class)->import($this->workbook(['Detail' => [$this->row()]]), 'failure.xlsx');
            $this->fail('The trigger should reject this import.');
        } catch (\Illuminate\Database\QueryException $exception) {
            $this->assertStringContainsString('test insert failure', $exception->getMessage());
        }
        $this->assertSame(0, DB::table('research_imports')->count());
        $this->assertSame(0, DB::table('rectorate_research')->count());
        $this->assertSame(1, DB::table('researchs')->count());
    }

    public function test_gallery_groups_participants_without_multiplying_funding_and_keeps_variants(): void
    {
        app(RectorateResearchImporter::class)->import($this->workbook(['Detail' => [
            $this->row(), $this->row(['kode_dosen_nim' => 'D002', 'nama' => 'Second Person', 'peran' => 'Anggota 1',
                'program_hibah' => 'Different program', 'sdgs' => '9 - Industry, Innovation and Infrastructure']),
        ]]), 'grouped.xlsx');
        $projects = app(ResearchGallery::class)->rectorateProjects();
        $this->assertCount(1, $projects);
        $project = $projects->first();
        $this->assertCount(2, $project['people']);
        $this->assertEquals(10000000, $project['amount']);
        $this->assertSame([8, 9, 16], $project['sdgs']);
        $this->assertCount(2, $project['variants']['program_hibah']);
    }

    public function test_public_gallery_filters_sources_year_and_researcher_and_escapes_detail(): void
    {
        $title = '<script>alert(1)</script> Rectorate research';
        app(RectorateResearchImporter::class)->import($this->workbook(['Detail' => [$this->row(['judul' => $title])]]), 'public.xlsx');
        $this->get(route('research-gallery.index'))->assertOk()->assertSee('Rectorate research')->assertDontSee('Existing system research')
            ->assertSee('id="header"', false)->assertSee('id="footer"', false)
            ->assertSee('href="'.route('publication-dashboard').'"', false)->assertDontSee('change_perms');
        $this->assertGuest();
        $this->get(route('research-gallery.index', ['source' => 'system']))->assertOk()->assertSee('Existing system research')->assertDontSee('Rectorate research');
        $this->get(route('research-gallery.index', ['year' => 2024]))->assertOk()->assertSee('0 proyek ditemukan');
        $this->get(route('research-gallery.index', ['person' => 'D002']))->assertOk()->assertSee('0 proyek ditemukan');
        $this->get(route('research-gallery.index', ['search' => 'Rectorate']))->assertOk()->assertSee('1 proyek ditemukan');
        $project = app(ResearchGallery::class)->rectorateProjects()->first();
        $this->get(route('research-gallery.show', ['rectorate', $project['id']]))->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('PRIVATE-NIDN')->assertDontSee('private-contact@example.org')
            ->assertSee('id="header"', false)->assertSee('id="footer"', false);
        $this->assertGuest();
    }

    public function test_unknown_detail_is_404_and_home_section_uses_actual_projects(): void
    {
        $this->get(route('research-gallery.show', ['rectorate', str_repeat('a', 64)]))->assertNotFound();
        app(RectorateResearchImporter::class)->import($this->workbook(['Detail' => [$this->row()]]), 'home.xlsx');
        $projects = app(ResearchGallery::class)->rectorateProjects();
        $this->view('landing.research_gallery.home_section', ['researchGalleryProjects' => $projects, 'researchGalleryCount' => $projects->count()])
            ->assertSee('Rectorate research')->assertSee('Upload Rectorate')->assertDontSee('Metropolitan Office Tower');
    }

    public function test_system_projects_with_the_same_contract_keep_their_separate_ids(): void
    {
        DB::table('researchs')->insert(['ID' => 'SYSTEM2', 'kode_dosen' => 'D002', 'title' => 'Another project under same contract',
            'budget_year' => '2026', 'contract_number' => 'SYS1', 'researcher' => '[]', 'funding' => '20000000']);
        $projects = app(ResearchGallery::class)->projects()->where('source', 'system');
        $this->assertCount(2, $projects);
        $this->assertCount(2, $projects->pluck('id')->unique());
        $this->get(route('research-gallery.index', ['source' => 'system']))->assertOk()
            ->assertSee('Existing system research')->assertSee('Another project under same contract');
    }

    public function test_upload_form_submits_the_detail_import_and_dry_run_writes_nothing(): void
    {
        $this->actingAs((new User)->forceFill(['id' => 1, 'name' => 'Admin']));
        $path = $this->workbook(['Detail' => [$this->row()]]);
        $summary = app(RectorateResearchImporter::class)->import($path, 'dry.xlsx', true);
        $this->assertTrue($summary['dry_run']);
        $this->assertSame(0, DB::table('research_imports')->count());
        $this->get(route('research-import.index'))->assertRedirect('/dashboard/importrectorate');
        $page = $this->get(route('importrectorate.index'))->assertOk()->json('page');
        $this->assertStringContainsString('Lokasi Kampus = Binus @Malang', base64_decode($page));
        $file = new UploadedFile($path, 'research.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
        $this->post(route('research-import.store'), ['research_file' => $file, 'year' => 2026, 'month' => 9])->assertRedirect('/dashboard/importrectorate')->assertSessionHas('research_summary');
        $this->assertSame(1, DB::table('rectorate_research')->count());
    }

    public function test_guests_cannot_open_or_submit_research_imports(): void
    {
        $this->get(route('research-import.index'))->assertForbidden();
        $this->post(route('research-import.store'))->assertForbidden();
        $this->get(route('importrectorate.index'))->assertForbidden();
        $this->postJson(route('importrectorate.upload'))->assertForbidden();
        $this->assertSame(0, DB::table('research_imports')->count());
        $this->assertSame(0, DB::table('rectorate_research')->count());
        $this->assertGuest();
    }

    public function test_malang_precedes_detail_and_evidence_is_preserved(): void
    {
        $file = $this->workbook(['Detail' => [$this->row(['judul' => 'Wrong source'])], 'MALANG' => [$this->row(['evidence' => 'Evidence reference'])]]);
        $summary = app(RectorateResearchImporter::class)->import($file, 'malang.xlsx', year: 2026, month: 9);
        $this->assertSame('MALANG', $summary['sheet']);
        $this->assertSame('Evidence reference', DB::table('rectorate_research')->value('evidence'));
        $this->assertSame('Rectorate research', DB::table('rectorate_research')->value('judul'));
    }

    public function test_monthly_replacement_and_backdated_upload_preserve_latest_snapshot(): void
    {
        $importer = app(RectorateResearchImporter::class);
        $file = $this->workbook(['MALANG' => [$this->row()]]);
        $importer->import($file, 'september.xlsx', year: 2026, month: 9);
        $importer->import($file, 'august.xlsx', year: 2026, month: 8);
        $this->assertSame(2, DB::table('research_imports')->count());
        $importer->import($this->workbook(['MALANG' => [$this->row(['judul' => 'Corrected September'])]]), 'replacement.xlsx', year: 2026, month: 9);
        $importer->import($this->workbook(['MALANG' => [$this->row(['judul' => 'Late August'])]]), 'older.xlsx', year: 2026, month: 8);
        $this->assertSame(2, DB::table('rectorate_research')->count());
        $this->assertSame('Corrected September', app(RectorateResearchRepository::class)->activeRows()->sole()->judul);
    }

    private function row(array $overrides = []): array
    {
        return array_replace(array_fill_keys(array_keys(RectorateResearchReader::HEADERS), ''), [
            'no' => '1', 'tahun_anggaran' => '2026', 'kd_prop' => 'BNS2026-001', 'kode_dosen_nim' => 'D001',
            'nama' => 'Test Researcher', 'peran' => 'Ketua', 'kategori_fm_eksternal_mahasiswa' => 'FM',
            'judul' => 'Rectorate research', 'lokasi_kampus' => 'Binus @Malang', 'dana_disetujui' => '10000000',
            'bobot_sumber_dana' => '0.6', 'tanggal_mulai' => '46023', 'tanggal_selesai' => '01/12/2026',
            'program_hibah' => 'Research program', 'pengakuan_kpi_research_program' => 'Y',
            'sdgs' => '8. Decent Work and Economic Growth, 16. Peace, Justice, and Strong Institutions',
            'nidn' => 'PRIVATE-NIDN', 'email_mitra' => 'private-contact@example.org',
        ], $overrides);
    }

    private function workbook(array $sheets, bool $reverse = false): string
    {
        $file = tempnam(sys_get_temp_dir(), 'research-test-');
        $this->files[] = $file;
        $zip = new ZipArchive;
        $zip->open($file, ZipArchive::OVERWRITE);
        $ns = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
        $relsNs = 'http://schemas.openxmlformats.org/package/2006/relationships';
        $docNs = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
        $zip->addFromString('[Content_Types].xml', '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<Relationships xmlns="'.$relsNs.'"><Relationship Id="rId1" Type="'.$docNs.'/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $book = '<workbook xmlns="'.$ns.'" xmlns:r="'.$docNs.'"><sheets>';
        $rels = '<Relationships xmlns="'.$relsNs.'">';
        $index = 0;
        foreach ($sheets as $name => $records) {
            $index++;
            $book .= '<sheet name="'.htmlspecialchars($name, ENT_XML1).'" sheetId="'.$index.'" r:id="rId'.$index.'"/>';
            $rels .= '<Relationship Id="rId'.$index.'" Type="'.$docNs.'/worksheet" Target="worksheets/sheet'.$index.'.xml"/>';
            $headers = RectorateResearchReader::HEADERS;
            $headers['bobot_sumber_dana'] = "Bobot \nsumber \ndana";
            $headers['status_usulan'] .= ' ';
            $rows = [array_values($headers), ...array_map('array_values', $records)];
            $xml = '<worksheet xmlns="'.$ns.'"><sheetData>';
            foreach ($rows as $r => $cells) {
                $xml .= '<row r="'.($r + 1).'">';
                foreach ($reverse ? array_reverse($cells) : $cells as $c => $value) {
                    $column = '';
                    for ($n = $c + 1; $n > 0; $n = intdiv($n - 1, 26)) {
                        $column = chr(65 + ($n - 1) % 26).$column;
                    }
                    $xml .= '<c r="'.$column.($r + 1).'" t="inlineStr"><is><t>'.htmlspecialchars((string) $value, ENT_XML1).'</t></is></c>';
                }
                $xml .= '</row>';
            }
            $zip->addFromString('xl/worksheets/sheet'.$index.'.xml', $xml.'</sheetData></worksheet>');
        }
        $zip->addFromString('xl/workbook.xml', $book.'</sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', $rels.'</Relationships>');
        $zip->close();

        return $file;
    }
}
