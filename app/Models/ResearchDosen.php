<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResearchDosen extends Model
{
    protected $table = 'rectorate_research';
    protected $primaryKey = 'id_research';
    protected $keyType = 'string';
    protected $fillable = [
        'id_research',
        'no',
        'pengakuan_kpi_research_program',
        'pengakuan_kpi_non_tuition',
        'sumber_dana',
        'bobot_sumber_dana',
        'tahun_anggaran',
        'kd_prop',
        'kode_dosen_nim',
        'nidn',
        'nama',
        'peran',
        'kategori_fm_eksternal_mahasiswa',
        'rig_bdsrc_fbrc',
        'bobot_peran',
        'bobot_sumber_x_bobot_peran',
        'prodi_di_kpi',
        'fakultas_kpi',
        'prodi_jur_binaan',
        'prodi_pdpt',
        'lokasi_kampus',
        'tahun_ke',
        'total_tahun_penelitian',
        'tanggal_mulai',
        'tanggal_selesai',
        'sumber_pemberi_hibah',
        'jenis_institusi_pemberi_hibah',
        'nama_pemberi_hibah',
        'program_hibah',
        'skema',
        'status_usulan',
        'judul',
        'nilai_hibah_selain_rupiah',
        'nama_mata_uang',
        'nilai_tukar_to_rupiah',
        'hibah_internal_binus',
        'dana_disetujui',
        'hibah_per_prodi',
        'jenis_penelitian',
        'tkt',
        'sdgs',
        'keyword_sdgs',
        'bidang_ilmu_by_qs_subject',
        'subtopik_research_roadmap',
        'produk_yang_dihasilkan',
        'bidang_penelitian',
        'sub_bidang_penelitian',
        'tujuan_sosial_ekonomi',
        'sub_tujuan_sosial_ekonomi',
        'email_peneliti_luar',
        'keterangan',
        'mitra',
        'nama_mitra',
        'email_mitra',
        'multi_disiplin',
        'created_at',
        'updated_at',
    ];
}
