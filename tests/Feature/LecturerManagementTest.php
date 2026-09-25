<?php

namespace Tests\Feature;

use App\Models\DataDosen;
use App\Models\User;
use App\Services\Lecturers\LecturerWorkbook;
use App\Services\Research\RectorateResearchImporter;
use App\Services\Research\RectorateResearchReader;
use App\Services\Research\ResearchGallery;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\Support\CreatesWorkbook;
use Tests\TestCase;

class LecturerManagementTest extends TestCase
{
    use CreatesWorkbook;

    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Run PHP with -d extension=pdo_sqlite.');
        }
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Http::fake(['*' => Http::response([], 503)]);
        Schema::create('database_dosen_new', function (Blueprint $table) {
            $table->string('kode_dosen')->primary();
            foreach (array_diff(array_keys(LecturerWorkbook::HEADERS), ['kode_dosen']) as $field) {
                $table->text($field)->nullable();
            }
            $table->timestamps();
        });
        (require database_path('migrations/2026_09_25_000000_add_visibility_and_soft_deletes_to_database_dosen_new.php'))->up();
        Schema::create('identitas_dosen', function (Blueprint $table) {
            $table->string('id_identitas')->primary();
            $table->string('kode_dosen');
            $table->text('foto_dosen')->nullable();
            $table->text('deskripsi_dosen')->nullable();
        });
        Schema::create('database_attribute_dosen', function (Blueprint $table) {
            $table->string('id_attribute')->primary();
            $table->string('kode_dosen');
            $table->text('attribute_dosen');
            $table->text('attribute_icon');
        });
        Schema::create('comdevs', function (Blueprint $table) {
            $table->string('ID')->primary();
            foreach (['kode_dosen', 'community_name', 'location', 'topic_name'] as $field) {
                $table->text($field);
            }
        });
        Schema::create('researchs', function (Blueprint $table) {
            $table->string('ID')->primary();
            foreach (['kode_dosen', 'title', 'budget_year', 'contract_number', 'abstract', 'source_of_fund', 'funding', 'researcher', 'permalink', 'institution'] as $field) {
                $table->text($field)->nullable();
            }
            $table->timestamps();
        });
        (require database_path('migrations/2026_09_08_000200_add_rectorate_research_imports.php'))->up();
        (require database_path('migrations/2026_09_24_000000_add_monthly_research_snapshots.php'))->up();
        DataDosen::create(['kode_dosen' => 'D001', 'nama_dosen' => 'Visible Lecturer', 'nama_gugus_binaan' => 'Computer Science']);
    }

    protected function tearDown(): void
    {
        $this->removeWorkbooks();
        parent::tearDown();
    }

    public function test_guest_cannot_delete_or_change_visibility(): void
    {
        $this->deleteJson(route('dosen.delete'), ['kode_dosen' => 'D001'])->assertUnauthorized();
        $this->patchJson(route('dosen.visibility'), ['kode_dosen' => 'D001', 'is_hidden' => true])->assertUnauthorized();
        $this->assertFalse(DataDosen::find('D001')->is_hidden);
        $this->assertFalse(DataDosen::find('D001')->trashed());
    }

    public function test_visibility_hides_public_profiles_and_can_be_reversed_in_admin(): void
    {
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $this->patchJson(route('dosen.visibility'), ['kode_dosen' => 'D001', 'is_hidden' => true])
            ->assertOk()->assertJsonPath('is_hidden', true);
        $this->postJson(route('dosen.init_table'), ['prodi' => 'All'])
            ->assertOk()->assertJsonPath('recordsTotal', 1)->assertJsonPath('data.0.is_hidden', true);
        $this->get(route('lecturers'))->assertOk()->assertDontSee('Visible Lecturer');
        $this->get(route('lecture_detail', 'D001'))->assertNotFound();
        Http::assertNothingSent();
        $this->patchJson(route('dosen.visibility'), ['kode_dosen' => 'D001', 'is_hidden' => false])
            ->assertOk()->assertJsonPath('is_hidden', false);
        $this->get(route('lecturers'))->assertOk()->assertSee('Visible Lecturer');
        $this->get(route('lecture_detail', 'D001'))->assertOk()->assertSee('Visible Lecturer');
    }

    public function test_soft_delete_preserves_profile_and_research_records(): void
    {
        DB::table('identitas_dosen')->insert(['id_identitas' => 'I1', 'kode_dosen' => 'D001', 'deskripsi_dosen' => 'Profile archive']);
        DB::table('database_attribute_dosen')->insert(['id_attribute' => 'A1', 'kode_dosen' => 'D001', 'attribute_dosen' => 'Researcher', 'attribute_icon' => 'bi-book']);
        $this->systemProject('S1', 'D001', 2026);
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $this->deleteJson(route('dosen.delete'), ['kode_dosen' => 'D001'])->assertOk()->assertJsonPath('success', true);

        $this->assertSoftDeleted('database_dosen_new', ['kode_dosen' => 'D001']);
        $this->assertSame(1, DB::table('identitas_dosen')->count());
        $this->assertSame(1, DB::table('database_attribute_dosen')->count());
        $this->assertSame(1, DB::table('researchs')->count());
        $this->assertCount(1, app(ResearchGallery::class)->projects());
        $this->postJson(route('dosen.init_table'))->assertOk()->assertJsonPath('recordsTotal', 0);
        $this->get(route('lecturers'))->assertOk()->assertDontSee('Visible Lecturer');
        $this->get(route('lecture_detail', 'D001'))->assertNotFound();
        $this->patchJson(route('dosen.visibility'), ['kode_dosen' => 'D001', 'is_hidden' => false])->assertNotFound();
    }

    public function test_visibility_and_deletion_inputs_are_validated(): void
    {
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $this->patchJson(route('dosen.visibility'), ['kode_dosen' => 'D001', 'is_hidden' => 'invalid'])
            ->assertUnprocessable()->assertJsonValidationErrors('is_hidden');
        $this->deleteJson(route('dosen.delete'))->assertUnprocessable()->assertJsonValidationErrors('kode_dosen');
        $this->deleteJson(route('dosen.delete'), ['kode_dosen' => 'UNKNOWN'])->assertNotFound();
        $this->assertSame(1, DataDosen::count());
    }

    public function test_public_search_cannot_bypass_visibility_or_program_filter(): void
    {
        DataDosen::create(['kode_dosen' => 'D002', 'nama_dosen' => 'Hidden Lecturer', 'nama_gugus_binaan' => 'Computer Science'])
            ->forceFill(['is_hidden' => true])->save();
        DataDosen::create(['kode_dosen' => 'D003', 'nama_dosen' => 'Archived Lecturer', 'nama_gugus_binaan' => 'Computer Science'])->delete();
        DataDosen::create(['kode_dosen' => 'D004', 'nama_dosen' => 'Computer Science Visitor', 'nama_gugus_binaan' => 'Design']);
        $response = $this->get(route('lecturers', ['search' => 'Computer Science', 'gugus' => 'Computer Science']))->assertOk();
        $this->assertSame(['D001'], $response->viewData('dosen')->pluck('kode_dosen')->all());
        $response->assertDontSee('Hidden Lecturer')->assertDontSee('Archived Lecturer')->assertDontSee('Computer Science Visitor');
        $this->get(route('lecture_detail', 'UNKNOWN'))->assertNotFound();
    }

    public function test_home_only_features_visible_lecturers(): void
    {
        DataDosen::create(['kode_dosen' => 'D002', 'nama_dosen' => 'Hidden Lecturer'])->forceFill(['is_hidden' => true])->save();
        DataDosen::create(['kode_dosen' => 'D003', 'nama_dosen' => 'Archived Lecturer'])->delete();
        Schema::create('v_statistik_prodi', function (Blueprint $table) {
            $table->string('nama_gugus_binaan');
            $table->integer('jml_fm');
        });
        Schema::create('umkm_partnership', function (Blueprint $table) {
            $table->integer('tahun_bergabung')->nullable();
            $table->string('cluster')->nullable();
        });
        $response = $this->get(route('home'))->assertOk()->assertDontSee('Hidden Lecturer')->assertDontSee('Archived Lecturer');
        $this->assertSame(['D001'], $response->viewData('dosen')->pluck('kode_dosen')->all());
    }

    public function test_detail_combines_both_sources_limits_to_three_and_links_to_filtered_gallery(): void
    {
        $this->systemProject('S1', 'D999', 2025, [['ID' => 'D001', 'name' => 'Visible Lecturer']]);
        $this->systemProject('S2', 'D001', 2023);
        $this->systemProject('S3', 'D001', 2022);
        $this->systemProject('S4', 'D0010', 2026);
        $this->importGrants();
        DB::table('comdevs')->insert(['ID' => 'C1', 'kode_dosen' => 'D001', 'community_name' => 'Community fallback', 'location' => 'Malang', 'topic_name' => 'Education']);

        $response = $this->get(route('lecture_detail', 'D001'))->assertOk();
        $projects = $response->viewData('researchs');
        $this->assertSame(5, $response->viewData('researchCount'));
        $this->assertCount(3, $projects);
        $this->assertSame([2026, 2025, 2024], $projects->pluck('year')->all());
        $this->assertSame(['rectorate', 'system', 'rectorate'], $projects->pluck('source')->all());
        $response->assertSee('Upload Rectorate')->assertSee('Sistem Riset')->assertSee('Lihat Lainnya')->assertSee('Community fallback')
            ->assertDontSee('System S2')->assertDontSee('System S4');
        $this->assertSame(3, substr_count($response->getContent(), 'class="timeline-item lecturer-research-project"'));
        foreach ($projects as $project) {
            $url = route('research-gallery.show', ['source' => $project['source'], 'id' => $project['id']]);
            $response->assertSee($url);
            $this->get($url)->assertOk()->assertSee($project['title']);
        }
        $galleryUrl = route('research-gallery.index', ['source' => 'all', 'person' => 'D001', 'sort' => 'new']);
        $response->assertSee($galleryUrl.'#showcase');
        $gallery = $this->get($galleryUrl)->assertOk()->viewData('projects');
        $this->assertSame(5, $gallery->total());
        $this->assertSame([2026, 2025, 2024, 2023, 2022], $gallery->pluck('year')->all());
        $this->assertTrue($gallery->every(fn ($project) => collect($project['people'])->contains('code', 'D001')));
    }

    public function test_lecturer_without_projects_shows_an_empty_state(): void
    {
        $this->get(route('lecture_detail', 'D001'))->assertOk()
            ->assertSee('Belum ada proyek riset dosen ini')->assertDontSee('Lihat Lainnya');
    }

    private function systemProject(string $id, string $code, int $year, array $people = []): void
    {
        DB::table('researchs')->insert([
            'ID' => $id, 'kode_dosen' => $code, 'title' => 'System '.$id, 'budget_year' => $year,
            'researcher' => json_encode($people), 'abstract' => 'Research description', 'updated_at' => now(),
        ]);
    }

    private function importGrants(): void
    {
        $rows = [];
        foreach ([[2026, 'P1', 'D001', 'Ketua'], [2026, 'P1', 'D002', 'Anggota'], [2024, 'P2', 'D001', 'Anggota']] as [$year, $code, $person, $role]) {
            $rows[] = array_values(array_replace(array_fill_keys(array_keys(RectorateResearchReader::HEADERS), ''), [
                'no' => count($rows) + 1, 'tahun_anggaran' => $year, 'kd_prop' => $code,
                'kode_dosen_nim' => $person, 'nama' => 'Researcher '.$person, 'peran' => $role,
                'kategori_fm_eksternal_mahasiswa' => 'FM', 'judul' => 'Grant '.$code, 'lokasi_kampus' => 'BINUS @Malang',
                'sdgs' => '8. Decent Work and Economic Growth', 'subtopik_research_roadmap' => 'Sustainable communities',
            ]));
        }
        app(RectorateResearchImporter::class)->import(
            $this->makeWorkbook(['MALANG' => [array_values(RectorateResearchReader::HEADERS), ...$rows]]),
            'grants.xlsx', year: 2026, month: 9
        );
    }
}
