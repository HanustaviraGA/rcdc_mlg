<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PKMDosen extends Model
{
    protected $table = 'pkm_dosen';
    protected $primaryKey = 'id_pkm';
    protected $keyType = 'string';
    protected $fillable = [
        'id_pkm',
        'periode',
        'kode_dosen',
        'judul_pkm',
        'jenis_pkm',
        'peserta',
        'skema_pendanaan',
        'nama_mahasiswa',
        'link_evidence',
        'year',
        'period',
        'created_at',
        'updated_at'
    ];
}
