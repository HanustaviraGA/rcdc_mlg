<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdentitasDosen extends Model
{
    protected $table = 'identitas_dosen';
    protected $primaryKey = 'id_identitas';
    protected $keyType = 'string';
    protected $fillable = [
        'id_identitas',
        'kode_dosen',
        'foto_dosen',
        'video_dosen',
        'deskripsi_dosen',
        'created_at',
        'updated_at'
    ];
}
