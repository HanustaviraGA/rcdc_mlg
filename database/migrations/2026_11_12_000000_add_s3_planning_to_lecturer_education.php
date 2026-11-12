<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['database_dosen_new', 'lecturer_snapshot_rows', 'lecturer_education_statuses'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedSmallInteger('roadmap_s3')->nullable();
                $table->unsignedSmallInteger('proyeksi_lulus_s3')->nullable();
                $table->text('pembiayaan')->nullable();
                $table->text('universitas_tujuan')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['database_dosen_new', 'lecturer_snapshot_rows', 'lecturer_education_statuses'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn(['roadmap_s3', 'proyeksi_lulus_s3', 'pembiayaan', 'universitas_tujuan']);
            });
        }
    }
};
