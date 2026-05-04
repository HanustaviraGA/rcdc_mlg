<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('identitas_dosen', function (Blueprint $table) {
            if (! Schema::hasColumn('identitas_dosen', 'link_garuda')) {
                $table->string('link_garuda')->nullable()->after('link_sinta');
            }

            if (! Schema::hasColumn('identitas_dosen', 'link_orcid')) {
                $table->string('link_orcid')->nullable()->after('link_garuda');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('identitas_dosen', function (Blueprint $table) {
            if (Schema::hasColumn('identitas_dosen', 'link_orcid')) {
                $table->dropColumn('link_orcid');
            }

            if (Schema::hasColumn('identitas_dosen', 'link_garuda')) {
                $table->dropColumn('link_garuda');
            }
        });
    }
};
