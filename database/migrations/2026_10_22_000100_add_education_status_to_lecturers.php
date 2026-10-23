<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['database_dosen_new', 'lecturer_snapshot_rows'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->text('status_pendidikan')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['database_dosen_new', 'lecturer_snapshot_rows'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('status_pendidikan');
            });
        }
    }
};
