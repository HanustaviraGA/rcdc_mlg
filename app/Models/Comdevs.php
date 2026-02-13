<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comdevs extends Model
{
    protected $table = 'comdevs';
    protected $primaryKey = 'ID';
    protected $keyType = 'string';
    protected $fillable = [
        'ID',
        'kode_dosen',
        'community_name',
        'topic_name',
        'subtopic_name',
        'location',
        'event_date',
        'created_at',
        'updated_at'
    ];
}
