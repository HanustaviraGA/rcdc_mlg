<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_fm_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('kode_dosen', 50);
            $table->unsignedSmallInteger('year');
            $table->string('cluster', 30)->nullable();
            $table->string('mentor_label', 60)->nullable();
            $table->text('source')->nullable();
            $table->timestamps();
            $table->unique(['kode_dosen', 'year']);
        });
        Schema::create('kpi_research_priorities', function (Blueprint $table) {
            $table->id();
            $table->string('kode_dosen', 50);
            $table->unsignedSmallInteger('year');
            $table->text('topic');
            $table->json('sdgs')->nullable();
            $table->text('source')->nullable();
            $table->timestamps();
            $table->index(['year', 'kode_dosen']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_research_priorities');
        Schema::dropIfExists('kpi_fm_profiles');
    }
};
