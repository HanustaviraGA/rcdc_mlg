<?php

namespace Modules\OutletPublikasi\Models;

use Illuminate\Database\Eloquent\Model;

class OutletPublikasiReferensi extends Model
{
    protected $table = 'outlet_publikasi_referensi';

    protected $fillable = [
        'kategori',
        'nama',
        'issn',
        'quartile_sjr',
        'sinta',
        'publication_frequency',
        'scope',
        'deadline_submission',
        'url_website',
    ];

    protected function casts(): array
    {
        return [
            'deadline_submission' => 'date:Y-m-d',
        ];
    }
}
