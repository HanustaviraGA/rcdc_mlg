<?php

namespace App\Services\Publications;

use Aspera\Spreadsheet\XLSX\Reader;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Validation\ValidationException;

class RawPublicationReader
{
    public const SUBMITTED = ['Non Scopus FM', 'Scopus FM'];

    private const HEADERS = [
        'request_code' => ['requestcode', 'request code'], 'author' => ['author'],
        'fm_author' => ['fm author'], 'kode_dosen' => ['kode dosen'], 'jja' => ['jja'],
        'pendidikan' => ['pendidikan'], 'type' => ['type'], 's_f' => ['s/f'],
        'dept' => ['dept'], 'kampus' => ['kampus'], 'first_author' => ['first author'],
        'sumber_paper' => ['skema', 'sumber paper'], 'bobot_asli' => ['bobot'],
        'submitted' => ['submitted'], 'jenis' => ['jenis'], 'tipe_publikasi' => ['tipe publikasi'],
        'title' => ['title'], 'scopus_year' => ['scopus year'], 'source_title' => ['source title'],
        'quartile_jurnal' => ['quartile jurnal'], 'publisher' => ['publisher'],
        'tanggal_pelaporan' => ['tanggal pelaporan'], 'notes' => ['notes'], 'prodi_kpi' => ['prodi kpi'],
    ];

