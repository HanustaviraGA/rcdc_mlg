<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prodi_fm_roadmaps', function (Blueprint $table) {
            // New FM Recruits (NR) joining in the year: people from outside, by the degree they join with.
            // Lecturers moving up to S3 are graduates, not recruits.
            $table->unsignedSmallInteger('jumlah_nr_s2')->default(0)->after('jumlah_s3');
            $table->unsignedSmallInteger('jumlah_nr_s3')->default(0)->after('jumlah_nr_s2');
            $table->text('catatan_nr_s2')->nullable()->after('catatan_s3');
            $table->text('catatan_nr_s3')->nullable()->after('catatan_nr_s2');
        });
    }

    public function down(): void
    {
        Schema::table('prodi_fm_roadmaps', function (Blueprint $table) {
            $table->dropColumn(['jumlah_nr_s2', 'jumlah_nr_s3', 'catatan_nr_s2', 'catatan_nr_s3']);
        });
    }
};
