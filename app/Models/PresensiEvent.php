<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresensiEvent extends Model
{
    protected $table = 'presensi_event';
    protected $primaryKey = 'id_presensi';
    protected $keyType = 'string';
    protected $fillable = [
        'id_presensi',
        'kode_dosen',
        'created_at',
        'updated_at'
    ];
}
