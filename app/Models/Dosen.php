<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    protected $table = 'database_dosen';
    protected $primaryKey = 'kode_dosen';
    protected $keyType = 'string';
    protected $fillable = [
        'kode_dosen',
        'nama_dosen',
        'jurusan_dosen',
        'jja_dosen',
        'ft_dosen',
        'created_at',
        'updated_at'
    ];
}
