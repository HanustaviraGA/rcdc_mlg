<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_development_imports', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year_start');
            $table->string('semester', 10);
            $table->string('filename');
            $table->json('column_mapping');
            $table->json('header_rows');
            $table->timestamps();
            $table->unique(['year_start', 'semester'], 'comdev_period_unique');
        });
        Schema::create('community_development_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained('community_development_imports')->cascadeOnDelete();
            $table->string('program_key', 150);
            $table->string('source_program');
            $table->boolean('is_total')->default(false);
            $table->unsignedInteger('faculty')->nullable();
            $table->unsignedInteger('activities')->nullable();
            $table->decimal('ratio', 12, 4)->nullable();
            $table->unsignedInteger('source_row');
            $table->unique(['import_id', 'program_key'], 'comdev_program_unique');
        });
        Schema::create('community_development_lecturers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained('community_development_imports')->cascadeOnDelete();
            // Profiles remain owned by Modules/Dosen and are joined by this code.
            $table->string('kode_dosen', 100);
            $table->unsignedInteger('reported_activities')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->text('status')->nullable();
            $table->unsignedInteger('source_row');
            $table->unique(['import_id', 'kode_dosen'], 'comdev_lecturer_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_development_lecturers');
        Schema::dropIfExists('community_development_programs');
        Schema::dropIfExists('community_development_imports');
    }
};
