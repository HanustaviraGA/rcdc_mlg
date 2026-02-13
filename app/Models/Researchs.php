<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Researchs extends Model
{
    protected $table = 'researchs';
    protected $primaryKey = 'ID';
    protected $keyType = 'string';
    protected $fillable = [
        'ID',
        'kode_dosen',
        'title',
        'propose_year',
        'budget_year',
        'institution',
        'contract_number',
        'abstract',
        'keywords',
        'source_of_fund',
        'funding',
        'researcher',
        'permalink',
        'created_at',
        'updated_at'
    ];
}
