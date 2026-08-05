<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('outlet_publikasi', 'url_website')) {
            Schema::table('outlet_publikasi', function (Blueprint $table) {
                $table->string('url_website', 2048)->nullable()->after('contact_pic');
            });
        }

        if (! Schema::hasTable('outlet_publikasi_referensi')) {
            Schema::create('outlet_publikasi_referensi', function (Blueprint $table) {
                $table->id();
                $table->string('kategori', 40);
                $table->string('nama');
                $table->string('issn', 50)->nullable();
                $table->string('quartile_sjr', 100)->nullable();
                $table->string('sinta', 50)->nullable();
                $table->string('publication_frequency')->nullable();
                $table->text('scope')->nullable();
                $table->date('deadline_submission')->nullable();
                $table->string('url_website', 2048);
                $table->timestamps();

                $table->index(['kategori', 'nama']);
                $table->index(['kategori', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('outlet_publikasi_referensi');

        if (Schema::hasColumn('outlet_publikasi', 'url_website')) {
            Schema::table('outlet_publikasi', function (Blueprint $table) {
                $table->dropColumn('url_website');
            });
        }
    }
};
