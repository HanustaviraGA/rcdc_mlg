<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Lecturers\ProdiFmRoadmap;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Modules\RoadmapLRC\Services\RoadmapWorkbook;
use Tests\Support\CreatesWorkbook;
use Tests\TestCase;

class RoadmapUploadTest extends TestCase
{
    use CreatesWorkbook;

    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Run PHP with -d extension=pdo_sqlite.');
        }
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        foreach (['2026_11_15_000000_create_prodi_fm_roadmaps_table.php', '2026_11_16_000000_add_new_recruits_to_prodi_fm_roadmaps_table.php'] as $migration) {
            (require base_path('Modules/RoadmapLRC/database/migrations/'.$migration))->up();
        }
    }

    protected function tearDown(): void
    {
        $this->removeWorkbooks();
        parent::tearDown();
    }

    private function sheet(): array
    {
        return [
            [], [],
            ['', ' PRODI ', ' JUMLAH FM 2026 ', 'JJP', '', "JUMLAH FM\n2027", 'JJP', ''],
            ['', '', '', ' S2 ', 'S3', '', 'S2', 'S3'],
            [],
            ['', 'CS ', '21', '18', '4', '22', '14', '8'],
            ['', 'EBC', '33', '22', '11', '34', '16', '18'],
            ['', ' TOTAL ', '999', '999', '999', '999', '999', '999'],
            ['', '', '', '999'],
        ];
    }

    private function workbook(?array $rows = null): string
    {
        return $this->makeWorkbook(['Sheet1' => [['unrelated data']], 'JJP ' => $rows ?? $this->sheet()]);
    }

    private function upload(string $path): UploadedFile
    {
        return new UploadedFile($path, 'roadmap.xlsx', null, null, true);
    }

    private function login(): void
    {
        $this->actingAs((new User)->forceFill(['id' => 1]));
    }

    private function importFile(string $path, array $extra = [])
    {
        return $this->postJson(route('roadmaplrc.upload.import'), $extra + [
            'roadmap' => $this->upload($path), 'preview_hash' => hash_file('sha256', $path), 'confirmed' => '1',
        ]);
    }

    public function test_preview_reads_multiline_headers_and_trailing_sheet_space_without_writing_data(): void
    {
        $this->login();
        $path = $this->workbook();
        $response = $this->postJson(route('roadmaplrc.upload.preview'), ['roadmap' => $this->upload($path)])
            ->assertOk()->assertJsonPath('summary.sheet', 'JJP ')->assertJsonPath('summary.selected', 4)
            ->assertJsonPath('summary.programs', ['CS', 'EBC'])->assertJsonPath('summary.years', [2026, 2027])
            ->assertJsonPath('summary.mismatches', 1)->assertJsonPath('preview_hash', hash_file('sha256', $path));
        $html = $response->json('preview_html');
        $this->assertStringContainsString('CS 2026: S2 + S3 = 22, Jumlah FM = 21.', $html);
        $this->assertStringContainsString('bg-light-warning', $html);
        $this->assertStringNotContainsString('999', $html);
        $this->assertSame(0, DB::table(ProdiFmRoadmap::TABLE)->count());
    }

    public function test_import_inserts_updates_and_preserves_nr_notes_and_unlisted_program_years(): void
    {
        $this->login();
        DB::table(ProdiFmRoadmap::TABLE)->insert([
            ['prodi' => 'CS', 'year' => 2026, 'jumlah_fm' => 20, 'jumlah_s2' => 16, 'jumlah_s3' => 4,
                'jumlah_nr_s2' => 2, 'jumlah_nr_s3' => 1, 'catatan_fm' => 'Catatan FM', 'catatan_s2' => 'Catatan S2',
                'catatan_s3' => 'Catatan S3', 'catatan_nr_s2' => 'Catatan NR S2', 'catatan_nr_s3' => 'Catatan NR S3'],
            ['prodi' => 'DP', 'year' => 2035, 'jumlah_fm' => 3, 'jumlah_s2' => 1, 'jumlah_s3' => 2,
                'jumlah_nr_s2' => 0, 'jumlah_nr_s3' => 0, 'catatan_fm' => null, 'catatan_s2' => null,
                'catatan_s3' => null, 'catatan_nr_s2' => null, 'catatan_nr_s3' => null],
        ]);
        $path = $this->workbook();
        $response = $this->importFile($path)->assertOk()->assertJsonPath('summary.inserted', 3)
            ->assertJsonPath('summary.updated', 1)->assertJsonPath('summary.unchanged', 0)
            ->assertJsonPath('summary.mismatches', 1)->assertJsonPath('period_label', ', 2026–2035');
        $this->assertStringContainsString('data-roadmap-mismatches="1"', $response->json('table_html'));
        $this->assertSame(5, DB::table(ProdiFmRoadmap::TABLE)->count());
        $row = DB::table(ProdiFmRoadmap::TABLE)->where(['prodi' => 'CS', 'year' => 2026])->first();
        $this->assertSame([21, 18, 4, 2, 1], [(int) $row->jumlah_fm, (int) $row->jumlah_s2, (int) $row->jumlah_s3, (int) $row->jumlah_nr_s2, (int) $row->jumlah_nr_s3]);
        foreach (['fm', 's2', 's3', 'nr_s2', 'nr_s3'] as $field) {
            $this->assertSame('Catatan '.strtoupper(str_replace('_', ' ', $field)), $row->{'catatan_'.$field});
        }
        $new = DB::table(ProdiFmRoadmap::TABLE)->where(['prodi' => 'EBC', 'year' => 2027])->first();
        $this->assertSame(['Entrepreneurship', 1, 0, 0], [$new->program_studi, (int) $new->urutan, (int) $new->jumlah_nr_s2, (int) $new->jumlah_nr_s3]);
        $this->assertSame(3, (int) DB::table(ProdiFmRoadmap::TABLE)->where('prodi', 'DP')->value('jumlah_fm'));
        $before = DB::table(ProdiFmRoadmap::TABLE)->orderBy('id')->get()->toArray();
        $this->importFile($path)->assertOk()->assertJsonPath('summary.inserted', 0)
            ->assertJsonPath('summary.updated', 0)->assertJsonPath('summary.unchanged', 4);
        $this->assertEquals($before, DB::table(ProdiFmRoadmap::TABLE)->orderBy('id')->get()->toArray());
    }

    public function test_values_are_validated_for_the_entire_sheet_before_any_write(): void
    {
        $this->login();
        foreach (['', '-1', '2.5', '10000', 'abc', '#VALUE!', '1,000'] as $value) {
            $rows = $this->sheet();
            $rows[6][7] = $value; // Last value, after all earlier records were valid.
            $this->importFile($this->workbook($rows))->assertUnprocessable()->assertJsonValidationErrors('roadmap');
            $this->assertSame(0, DB::table(ProdiFmRoadmap::TABLE)->count());
        }
        $rows[6][7] = '0';
        $rows[6][6] = '34.0';
        $this->importFile($this->workbook($rows))->assertOk();
        $this->assertSame(0, (int) DB::table(ProdiFmRoadmap::TABLE)->where(['prodi' => 'EBC', 'year' => 2027])->value('jumlah_s3'));
    }

    public function test_invalid_headers_programs_and_duplicate_years_are_rejected(): void
    {
        $variants = [];
        $rows = $this->sheet();
        $rows[2][1] = 'Nama';
        $variants[] = $rows;
        $rows = $this->sheet();
        $rows[3][7] = 'S2';
        $variants[] = $rows;
        $rows = $this->sheet();
        $rows[2][5] = 'JUMLAH FM 2026';
        $variants[] = $rows;
        $rows = $this->sheet();
        $rows[2][5] = 'JUMLAH FM 2200';
        $variants[] = $rows;
        foreach (['UNKNOWN', '', 'cs'] as $code) {
            $rows = $this->sheet();
            $rows[6][1] = $code;
            $variants[] = $rows;
        }
        $variants[] = array_slice($this->sheet(), 0, 5);
        $variants[] = [];
        foreach ($variants as $rows) {
            try {
                app(RoadmapWorkbook::class)->import($this->workbook($rows));
                $this->fail('Invalid workbook must be rejected.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('roadmap', $exception->errors());
            }
            $this->assertSame(0, DB::table(ProdiFmRoadmap::TABLE)->count());
        }
    }

    public function test_year_groups_are_detected_from_headers_instead_of_assuming_2026_to_2035(): void
    {
        $rows = $this->sheet();
        $rows[2][2] = 'JUMLAH FM 2038';
        $rows[2][5] = 'JUMLAH FM 2037';
        $parsed = app(RoadmapWorkbook::class)->read($this->workbook($rows));
        $this->assertSame([2037, 2038], $parsed['summary']['years']);
        $this->assertSame([2037, 22, 14, 8], array_values(array_diff_key($parsed['records'][0], ['prodi' => true])));
    }

    public function test_missing_sheet_and_corrupt_workbook_have_useful_errors(): void
    {
        $this->login();
        $path = $this->makeWorkbook(['Other' => $this->sheet()]);
        $this->postJson(route('roadmaplrc.upload.preview'), ['roadmap' => $this->upload($path)])
            ->assertUnprocessable()->assertJsonFragment(['roadmap' => ['Sheet JJP tidak ditemukan atau namanya ambigu pada file Excel. Data sebelumnya tetap tersimpan.']]);
        $path = $this->workbook();
        $zip = new \ZipArchive;
        $zip->open($path);
        $zip->deleteName('xl/workbook.xml');
        $zip->close();
        try {
            app(RoadmapWorkbook::class)->read($path);
            $this->fail('Corrupt workbook must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('File Excel tidak dapat dibaca.', $exception->errors()['roadmap'][0]);
        }
    }

    public function test_preview_and_import_require_login_and_valid_xlsx_uploads(): void
    {
        foreach (['roadmaplrc.upload.preview', 'roadmaplrc.upload.import'] as $route) {
            $this->postJson(route($route))->assertForbidden();
        }
        $this->login();
        foreach (['roadmaplrc.upload.preview', 'roadmaplrc.upload.import'] as $route) {
            $this->postJson(route($route))->assertUnprocessable()->assertJsonValidationErrors('roadmap');
            $this->postJson(route($route), ['roadmap' => UploadedFile::fake()->create('bad.xlsx', 10, 'text/plain')])
                ->assertUnprocessable()->assertJsonValidationErrors('roadmap');
            $this->postJson(route($route), ['roadmap' => UploadedFile::fake()->create('large.xlsx', 20481)])
                ->assertUnprocessable()->assertJsonValidationErrors('roadmap');
        }
    }

    public function test_import_requires_confirmation_and_the_same_file_as_the_preview(): void
    {
        $this->login();
        $path = $this->workbook();
        $this->importFile($path, ['confirmed' => '0'])->assertUnprocessable()->assertJsonValidationErrors('confirmed');
        $this->postJson(route('roadmaplrc.upload.import'), ['roadmap' => $this->upload($path)])
            ->assertUnprocessable()->assertJsonValidationErrors(['confirmed', 'preview_hash']);
        $this->importFile($path, ['preview_hash' => str_repeat('a', 64)])
            ->assertUnprocessable()->assertJsonPath('errors.roadmap.0', 'File berubah setelah pratinjau. Baca ulang pratinjau sebelum mengimpor.');
        $this->assertSame(0, DB::table(ProdiFmRoadmap::TABLE)->count());
    }

    public function test_database_failure_rolls_back_all_imported_records(): void
    {
        DB::unprepared("CREATE TRIGGER reject_roadmap BEFORE INSERT ON prodi_fm_roadmaps WHEN NEW.prodi = 'EBC' BEGIN SELECT RAISE(ABORT, 'roadmap write failure'); END");
        try {
            app(RoadmapWorkbook::class)->import($this->workbook());
            $this->fail('The database should reject the EBC row.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('roadmap write failure', $exception->getMessage());
        }
        $this->assertSame(0, DB::table(ProdiFmRoadmap::TABLE)->count());
    }

    public function test_import_reports_missing_migration(): void
    {
        Schema::drop(ProdiFmRoadmap::TABLE);
        $this->login();
        $this->importFile($this->workbook())->assertUnprocessable()
            ->assertJsonPath('errors.roadmap.0', 'Migrasi roadmap jumlah FM belum dijalankan. Data sebelumnya tetap tersimpan.');
    }
}
