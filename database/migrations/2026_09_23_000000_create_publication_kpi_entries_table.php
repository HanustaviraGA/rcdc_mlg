<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publication_kpi_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_import_id')->constrained('publication_imports')->cascadeOnDelete();
            $table->string('kode_dosen');
            $table->string('name');
            $table->string('program');
            $table->string('academic_rank')->nullable();
            $table->string('faculty_type')->nullable();
            $table->double('rtto_non_scopus')->nullable();
            $table->double('rtto_scopus')->nullable();
            $table->unsignedTinyInteger('rtto_score')->nullable();
            $table->unsignedInteger('source_row');
            $table->json('source_payload');
            $table->timestamps();
            $table->unique(['publication_import_id', 'kode_dosen']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publication_kpi_entries');
    }
};
