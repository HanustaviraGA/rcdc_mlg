<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('database_dosen_new')) {
            return;
        }

        Schema::table('database_dosen_new', function (Blueprint $table) {
            $table->boolean('is_hidden')->default(false);
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('database_dosen_new')) {
            Schema::table('database_dosen_new', function (Blueprint $table) {
                $table->dropColumn('is_hidden');
                $table->dropSoftDeletes();
            });
        }
    }
};
