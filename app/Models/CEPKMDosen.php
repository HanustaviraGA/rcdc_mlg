<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CEPKMDosen extends Model
{
    protected $table = 'ce_pkm_dosen';
    protected $primaryKey = 'id_ce';
    protected $keyType = 'string';
    protected $fillable = [
        'id_ce',
        'kode_dosen',
        'jumlah_kegiatan',
        'jumlah_laporan',
        'skor_sementara',
        'keterangan',
        'year',
        'period',
        'created_at',
        'updated_at'
    ];
}
