<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\ImportRectorate\Http\Middleware\VerifyImportDeletionCsrfToken;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ImportRectorateDeletionTest extends TestCase
{
    private const SOURCES = [
        'fm' => 'publication_imports',
        'mhs' => 'student_publication_imports',
        'hibah' => 'research_imports',
    ];

    private array $snapshots = [];

    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Run PHP with -d extension=pdo_sqlite.');
        }
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        Schema::create('rectorate_dosen', function (Blueprint $table) {
            $table->string('id_rectorate')->primary();
            $table->unsignedSmallInteger('year')->nullable();
            $table->unsignedTinyInteger('month')->nullable();
            $table->unsignedTinyInteger('period')->nullable();
        });
        foreach ([
            '2026_09_08_000000_add_publication_import_tracking.php',
            '2026_09_23_000000_create_publication_kpi_entries_table.php',
            '2026_09_24_000100_create_student_publication_imports.php',
            '2026_09_08_000200_add_rectorate_research_imports.php',
            '2026_09_24_000000_add_monthly_research_snapshots.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }

        foreach ([[2026, 9], [2026, 8], [2025, 9]] as [$year, $month]) {
            $this->snapshots["$year-$month"] = $this->seedSnapshot($year, $month);
        }
        $legacy = DB::table('research_imports')->insertGetId([
            'filename' => 'legacy.xlsx', 'sha256' => hash('sha256', 'legacy'), 'summary' => '{}',
        ]);
        DB::table('rectorate_research')->insert([
            ['id_research' => 'legacy', 'research_import_id' => $legacy],
            ['id_research' => 'unlinked', 'research_import_id' => null],
        ]);
        DB::table('rectorate_dosen')->insert([
            'id_rectorate' => 'unlinked', 'year' => 2026, 'month' => 9, 'period' => 3,
        ]);
    }

    public static function sourceTypes(): array
    {
        return [['fm'], ['mhs'], ['hibah']];
    }

    #[DataProvider('sourceTypes')]
    public function test_deletion_removes_only_selected_source_and_report_month(string $type): void
    {
        $this->signIn();
        $target = $this->snapshots['2026-9'];
        $response = $this->deleteJson($this->url($type))->assertOk()->assertJsonStructure(['message', 'history_html']);

        foreach ($this->snapshots as $period => $ids) {
            foreach (self::SOURCES as $source => $table) {
                if ($period === '2026-9' && $source === $type) {
                    $this->assertDatabaseMissing($table, ['id' => $ids[$source]]);
                } else {
                    $this->assertDatabaseHas($table, ['id' => $ids[$source]]);
                }
            }
        }
        $this->assertSame($type === 'fm' ? 0 : 1, DB::table('rectorate_dosen')->where('publication_import_id', $target['fm'])->count());
        $this->assertSame($type === 'fm' ? 0 : 1, DB::table('publication_kpi_entries')->where('publication_import_id', $target['fm'])->count());
        $this->assertSame($type === 'hibah' ? 0 : 1, DB::table('rectorate_research')->where('research_import_id', $target['hibah'])->count());
        $this->assertDatabaseHas('rectorate_dosen', ['id_rectorate' => 'unlinked', 'publication_import_id' => null]);
        $this->assertDatabaseHas('rectorate_research', ['id_research' => 'legacy']);
        $this->assertDatabaseHas('rectorate_research', ['id_research' => 'unlinked']);

        $html = $response->json('history_html');
        $this->assertStringNotContainsString("$type-2026-9.xlsx", $html);
        $this->assertStringContainsString('Belum diupload', $html);
        $this->assertStringContainsString('2026-09', $html);
        foreach (array_diff(array_keys(self::SOURCES), [$type]) as $other) {
            $this->assertStringContainsString("$other-2026-9.xlsx", $html);
        }
    }

    public function test_deleting_all_sources_removes_the_period_row_and_repeated_delete_is_harmless(): void
    {
        $this->signIn();
        foreach (array_keys(self::SOURCES) as $type) {
            $response = $this->deleteJson($this->url($type))->assertOk();
        }
        $this->assertStringNotContainsString('2026-09', $response->json('history_html'));
        $this->assertStringContainsString('2026-08', $response->json('history_html'));
        $before = $this->storedRows();
        $this->deleteJson($this->url('fm'))->assertNotFound();
        $this->deleteJson($this->url('mhs', 2024, 1))->assertNotFound();
        $this->assertEquals($before, $this->storedRows());
    }

    public function test_all_fm_batches_in_the_month_and_their_details_are_deleted(): void
    {
        $this->signIn();
        $this->seedSnapshot(2026, 9, 2);
        $this->deleteJson($this->url('fm'))->assertOk();
        $this->assertDatabaseMissing('publication_imports', ['year' => 2026, 'month' => 9]);
        $this->assertDatabaseCount('publication_kpi_entries', 2);
        $this->assertDatabaseCount('rectorate_dosen', 3);
        $this->assertDatabaseCount('student_publication_imports', 3);
        $this->assertDatabaseCount('research_imports', 4);
    }

    public function test_invalid_source_and_period_never_delete_data(): void
    {
        $this->signIn();
        $before = $this->storedRows();
        foreach ([['all', 2026, 9, 'type'], ['fm', 1999, 9, 'year'], ['mhs', 2101, 9, 'year'],
            ['hibah', 2026, 0, 'month'], ['fm', 2026, 13, 'month'], ['fm', 'invalid', 9, 'year']] as [$type, $year, $month, $field]) {
            $this->deleteJson($this->url($type, $year, $month))->assertUnprocessable()->assertJsonValidationErrors($field);
        }
        $this->assertEquals($before, $this->storedRows());
    }

    public function test_guests_cannot_delete_imports(): void
    {
        $before = $this->storedRows();
        $this->deleteJson($this->url('fm'))->assertForbidden();
        $this->assertEquals($before, $this->storedRows());
    }

    public function test_deletion_checks_csrf_despite_the_legacy_backoffice_exception(): void
    {
        $this->signIn();
        $this->app->bind(VerifyImportDeletionCsrfToken::class, fn ($app) => new class($app, $app['encrypter']) extends VerifyImportDeletionCsrfToken
        {
            protected function runningUnitTests()
            {
                return false;
            }
        });
        $this->withSession(['_token' => 'deletion-test-token']);
        $before = $this->storedRows();
        $this->deleteJson($this->url('fm'))->assertStatus(419);
        $this->deleteJson($this->url('fm'), [], ['X-CSRF-TOKEN' => 'wrong-token'])->assertStatus(419);
        $this->assertEquals($before, $this->storedRows());
        $this->deleteJson($this->url('fm'), [], ['X-CSRF-TOKEN' => 'deletion-test-token'])->assertOk();
    }

    #[DataProvider('sourceTypes')]
    public function test_database_failure_rolls_back_batch_and_detail_deletions(string $type): void
    {
        $this->signIn();
        $table = self::SOURCES[$type];
        DB::unprepared("CREATE TRIGGER reject_import_delete BEFORE DELETE ON {$table} BEGIN SELECT RAISE(ABORT, 'test delete failure'); END");
        $before = $this->storedRows();
        $this->withoutExceptionHandling();
        try {
            $this->deleteJson($this->url($type));
            $this->fail('Deletion should fail.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('test delete failure', $exception->getMessage());
        }
        $this->assertEquals($before, $this->storedRows());
    }

    public function test_non_json_deletion_redirects_with_success_message(): void
    {
        $this->signIn();
        $this->delete($this->url('mhs'))->assertRedirect('/dashboard/importrectorate')
            ->assertSessionHas('monthly_import_message', 'Publikasi Mahasiswa periode 2026-09 berhasil dihapus.');
    }

    private function signIn(): void
    {
        $this->actingAs((new User)->forceFill(['id' => 1]));
    }

    private function url(string $type, int|string $year = 2026, int $month = 9): string
    {
        return route('importrectorate.destroy', compact('type', 'year', 'month'));
    }

    private function seedSnapshot(int $year, int $month, ?int $fmPeriod = null): array
    {
        $ids = [];
        foreach (self::SOURCES as $type => $table) {
            if ($fmPeriod !== null && $type !== 'fm') {
                continue;
            }
            $extra = match ($type) {
                'fm' => ['period' => $fmPeriod ?? (int) ceil($month / 3)],
                'mhs' => ['records' => '[]', 'publication_list' => '[]'],
                default => [],
            };
            $ids[$type] = DB::table($table)->insertGetId($extra + [
                'year' => $year, 'month' => $month, 'filename' => "$type-$year-$month.xlsx",
                'sha256' => hash('sha256', "$type-$year-$month-$fmPeriod"), 'summary' => '{}',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        DB::table('rectorate_dosen')->insert([
            'id_rectorate' => "fm-$year-$month-$fmPeriod", 'publication_import_id' => $ids['fm'],
            'year' => $year, 'month' => $month, 'period' => $fmPeriod ?? (int) ceil($month / 3),
        ]);
        DB::table('publication_kpi_entries')->insert([
            'publication_import_id' => $ids['fm'], 'kode_dosen' => 'D001', 'name' => 'Lecturer',
            'program' => 'CS', 'source_row' => 2, 'source_payload' => '{}',
        ]);
        if (isset($ids['hibah'])) {
            DB::table('rectorate_research')->insert([
                'id_research' => "hibah-$year-$month", 'research_import_id' => $ids['hibah'], 'budget_year' => 2024,
            ]);
        }

        return $ids;
    }

    private function storedRows(): array
    {
        $rows = [];
        foreach ([...array_values(self::SOURCES), 'rectorate_dosen', 'publication_kpi_entries', 'rectorate_research'] as $table) {
            $rows[$table] = DB::table($table)->get()->all();
        }

        return $rows;
    }
}
