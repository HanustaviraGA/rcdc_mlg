<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdentitasDosen extends Model
{
    protected $table = 'identitas_dosen';

    protected $primaryKey = 'id_identitas';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id_identitas',
        'kode_dosen',
        'foto_dosen',
        'video_dosen',
        'deskripsi_dosen',
        'link_google_scholar',
        'link_scopus',
        'link_sinta',
        'link_garuda',
        'link_orcid',
        'created_at',
        'updated_at',
    ];
}
