<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('character_building_targets', function (Blueprint $table) {
            $table->id();
            // publikasi, pengajaran, comdev or pengembangan_diri.
            $table->string('category', 30);
            // Quarterly targets: calendar year and quarter 1–4. Semester targets: first year of
            // the academic year, and 1 for Ganjil or 2 for Genap.
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('term');
            // The achievement to reach (e.g. Scopus titles), and the score it earns.
            $table->decimal('target', 10, 2);
            $table->decimal('score', 8, 2);
            $table->timestamps();
            $table->unique(['category', 'year', 'term']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('character_building_targets');
    }
};
