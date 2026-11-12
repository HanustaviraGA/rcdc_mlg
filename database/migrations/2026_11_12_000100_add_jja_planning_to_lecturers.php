<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['database_dosen_new', 'lecturer_snapshot_rows'] as $tableName) {
            if (! Schema::hasColumn($tableName, 'roadmap_kenaikan_jja')) {
                Schema::table($tableName, fn (Blueprint $table) => $table->text('roadmap_kenaikan_jja')->nullable());
            }
            if (! Schema::hasColumn($tableName, 'eligible_kenaikan_jja')) {
                Schema::table($tableName, fn (Blueprint $table) => $table->date('eligible_kenaikan_jja')->nullable());
            }
        }
    }

    public function down(): void
    {
        foreach (['database_dosen_new', 'lecturer_snapshot_rows'] as $tableName) {
            Schema::table($tableName, fn (Blueprint $table) => $table->dropColumn(['roadmap_kenaikan_jja', 'eligible_kenaikan_jja']));
        }
    }
};
