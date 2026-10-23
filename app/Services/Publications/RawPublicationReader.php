<?php

namespace App\Services\Publications;

use App\Services\ColumnMapping\ColumnMapperService;
use App\Services\Imports\ImportPolicy;
use Aspera\Spreadsheet\XLSX\Reader;
use Aspera\Spreadsheet\XLSX\ReaderConfiguration;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Validation\ValidationException;

class RawPublicationReader
{
    public const SUBMITTED = ['Non Scopus FM', 'Scopus FM'];

    public function read(string $path, int $year, ?array $userMapping = null): array
    {
        $reader = new Reader((new ReaderConfiguration)->setReturnUnformatted(true));
        $summary = ['sheet' => 'Raw', 'read' => 0, 'excluded_campus' => 0, 'excluded_submitted' => 0,
            'duplicates' => 0, 'selected' => 0, 'submitted' => array_fill_keys(self::SUBMITTED, 0),
            'missing' => [], 'warnings' => []];
        $records = [];
        $mapper = app(ColumnMapperService::class);
        $sourceHeaders = [];
        $policy = new ImportPolicy('rectorate_fm');
        try {
            $reader->open($path);
            $sheets = [];
            foreach ($reader->getSheets() as $index => $sheet) {
                $sheets[strtolower(trim($sheet->getName()))] = $index;
            }
            $sheetIndex = $sheets['malang'] ?? $sheets['raw'] ?? null;
            if ($sheetIndex === null) {
                $this->invalid('Sheet Raw atau MALANG tidak ditemukan. Data sebelumnya tetap tersimpan.');
            }
            $summary['sheet'] = isset($sheets['malang']) ? 'MALANG' : 'Raw';
            $reader->changeSheet($sheetIndex);
            $map = null;
            $headers = [];
            foreach ($reader as $number => $row) {
                if (! array_filter($row, fn ($cell) => trim((string) $cell) !== '')) {
                    continue;
                }
                if ($map === null) {
                    $sourceHeaders = array_map(fn ($header) => trim((string) $header), $row);
                    $headers = array_map(fn ($h) => $this->normalize((string) $h), $row);
                    $map = $mapper->resolveMapping($headers, 'fm_file', $userMapping, $year);
                    $missingOptional = array_keys(array_filter($map, fn ($index) => $index === null));
                    if ($missingOptional) {
                        $summary['warnings'][] = 'Kolom opsional tidak dipetakan: '.implode(', ', $missingOptional).'. Import tetap dilanjutkan; nilai terkait belum tersedia.';
                    }

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
                if ($policy->force && $record['kode_dosen'] === '') {
                    $policy->skip("Baris {$number}: Kode Dosen kosong.");

                    continue;
                }
                $validation = $mapper->validateRow($record, 'fm_file', (int) $number, $map, $sourceHeaders, $year);
                if (! $validation['valid']) {
                    $policy->accept(implode(' ', $validation['errors']));
                }
                $weight = str_replace(',', '.', $record['bobot_asli'] ?? '');
                if ($record['bobot_asli'] !== null && (! is_numeric($weight) || ! is_finite((float) $weight) || (float) $weight < 0)) {
                    $policy->accept("Baris {$number}: Bobot harus berupa angka nol atau positif; nilai tidak valid dikosongkan.");
                    $record['bobot_asli'] = null;
                }
                $record['bobot_asli'] = $record['bobot_asli'] === null ? null : (float) $weight;
                $record['bobot'] = $record['bobot_asli'] === null ? null : self::adjustedWeight($record);
                $record['tanggal_pelaporan'] = $this->date($record['tanggal_pelaporan']);
                $key = PublicationWorkbookMetrics::publicationKey($record + ['source_row' => (int) $number]).'|'.$record['kode_dosen'];
                if (isset($records[$key])) {
                    $previous = array_intersect_key($records[$key], $record);
                    if ($previous !== $record) {
                        $policy->skip("Baris {$number}: identitas publikasi dan Kode Dosen duplikat dengan isi berbeda; data pertama dipakai.");

                        continue;
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
            $this->invalid('Tidak ada baris '.$summary['sheet'].' dengan Kampus MALANG dan Submitted Non Scopus FM / Scopus FM. Data lama tetap tersimpan.');
        }
        $summary['selected'] = count($records);
        $summary['lecturers'] = count(array_unique(array_column($records, 'kode_dosen')));
        $summary['publications'] = count(array_unique(array_map(PublicationWorkbookMetrics::publicationKey(...), $records)));

        return ['records' => array_values($records), 'summary' => $policy->summary($summary)];
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
