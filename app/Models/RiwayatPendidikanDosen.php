<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatPendidikanDosen extends Model
{
    protected $table = 'riwayat_pendidikan_dosen';
    protected $primaryKey = 'id_riwayat';
    protected $keyType = 'string';
    protected $fillable = [
        'id_riwayat',
        'kode_dosen',
        'prev_pendidikan',
        'current_pendidikan',
        'tanggal_pendidikan',
        'created_at',
        'updated_at'
    ];
}
