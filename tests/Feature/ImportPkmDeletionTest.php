<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ImportPkmDeletionTest extends TestCase
{
    private array $snapshots = [];

    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Run PHP with -d extension=pdo_sqlite.');
        }
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        foreach ([
            '2026_10_23_000000_create_community_development_imports.php',
            '2026_11_24_000000_add_type_and_date_range_to_community_development_imports.php',
            '2026_10_27_000000_create_community_partner_imports.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }

        foreach (['semester', 'periodic', 'partners'] as $type) {
            foreach ([2025, 2026] as $year) {
                $this->snapshots[$type][$year] = $this->seedSnapshot($type, $year);
            }
        }
    }

    public static function importTypes(): array
    {
        return [['semester'], ['periodic'], ['partners']];
    }

    #[DataProvider('importTypes')]
    public function test_deletion_removes_only_selected_import_and_its_details(string $type): void
    {
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $response = $this->deleteJson($this->url($type))->assertOk()->assertJsonPath('success', true);

        foreach ($this->snapshots as $source => $years) {
            foreach ($years as $year => $id) {
                $expected = $source === $type && $year === 2025 ? 0 : 1;
                $this->assertSame($expected, DB::table($this->table($source))->where('id', $id)->count());
                $details = $source === 'partners' ? 'community_partners' : 'community_development_lecturers';
                $this->assertSame($expected, DB::table($details)->where('import_id', $id)->count());
                if ($source === 'semester') {
                    $this->assertSame($expected, DB::table('community_development_programs')->where('import_id', $id)->count());
                }
            }
        }

        $history = $response->json('history_html');
        if ($type === 'partners') {
            $this->assertStringNotContainsString('partners-2025.xlsx', $history);
            $this->assertStringContainsString('partners-2026.xlsx', $history);
        } else {
            $periodicHistory = $response->json('periodic_history_html');
            $this->assertStringNotContainsString($type.'-2025.xlsx', $history.$periodicHistory);
            $this->assertStringContainsString('semester-2026.xlsx', $history);
            $this->assertStringContainsString('periodic-2026.xlsx', $periodicHistory);
            $this->assertStringNotContainsString('periodic-', $history);
            $this->assertStringNotContainsString('semester-', $periodicHistory);
        }

        $before = $this->storedRows();
        $this->deleteJson($this->url($type))->assertNotFound();
        $this->assertEquals($before, $this->storedRows());
    }

    #[DataProvider('importTypes')]
    public function test_guests_cannot_delete_imports(string $type): void
    {
        $before = $this->storedRows();
        $this->deleteJson($this->url($type))->assertUnauthorized();
        $this->assertEquals($before, $this->storedRows());
    }

    #[DataProvider('importTypes')]
    public function test_database_failure_rolls_back_deleted_details(string $type): void
    {
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $table = $this->table($type);
        DB::unprepared("CREATE TRIGGER reject_delete BEFORE DELETE ON {$table} BEGIN SELECT RAISE(ABORT, 'delete failed'); END");
        $before = $this->storedRows();

        $this->deleteJson($this->url($type))->assertStatus(500);
        $this->assertEquals($before, $this->storedRows());
    }

    private function table(string $type): string
    {
        return $type === 'partners' ? 'community_partner_imports' : 'community_development_imports';
    }

    private function url(string $type): string
    {
        return route($type === 'partners' ? 'importpkm.partners.destroy' : 'importpkm.destroy', $this->snapshots[$type][2025]);
    }

    private function seedSnapshot(string $type, int $year): int
    {
        $metadata = ['filename' => $type.'-'.$year.'.xlsx', 'column_mapping' => '{}', 'created_at' => now(), 'updated_at' => now()];
        if ($type === 'partners') {
            $id = DB::table($this->table($type))->insertGetId($metadata + [
                'data_year' => $year, 'sheet' => 'Mitra', 'header_row' => 1, 'partner_count' => 1,
            ]);
            DB::table('community_partners')->insert([
                'import_id' => $id, 'source_row' => 2, 'name' => 'Mitra '.$year,
                'cluster' => 'Kriya', 'scale' => 'Mikro', 'area' => 'Malang',
            ]);

            return $id;
        }

        $id = DB::table($this->table($type))->insertGetId($metadata + [
            'type' => $type, 'header_rows' => '{}',
            'year_start' => $type === 'semester' ? $year : null,
            'semester' => $type === 'semester' ? 'Ganjil' : null,
            'start_date' => $type === 'periodic' ? $year.'-01-01' : null,
            'end_date' => $type === 'periodic' ? $year.'-06-30' : null,
        ]);
        DB::table('community_development_lecturers')->insert([
            'import_id' => $id, 'kode_dosen' => 'D1', 'source_row' => 2, 'reported_activities' => 1,
        ]);
        if ($type === 'semester') {
            DB::table('community_development_programs')->insert([
                'import_id' => $id, 'program_key' => 'ebc', 'source_program' => 'EBC', 'source_row' => 2,
            ]);
        }

        return $id;
    }

    private function storedRows(): array
    {
        return array_map(fn ($table) => DB::table($table)->orderBy('id')->get()->toArray(), [
            'community_development_imports', 'community_development_programs', 'community_development_lecturers',
            'community_partner_imports', 'community_partners',
        ]);
    }
}
