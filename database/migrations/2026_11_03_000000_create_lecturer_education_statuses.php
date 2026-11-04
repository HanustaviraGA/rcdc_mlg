<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lecturer_education_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('kode_dosen', 100);
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->text('status_pendidikan')->nullable();
            $table->timestamps();
            $table->unique(['kode_dosen', 'year', 'month']);
        });
        if (! Schema::hasTable('lecturer_imports') || ! Schema::hasColumn('lecturer_snapshot_rows', 'status_pendidikan')) {
            return;
        }
        // Statuses uploaded earlier only live on the Data Center rows of their period; keep them.
        DB::table('lecturer_snapshot_rows')
            ->join('lecturer_imports', 'lecturer_imports.id', '=', 'lecturer_snapshot_rows.lecturer_import_id')
            ->whereNotNull('lecturer_snapshot_rows.status_pendidikan')
            ->select('lecturer_snapshot_rows.id', 'lecturer_snapshot_rows.kode_dosen', 'lecturer_snapshot_rows.status_pendidikan', 'lecturer_imports.year', 'lecturer_imports.month')
            ->orderBy('lecturer_snapshot_rows.id')
            ->chunk(500, function ($rows) {
                DB::table('lecturer_education_statuses')->insertOrIgnore($rows->map(fn ($row) => [
                    'kode_dosen' => strtoupper(trim($row->kode_dosen)), 'year' => $row->year, 'month' => $row->month,
                    'status_pendidikan' => $row->status_pendidikan, 'created_at' => now(), 'updated_at' => now(),
                ])->all());
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('lecturer_education_statuses');
    }
};
