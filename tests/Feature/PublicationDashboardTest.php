<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Publications\ImportedLecturerKpi;
use App\Services\Publications\PublicationDashboard;
use App\Services\Publications\PublicationImporter;
use App\Services\Publications\PublicationScore;
use App\Services\Publications\RawPublicationReader;
use App\Services\Research\RectorateResearchImporter;
use App\Services\Research\RectorateResearchReader;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use ZipArchive;

class PublicationDashboardTest extends TestCase
{
    private array $files = [];

    public function test_admin_kpi_defaults_to_all_imported_lecturers_and_all_months(): void
    {
        $importer = app(PublicationImporter::class);
        $importer->import($this->workbookWithKpi([$this->row()], [
            $this->kpiRow(['Scopus RTTO' => 2, 'Score KPI RTTO' => 3]),
            $this->kpiRow(['Kode Dosen' => 'D999', 'Nama Dosen' => 'New <script>alert(1)</script>', 'Score KPI RTTO' => 0]),
        ]), 'august.xlsx', 2026, 8, 3);
        $importer->import($this->workbookWithKpi([$this->row(['Bobot' => 0.75])], [
            $this->kpiRow(['Scopus RTTO' => 4, 'Score KPI RTTO' => 5]),
            $this->kpiRow(['Kode Dosen' => 'D002', 'Scopus RTTO' => '', 'Non Scopus RTTO' => '', 'Score KPI RTTO' => '']),
        ]), 'september.xlsx', 2026, 9, 3);

        $rows = app(ImportedLecturerKpi::class)->rows();
        $this->assertCount(4, $rows);
        $this->assertSame(4.0, $rows->where('month', 9)->firstWhere('code', 'D001')['scopus']);
        $this->assertSame(2.0, $rows->where('month', 8)->firstWhere('code', 'D001')['scopus']);
        $this->assertSame(0.75, $rows->where('month', 9)->firstWhere('code', 'D001')['rectorate_scopus']);
        $this->assertSame(0, $rows->firstWhere('code', 'D999')['score']);
        $this->assertNull($rows->firstWhere('code', 'D002')['score']);
        $this->assertNull($rows->firstWhere('code', 'D002')['rtto_scopus']);
        $this->assertNull($rows->first()['grant_chair']);

        $this->actingAs((new User)->forceFill(['id' => 1]));
        $html = base64_decode($this->get(route('perhitungankpi.index'))->assertOk()->json('page'));
        $this->assertStringContainsString('4 baris dari 3 dosen', $html);
        $this->assertStringContainsString('Semua tahun', $html);
        $this->assertStringContainsString('Semua bulan', $html);
        $this->assertStringNotContainsString('filter_period', $html);
        $this->assertStringNotContainsString('Quarter', $html);
        $this->assertStringContainsString('data-code="D999"', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->postJson(route('perhitungankpi.init_table'))->assertOk()->assertJsonPath('count', 4)->assertJsonPath('lecturers', 3);
        $this->postJson(route('perhitungankpi.init_table'), ['year' => '', 'month' => '', 'prodi' => '', 'search' => ''])
            ->assertOk()->assertJsonPath('count', 4);
    }

    public function test_admin_kpi_filters_and_search_are_optional_and_composable(): void
    {
        $importer = app(PublicationImporter::class);
        $file = $this->workbookWithKpi([$this->row()], [
            $this->kpiRow(['Nama Dosen' => 'Alpha Researcher']),
            $this->kpiRow(['Kode Dosen' => 'D999', 'Nama Dosen' => 'Beta Researcher', 'Jurusan Binaan' => 'Interior Design']),
        ]);
        $importer->import($file, 'august.xlsx', 2026, 8, 3);
        $importer->import($file, 'september.xlsx', 2026, 9, 3);
        $this->actingAs((new User)->forceFill(['id' => 1]));
        foreach ([
            [['search' => 'BETA'], 2], [['search' => 'd001'], 2], [['prodi' => 'DI'], 2],
            [['year' => 2026, 'month' => 8, 'prodi' => 'CS', 'search' => 'alpha'], 1],
            [['year' => 2025], 0], [['month' => 7], 0], [['search' => 'missing'], 0],
        ] as [$filters, $expected]) {
            $this->postJson(route('perhitungankpi.init_table'), $filters)->assertOk()->assertJsonPath('count', $expected);
        }
        $this->postJson(route('perhitungankpi.init_table'), ['month' => 13])->assertUnprocessable()->assertJsonValidationErrors('month');
    }

    public function test_admin_kpi_legacy_calculation_uses_only_its_own_month(): void
    {
        $importer = app(PublicationImporter::class);
        $importer->import($this->rawFile([$this->row(['Bobot' => 0.1, 'Quartile Jurnal' => ''])]), 'low.xlsx', 2026, 8, 3);
        $importer->import($this->rawFile([$this->row(['Bobot' => 10, 'Quartile Jurnal' => ''])]), 'high.xlsx', 2026, 9, 3);
        $rows = app(ImportedLecturerKpi::class)->rows()->keyBy('month');
        $this->assertCount(2, $rows);
        $this->assertSame('Sistem', $rows[8]['score_source']);
        $this->assertLessThan($rows[9]['score'], $rows[8]['score']);
        $this->assertSame(0.1, $rows[8]['scopus']);
        $this->assertSame(10.0, $rows[9]['scopus']);
        $this->assertFalse(app(ImportedLecturerKpi::class)->rows()->contains('code', 'D002'));
    }

    public function test_admin_kpi_requires_login_and_handles_an_empty_database(): void
    {
        $this->get(route('perhitungankpi.index'))->assertForbidden();
        $this->postJson(route('perhitungankpi.init_table'))->assertForbidden();
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $html = base64_decode($this->get(route('perhitungankpi.index'))->assertOk()->json('page'));
        $this->assertStringContainsString('0 baris dari 0 dosen', $html);
        $this->postJson(route('perhitungankpi.init_table'))->assertOk()->assertJsonPath('count', 0);
    }

    public function test_admin_kpi_retains_legacy_imports_without_a_campus_or_tracking_batch(): void
    {
        app(PublicationImporter::class)->import($this->rawFile([$this->row()]), 'legacy.xlsx', 2026, 8, 3);
        DB::table('rectorate_dosen')->update(['publication_import_id' => null, 'kampus' => null]);
        DB::table('publication_imports')->delete();
        $rows = app(ImportedLecturerKpi::class)->rows();
        $this->assertCount(1, $rows);
        $this->assertSame('D001', $rows->sole()['code']);
        $this->assertSame(8, $rows->sole()['month']);
        DB::table('rectorate_dosen')->update(['kampus' => 'JAKARTA']);
        $this->assertCount(0, app(ImportedLecturerKpi::class)->rows());
        DB::table('rectorate_dosen')->update(['kampus' => null, 'submitted' => 'Scopus Mahasiswa']);
        $this->assertCount(0, app(ImportedLecturerKpi::class)->rows());
    }

    private const HEADER = ['RequestCode', 'Author', 'FM Author', 'Kode Dosen', 'JJA', 'Pendidikan', 'Type', 'S/F', 'Dept', 'Kampus', 'First Author', 'Skema', 'Bobot', 'Submitted', 'St2026', 'Jenis', 'Tipe Publikasi', 'Title ', 'Scopus Year', 'Tanggal Pelaporan', 'Source title', 'Quartile Jurnal', 'Notes', 'Prodi KPI'];

    private const KPI_HEADER = ['Kode Dosen', 'Nama Dosen', 'Jurusan Binaan', 'JJA', 'Faculty Type', 'Non Scopus', 'Scopus', 'Jumlah First Author', 'Score KPI', 'Scopus First Author', 'Non Scopus Rectorate', 'Scopus Rectorate', 'Non Scopus RTTO', 'Scopus RTTO', 'Score KPI RTTO', 'Punya Scopus'];

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
        (require database_path('migrations/2026_09_23_000000_create_publication_kpi_entries_table.php'))->up();
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
        $this->assertSame('B', $faculty['D001']['annual'][2026]['cluster']);
        $this->assertSame('Otomatis dari import', $faculty['D001']['annual'][2026]['cluster_analysis']['source']);
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

    public function test_uploaded_grants_populate_topics_years_and_clusters_without_counting_members_twice(): void
    {
        app(PublicationImporter::class)->import($this->rawFile([$this->row()]), 'fm.xlsx', 2026, 9, 3);
        (require database_path('migrations/2026_09_08_000200_add_rectorate_research_imports.php'))->up();
        (require database_path('migrations/2026_09_24_000000_add_monthly_research_snapshots.php'))->up();
        $base = array_replace(array_fill_keys(array_keys(RectorateResearchReader::HEADERS), ''), [
            'tahun_anggaran' => '2026', 'kd_prop' => 'P1', 'kode_dosen_nim' => 'D001', 'nama' => 'Lecturer',
            'peran' => 'Ketua', 'kategori_fm_eksternal_mahasiswa' => 'FM', 'lokasi_kampus' => 'Binus @Malang',
            'judul' => 'Digital business project', 'sdgs' => '8 - Decent Work and Economic Growth',
            'subtopik_research_roadmap' => 'Sustainable business', 'sumber_dana' => 'Nasional - DIKTI',
            'sumber_pemberi_hibah' => 'Dalam Negeri (Nasional)', 'nidn' => 'PRIVATE-NIDN', 'email_mitra' => 'private@example.org',
        ]);
        $make = fn ($rows) => $this->workbook(['MALANG' => [array_values(RectorateResearchReader::HEADERS), ...array_map('array_values', $rows)]]);
        $importer = app(RectorateResearchImporter::class);
        $importer->import($make([
            $base,
            array_replace($base, ['kode_dosen_nim' => 'D999', 'nama' => 'Grant Only Lecturer', 'peran' => 'Anggota 1']),
            array_replace($base, ['tahun_anggaran' => '2023', 'kd_prop' => 'HISTORY', 'judul' => 'Historical education', 'sdgs' => '4 - Quality Education']),
        ]), 'september.xlsx', year: 2026, month: 9);
        $importer->import($make([array_replace($base, ['judul' => 'Outdated title', 'sdgs' => '9 - Industry'])]), 'late-august.xlsx', year: 2026, month: 8);

        $dashboard = app(PublicationDashboard::class)->data();
        $this->assertSame(['2023', '2026'], $dashboard['years']);
        $this->assertSame('2026', $dashboard['default_year']);
        $projects = collect($dashboard['research_projects']);
        $this->assertCount(2, $projects);
        $project = $projects->firstWhere('year', 2026);
        $this->assertSame(['D001', 'D999'], $project['codes']);
        $this->assertSame([8], $project['sdgs']);
        $this->assertSame(['Sustainable business'], $project['topics']);
        $this->assertSame('Digital business project', $project['title']);
        $faculty = collect($dashboard['faculty'])->keyBy('code');
        $this->assertSame('A', $faculty['D001']['annual'][2026]['cluster']);
        $this->assertNull($faculty['D001']['annual'][2023]['score']);
        $this->assertNull($faculty['D001']['annual'][2023]['cluster']);
        $this->assertSame('Grant Only Lecturer', $faculty['D999']['name']);
        $this->get(route('publication-dashboard'))->assertOk()->assertSee('Peta SDG dan topik penelitian')
            ->assertSee('publication-research.js')->assertDontSee('PRIVATE-NIDN')->assertDontSee('private@example.org')
            ->assertDontSee('Outdated title');
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

    public function test_malang_takes_precedence_over_raw_and_reconciles_each_category_independently(): void
    {
        $papers = [$this->row(), $this->row(['RequestCode' => 'NON', 'Tipe Publikasi' => 'Nscopus', 'Submitted' => 'Non Scopus FM', 'Bobot' => '2'])];
        $kpi = $this->kpiRow(['Non Scopus RTTO' => 1, 'Scopus RTTO' => 4, 'Score KPI RTTO' => 3]);
        $file = $this->workbook([
            'Raw' => [self::HEADER, array_values($this->row(['RequestCode' => 'EXCLUDED']))],
            ' malang ' => [self::HEADER, ...array_map('array_values', $papers)],
            'KPI' => [array_reverse(self::KPI_HEADER), array_values(array_reverse($kpi, true))],
        ]);
        $summary = app(PublicationImporter::class)->import($file, 'workbook.xlsx', 2026, 9, 3);
        $this->assertSame('MALANG', $summary['sheet']);
        $this->assertSame(2, $summary['selected']);
        $this->assertSame(1, $summary['kpi']['lecturers']);
        $dashboard = app(PublicationDashboard::class)->data();
        $faculty = collect($dashboard['faculty'])->keyBy('code');
        $annual = $faculty['D001']['annual'][2026];
        $this->assertSame(3, $annual['score']);
        $this->assertNotNull($annual['system_score']);
        $this->assertSame(0.5, $annual['workbook']['rectorate_scopus']);
        $this->assertSame(4.0, $annual['workbook']['scopus']);
        $this->assertSame(2.0, $annual['workbook']['non_scopus']);
        $this->assertSame('RTTO', $annual['workbook']['scopus_source']);
        $this->assertSame('Rectorate', $annual['workbook']['non_scopus_source']);
        $this->assertSame(1, $annual['workbook']['first_author']);
        $this->assertSame(5.0, $annual['weight']);
        $this->assertTrue($dashboard['snapshots'][2026]['has_kpi']);
        $this->assertSame(['R1', 'NON'], collect($dashboard['publications'])->pluck('request_code')->all());
        $this->assertSame('Y', $dashboard['publications'][0]['first_author']);
        $this->assertSame('Scopus', $dashboard['publications'][0]['tipe_publikasi']);
    }

    public function test_first_author_requires_all_three_filters_and_counts_author_contributions(): void
    {
        $papers = [
            $this->row(),
            $this->row(['Kode Dosen' => 'D002', 'First Author' => 'N']),
            $this->row(['RequestCode' => 'WRONG-TYPE', 'Tipe Publikasi' => 'Nscopus']),
            $this->row(['RequestCode' => 'WRONG-SUBMITTED', 'Submitted' => 'Non Scopus FM']),
            $this->row(['RequestCode' => 'NON-FIRST', 'First Author' => 'N']),
        ];
        $summary = app(PublicationImporter::class)->import($this->rawFile($papers), 'filters.xlsx', 2026, 9, 3);
        $this->assertSame(3, $summary['workbook_totals']['titles']);
        $this->assertSame(1, $summary['workbook_totals']['first_author']);
        $this->assertSame(2.0, $summary['workbook_totals']['rectorate_scopus']);
        $this->assertSame(0.5, $summary['workbook_totals']['rectorate_non_scopus']);
        $this->assertSame(4, $summary['publications']);
    }

    public function test_explicit_zero_rtto_only_and_absent_kpi_scores_remain_distinct(): void
    {
        $kpi = [
            $this->kpiRow(['Kode Dosen' => 'D002', 'Score KPI RTTO' => 0]),
            $this->kpiRow(['Kode Dosen' => 'D999', 'Nama Dosen' => 'External-only Lecturer', 'Scopus RTTO' => 3.25, 'Score KPI RTTO' => 5]),
        ];
        $file = $this->workbookWithKpi([$this->row()], $kpi);
        $summary = app(PublicationImporter::class)->import($file, 'zero.xlsx', 2026, 9, 3);
        $this->assertSame(['D999'], $summary['unmatched_codes']);
        $faculty = collect(app(PublicationDashboard::class)->data()['faculty'])->keyBy('code');
        $this->assertNull($faculty['D001']['annual'][2026]['score']);
        $this->assertNotNull($faculty['D001']['annual'][2026]['system_score']);
        $this->assertSame(0, $faculty['D002']['annual'][2026]['score']);
        $this->assertSame(0, $faculty['D002']['annual'][2026]['rows']);
        $this->assertSame(5, $faculty['D999']['annual'][2026]['score']);
        $this->assertSame(3.25, $faculty['D999']['annual'][2026]['workbook']['scopus']);
        $this->assertSame('External-only Lecturer', $faculty['D999']['name']);
        $this->assertCount(1, app(PublicationDashboard::class)->data()['publications']);
    }

    public function test_blank_rtto_does_not_become_zero_and_derived_cache_is_audited(): void
    {
        $file = $this->workbookWithKpi([$this->row()], [$this->kpiRow([
            'Scopus RTTO' => '', 'Non Scopus RTTO' => '', 'Score KPI RTTO' => '',
            'Scopus Rectorate' => '99', 'Jumlah First Author' => '99',
        ])]);
        $summary = app(PublicationImporter::class)->import($file, 'blanks.xlsx', 2026, 9, 3);
        $this->assertSame(1, $summary['kpi']['missing_rtto']);
        $this->assertCount(2, $summary['kpi']['differences']);
        $faculty = collect(app(PublicationDashboard::class)->data()['faculty'])->keyBy('code');
        $m = $faculty['D001']['annual'][2026]['workbook'];
        $this->assertNull($m['rtto_scopus']);
        $this->assertNull($m['score']);
        $this->assertSame(0.5, $m['scopus']);
        $this->assertSame(1, $m['first_author']);
    }

    public function test_latest_snapshot_does_not_inherit_older_rtto_and_reimport_replaces_both_sources(): void
    {
        $importer = app(PublicationImporter::class);
        $old = $this->workbookWithKpi([$this->row()], [$this->kpiRow(['Scopus RTTO' => 9, 'Score KPI RTTO' => 6])]);
        $latest = $this->workbookWithKpi([$this->row()], [$this->kpiRow(['Scopus RTTO' => 1, 'Score KPI RTTO' => 2])]);
        $importer->import($old, 'august.xlsx', 2026, 8, 3);
        $importer->import($latest, 'september.xlsx', 2026, 9, 3);
        $faculty = collect(app(PublicationDashboard::class)->data()['faculty'])->keyBy('code');
        $this->assertSame(2, $faculty['D001']['annual'][2026]['score']);
        $this->assertSame(1.0, $faculty['D001']['annual'][2026]['workbook']['scopus']);
        $importer->import($latest, 'again.xlsx', 2026, 9, 3);
        $this->assertSame(2, DB::table('publication_kpi_entries')->count());
        $importer->import($this->rawFile([$this->row()]), 'raw.xlsx', 2026, 9, 3);
        $dashboard = app(PublicationDashboard::class)->data();
        $this->assertFalse($dashboard['snapshots'][2026]['has_kpi']);
        $faculty = collect($dashboard['faculty'])->keyBy('code');
        $this->assertSame(4, $faculty['D001']['annual'][2026]['score']);
        $this->assertNull($faculty['D001']['annual'][2026]['workbook']['rtto_scopus']);
        $this->assertSame(1, DB::table('publication_kpi_entries')->count());
        $this->assertSame(2, DB::table('rectorate_dosen')->count());
    }

    public function test_invalid_rtto_and_duplicate_kpi_codes_preserve_previous_snapshot(): void
    {
        $importer = app(PublicationImporter::class);
        $importer->import($this->workbookWithKpi([$this->row()], [$this->kpiRow()]), 'valid.xlsx', 2026, 9, 3);
        foreach ([['Scopus RTTO' => '#REF!'], ['Non Scopus RTTO' => -1], ['Score KPI RTTO' => 7], ['Score KPI RTTO' => 2.5]] as $invalid) {
            try {
                $importer->import($this->workbookWithKpi([$this->row()], [$this->kpiRow($invalid)]), 'invalid.xlsx', 2026, 9, 3);
                $this->fail('Invalid RTTO must fail.');
            } catch (ValidationException $e) {
                $this->assertStringContainsString('Baris KPI', $e->getMessage());
            }
        }
        try {
            $importer->import($this->workbookWithKpi([$this->row()], [$this->kpiRow(), $this->kpiRow()]), 'duplicate.xlsx', 2026, 9, 3);
            $this->fail('Duplicate KPI code must fail.');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('duplikat', $e->getMessage());
        }
        $this->assertSame('valid.xlsx', DB::table('publication_imports')->value('filename'));
        $this->assertSame(1, DB::table('rectorate_dosen')->count());
        $this->assertSame(1, DB::table('publication_kpi_entries')->count());
    }

    public function test_kpi_and_publications_roll_back_together_on_insert_failure(): void
    {
        $importer = app(PublicationImporter::class);
        $file = $this->workbookWithKpi([$this->row()], [$this->kpiRow(['Scopus RTTO' => 3])]);
        $importer->import($file, 'original.xlsx', 2026, 9, 3);
        DB::unprepared("CREATE TRIGGER reject_workbook BEFORE INSERT ON rectorate_dosen BEGIN SELECT RAISE(ABORT, 'test rollback'); END");
        try {
            $importer->import($this->workbookWithKpi([$this->row()], [$this->kpiRow(['Scopus RTTO' => 10])]), 'replacement.xlsx', 2026, 9, 3);
            $this->fail('Database trigger must abort.');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertStringContainsString('test rollback', $e->getMessage());
        }
        $this->assertEquals(3, DB::table('publication_kpi_entries')->value('rtto_scopus'));
        $this->assertSame('original.xlsx', DB::table('publication_imports')->value('filename'));
        $this->assertSame(1, DB::table('rectorate_dosen')->count());
    }

    public function test_workbook_dry_run_validates_without_writing_either_source(): void
    {
        $file = $this->workbookWithKpi([$this->row()], [$this->kpiRow()]);
        $summary = app(PublicationImporter::class)->import($file, 'dry.xlsx', 2026, 9, 3, true);
        $this->assertSame(1, $summary['kpi']['lecturers']);
        $this->assertSame(0, DB::table('publication_imports')->count());
        $this->assertSame(0, DB::table('publication_kpi_entries')->count());
        $this->assertSame(0, DB::table('rectorate_dosen')->count());
        $this->get('/kpi-publikasi')->assertOk()
            ->assertSee('Peringkat dosen')->assertSee('Status produktivitas dosen')
            ->assertSee('id="topToggle"', false)->assertSee('id="donut"', false)
            ->assertDontSee('id="workbookTabs"', false)->assertDontSee('id="scoreBasis"', false);
    }

    public function test_excel_display_format_does_not_round_weights_or_formula_results_during_import(): void
    {
        $file = $this->workbookWithKpi([$this->row()], [$this->kpiRow()]);
        $zip = new ZipArchive;
        $zip->open($file);
        $ns = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
        $zip->addFromString('xl/styles.xml', '<styleSheet xmlns="'.$ns.'"><cellXfs count="2"><xf numFmtId="0"/><xf numFmtId="2" applyNumberFormat="1"/></cellXfs></styleSheet>');
        $rels = str_replace('</Relationships>', '<Relationship Id="styles" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>', $zip->getFromName('xl/_rels/workbook.xml.rels'));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $rels);
        $publications = preg_replace('/<c r="M2".*?<\/c>/', '<c r="M2" s="1"><v>0.3333333333333333</v></c>', $zip->getFromName('xl/worksheets/sheet1.xml'));
        $zip->addFromString('xl/worksheets/sheet1.xml', $publications);
        $kpi = preg_replace('/<c r="N2".*?<\/c>/', '<c r="N2" s="1"><f>1/3</f><v>0.3333333333333333</v></c>', $zip->getFromName('xl/worksheets/sheet2.xml'));
        $zip->addFromString('xl/worksheets/sheet2.xml', $kpi);
        $zip->close();
        app(PublicationImporter::class)->import($file, 'precision.xlsx', 2026, 9, 3);
        $this->assertEqualsWithDelta(1 / 3, DB::table('rectorate_dosen')->value('bobot_asli'), 0.000000000001);
        $this->assertEqualsWithDelta(1 / 3, DB::table('publication_kpi_entries')->value('rtto_scopus'), 0.000000000001);
    }

    public function test_missing_kpi_header_and_invalid_malang_do_not_fall_back_to_other_sheets(): void
    {
        $reader = app(PublicationImporter::class);
        $badKpi = $this->workbook(['MALANG' => [self::HEADER, array_values($this->row())], 'KPI' => [['Kode Dosen'], ['D001']]]);
        try {
            $reader->import($badKpi, 'bad.xlsx', 2026, 9, 3);
            $this->fail('Malformed KPI must fail.');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('Header nama dosen', $e->getMessage());
        }
        $badMalang = $this->workbook(['MALANG' => [self::HEADER], 'Raw' => [self::HEADER, array_values($this->row())]]);
        try {
            $reader->import($badMalang, 'bad.xlsx', 2026, 9, 3);
            $this->fail('Empty MALANG must fail.');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('Tidak ada baris MALANG', $e->getMessage());
        }
        $this->assertSame(0, DB::table('publication_imports')->count());
    }

    public function test_kpi_workbook_requires_first_author_and_program_source_columns(): void
    {
        foreach (['First Author', 'Prodi KPI'] as $missing) {
            $header = array_values(array_filter(self::HEADER, fn ($h) => $h !== $missing));
            $row = $this->row();
            unset($row[$missing]);
            $file = $this->workbook(['MALANG' => [$header, array_values($row)], 'KPI' => [self::KPI_HEADER, array_values($this->kpiRow())]]);
            try {
                app(PublicationImporter::class)->import($file, 'missing.xlsx', 2026, 9, 3);
                $this->fail('Workbook must contain the fields used in its summaries.');
            } catch (ValidationException $e) {
                $this->assertStringContainsString(str_replace(' ', '_', strtolower($missing)), $e->getMessage());
            }
        }
        $this->assertSame(0, DB::table('publication_imports')->count());
    }

    private function kpiRow(array $overrides = []): array
    {
        return array_replace(array_combine(self::KPI_HEADER, ['D001', 'Test Lecturer', 'CS', 'L200', 'Functional Faculty(A)', '', '', '', '', '', '', '', 0, 0, 0, '']), $overrides);
    }

    private function workbookWithKpi(array $papers, array $kpi): string
    {
        return $this->workbook(['MALANG' => [self::HEADER, ...array_map('array_values', $papers)], 'KPI' => [self::KPI_HEADER, ...array_map('array_values', $kpi)]]);
    }

    public function test_multiple_monthly_files_roll_back_when_a_later_file_is_invalid(): void
    {
        $original = $this->workbook(['Raw' => [self::HEADER, array_values($this->row())]]);
        app(PublicationImporter::class)->import($original, 'original.xlsx', 2026, 9, 3);
        $replacement = $this->workbook(['Raw' => [self::HEADER, array_values($this->row(['Title ' => 'Replacement']))]]);
        $invalid = $this->workbook(['MALANG' => [['Incorrect header']]]);
        $this->actingAs((new User)->forceFill(['id' => 1, 'name' => 'Editor']));
        $this->postJson(route('importrectorate.upload'), [
            'year' => 2026, 'month' => 9,
            'fm_file' => new \Illuminate\Http\UploadedFile($replacement, 'fm.xlsx', null, null, true),
            'mhs_file' => new \Illuminate\Http\UploadedFile($invalid, 'mhs.xlsx', null, null, true),
        ])->assertUnprocessable();
        $this->assertSame('original.xlsx', DB::table('publication_imports')->value('filename'));
        $this->assertNotSame('Replacement', DB::table('rectorate_dosen')->value('title'));
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
