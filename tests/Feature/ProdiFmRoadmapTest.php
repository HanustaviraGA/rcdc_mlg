<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Lecturers\ProdiFmRoadmap;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\RoadmapLRC\Database\Seeders\RoadmapLRCDatabaseSeeder;
use Tests\TestCase;

class ProdiFmRoadmapTest extends TestCase
{
    private const MIGRATION = 'Modules/RoadmapLRC/database/migrations/2026_11_15_000000_create_prodi_fm_roadmaps_table.php';

    private const PRODI = ['EBC', 'CS', 'DP', 'DKV', 'DI', 'PR', 'COMM', 'CBDC', 'ENG'];

    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Run PHP with -d extension=pdo_sqlite.');
        }
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        (require base_path(self::MIGRATION))->up();
    }

    public function test_seeder_fills_the_corrected_roadmap_of_sheet_jjp(): void
    {
        $this->seed(RoadmapLRCDatabaseSeeder::class);

        $this->assertSame(90, DB::table(ProdiFmRoadmap::TABLE)->count());
        $roadmap = app(ProdiFmRoadmap::class)->table();
        $this->assertSame(range(2026, 2035), $roadmap['years']);
        $this->assertSame(self::PRODI, array_column($roadmap['rows'], 'prodi'));
        $this->assertSame('Computer Science', $roadmap['rows'][1]['program_studi']);
        $this->assertSame(['id' => 15, 'fm' => 25, 's2' => 12, 's3' => 13, 'mismatch' => false, 'notes' => ['fm' => null, 's2' => null, 's3' => null]],
            $roadmap['rows'][1]['years'][2030]);
        // The FM totals of the sheet; S2 and S3 include the corrections.
        $this->assertSame([117, 120, 122, 125, 129, 132, 134, 137, 139, 141], array_values(array_column($roadmap['totals'], 'fm')));
        $this->assertSame([101, 85, 78, 78, 73, 66, 35, 25, 9, 8], array_values(array_column($roadmap['totals'], 's2')));
        $this->assertSame([16, 35, 44, 47, 56, 66, 99, 112, 130, 133], array_values(array_column($roadmap['totals'], 's3')));
        $this->assertSame(0, $roadmap['mismatches']);
        $this->assertSame([], array_keys(array_filter(array_column($roadmap['totals'], 'mismatch'))));
        // Each corrected number keeps the sheet's number in its note.
        $notes = collect($roadmap['rows'])->flatMap(fn (array $row) => collect($row['years'])->flatMap(fn (array $cell, int $year) => collect($cell['notes'])
            ->filter()->mapWithKeys(fn (string $note, string $field) => ["{$row['prodi']}-{$year}-{$field}" => [$cell[$field], $note]])))->all();
        $this->assertSame([
            'EBC-2033-s3' => [38, 'Sheet JJP: 39. Disesuaikan agar S2 + S3 sama dengan Jumlah FM (40).'],
            'EBC-2034-s3' => [41, 'Sheet JJP: 40. Disesuaikan agar S2 + S3 sama dengan Jumlah FM (41).'],
            'EBC-2035-s3' => [42, 'Sheet JJP: 41. Disesuaikan agar S2 + S3 sama dengan Jumlah FM (42).'],
            'CS-2026-s3' => [3, 'Sheet JJP: 4. Disesuaikan agar S2 + S3 sama dengan Jumlah FM (21).'],
            'CBDC-2031-s2' => [6, 'Sheet JJP: 5. Disesuaikan agar S2 + S3 sama dengan Jumlah FM (11).'],
            'ENG-2034-s2' => [0, 'Sheet JJP: 2. Disesuaikan agar S2 + S3 sama dengan Jumlah FM (2).'],
            'ENG-2035-s2' => [0, 'Sheet JJP: 2. Disesuaikan agar S2 + S3 sama dengan Jumlah FM (2).'],
        ], $notes);
    }

    public function test_seeding_again_keeps_numbers_and_notes_edited_on_the_page(): void
    {
        $this->seed(RoadmapLRCDatabaseSeeder::class);
        DB::table(ProdiFmRoadmap::TABLE)->where(['prodi' => 'CS', 'year' => 2030])->update(['jumlah_fm' => 26, 'catatan_fm' => 'Tambah satu dosen.']);
        DB::table(ProdiFmRoadmap::TABLE)->where(['prodi' => 'ENG', 'year' => 2035])->delete();
        $this->seed(RoadmapLRCDatabaseSeeder::class);

        $this->assertSame(90, DB::table(ProdiFmRoadmap::TABLE)->count());
        $this->assertSame([26, 'Tambah satu dosen.'], array_values((array) DB::table(ProdiFmRoadmap::TABLE)
            ->where(['prodi' => 'CS', 'year' => 2030])->first(['jumlah_fm', 'catatan_fm'])));
        $this->assertSame(0, DB::table(ProdiFmRoadmap::TABLE)->where(['prodi' => 'ENG', 'year' => 2035])->value('jumlah_s2'));
    }

    public function test_page_shows_each_number_as_an_input_that_opens_the_edit_modal(): void
    {
        $this->seed(RoadmapLRCDatabaseSeeder::class);
        $page = $this->page();

        $this->assertStringContainsString('Roadmap Jumlah FM', $page);
        $this->assertStringContainsString('beserta jenjang pendidikan S2 dan S3, 2026–2035.', $page);
        $this->assertStringNotContainsString('JJP<', $page);
        $this->assertStringNotContainsString('(JJP', $page);
        preg_match_all('/<th scope="col"[^>]*>([^<]+(?:<br>\d{4})?)<\/th>/', $page, $headers);
        $this->assertSame(['Prodi', 'Jumlah FM<br>2026', 'S2', 'S3', 'Jumlah FM<br>2027'], array_slice($headers[1], 0, 5));
        $this->assertCount(31, $headers[1]);
        preg_match_all('/data-roadmap-prodi="([^"]+)"/', $page, $rows);
        $this->assertSame([...self::PRODI, 'total'], $rows[1]);
        $this->assertStringContainsString('<span class="d-block text-muted fs-8 fw-normal">Visual Communication Design</span>', $page);
        $this->assertSame(270, substr_count($page, 'data-roadmap-edit '));

        $id = DB::table(ProdiFmRoadmap::TABLE)->where(['prodi' => 'CS', 'year' => 2026])->value('id');
        $this->assertSame(['id' => (string) $id, 'field' => 'fm', 'label' => 'Jumlah FM CS 2026', 'value' => '21', 'note' => '', 'title' => null, 'cell' => 'roadmap-year-start'],
            $this->input($page, 'CS-2026-fm'));
        $note = 'Sheet JJP: 4. Disesuaikan agar S2 + S3 sama dengan Jumlah FM (21).';
        $this->assertSame(['id' => (string) $id, 'field' => 's3', 'label' => 'S3 CS 2026', 'value' => '3', 'note' => $note, 'title' => "Catatan: {$note}", 'cell' => 'roadmap-has-note'],
            $this->input($page, 'CS-2026-s3'));
        $this->assertStringContainsString('aria-label="S3 CS 2026 (ada catatan)"', $page);
        // The total row only shows its sums.
        $this->assertMatchesRegularExpression('/<td class="roadmap-year-start" data-roadmap-cell="total-2026-fm"\s*>\s*117\s*<\/td>/', $page);
        $this->assertMatchesRegularExpression('/<td class="" data-roadmap-cell="total-2035-s3"\s*>\s*133\s*<\/td>/', $page);
        $this->assertStringNotContainsString('data-roadmap-mismatches', $page);

        $this->assertStringContainsString('data-update-url="'.route('roadmaplrc.update').'"', $page);
        $this->assertStringContainsString('id="roadmapLrcModal"', $page);
        $this->assertMatchesRegularExpression('/<label class="form-label required" for="roadmapLrcValue">Nilai<\/label>\s*<input class="form-control" type="number" id="roadmapLrcValue" name="nilai"/', $page);
        $this->assertMatchesRegularExpression('/<label class="form-label" for="roadmapLrcNote">Catatan<\/label>\s*<textarea class="form-control" id="roadmapLrcNote" name="catatan"/', $page);
    }

    public function test_editing_a_number_saves_it_with_its_note_and_returns_the_table(): void
    {
        $this->seed(RoadmapLRCDatabaseSeeder::class);
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $id = DB::table(ProdiFmRoadmap::TABLE)->where(['prodi' => 'CS', 'year' => 2026])->value('id');

        $response = $this->putJson(route('roadmaplrc.update'), ['id' => $id, 'field' => 'fm', 'nilai' => 22, 'catatan' => '  Sesuai Data Center November 2026.  '])
            ->assertOk()->assertJsonPath('message', 'Jumlah FM CS 2026 disimpan: 22.');
        $row = DB::table(ProdiFmRoadmap::TABLE)->find($id);
        $this->assertSame([22, 18, 3, 'Sesuai Data Center November 2026.'], [(int) $row->jumlah_fm, (int) $row->jumlah_s2, (int) $row->jumlah_s3, $row->catatan_fm]);
        // The new table shows the number, its note and that S2 + S3 no longer equals it.
        $table = $response->json('table_html');
        $this->assertSame('22', $this->input($table, 'CS-2026-fm')['value']);
        $this->assertSame("S2 + S3 = 21, tidak sama dengan jumlah FM 22\nCatatan: Sesuai Data Center November 2026.", $this->input($table, 'CS-2026-fm')['title']);
        $this->assertStringContainsString('data-roadmap-mismatches="1"', $table);
        $this->assertMatchesRegularExpression('/<td class="roadmap-year-start bg-light-warning" data-roadmap-cell="total-2026-fm"\s*title="S2 \+ S3 = 117, tidak sama dengan jumlah FM 118"\s*>\s*118\s*</', $table);
        $this->assertStringNotContainsString('<html', $table);

        // An empty note clears it.
        $this->putJson(route('roadmaplrc.update'), ['id' => $id, 'field' => 's2', 'nilai' => 19, 'catatan' => ''])->assertOk()
            ->assertJsonPath('message', 'S2 CS 2026 disimpan: 19.');
        $this->putJson(route('roadmaplrc.update'), ['id' => $id, 'field' => 'fm', 'nilai' => 22, 'catatan' => ' '])->assertOk();
        $row = DB::table(ProdiFmRoadmap::TABLE)->find($id);
        $this->assertSame([22, 19, null], [(int) $row->jumlah_fm, (int) $row->jumlah_s2, $row->catatan_fm]);
        $this->assertStringNotContainsString('data-roadmap-mismatches', $this->page());
    }

    public function test_editing_rejects_invalid_numbers_and_guests(): void
    {
        $this->seed(RoadmapLRCDatabaseSeeder::class);
        $id = DB::table(ProdiFmRoadmap::TABLE)->where(['prodi' => 'CS', 'year' => 2026])->value('id');
        $this->putJson(route('roadmaplrc.update'), ['id' => $id, 'field' => 'fm', 'nilai' => 22])
            ->assertForbidden()->assertJsonPath('message', 'Silakan login untuk mengubah roadmap.');

        $this->actingAs((new User)->forceFill(['id' => 1]));
        foreach ([
            [['field' => 'fm', 'nilai' => ''], 'nilai', 'Nilai wajib diisi.'],
            [['field' => 'fm', 'nilai' => '2.5'], 'nilai', 'Nilai harus berupa bilangan bulat.'],
            [['field' => 'fm', 'nilai' => -1], 'nilai', 'Nilai minimal 0.'],
            [['field' => 'fm', 'nilai' => 10000], 'nilai', 'Nilai maksimal 9999.'],
            [['field' => 'jumlah_fm', 'nilai' => 1], 'field', 'Kolom yang diubah harus Jumlah FM, S2, atau S3.'],
            [['field' => 's3', 'nilai' => 1, 'catatan' => str_repeat('a', 1001)], 'catatan', 'Catatan maksimal 1000 karakter.'],
            [['id' => 999, 'field' => 's3', 'nilai' => 1], 'id', 'Baris roadmap tidak ditemukan.'],
        ] as [$input, $key, $message]) {
            $this->putJson(route('roadmaplrc.update'), $input + ['id' => $id])->assertUnprocessable()->assertJsonPath("errors.{$key}.0", $message);
        }
        $this->assertSame(21, (int) DB::table(ProdiFmRoadmap::TABLE)->where('id', $id)->value('jumlah_fm'));
    }

    public function test_page_without_roadmap_rows_or_table_is_empty(): void
    {
        $page = $this->page();
        $this->assertStringContainsString('Belum ada data roadmap jumlah FM.', $page);
        $this->assertStringNotContainsString('<table', $page);

        (require base_path(self::MIGRATION))->down();
        $this->assertFalse(Schema::hasTable(ProdiFmRoadmap::TABLE));
        $this->assertStringContainsString('Belum ada data roadmap jumlah FM.', $this->page());
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $this->putJson(route('roadmaplrc.update'), ['id' => 1, 'field' => 'fm', 'nilai' => 1])
            ->assertUnprocessable()->assertJsonPath('errors.id.0', 'Migrasi roadmap jumlah FM belum dijalankan.');
    }

    private function page(): string
    {
        return base64_decode($this->getJson(route('roadmaplrc.index'))->assertOk()->json('page'));
    }

    /** @return array{id: string, field: string, label: string, value: string, note: string, title: ?string, cell: string} the input of a cell */
    private function input(string $html, string $key): array
    {
        $this->assertSame(1, preg_match('/<td class="([^"]*)" data-roadmap-cell="'.preg_quote($key, '/').'"[^>]*>\s*(<input[^>]*>)/', $html, $match), "No input for {$key}");
        preg_match_all('/\s(data-[a-z]+|title)="([^"]*)"/', $match[2], $attributes);
        $attributes = array_map(fn ($value) => html_entity_decode($value, ENT_QUOTES), array_combine($attributes[1], $attributes[2]));

        return ['id' => $attributes['data-id'], 'field' => $attributes['data-field'], 'label' => $attributes['data-label'], 'value' => $attributes['data-value'],
            'note' => $attributes['data-note'], 'title' => $attributes['title'] ?? null, 'cell' => $match[1]];
    }
}
