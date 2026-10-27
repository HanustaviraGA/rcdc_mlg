<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ImportRectorateHistoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Run PHP with -d extension=pdo_sqlite.');
        }

        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->actingAs((new User)->forceFill(['id' => 1]));
    }

    public function test_history_reads_only_metadata_and_preserves_latest_files_and_legacy_notice(): void
    {
        $tables = ['publication_imports', 'student_publication_imports', 'research_imports'];
        foreach ($tables as $tableName) {
            Schema::create($tableName, function (Blueprint $table) use ($tableName) {
                $table->id();
                $table->unsignedSmallInteger('year')->nullable();
                $table->unsignedTinyInteger('month')->nullable();
                $table->string('filename');
                $table->json('summary')->nullable();
                if ($tableName === 'student_publication_imports') {
                    $table->json('records')->nullable();
                    $table->json('publication_list')->nullable();
                }
                $table->timestamps();
            });

            DB::table($tableName)->insert([
                'year' => 2026, 'month' => 9, 'filename' => $tableName.'.xlsx',
                'updated_at' => '2026-09-30 12:00:00',
            ]);
        }

        DB::table('publication_imports')->insert([
            ['year' => 2026, 'month' => 9, 'filename' => 'outdated-fm.xlsx', 'updated_at' => '2026-09-01 12:00:00'],
            ['year' => 2025, 'month' => 12, 'filename' => 'previous-year.xlsx', 'updated_at' => '2026-10-01 12:00:00'],
        ]);
        DB::table('research_imports')->insert(['filename' => 'legacy.xlsx']);
        DB::table('student_publication_imports')->update([
            'records' => json_encode([str_repeat('Large workbook row ', 100000)], JSON_THROW_ON_ERROR),
            'publication_list' => json_encode([str_repeat('Large publication list ', 100000)], JSON_THROW_ON_ERROR),
        ]);

        DB::enableQueryLog();
        $page = $this->get(route('importrectorate.index'))->assertOk()->json('page');
        $queries = collect(DB::getQueryLog())->pluck('query')->filter(
            fn (string $query) => str_starts_with($query, 'select ') && str_contains($query, 'order by')
        );
        DB::disableQueryLog();

        $this->assertCount(3, $queries);
        foreach ($queries as $query) {
            foreach (['*', 'summary', 'records', 'publication_list'] as $payload) {
                $this->assertStringNotContainsString($payload, $query);
            }
        }

        $html = base64_decode($page, true);
        foreach ($tables as $table) {
            $this->assertStringContainsString($table.'.xlsx', $html);
        }
        $this->assertStringNotContainsString('outdated-fm.xlsx', $html);
        $this->assertStringContainsString('previous-year.xlsx', $html);
        $this->assertLessThan(strpos($html, '2025-12'), strpos($html, '2026-09'));
        $this->assertStringContainsString('Belum diupload', $html);
        $this->assertStringContainsString('Import hibah lama tanpa bulan laporan tetap tersimpan.', $html);
    }

    public function test_history_handles_missing_import_tables(): void
    {
        $page = $this->get(route('importrectorate.index'))->assertOk()->json('page');

        $this->assertStringContainsString('Belum ada laporan bulanan.', base64_decode($page, true));
        $this->assertStringNotContainsString('Import hibah lama', base64_decode($page, true));
    }
}
