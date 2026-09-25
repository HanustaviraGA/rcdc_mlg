<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('research_imports', function (Blueprint $table) {
            if (Schema::hasIndex('research_imports', 'research_imports_sha256_unique')) {
                $table->dropUnique(['sha256']);
            }
            $table->unsignedSmallInteger('year')->nullable();
            $table->unsignedTinyInteger('month')->nullable();
            $table->unique(['year', 'month']);
        });
        Schema::table('rectorate_research', function (Blueprint $table) {
            $table->text('evidence')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('rectorate_research', fn (Blueprint $table) => $table->dropColumn('evidence'));
        Schema::table('research_imports', function (Blueprint $table) {
            $table->dropUnique(['year', 'month']);
            $table->dropColumn(['year', 'month']);
            // The same file can belong to multiple months; retain those batches on rollback.
        });
    }
};
