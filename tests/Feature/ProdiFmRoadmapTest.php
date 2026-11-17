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

    private const NR_MIGRATION = 'Modules/RoadmapLRC/database/migrations/2026_11_16_000000_add_new_recruits_to_prodi_fm_roadmaps_table.php';

    private const PRODI = ['EBC', 'CS', 'DP', 'DKV', 'DI', 'PR', 'COMM', 'CBDC', 'ENG'];

    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Run PHP with -d extension=pdo_sqlite.');
        }
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        (require base_path(self::MIGRATION))->up();
        (require base_path(self::NR_MIGRATION))->up();
    }

    public function test_seeder_fills_the_agreed_roadmap_with_its_new_fm_recruits(): void
    {
        $this->seed(RoadmapLRCDatabaseSeeder::class);

        $this->assertSame(90, DB::table(ProdiFmRoadmap::TABLE)->count());
        $roadmap = app(ProdiFmRoadmap::class)->table();
        $this->assertSame(range(2026, 2035), $roadmap['years']);
        $this->assertSame(self::PRODI, array_column($roadmap['rows'], 'prodi'));
        $this->assertSame('Computer Science', $roadmap['rows'][1]['program_studi']);
        $this->assertSame(['id' => 15, 'fm' => 25, 's2' => 12, 's3' => 13, 'nr_s2' => 0, 'nr_s3' => 1, 'mismatch' => false,
            'notes' => ['fm' => null, 's2' => null, 's3' => null, 'nr_s2' => null, 'nr_s3' => null]], $roadmap['rows'][1]['years'][2030]);
        $totals = fn (string $field) => array_values(array_column($roadmap['totals'], $field));
        $this->assertSame([117, 119, 122, 125, 129, 131, 134, 138, 141, 143], $totals('fm'));
        $this->assertSame([99, 83, 78, 78, 73, 65, 35, 25, 11, 10], $totals('s2'));
        $this->assertSame([18, 36, 44, 47, 56, 66, 99, 113, 130, 133], $totals('s3'));
        $this->assertSame([0, 1, 2, 1, 2, 0, 1, 1, 2, 0], $totals('nr_s2'));
        $this->assertSame([0, 1, 1, 2, 2, 2, 3, 2, 3, 2], $totals('nr_s3'));
        $this->assertSame(0, $roadmap['mismatches']);
        $this->assertSame([], array_keys(array_filter(array_column($roadmap['totals'], 'mismatch'))));
    }

    public function test_seeding_again_keeps_numbers_and_notes_edited_on_the_page(): void
    {
        $this->seed(RoadmapLRCDatabaseSeeder::class);
        DB::table(ProdiFmRoadmap::TABLE)->where(['prodi' => 'CS', 'year' => 2030])
            ->update(['jumlah_fm' => 26, 'catatan_fm' => 'Tambah satu dosen.', 'jumlah_nr_s2' => 1, 'catatan_nr_s2' => 'Dosen praktisi.']);
        DB::table(ProdiFmRoadmap::TABLE)->where(['prodi' => 'ENG', 'year' => 2034])->delete();
        $this->seed(RoadmapLRCDatabaseSeeder::class);

        $this->assertSame(90, DB::table(ProdiFmRoadmap::TABLE)->count());
        $this->assertSame([26, 'Tambah satu dosen.', 1, 'Dosen praktisi.'], array_values((array) DB::table(ProdiFmRoadmap::TABLE)
            ->where(['prodi' => 'CS', 'year' => 2030])->first(['jumlah_fm', 'catatan_fm', 'jumlah_nr_s2', 'catatan_nr_s2'])));
        $this->assertSame([4, 2], array_values((array) DB::table(ProdiFmRoadmap::TABLE)
            ->where(['prodi' => 'ENG', 'year' => 2034])->first(['jumlah_fm', 'jumlah_nr_s2'])));
    }

    public function test_page_shows_each_number_and_new_fm_recruit_as_an_input_that_opens_the_edit_modal(): void
    {
        $this->seed(RoadmapLRCDatabaseSeeder::class);
        DB::table(ProdiFmRoadmap::TABLE)->where(['prodi' => 'CS', 'year' => 2027])->update(['catatan_nr_s3' => 'Lulusan luar negeri.']);
        $page = $this->page();

        $this->assertStringContainsString('Roadmap Jumlah FM', $page);
        $this->assertStringContainsString('beserta jenjang pendidikan S2 dan S3, serta New FM Recruit (NR) yang masuk setiap tahun, 2026–2035.', $page);
        $this->assertStringNotContainsString('JJP<', $page);
        preg_match_all('/<th scope="col"[^>]*>([^<]+(?:<br>\d{4})?)<\/th>/', $page, $headers);
        $this->assertSame(['Prodi', 'Jumlah FM<br>2026', 'S2', 'S3', 'NR S2', 'NR S3', 'Jumlah FM<br>2027'], array_slice($headers[1], 0, 7));
        $this->assertCount(51, $headers[1]);
        $this->assertStringContainsString('<th scope="col" class="roadmap-nr" title="New FM Recruit bergelar S3 yang masuk tahun 2027">NR S3</th>', $page);
        preg_match_all('/data-roadmap-prodi="([^"]+)"/', $page, $rows);
        $this->assertSame([...self::PRODI, 'total'], $rows[1]);
        $this->assertStringContainsString('<span class="d-block text-muted fs-8 fw-normal">Visual Communication Design</span>', $page);
        $this->assertSame(450, substr_count($page, 'data-roadmap-edit '));
        $this->assertStringContainsString('orang baru dari luar yang masuk menjadi FM pada tahun itu', $page);

        $id = DB::table(ProdiFmRoadmap::TABLE)->where(['prodi' => 'CS', 'year' => 2026])->value('id');
        $this->assertSame(['id' => (string) $id, 'field' => 'fm', 'label' => 'Jumlah FM CS 2026', 'value' => '21', 'note' => '', 'title' => null, 'cell' => 'roadmap-year-start'],
            $this->input($page, 'CS-2026-fm'));
        $this->assertSame(['id' => (string) ($id + 1), 'field' => 'nr_s3', 'label' => 'NR S3 CS 2027', 'value' => '1', 'note' => 'Lulusan luar negeri.',
            'title' => 'Catatan: Lulusan luar negeri.', 'cell' => 'roadmap-nr roadmap-has-note'], $this->input($page, 'CS-2027-nr_s3'));
        $this->assertStringContainsString('aria-label="NR S3 CS 2027 (ada catatan)"', $page);
        // The total row only shows its sums.
        $this->assertMatchesRegularExpression('/<td class="roadmap-year-start" data-roadmap-cell="total-2026-fm"\s*>\s*117\s*<\/td>/', $page);
        $this->assertMatchesRegularExpression('/<td class="" data-roadmap-cell="total-2035-s3"\s*>\s*133\s*<\/td>/', $page);
        $this->assertMatchesRegularExpression('/<td class="roadmap-nr" data-roadmap-cell="total-2028-nr_s2"\s*>\s*2\s*<\/td>/', $page);
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
        $this->assertSame([22, 17, 4, 'Sesuai Data Center November 2026.'], [(int) $row->jumlah_fm, (int) $row->jumlah_s2, (int) $row->jumlah_s3, $row->catatan_fm]);
        // The new table shows the number, its note and that S2 + S3 no longer equals it; NR is not part of that check.
        $table = $response->json('table_html');
        $this->assertSame('22', $this->input($table, 'CS-2026-fm')['value']);
        $this->assertSame("S2 + S3 = 21, tidak sama dengan jumlah FM 22\nCatatan: Sesuai Data Center November 2026.", $this->input($table, 'CS-2026-fm')['title']);
        $this->assertSame('bg-light-warning', $this->input($table, 'CS-2026-s2')['cell']);
        $this->assertSame(['roadmap-nr', null], [$this->input($table, 'CS-2026-nr_s2')['cell'], $this->input($table, 'CS-2026-nr_s2')['title']]);
        $this->assertStringContainsString('data-roadmap-mismatches="1"', $table);
        $this->assertMatchesRegularExpression('/<td class="roadmap-year-start bg-light-warning" data-roadmap-cell="total-2026-fm"\s*title="S2 \+ S3 = 117, tidak sama dengan jumlah FM 118"\s*>\s*118\s*</', $table);
        $this->assertStringNotContainsString('<html', $table);

        // A New FM Recruit is edited like any number.
        $table = $this->putJson(route('roadmaplrc.update'), ['id' => $id, 'field' => 'nr_s2', 'nilai' => 1, 'catatan' => 'Rekrut praktisi.'])->assertOk()
            ->assertJsonPath('message', 'NR S2 CS 2026 disimpan: 1.')->json('table_html');
        $this->assertSame(['1', 'Catatan: Rekrut praktisi.'], [$this->input($table, 'CS-2026-nr_s2')['value'], $this->input($table, 'CS-2026-nr_s2')['title']]);
        $this->assertMatchesRegularExpression('/data-roadmap-cell="total-2026-nr_s2"\s*>\s*1\s*</', $table);

        // An empty note clears it.
        $this->putJson(route('roadmaplrc.update'), ['id' => $id, 'field' => 's2', 'nilai' => 18, 'catatan' => ''])->assertOk()
            ->assertJsonPath('message', 'S2 CS 2026 disimpan: 18.');
        $this->putJson(route('roadmaplrc.update'), ['id' => $id, 'field' => 'fm', 'nilai' => 22, 'catatan' => ' '])->assertOk();
        $row = DB::table(ProdiFmRoadmap::TABLE)->find($id);
        $this->assertSame([22, 18, null, 1], [(int) $row->jumlah_fm, (int) $row->jumlah_s2, $row->catatan_fm, (int) $row->jumlah_nr_s2]);
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
            [['field' => 'nr_s3', 'nilai' => -1], 'nilai', 'Nilai minimal 0.'],
            [['field' => 'fm', 'nilai' => 10000], 'nilai', 'Nilai maksimal 9999.'],
            [['field' => 'jumlah_fm', 'nilai' => 1], 'field', 'Kolom yang diubah harus Jumlah FM, S2, S3, NR S2, atau NR S3.'],
            [['field' => 's3', 'nilai' => 1, 'catatan' => str_repeat('a', 1001)], 'catatan', 'Catatan maksimal 1000 karakter.'],
            [['id' => 999, 'field' => 's3', 'nilai' => 1], 'id', 'Baris roadmap tidak ditemukan.'],
        ] as [$input, $key, $message]) {
            $this->putJson(route('roadmaplrc.update'), $input + ['id' => $id])->assertUnprocessable()->assertJsonPath("errors.{$key}.0", $message);
        }
        $this->assertSame([21, 0], array_values(array_map('intval', (array) DB::table(ProdiFmRoadmap::TABLE)->where('id', $id)->first(['jumlah_fm', 'jumlah_nr_s3']))));
    }

    public function test_new_fm_recruit_columns_wait_for_their_migration(): void
    {
        (require base_path(self::NR_MIGRATION))->down();
        DB::table(ProdiFmRoadmap::TABLE)->insert(['prodi' => 'CS', 'program_studi' => 'Computer Science', 'urutan' => 1, 'year' => 2026,
            'jumlah_fm' => 21, 'jumlah_s2' => 17, 'jumlah_s3' => 4]);
        $page = $this->page();
        $this->assertSame('0', $this->input($page, 'CS-2026-nr_s2')['value']);

        $this->actingAs((new User)->forceFill(['id' => 1]));
        $this->putJson(route('roadmaplrc.update'), ['id' => 1, 'field' => 'nr_s2', 'nilai' => 1])
            ->assertUnprocessable()->assertJsonPath('errors.field.0', 'Migrasi kolom NR roadmap belum dijalankan.');
        $this->putJson(route('roadmaplrc.update'), ['id' => 1, 'field' => 'fm', 'nilai' => 22])->assertOk();
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
