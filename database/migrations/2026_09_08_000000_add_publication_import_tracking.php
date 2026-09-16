<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('publication_imports')) {
            Schema::create('publication_imports', function (Blueprint $table) {
                $table->id();
                $table->unsignedSmallInteger('year');
                $table->unsignedTinyInteger('month');
                $table->unsignedTinyInteger('period');
                $table->string('filename');
                $table->string('sha256', 64);
                $table->json('summary');
                $table->timestamps();
                $table->unique(['year', 'month', 'period']);
            });
        }

        Schema::table('rectorate_dosen', function (Blueprint $table) {
            // Legacy month/period fields can be TEXT. Index only the new batch FK.
            if (! Schema::hasColumn('rectorate_dosen', 'publication_import_id')) {
                $table->foreignId('publication_import_id')->nullable()->constrained('publication_imports')->nullOnDelete();
            }
            if (! Schema::hasColumn('rectorate_dosen', 'source_row')) {
                $table->unsignedInteger('source_row')->nullable();
            }
            if (! Schema::hasColumn('rectorate_dosen', 'tanggal_pelaporan')) {
                $table->date('tanggal_pelaporan')->nullable();
            }
            if (! Schema::hasColumn('rectorate_dosen', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (! Schema::hasColumn('rectorate_dosen', 'prodi_kpi')) {
                $table->string('prodi_kpi')->nullable();
            }
            if (! Schema::hasColumn('rectorate_dosen', 'source_payload')) {
                $table->json('source_payload')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('rectorate_dosen', function (Blueprint $table) {
            $table->dropForeign(['publication_import_id']);
            $table->dropColumn(['publication_import_id', 'source_row', 'tanggal_pelaporan', 'notes', 'prodi_kpi', 'source_payload']);
        });
        Schema::dropIfExists('publication_imports');
    }
};
