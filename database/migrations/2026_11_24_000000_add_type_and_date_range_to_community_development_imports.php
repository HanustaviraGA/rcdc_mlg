<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_development_imports', function (Blueprint $table) {
            $table->string('type', 20)->default('semester')->after('id');
            $table->date('start_date')->nullable()->after('type');
            $table->date('end_date')->nullable()->after('start_date');
            $table->unsignedSmallInteger('year_start')->nullable()->change();
            $table->string('semester', 10)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('community_development_imports', function (Blueprint $table) {
            $table->dropColumn(['type', 'start_date', 'end_date']);
            $table->unsignedSmallInteger('year_start')->nullable(false)->change();
            $table->string('semester', 10)->nullable(false)->change();
        });
    }
};
