<?php

namespace App\Services\Imports;

use App\Models\CEPKMDosen;
use App\Models\PKMDosen;
use Aspera\Spreadsheet\XLSX\Reader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PkmWorkbook
{
    private const MAPPING = [
        'List' => ['periode' => 1, 'kode_dosen' => 3, 'judul_pkm' => 4, 'jenis_pkm' => 5,
            'peserta' => 6, 'skema_pendanaan' => 7, 'nama_mahasiswa' => 8, 'link_evidence' => 9],
        'CE' => ['kode_dosen' => 2, 'jumlah_kegiatan' => 3, 'jumlah_laporan' => 4, 'skor_sementara' => 5, 'keterangan' => 6],
    ];

    public function import(string $path, int $year, string $period): array
    {
        if ($year < 2000 || $year > 2100 || trim($period) === '' || strlen($period) > 50) {
            throw ValidationException::withMessages(['period' => 'Tahun/periode PKM tidak valid.']);
        }
        $policy = new ImportPolicy('pkm', 'pkm');
        $reader = new Reader;
        $records = [];
        try {
            $reader->open($path);
            foreach ($reader->getSheets() as $index => $sheet) {
                $name = $sheet->getName();
                if (! isset(self::MAPPING[$name])) {
                    continue;
                }
                $reader->changeSheet($index);
                $headers = null;
                $records[$name] = [];
                foreach ($reader as $number => $cells) {
                    $cells = array_map(fn ($value) => trim((string) $value), $cells);
                    if (! array_filter($cells, fn ($value) => $value !== '')) {
                        continue;
                    }
                    if ($headers === null) {
                        $headers = $cells;
                        $required = $name === 'List' ? ['kode_dosen', 'judul_pkm'] : ['kode_dosen'];
                        foreach ($required as $field) {
                            if (trim($headers[self::MAPPING[$name][$field]] ?? '') === '') {
                                throw ValidationException::withMessages(['pkm' => "Kolom wajib {$field} pada sheet {$name} tidak tersedia di posisi template."]);
                            }
                        }

                        continue;
                    }
                    $record = [];
                    foreach (self::MAPPING[$name] as $field => $column) {
                        $value = $cells[$column] ?? '';
                        $record[$field] = $value === '' ? null : $value;
                    }
                    if ($record['kode_dosen'] === null) {
                        $policy->skip("Sheet {$name} baris {$number}: Kode Dosen kosong.");

                        continue;
                    }
                    $record['kode_dosen'] = strtoupper($record['kode_dosen']);
                    if ($name === 'List' && $record['judul_pkm'] === null) {
                        $policy->accept("Sheet List baris {$number}: Judul PKM kosong.");
                    }
                    if ($name === 'CE') {
                        foreach (['jumlah_kegiatan', 'jumlah_laporan', 'skor_sementara'] as $field) {
                            $value = str_replace(',', '.', $record[$field] ?? '');
                            if ($value !== '' && (! is_numeric($value) || ! is_finite((float) $value) || (float) $value < 0)) {
                                $policy->accept("Sheet CE baris {$number}: {$field} tidak valid dan dikosongkan.");
                                $record[$field] = null;
                            } else {
                                $record[$field] = $value === '' ? null : (float) $value;
                            }
                        }
                    }
                    $records[$name][] = $record;
                }
                if ($headers === null) {
                    throw ValidationException::withMessages(['pkm' => "Sheet {$name} tidak berisi baris yang dapat diimpor. Data lama tetap tersimpan."]);
                }
                if (! $records[$name]) {
                    $policy->accept("Sheet {$name} tidak berisi baris yang dapat diimpor; data lama untuk sheet tersebut tetap tersimpan.");
                    unset($records[$name]);
                }
            }
        } finally {
            $reader->close();
        }
        if (! $records) {
            throw ValidationException::withMessages(['pkm' => 'Sheet List atau CE tidak ditemukan.']);
        }

        return DB::transaction(function () use ($records, $year, $period, $policy) {
            foreach ($records as $sheet => $rows) {
                $modelClass = $sheet === 'List' ? PKMDosen::class : CEPKMDosen::class;
                $modelClass::where('year', $year)->where('period', $period)->delete();
                foreach ($rows as $row) {
                    $modelClass::create($row + [$sheet === 'List' ? 'id_pkm' : 'id_ce' => md5((string) Str::uuid()),
                        'year' => $year, 'period' => $period]);
                }
            }

            return $policy->summary(['selected' => array_sum(array_map('count', $records)),
                'sheets' => array_map('count', $records), 'year' => $year, 'period' => $period]);
        });
    }
}
