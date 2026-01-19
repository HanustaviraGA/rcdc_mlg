<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeDosen extends Model
{
    protected $table = 'database_attribute_dosen';
    protected $primaryKey = 'id_attribute';
    protected $fillable = [
        'id_attribute',
        'kode_dosen',
        'attribute_dosen',
        'attribute_icon',
        'created_at',
        'updated_at'
    ];
}
