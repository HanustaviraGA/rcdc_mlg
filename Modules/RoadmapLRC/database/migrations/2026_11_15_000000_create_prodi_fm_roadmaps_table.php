<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The planned FM count of a study program in a year and its education levels (sheet JJP).
        Schema::create('prodi_fm_roadmaps', function (Blueprint $table) {
            $table->id();
            // Program code as on the sheet (EBC, CS, DKV, …).
            $table->string('prodi', 20);
            // The same program in the Data Center (nama_gugus_binaan), e.g. Computer Science for CS.
            $table->string('program_studi', 100)->nullable();
            // Row order of the program in the table.
            $table->unsignedTinyInteger('urutan')->default(0);
            $table->unsignedSmallInteger('year');
            $table->unsignedSmallInteger('jumlah_fm');
            $table->unsignedSmallInteger('jumlah_s2');
            $table->unsignedSmallInteger('jumlah_s3');
            // A note per number, entered with it on the page.
            $table->text('catatan_fm')->nullable();
            $table->text('catatan_s2')->nullable();
            $table->text('catatan_s3')->nullable();
            $table->timestamps();
            $table->unique(['prodi', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prodi_fm_roadmaps');
    }
};