    public function read(string $path, int $year): array
    {
        $reader = new Reader;
        $summary = ['sheet' => 'Raw', 'read' => 0, 'excluded_campus' => 0, 'excluded_submitted' => 0,
            'duplicates' => 0, 'selected' => 0, 'submitted' => array_fill_keys(self::SUBMITTED, 0),
            'missing' => [], 'warnings' => []];
        $records = [];
        try {
            $reader->open($path);
            $sheetIndex = null;
            foreach ($reader->getSheets() as $index => $sheet) {
                if (strtolower(trim($sheet->getName())) === 'raw') {
                    $sheetIndex = $index;
                    break;
                }
            }
            if ($sheetIndex === null) {
                $this->invalid('Sheet Raw tidak ditemukan. Data sebelumnya tetap tersimpan.');
            }
            $reader->changeSheet($sheetIndex);
            $map = null;
            foreach ($reader as $number => $row) {
                if (! array_filter($row, fn ($cell) => trim((string) $cell) !== '')) {
                    continue;
                }
                if ($map === null) {
                    $headers = array_map(fn ($h) => $this->normalize((string) $h), $row);
                    $map = $this->mapHeaders($headers, $year);

                    continue;
                }
                $summary['read']++;
                $record = [];
                foreach ($map as $key => $index) {
                    $value = $index === null ? null : ($row[$index] ?? null);
                    $value = $value instanceof DateTimeInterface ? $value->format('Y-m-d') : trim((string) $value);
                    $record[$key] = $value === '' ? null : $value;
                }
                if (strtoupper($record['kampus'] ?? '') !== 'MALANG') {
                    $summary['excluded_campus']++;

                    continue;
                }
                $submitted = array_search($this->normalize($record['submitted'] ?? ''), array_map($this->normalize(...), self::SUBMITTED), true);
                if ($submitted === false) {
                    $summary['excluded_submitted']++;

                    continue;
                }
                $record['submitted'] = self::SUBMITTED[$submitted];
                $record['kampus'] = 'MALANG';
                $record['kode_dosen'] = strtoupper($record['kode_dosen'] ?? '');
                foreach (['request_code', 'kode_dosen', 'title', 'jenis', 'tipe_publikasi', 'status'] as $required) {
                    if (empty($record[$required])) {
                        $this->invalid("Baris {$number}: {$required} kosong. Perbaiki file sebelum import; data lama tetap tersimpan.");
                    }
                }
                $weight = str_replace(',', '.', $record['bobot_asli'] ?? '');
                if (! is_numeric($weight) || (float) $weight < 0) {
                    $this->invalid("Baris {$number}: Bobot harus berupa angka nol atau positif.");
                }
                $record['bobot_asli'] = (float) $weight;
                $record['bobot'] = self::adjustedWeight($record);
                $record['tanggal_pelaporan'] = $this->date($record['tanggal_pelaporan']);
                $key = $record['request_code'].'|'.$record['kode_dosen'];
                if (isset($records[$key])) {
                    $previous = array_intersect_key($records[$key], $record);
                    if ($previous !== $record) {
                        $this->invalid("Baris {$number}: RequestCode dan Kode Dosen duplikat dengan isi berbeda ({$key}).");
                    }
                    $summary['duplicates']++;

                    continue;
                }
                foreach (['fm_author', 'jja', 'pendidikan', 'type', 'quartile_jurnal', 'publisher', 'tanggal_pelaporan', 'prodi_kpi'] as $field) {
                    if ($record[$field] === null) {
                        $summary['missing'][$field] = ($summary['missing'][$field] ?? 0) + 1;
                    }
                }
                $record['source_row'] = (int) $number;
                $record['source_payload'] = json_encode(array_combine($headers, array_pad(array_slice($row, 0, count($headers)), count($headers), null)), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                $records[$key] = $record;
                $summary['submitted'][$record['submitted']]++;
            }
        } finally {
            $reader->close();
        }
        if (! $records) {
            $this->invalid('Tidak ada baris Raw dengan Kampus MALANG dan Submitted Non Scopus FM / Scopus FM. Data lama tetap tersimpan.');
        }
        $summary['selected'] = count($records);
        $summary['lecturers'] = count(array_unique(array_column($records, 'kode_dosen')));
        $summary['publications'] = count(array_unique(array_column($records, 'request_code')));

        return ['records' => array_values($records), 'summary' => $summary];
    }

    public static function adjustedWeight(array $record): float
    {
        $weight = (float) $record['bobot_asli'];
        if (strtolower($record['tipe_publikasi'] ?? '') !== 'scopus') {
            return $weight;
        }
        if (strtolower($record['jenis'] ?? '') === 'seminar') {
            return 1;
        }
        if (strtolower($record['jenis'] ?? '') === 'jurnal') {
            return match (strtoupper($record['quartile_jurnal'] ?? '')) {
                'Q1', 'Q1-TOP 10%' => 3,
                'Q2' => 2,
                'Q3', 'Q4', 'Q2/Q3' => 1,
                default => $weight,
            };
        }

        return $weight;
    }

    private function mapHeaders(array $headers, int $year): array
    {
        $map = [];
        foreach (self::HEADERS + ['status' => ['st'.$year, 'status']] as $field => $aliases) {
            $matches = array_keys(array_intersect($headers, $aliases));
            if (count($matches) > 1) {
                $this->invalid("Kolom {$field} ambigu atau duplikat pada sheet Raw.");
            }
            $map[$field] = $matches[0] ?? null;
        }
        foreach (['request_code', 'kode_dosen', 'kampus', 'submitted', 'bobot_asli', 'title', 'jenis', 'tipe_publikasi', 'status'] as $field) {
            if ($map[$field] === null) {
                $this->invalid("Kolom {$field} tidak ditemukan pada sheet Raw. Kolom status harus Status atau St{$year}; periksa tahun import.");
            }
        }

        return $map;
    }

    private function normalize(string $value): string
    {
        return strtolower(trim(preg_replace('/\s+/u', ' ', str_replace("\xC2\xA0", ' ', $value))));
    }

    private function date(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (is_numeric($value) && (float) $value > 0 && (float) $value < 100000) {
            return (new DateTimeImmutable('1899-12-30'))->modify('+'.(int) $value.' days')->format('Y-m-d');
        }
        foreach (['!j-M-y', '!j-M-Y', '!Y-m-d', '!d/m/Y'] as $format) {
            $date = DateTimeImmutable::createFromFormat($format, $value);
            $errors = DateTimeImmutable::getLastErrors();
            if ($date && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    private function invalid(string $message): never
    {
        throw ValidationException::withMessages(['rectorate' => $message]);
    }
}
