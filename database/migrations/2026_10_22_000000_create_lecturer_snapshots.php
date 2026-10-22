<?php

use App\Services\Lecturers\LecturerWorkbook;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lecturer_imports', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->string('filename')->nullable();
            $table->string('sheet')->nullable();
            $table->json('column_mapping')->nullable();
            $table->timestamps();
            $table->unique(['year', 'month']);
        });
        Schema::create('lecturer_snapshot_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lecturer_import_id')->constrained('lecturer_imports')->cascadeOnDelete();
            foreach (LecturerWorkbook::HEADERS as $field => $label) {
                $field === 'kode_dosen' ? $table->string($field, 100) : $table->text($field)->nullable();
            }
            $table->boolean('is_hidden')->default(false);
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['lecturer_import_id', 'kode_dosen']);
        });

        // Preserve the existing master as the baseline of the current year only.
        if (Schema::hasTable('database_dosen_new') && DB::table('database_dosen_new')->exists()) {
            $id = DB::table('lecturer_imports')->insertGetId([
                'year' => now()->year, 'month' => now()->month, 'filename' => 'Master sebelum snapshot',
                'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('database_dosen_new')->orderBy('kode_dosen')->chunk(100, function ($rows) use ($id) {
                foreach ($rows as $row) {
                    $data = array_intersect_key((array) $row, LecturerWorkbook::HEADERS + array_flip(['is_hidden', 'deleted_at']));
                    DB::table('lecturer_snapshot_rows')->insert($data + [
                        'lecturer_import_id' => $id, 'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lecturer_snapshot_rows');
        Schema::dropIfExists('lecturer_imports');
    }
};
