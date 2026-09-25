<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataDosen extends Model
{
    use SoftDeletes;

    protected $table = 'database_dosen_new';

    protected $primaryKey = 'kode_dosen';

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return ['is_hidden' => 'boolean'];
    }

    public function scopeVisibleOnWebsite(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('is_hidden'), false);
    }

    protected $fillable = [
        'kode_dosen',
        'fakultas_internal',
        'nama_gugus_binaan',
        'nama_program',
        'lokasi',
        'campus',
        'nama_gugus_binaan_eksternal',
        'acad_career',
        'nama_dosen',
        'tipe',
        'nama_tipe_dosen_detail',
        'status',
        'effdate_dosen_cuti',
        'remun',
        'homebase_remun',
        'jenis_registrasi',
        'nomor_nidn_nupn',
        'university_registered_nidn',
        'pendidikan',
        'alumni',
        'jurusan',
        'jja',
        'tmt_jja',
        'university_registered_jja',
        'nomor_sk_jja',
        'jka',
        'tmt_jka',
        'toefl',
        'status_serdos',
        'jenis_kelamin',
        'tanggal_lahir',
        'usia',
        'agama',
        'alamat',
        'no_telp',
        'no_hp',
        'no_hp_2',
        'email_1',
        'email_2',
        'tgl_mulai_mengajar',
        'kewarganegaraan',
        'bn_id',
        'nama_kelompok_rumpun_ilmu',
        'nama_rumpun_ilmu',
        'tipe_faculty',
        'tax_status',
        'status_pernikahan',
        'note',
        'created_at',
        'updated_at',
    ];
}
