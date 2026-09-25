<?php

namespace App\Services\Lecturers;

use App\Models\DataDosen;
use Aspera\Spreadsheet\XLSX\Reader;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LecturerWorkbook
{
    public const HEADERS = [
        'kode_dosen' => 'Kode Dosen', 'fakultas_internal' => 'Fakultas Internal',
        'nama_gugus_binaan' => 'Nama Gugus Binaan', 'nama_program' => 'Nama Program',
        'lokasi' => 'Lokasi', 'campus' => 'Campus', 'nama_gugus_binaan_eksternal' => 'Nama Gugus Binaan Eksternal',
        'acad_career' => 'Acad Career', 'nama_dosen' => 'Nama Dosen', 'tipe' => 'Tipe',
        'nama_tipe_dosen_detail' => 'Nama Tipe Dosen Detail', 'status' => 'Status',
        'effdate_dosen_cuti' => 'Effdate Dosen Cuti', 'remun' => 'Remun', 'homebase_remun' => 'Homebase Remun',
        'jenis_registrasi' => 'Jenis Registrasi', 'nomor_nidn_nupn' => 'Nomor Nidn/Nupn',
        'university_registered_nidn' => 'University Registered NIDN', 'pendidikan' => 'Pendidikan',
        'alumni' => 'Alumni', 'jurusan' => 'Jurusan', 'jja' => 'JJA', 'tmt_jja' => 'Tmt JJA',
        'university_registered_jja' => 'University Registered JJA', 'nomor_sk_jja' => 'Nomor SK JJA',
        'jka' => 'JKA', 'tmt_jka' => 'Tmt JKA', 'toefl' => 'TOEFL', 'status_serdos' => 'Status Serdos',
        'jenis_kelamin' => 'Jenis Kelamin', 'tanggal_lahir' => 'Tanggal Lahir', 'usia' => 'Usia',
        'agama' => 'Agama', 'alamat' => 'Alamat', 'no_telp' => 'No Telp', 'no_hp' => 'No HP',
        'no_hp_2' => 'No HP2', 'email_1' => 'Email 1', 'email_2' => 'Email 2',
        'tgl_mulai_mengajar' => 'Tgl Mulai Mengajar', 'kewarganegaraan' => 'Kewarganegaraan',
        'bn_id' => 'Binusian ID', 'nama_kelompok_rumpun_ilmu' => 'Nama Kelompok Rumpun Ilmu',
        'nama_rumpun_ilmu' => 'Nama Rumpun Ilmu', 'tipe_faculty' => 'Tipe Faculty',
        'tax_status' => 'Tax Status', 'status_pernikahan' => 'Status Pernikahan', 'note' => 'NOTE',
    ];

    public function read(string $path): array
    {
        $reader = new Reader;
        $records = [];
        $map = null;
        $headers = [];
        $duplicates = 0;
        try {
            $reader->open($path);
            $sheets = [];
            foreach ($reader->getSheets() as $index => $sheet) {
                $sheets[$this->normalize($sheet->getName())] = ['index' => $index, 'name' => $sheet->getName()];
            }
            $sheet = $sheets['malang'] ?? $sheets['fm malang'] ?? $sheets['fm binus malang'] ?? null;
            if ($sheet === null) {
                $this->invalid('Sheet Malang, FM Malang, atau FM BINUS Malang tidak ditemukan.');
            }
            $reader->changeSheet($sheet['index']);
            foreach ($reader as $number => $row) {
                $row = array_map(fn ($value) => $value instanceof DateTimeInterface ? $value->format('Y-m-d') : trim((string) $value), $row);
                if (! array_filter($row, fn ($value) => $value !== '')) {
                    continue;
                }
                if ($map === null) {
                    $normalized = array_map($this->normalize(...), $row);
                    if (! in_array('kode dosen', $normalized, true)) {
                        continue;
                    }
                    $headers = array_values(array_filter($row, fn ($value) => $value !== ''));
                    $map = [];
                    foreach (self::HEADERS as $field => $label) {
                        $matches = array_keys($normalized, $this->normalize($label), true);
                        if (count($matches) > 1) {
                            $this->invalid("Kolom {$label} duplikat.");
                        }
                        if ($matches) {
                            $map[$field] = $matches[0];
                        }
                    }
                    if (! isset($map['nama_dosen'])) {
                        $this->invalid('Kolom Nama Dosen tidak ditemukan.');
                    }

                    continue;
                }
                $record = [];
                foreach ($map as $field => $index) {
                    $record[$field] = ($row[$index] ?? '') === '' ? null : $row[$index];
                }
                $code = strtoupper(trim($record['kode_dosen'] ?? ''));
                if ($code === '' || ! $record['nama_dosen']) {
                    $this->invalid("Baris {$number}: Kode Dosen dan Nama Dosen wajib diisi.");
                }
                $record['kode_dosen'] = $code;
                if (isset($records[$code])) {
                    if ($records[$code] !== $record) {
                        $this->invalid("Baris {$number}: kode dosen {$code} duplikat dengan isi berbeda.");
                    }
                    $duplicates++;

                    continue;
                }
                $records[$code] = $record;
            }
        } finally {
            $reader->close();
        }
        if (! $records) {
            $this->invalid('Sheet dosen tidak berisi data atau header Kode Dosen tidak ditemukan.');
        }
        $mapped = array_intersect_key(self::HEADERS, $map);
        $unknown = array_values(array_filter($headers, fn ($header) => ! in_array($this->normalize($header), array_map($this->normalize(...), self::HEADERS), true)));

        return ['records' => array_values($records), 'summary' => [
            'sheet' => $sheet['name'], 'selected' => count($records), 'duplicates' => $duplicates,
            'headers' => $headers, 'mapped_columns' => count($mapped), 'unknown_columns' => $unknown,
            'preserved_columns' => array_values(array_diff_key(self::HEADERS, $map)),
        ]];
    }

    public function import(string $path): array
    {
        $parsed = $this->read($path);

        return DB::transaction(function () use ($parsed) {
            $created = 0;
            foreach ($parsed['records'] as $record) {
                // Only supplied columns are updated; missing optional columns retain existing data.
                $model = DataDosen::updateOrCreate(['kode_dosen' => $record['kode_dosen']], $record);
                $created += (int) $model->wasRecentlyCreated;
            }

            return $parsed['summary'] + ['created' => $created, 'updated' => count($parsed['records']) - $created];
        });
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', str_replace("\xC2\xA0", ' ', $value))));
    }

    private function invalid(string $message): never
    {
        throw ValidationException::withMessages(['dosen' => $message.' Data sebelumnya tetap tersimpan.']);
    }
}
