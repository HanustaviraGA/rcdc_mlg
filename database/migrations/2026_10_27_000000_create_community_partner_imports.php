<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_partner_imports', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('data_year')->unique();
            $table->string('filename');
            $table->string('sheet');
            $table->unsignedInteger('header_row');
            $table->json('column_mapping');
            $table->unsignedInteger('partner_count');
            $table->timestamps();
        });
        Schema::create('community_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained('community_partner_imports')->cascadeOnDelete();
            $table->unsignedInteger('source_row');
            $table->string('name');
            $table->string('cluster');
            $table->string('scale');
            $table->string('area');
            $table->unsignedSmallInteger('joined_year')->nullable();
            $table->unique(['import_id', 'source_row']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_partners');
        Schema::dropIfExists('community_partner_imports');
    }
};
