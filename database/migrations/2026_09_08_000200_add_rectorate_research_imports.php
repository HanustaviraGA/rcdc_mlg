<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('research_imports', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('sha256', 64)->unique();
            $table->string('sheet')->default('Detail');
            $table->string('campus')->default('BINUS @Malang');
            $table->json('summary');
            $table->timestamps();
        });
        // Existing installations already have this dedicated, separate rectorate table.
        if (! Schema::hasTable('rectorate_research')) {
            Schema::create('rectorate_research', function (Blueprint $table) {
                $table->string('id_research', 32)->primary();
                foreach ([
                    'no', 'pengakuan_kpi_research_program', 'pengakuan_kpi_non_tuition', 'sumber_dana', 'bobot_sumber_dana',
                    'tahun_anggaran', 'kd_prop', 'kode_dosen_nim', 'nidn', 'nama', 'peran', 'kategori_fm_eksternal_mahasiswa',
                    'rig_bdsrc_fbrc', 'bobot_peran', 'bobot_sumber_x_bobot_peran', 'prodi_di_kpi', 'fakultas_kpi',
                    'prodi_jur_binaan', 'prodi_pdpt', 'lokasi_kampus', 'tahun_ke', 'total_tahun_penelitian',
                    'tanggal_mulai', 'tanggal_selesai', 'sumber_pemberi_hibah', 'jenis_institusi_pemberi_hibah',
                    'nama_pemberi_hibah', 'program_hibah', 'skema', 'status_usulan', 'judul', 'nilai_hibah_selain_rupiah',
                    'nama_mata_uang', 'nilai_tukar_to_rupiah', 'hibah_internal_binus', 'dana_disetujui', 'hibah_per_prodi',
                    'jenis_penelitian', 'tkt', 'sdgs', 'keyword_sdgs', 'bidang_ilmu_by_qs_subject', 'subtopik_research_roadmap',
                    'produk_yang_dihasilkan', 'bidang_penelitian', 'sub_bidang_penelitian', 'tujuan_sosial_ekonomi',
                    'sub_tujuan_sosial_ekonomi', 'email_peneliti_luar', 'keterangan', 'mitra', 'nama_mitra', 'email_mitra', 'multi_disiplin',
                ] as $field) {
                    $table->text($field)->nullable();
                }
                $table->timestamps();
            });
        }
        Schema::table('rectorate_research', function (Blueprint $table) {
            $table->foreignId('research_import_id')->nullable()->constrained('research_imports')->restrictOnDelete();
            $table->unsignedSmallInteger('budget_year')->nullable()->index();
            $table->string('project_key', 64)->nullable()->index();
            $table->string('row_key', 64)->nullable();
            $table->unique(['research_import_id', 'row_key'], 'research_import_unique_row');
            $table->unsignedInteger('source_row')->nullable();
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->decimal('approved_amount', 20, 2)->nullable();
            $table->decimal('internal_amount', 20, 2)->nullable();
            $table->decimal('program_amount', 20, 2)->nullable();
            $table->json('sdg_numbers')->nullable();
            $table->json('data_issues')->nullable();
            $table->json('source_payload')->nullable();
        });
    }

    public function down(): void
    {
        // Preserve the legacy rectorate table and all source rows, including on rollback.
        Schema::table('rectorate_research', function (Blueprint $table) {
            $table->dropForeign(['research_import_id']);
            $table->dropUnique('research_import_unique_row');
            $table->dropIndex(['budget_year']);
            $table->dropIndex(['project_key']);
            $table->dropColumn(['research_import_id', 'budget_year', 'project_key', 'row_key', 'source_row',
                'starts_on', 'ends_on', 'approved_amount', 'internal_amount', 'program_amount', 'sdg_numbers', 'data_issues', 'source_payload']);
        });
        Schema::dropIfExists('research_imports');
    }
};
