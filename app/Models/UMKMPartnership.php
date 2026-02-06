<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UMKMPartnership extends Model
{
    protected $table = 'umkm_partnership';
    protected $primaryKey = 'id_umkm';
    protected $keyType = 'string';
    protected $fillable = [
        'id_umkm',
        'nama_lengkap_pemilik',
        'nama_usaha',
        'jenis_usaha',
        'tahun_bergabung',
        'cluster',
        'nomor_induk_ktp',
        'alamat_sesuai_ktp',
        'area',
        'usia',
        'email_umkm',
        'akun_instagram',
        'akun_tiktok',
        'akun_medsos_lainnya',
        'website_umkm',
        'nomor_whatsapp',
        'email_address',
        'created_at',
        'updated_at'
    ];
}
