<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RectorateDosen extends Model
{
    protected $table = 'rectorate_dosen';
    protected $primaryKey = 'id_rectorate';
    protected $keyType = 'string';
    protected $fillable = [
        'id_rectorate',
        'request_code',
        'author',
        // 'fm_author',
        'kode_dosen',
        'first_author',
        'sumber_paper',
        'bobot',
        'submitted',
        'status',
        'jenis',
        'tipe_publikasi',
        'title',
        'scopus_year',
        'source_title',
        'publisher',
        'quartile_jurnal',
        'year',
        'period',
        'month',
        'created_at',
        'updated_at'
    ];
}
