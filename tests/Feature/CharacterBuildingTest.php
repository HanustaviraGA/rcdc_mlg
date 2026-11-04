<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CharacterBuilding\CharacterBuildingProgress;
use App\Services\CharacterBuilding\CharacterBuildingTargets;
use App\Services\Publications\PublicationImporter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\CreatesWorkbook;
use Tests\TestCase;

class CharacterBuildingTest extends TestCase
{
    use CreatesWorkbook;

    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Run PHP with -d extension=pdo_sqlite.');
        }
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->travelTo(now()->setDate(2026, 11, 4));
        Schema::create('database_dosen', function (Blueprint $table) {
            $table->string('kode_dosen')->primary();
        });
        DB::table('database_dosen')->insert([['kode_dosen' => 'D001'], ['kode_dosen' => 'D002']]);
        Schema::create('database_dosen_new', function (Blueprint $table) {
            $table->string('kode_dosen')->primary();
            $table->text('nama_dosen')->nullable();
            $table->text('nama_gugus_binaan')->nullable();
        });
        DB::table('database_dosen_new')->insert([
            ['kode_dosen' => 'D001', 'nama_dosen' => 'Dosen Karakter', 'nama_gugus_binaan' => 'Character Building'],
            ['kode_dosen' => 'D002', 'nama_dosen' => 'Dosen Informatika', 'nama_gugus_binaan' => 'Computer Science'],
        ]);
        Schema::create('rectorate_dosen', function (Blueprint $table) {
            $table->string('id_rectorate')->primary();
            foreach ([
                'request_code', 'author', 'fm_author', 'kode_dosen', 'jja', 'pendidikan', 'type', 's_f', 'dept',
                'kampus', 'first_author', 'sumber_paper', 'submitted', 'status', 'jenis', 'tipe_publikasi', 'title',
                'scopus_year', 'source_title', 'quartile_jurnal', 'publisher', 'year', 'month', 'period',
            ] as $column) {
                $table->string($column)->nullable();
            }
            $table->double('bobot_asli')->nullable();
            $table->double('bobot')->nullable();
            $table->timestamps();
        });
        (require database_path('migrations/2026_09_08_000000_add_publication_import_tracking.php'))->up();
        (require database_path('migrations/2026_09_23_000000_create_publication_kpi_entries_table.php'))->up();
        (require database_path('migrations/2026_10_23_000000_create_community_development_imports.php'))->up();
        (require base_path('Modules/CharacterBuilding/database/migrations/2026_11_04_000000_create_character_building_targets_table.php'))->up();
    }

    protected function tearDown(): void
    {
        $this->removeWorkbooks();
        $this->travelBack();
        parent::tearDown();
    }

    public function test_page_has_five_tabs_with_target_forms_and_coming_soon_tabs(): void
    {
        $page = base64_decode($this->getJson(route('characterbuilding.index'))->assertOk()->json('page'));
        preg_match_all('/data-bs-target="#cbTab-([a-z]+)"[^>]*>([^<]+)</', $page, $tabs);
        $this->assertSame(['target', 'publikasi', 'pengajaran', 'comdev', 'pengembangan'], $tabs[1]);
        $this->assertSame(['1. Target Skor', '2. Publikasi', '3. Pengajaran', '4. Community Development', '5. Pengembangan Diri'], $tabs[2]);
        $this->assertSame(2, substr_count($page, 'Coming Soon'));
        foreach (['publikasi' => 'quarter', 'pengajaran' => 'semester', 'comdev' => 'quarter', 'pengembangan_diri' => 'quarter'] as $category => $period) {
            $this->assertStringContainsString("data-cb-target-form=\"{$category}\"", $page);
            $this->assertStringContainsString("id=\"cb-{$category}-target\"", $page);
            $this->assertStringContainsString("id=\"cb-{$category}-score\"", $page);
            $this->assertStringContainsString($period === 'semester' ? "id=\"cb-{$category}-academic-year\"" : "id=\"cb-{$category}-quarter\"", $page);
        }
        $this->assertStringContainsString('<option value="2026/2027" selected>2026/2027</option>', $page);
        $this->assertStringContainsString('Target (judul Scopus)', $page);
        $this->assertStringContainsString('Target (kegiatan)', $page);
    }

    public function test_the_targets_of_a_year_form_its_score_ranges(): void
    {
        $this->postJson(route('characterbuilding.create'), ['category' => 'publikasi', 'year' => 2026, 'quarter' => 1, 'target' => 3, 'score' => 2])->assertForbidden();
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $create = fn (array $data) => $this->postJson(route('characterbuilding.create'), $data);
        $quarter = fn (int $quarter, float $target, float $score, int $year = 2026) => ['category' => 'publikasi', 'year' => $year, 'quarter' => $quarter,
            'target' => $target, 'score' => $score];

        foreach ([[1, 3, 2], [2, 6, 3], [4, 11, 6]] as [$term, $target, $score]) {
            $create($quarter($term, $target, $score))->assertOk();
        }
        $response = $create($quarter(3, 9, 4))->assertOk()->assertJsonPath('category', 'publikasi')
            ->assertJsonPath('message', 'Target Publikasi 2026 Q3 (9 judul Scopus → skor 4) disimpan.');
        $targets = app(CharacterBuildingTargets::class);
        $ladder = $targets->ladder('publikasi', 2026);
        $this->assertSame([[3.0, 2.0], [6.0, 3.0], [9.0, 4.0], [11.0, 6.0]], $ladder->map(fn ($tier) => [$tier->target, $tier->score])->all());
        // 7 and 8 stay at score 3 until 9 is reached; from 11 up the score stays at its maximum 6.
        $this->assertSame([null, 2.0, 2.0, 3.0, 3.0, 3.0, 4.0, 4.0, 6.0, 6.0, null], array_map(
            fn ($realization) => CharacterBuildingTargets::reached($ladder, $realization)?->score, [2.0, 3.0, 5.0, 6.0, 7.0, 8.0, 9.0, 10.0, 11.0, 30.0, null]
        ));
        $list = $response->json('list_html');
        preg_match_all('/data-cb-period="([^"]+)"/', $list, $rows);
        $this->assertSame(['2026 Q1', '2026 Q2', '2026 Q3', '2026 Q4'], $rows[1]);
        $this->assertStringNotContainsString('Rentang', $list);
        $this->assertStringContainsString('data-target="9" data-score="4" data-label="2026 Q3 (9 → skor 4)"', $list);

        // Saving a period again replaces its target; the ranges follow.
        $create($quarter(2, 7, 3))->assertOk();
        $this->assertSame(4, DB::table('character_building_targets')->count());
        $this->assertSame([3.0, 7.0, 9.0, 11.0], $targets->ladder('publikasi', 2026)->pluck('target')->all());
        // Within a year a higher target must earn a higher score; other years are separate.
        $create($quarter(2, 10, 3))->assertUnprocessable()
            ->assertJsonValidationErrors(['score' => 'Skor harus naik mengikuti target. Pada 2026 Q3, target 9 memiliki skor 4.']);
        $create($quarter(2, 9, 3))->assertJsonValidationErrors(['score' => 'Skor harus naik mengikuti target. Pada 2026 Q3, target 9 memiliki skor 4.']);
        $create($quarter(2, 2, 3))->assertJsonValidationErrors(['score' => 'Skor harus naik mengikuti target. Pada 2026 Q1, target 3 memiliki skor 2.']);
        $create($quarter(1, 20, 1, 2025))->assertOk();
        // Periods sharing a target share its range.
        $create($quarter(1, 5, 2, 2027))->assertOk();
        $create($quarter(2, 5, 2, 2027))->assertOk();
        $this->assertSame([[5.0, 2.0]], $targets->ladder('publikasi', 2027)->map(fn ($tier) => [$tier->target, $tier->score])->all());

        // Pengajaran: both semesters of an academic year.
        $create(['category' => 'pengajaran', 'academic_year' => '2025/2026', 'semester' => 'Ganjil', 'target' => 10, 'score' => 60])->assertOk();
        $create(['category' => 'pengajaran', 'academic_year' => '2025/2026', 'semester' => 'Genap', 'target' => 12, 'score' => 80])->assertOk()
            ->assertJsonPath('message', 'Target Pengajaran 2025/2026 Genap (12 → skor 80) disimpan.');
        $this->assertSame([10.0, 12.0], $targets->ladder('pengajaran', 2025)->pluck('target')->all());
        $this->assertEquals(['year' => 2025, 'term' => 2, 'target' => 12, 'score' => 80], (array) DB::table('character_building_targets')
            ->where('category', 'pengajaran')->where('term', 2)->first(['year', 'term', 'target', 'score']));
        $this->assertSame(['2025/2026 Ganjil', '2025/2026 Genap'], $targets->grouped()['pengajaran']->pluck('label')->all());

        foreach ([
            $quarter(1, 5, 7, 2028),
            ['category' => 'publikasi', 'year' => 2028, 'quarter' => 1, 'target' => 5],
            $quarter(1, -1, 1, 2028),
            ['category' => 'publikasi', 'year' => 2028, 'quarter' => 1, 'target' => '3.555', 'score' => 1],
            ['category' => 'publikasi', 'year' => 2026, 'quarter' => 5, 'target' => 3, 'score' => 1],
            ['category' => 'publikasi', 'academic_year' => '2025/2026', 'semester' => 'Ganjil', 'target' => 3, 'score' => 1],
            ['category' => 'pengajaran', 'academic_year' => '2025/2027', 'semester' => 'Ganjil', 'target' => 3, 'score' => 1],
            ['category' => 'pengajaran', 'year' => 2026, 'quarter' => 1, 'target' => 3, 'score' => 1],
            ['category' => 'lainnya', 'year' => 2026, 'quarter' => 1, 'target' => 3, 'score' => 1],
        ] as $invalid) {
            $create($invalid)->assertUnprocessable();
        }
        $create($quarter(1, 5, 7, 2028))->assertJsonValidationErrors(['score' => 'Skor Publikasi maksimal 6.']);

        // Editing moves a target, but never onto a period that already has one.
        $id = DB::table('character_building_targets')->where('category', 'publikasi')->where('year', 2026)->where('term', 4)->value('id');
        $this->putJson(route('characterbuilding.update'), ['id' => $id, 'year' => 2026, 'quarter' => 2, 'target' => 11, 'score' => 6])
            ->assertUnprocessable()->assertJsonValidationErrors(['quarter' => 'Target 2026 Q2 sudah ada. Ubah target tersebut atau hapus terlebih dahulu.']);
        $this->putJson(route('characterbuilding.update'), ['id' => $id, 'year' => 2026, 'quarter' => 4, 'target' => 8, 'score' => 6])
            ->assertJsonValidationErrors('score');
        $this->putJson(route('characterbuilding.update'), ['id' => $id, 'year' => 2026, 'quarter' => 4, 'target' => 12, 'score' => 6])
            ->assertOk()->assertJsonPath('message', 'Target Publikasi 2026 Q4 (12 judul Scopus → skor 6) diperbarui.');
        $this->putJson(route('characterbuilding.update'), ['id' => $id, 'year' => 2028, 'quarter' => 1, 'target' => 12, 'score' => 6])->assertOk();
        $this->assertSame(['2028 Q1', '2027 Q1', '2027 Q2', '2026 Q1', '2026 Q2', '2026 Q3', '2025 Q1'], $targets->grouped()['publikasi']->pluck('label')->all());

        $this->deleteJson(route('characterbuilding.delete'), ['id' => $id])->assertOk()
            ->assertJsonPath('message', 'Target Publikasi 2028 Q1 (12 judul Scopus → skor 6) dihapus.');
        $this->deleteJson(route('characterbuilding.delete'), ['id' => $id])->assertUnprocessable();
        $this->assertSame(8, DB::table('character_building_targets')->count());
    }

    public function test_publication_tab_counts_cbdc_scopus_titles_of_the_last_upload_in_the_quarter(): void
    {
        $this->importFm(7, 2, 'July paper');
        $this->importFm(9, 4, 'September paper');
        $this->actingAs((new User)->forceFill(['id' => 1]));
        foreach ([[3, 1, 4], [4, 2, 6]] as [$quarter, $target, $score]) {
            $this->postJson(route('characterbuilding.create'), ['category' => 'publikasi', 'year' => 2026, 'quarter' => $quarter, 'target' => $target, 'score' => $score])->assertOk();
        }

        $data = app(CharacterBuildingProgress::class)->publication(2026, 3);
        $this->assertSame(9, (int) $data['batch']->month);
        $this->assertSame(['D001'], $data['lecturers']->pluck('code')->all());
        // Non Scopus titles are listed but not counted; other programs are left out.
        $this->assertSame(['September paper', 'Non Scopus paper'], $data['publications']->pluck('title')->all());
        $this->assertSame([1.0, 1.0, 1.0, 4.0, 2.0, 1.0], [$data['summary']['realization'], $data['summary']['target']->target,
            $data['summary']['reached']->target, $data['summary']['earned'], $data['summary']['next']->target, $data['summary']['gap']]);

        $html = $this->postJson(route('characterbuilding.read'), ['tab' => 'publikasi', 'year' => 2026, 'quarter' => 3])->assertOk()->json('html');
        $this->assertStringContainsString('September 2026', $html);
        $this->assertStringContainsString('Dosen CBDC (1)', $html);
        $this->assertMatchesRegularExpression('/data-cb-stat="score">4</', $html);
        $this->assertStringNotContainsString('Rentang', $html);
        $this->assertStringContainsString('Tercapai', $html);
        $this->assertStringContainsString('Kurang 1 untuk skor 6 (target 2).', $html);
        $this->assertStringNotContainsString('D002', $html);
        $empty = $this->postJson(route('characterbuilding.read'), ['tab' => 'publikasi', 'year' => 2026, 'quarter' => 4])->json('html');
        $this->assertStringContainsString('Belum ada upload Publikasi FM pada 2026 Q4', $empty);
        $this->assertMatchesRegularExpression('/data-cb-stat="target">2</', $empty);
        $this->assertStringContainsString('Belum ditetapkan; isi pada tab Target Skor.',
            $this->postJson(route('characterbuilding.read'), ['tab' => 'publikasi', 'year' => 2025, 'quarter' => 4])->json('html'));
        $this->postJson(route('characterbuilding.read'), ['tab' => 'pengajaran', 'year' => 2026, 'quarter' => 1])->assertUnprocessable();
        $this->assertSame([2026, 3], app(CharacterBuildingProgress::class)->defaultQuarter('publikasi'));
    }

    public function test_comdev_tab_counts_cbdc_activities_of_the_semester_holding_most_of_the_quarter(): void
    {
        $this->assertSame([[2025, 'Ganjil'], [2025, 'Genap'], [2025, 'Genap'], [2026, 'Ganjil']],
            array_map(fn ($quarter) => CharacterBuildingProgress::semesterFor(2026, $quarter), range(1, 4)));
        $id = DB::table('community_development_imports')->insertGetId([
            'year_start' => 2025, 'semester' => 'Ganjil', 'filename' => 'ganjil.xlsx', 'column_mapping' => '{}', 'header_rows' => '{}',
        ]);
        DB::table('community_development_programs')->insert([
            ['import_id' => $id, 'program_key' => 'cbdc', 'source_program' => 'CBDC', 'is_total' => false, 'faculty' => 10, 'activities' => 26, 'ratio' => 2.6, 'source_row' => 2],
            ['import_id' => $id, 'program_key' => 'cs', 'source_program' => 'Prodi CS', 'is_total' => false, 'faculty' => 21, 'activities' => 38, 'ratio' => 1.8, 'source_row' => 3],
        ]);
        DB::table('community_development_lecturers')->insert([
            ['import_id' => $id, 'kode_dosen' => 'D001', 'reported_activities' => 3, 'score' => 5, 'status' => 'Sudah PkM', 'source_row' => 2],
            ['import_id' => $id, 'kode_dosen' => 'D002', 'reported_activities' => 1, 'score' => 6, 'status' => 'Sudah PkM', 'source_row' => 3],
        ]);
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $this->postJson(route('characterbuilding.create'), ['category' => 'comdev', 'year' => 2026, 'quarter' => 1, 'target' => 30, 'score' => 4]);
        $this->postJson(route('characterbuilding.create'), ['category' => 'comdev', 'year' => 2026, 'quarter' => 2, 'target' => 20, 'score' => 3])->assertOk();

        $data = app(CharacterBuildingProgress::class)->comdev(2026, 1);
        $this->assertSame('2025/2026 Ganjil', $data['semester']);
        $this->assertSame(['D001'], $data['lecturers']->pluck('code')->all());
        $this->assertSame('Rasio', $data['source']);
        $this->assertSame([26.0, 30.0, 20.0, 3.0, 4.0], [$data['summary']['realization'], $data['summary']['target']->target,
            $data['summary']['reached']->target, $data['summary']['earned'], $data['summary']['gap']]);

        $html = $this->postJson(route('characterbuilding.read'), ['tab' => 'comdev', 'year' => 2026, 'quarter' => 1])->assertOk()->json('html');
        $this->assertStringContainsString('2025/2026 Ganjil', $html);
        $this->assertStringContainsString('Dosen Karakter', $html);
        $this->assertStringNotContainsString('Dosen Informatika', $html);
        $this->assertStringContainsString('Belum tercapai', $html);
        $this->assertMatchesRegularExpression('/data-cb-stat="score">3</', $html);
        $this->assertStringContainsString('Kurang 4 untuk skor 4 (target 30).', $html);
        $this->assertStringContainsString('Belum ada upload Community Development untuk semester 2025/2026 Genap',
            $this->postJson(route('characterbuilding.read'), ['tab' => 'comdev', 'year' => 2026, 'quarter' => 2])->json('html'));
        // The latest quarter up to now whose semester was uploaded.
        $this->assertSame([2026, 1], app(CharacterBuildingProgress::class)->defaultQuarter('comdev'));

        // Without activities on the Rasio row, the activities CBDC lecturers reported count.
        DB::table('community_development_programs')->where('program_key', 'cbdc')->update(['activities' => null]);
        $data = app(CharacterBuildingProgress::class)->comdev(2026, 1);
        $this->assertSame(['Keterlibatan', 3.0, null], [$data['source'], $data['summary']['realization'], $data['summary']['earned']]);
        $this->assertStringContainsString('Belum mencapai target terendah (20).',
            $this->postJson(route('characterbuilding.read'), ['tab' => 'comdev', 'year' => 2026, 'quarter' => 1])->json('html'));
    }

    public function test_public_cbdc_dashboard_shows_realization_target_and_score_per_quarter(): void
    {
        $this->importFm(9, 4, 'September paper');
        $id = DB::table('community_development_imports')->insertGetId([
            'year_start' => 2026, 'semester' => 'Ganjil', 'filename' => 'ganjil.xlsx', 'column_mapping' => '{}', 'header_rows' => '{}',
        ]);
        DB::table('community_development_programs')->insert([
            'import_id' => $id, 'program_key' => 'cbdc', 'source_program' => 'CBDC', 'is_total' => false, 'faculty' => 10, 'activities' => 26, 'ratio' => 2.6, 'source_row' => 2,
        ]);
        DB::table('community_development_lecturers')->insert([
            'import_id' => $id, 'kode_dosen' => 'D001', 'reported_activities' => 3, 'score' => 5, 'status' => 'Sudah PkM', 'source_row' => 2,
        ]);
        $this->actingAs((new User)->forceFill(['id' => 1]));
        foreach ([['publikasi', 3, 1, 4], ['publikasi', 4, 2, 6], ['comdev', 2, 20, 3], ['comdev', 4, 30, 4]] as [$category, $quarter, $target, $score]) {
            $this->postJson(route('characterbuilding.create'), compact('category', 'quarter', 'target', 'score') + ['year' => 2026])->assertOk();
        }
        auth()->logout();

        // Without a period: the latest quarter with a Publikasi FM upload.
        $response = $this->get(route('cbdc-dashboard'))->assertOk();
        $page = $response->getContent();
        $this->assertStringContainsString('Dashboard <span>CBDC.</span>', $page);
        $this->assertStringContainsString('Skor 2026 Q3', $page);
        $this->assertSame(['1', '1', '4'], $this->areaStats($page, 'publikasi'));
        $this->assertStringContainsString('Kurang 1 untuk skor 6 (target 2).', $page);
        $this->assertStringContainsString('Upload Publikasi FM September 2026.', $page);
        // The card headings are not <header>s, which the publication pages paint navy.
        $this->assertStringContainsString('<div class="cbdc-area-head">', $page);
        $this->assertStringNotContainsString('<header><span class="cbdc-area-icon">', $page);
        // Community Development is written in full on the page.
        preg_match('/<main\b.*<\/main>/s', $page, $main);
        $this->assertStringContainsString('<h3>Community Development</h3>', $main[0]);
        $this->assertStringNotContainsString('ComDev', $main[0]);
        // Q3 uses the Genap semester, which was not uploaded.
        $this->assertSame(['—', '—', '—'], $this->areaStats($page, 'comdev'));
        $this->assertStringContainsString('Belum ada upload Community Development untuk semester 2025/2026 Genap.', $page);
        $this->assertStringNotContainsString('Kelola target CBDC', $page);

        // The lecturer details follow the cards above: Publikasi, Pengajaran, Community Development, Pengembangan Diri.
        $this->assertSame(['publikasi', 'pengajaran', 'comdev', 'pengembangan_diri'],
            preg_match_all('/data-cbdc-detail="([a-z_]+)"/', $page, $cards) ? $cards[1] : []);
        $this->assertSame(4, substr_count($page, 'Coming Soon'), 'two score cards and two detail cards');
        $this->assertSame(['1', '1', '1'], $this->detailStats($page, 'publikasi'));
        $this->assertMatchesRegularExpression('/data-cbdc-open="publikasi"[^>]*aria-controls="cbdcPublicationDetail"\s*>Detail dosen/', $page);
        $this->assertSame(['—', '—', '—'], $this->detailStats($page, 'comdev'));
        $this->assertMatchesRegularExpression('/data-cbdc-open="comdev"[^>]*disabled[^>]*>List dosen/', $page);
        $this->assertStringContainsString('id="cbdcPublicationDetail"', $page);
        $this->assertStringContainsString('id="cbdcComdevDetail"', $page);
        $this->assertStringContainsString('initializeCbdc(window.cbdcDashboard, shared)', $page);
        // The page script gets the CBDC lecturers and their publications, as the Dashboard Riset shows them.
        $details = $response->viewData('cbdc')['details'];
        $this->assertSame([['code' => 'D001', 'name' => 'Dosen Karakter', 'rank' => 'L', 'faculty' => 'FM',
            'nonScopus' => 1.0, 'scopus' => 1.0, 'firstAuthor' => 1, 'score' => 4]], $details['publication']['faculty']);
        $this->assertSame([
            ['September paper', 'Jurnal', 'Scopus', 'Scopus FM', 'Y', 'R91', 'Dosen Karakter'],
            ['Non Scopus paper', 'Jurnal', 'Nscopus', 'Non Scopus FM', 'N', 'R93', 'Dosen Karakter'],
        ], array_map(fn ($row) => [$row['title'], $row['type'], $row['index'], $row['category'], $row['firstAuthor'], $row['request'], $row['name']],
            $details['publication']['publications']));
        $this->assertSame('2026-9', $details['publication']['period']);
        $this->assertSame([], $details['comdev']['lecturers']);
        $this->assertStringContainsString('Dosen Karakter', $page);
        $this->assertStringNotContainsString('Dosen Informatika', $page);

        // Q4 uses Ganjil 2026/2027: 26 activities reach the range of 20 (score 3), not the Q4 target 30.
        $page = $this->get(route('cbdc-dashboard', ['year' => 2026, 'quarter' => 4]))->assertOk()->getContent();
        $this->assertSame(['26', '30', '3'], $this->areaStats($page, 'comdev'));
        $this->assertStringContainsString('Belum tercapai', $page);
        $this->assertSame(['—', '2', '—'], $this->areaStats($page, 'publikasi'));
        $this->assertSame(['10', '26', '2,6'], $this->detailStats($page, 'comdev'));
        $this->assertMatchesRegularExpression('/data-cbdc-open="publikasi"[^>]*disabled[^>]*>Detail dosen/', $page);
        $this->assertMatchesRegularExpression('/data-cbdc-open="comdev"[^>]*aria-controls="cbdcComdevDetail"\s*>List dosen/', $page);
        $this->assertStringContainsString('COMMUNITY DEVELOPMENT · 2026/2027 GANJIL', $page);
        $this->assertSame([['code' => 'D001', 'name' => 'Dosen Karakter', 'activities' => 3, 'score' => 5.0, 'status' => 'Sudah PkM']],
            $this->get(route('cbdc-dashboard', ['year' => 2026, 'quarter' => 4]))->viewData('cbdc')['details']['comdev']['lecturers']);
        preg_match('/data-cbdc-trend="comdev">.*?<\/article>/s', $page, $trend);
        $this->assertMatchesRegularExpression('/<tr class="is-selected">\s*<td><a [^>]*quarter=4[^>]*aria-current="true"\s*>Q4<\/a><\/td>\s*<td class="cbdc-muted">2026\/2027 Ganjil<\/td>/', $trend[0]);
        $this->assertSame(['Q1', 'Q2', 'Q3', 'Q4'], preg_match_all('/>(Q\d)<\/a>/', $trend[0], $quarters) ? $quarters[1] : []);

        $this->get(route('cbdc-dashboard', ['quarter' => 5]))->assertSessionHasErrors('quarter');
        // A year alone shows its Q4.
        $page = $this->get(route('cbdc-dashboard', ['year' => 2023]))->assertOk()->getContent();
        $this->assertStringContainsString('Skor 2023 Q4', $page);
        $this->assertSame(['—', '—', '—'], $this->areaStats($page, 'publikasi'));
    }

    public function test_the_header_groups_the_public_dashboards_in_one_menu(): void
    {
        $elsewhere = view('landing.components.header')->render();
        $page = $this->get(route('cbdc-dashboard'))->assertOk()->getContent();
        preg_match('/<li class="dropdown" data-nav-dashboards>(.*?)<\/ul>/s', $page, $menu);
        preg_match_all('/<li><a href="([^"]+)"([^>]*)>([^<]+)<\/a><\/li>/', $menu[1], $links, PREG_SET_ORDER);
        $this->assertSame([
            [route('publication-dashboard'), 'Dashboard Riset'], [route('community-dashboard'), 'Dashboard ComDev'],
            [route('lrc-dashboard'), 'Dashboard LRC'], [route('cbdc-dashboard'), 'Dashboard CBDC'],
        ], array_map(fn ($link) => [$link[1], $link[3]], $links));
        $this->assertStringContainsString('aria-current="page"', $links[3][2]);
        $this->assertMatchesRegularExpression('/<a href="#"\s+class="active"\s+aria-haspopup="true"><span>Dashboard<\/span>/', $menu[1]);
        $this->assertMatchesRegularExpression('/<ul\s+class="dropdown-active"\s*>/', $menu[1]);
        // Outside the dashboards the menu starts closed on mobile.
        $this->assertMatchesRegularExpression('/<a href="#"\s+aria-haspopup="true"><span>Dashboard<\/span>/', $elsewhere);
        $this->assertStringNotContainsString('dropdown-active', $elsewhere);
        // The dashboards are no longer separate items of the main menu.
        $this->assertSame(1, substr_count($page, 'href="'.route('lrc-dashboard').'"'));
    }

    public function test_migration_rolls_back(): void
    {
        (require base_path('Modules/CharacterBuilding/database/migrations/2026_11_04_000000_create_character_building_targets_table.php'))->down();
        $this->assertFalse(Schema::hasTable('character_building_targets'));
        $this->getJson(route('characterbuilding.index'))->assertOk();
        $this->assertStringContainsString('Skor 2026 Q4', $this->get(route('cbdc-dashboard'))->assertOk()->getContent());
    }

    /** @return array{0: string, 1: string, 2: string} realization, target and score of an area card */
    private function areaStats(string $page, string $area): array
    {
        preg_match('/data-cbdc-area="'.$area.'">(.*?)<\/article>/s', $page, $card);
        preg_match_all('/data-cbdc-stat="(realization|target|score)">([^<]*)</', $card[1], $stats);

        return $stats[2];
    }

    /** @return list<string> the three numbers of a lecturer detail card */
    private function detailStats(string $page, string $area): array
    {
        preg_match('/data-cbdc-detail="'.$area.'">(.*?)<\/article>/s', $page, $card);
        preg_match_all('/<dd>([^<]*)<\/dd>/', $card[1], $stats);

        return $stats[1];
    }

    private function importFm(int $month, int $cbdcScore, string $title): void
    {
        $headers = ['RequestCode', 'Kode Dosen', 'Kampus', 'Submitted', 'Bobot', 'Title', 'Jenis', 'Tipe Publikasi', 'St2026', 'First Author', 'Prodi KPI'];
        $path = $this->makeWorkbook([
            'MALANG' => [$headers,
                ["R{$month}1", 'D001', 'MALANG', 'Scopus FM', '1', $title, 'Jurnal', 'Scopus', 'Accepted', 'Y', 'Character Building Development Center'],
                ["R{$month}2", 'D002', 'MALANG', 'Scopus FM', '1', 'Other paper', 'Jurnal', 'Scopus', 'Accepted', 'Y', 'Computer Science (BINUS @Malang)'],
                ["R{$month}3", 'D001', 'MALANG', 'Non Scopus FM', '1', 'Non Scopus paper', 'Jurnal', 'Nscopus', 'Accepted', 'N', 'Character Building Development Center'],
            ],
            'KPI' => [
                ['Kode Dosen', 'Nama Dosen', 'Jurusan Binaan', 'JJA', 'Faculty Type', 'Non Scopus RTTO', 'Scopus RTTO', 'Score KPI RTTO'],
                ['D001', 'Dosen Karakter', 'CBDC', 'L', 'FM', 0, 1, $cbdcScore],
                ['D002', 'Dosen Informatika', 'Computer Science', 'L', 'FM', 0, 1, 6],
            ],
        ]);
        app(PublicationImporter::class)->import($path, "fm-{$month}.xlsx", 2026, $month, (int) ceil($month / 3));
    }
}
