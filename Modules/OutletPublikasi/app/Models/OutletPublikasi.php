<?php

namespace Modules\OutletPublikasi\Models;

use Illuminate\Database\Eloquent\Model;

class OutletPublikasi extends Model
{
    protected $table = 'outlet_publikasi';

    protected $fillable = [
        'nama_conference',
        'tipe_kerjasama',
        'deadline_submission',
        'scope',
        'contact_pic',
    ];

    protected function casts(): array
    {
        return [
            'deadline_submission' => 'date:Y-m-d',
        ];
    }
}
