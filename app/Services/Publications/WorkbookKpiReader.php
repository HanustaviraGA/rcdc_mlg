<?php

namespace App\Services\Publications;

use Aspera\Spreadsheet\XLSX\Reader;
use Aspera\Spreadsheet\XLSX\ReaderConfiguration;
use Illuminate\Validation\ValidationException;

class WorkbookKpiReader
{
    private const HEADERS = [
        'kode_dosen' => 'kode dosen', 'name' => 'nama dosen', 'program' => 'jurusan binaan',
        'academic_rank' => 'jja', 'faculty_type' => 'faculty type',
        'rtto_non_scopus' => 'non scopus rtto', 'rtto_scopus' => 'scopus rtto', 'rtto_score' => 'score kpi rtto',
    ];

    public function read(string $path, array $publications): array
    {
        $reader = new Reader((new ReaderConfiguration)->setReturnUnformatted(true));
        $entries = [];
        $summary = ['available' => false, 'lecturers' => 0, 'missing_rtto' => 0, 'differences' => [], 'warnings' => []];
        try {
            $reader->open($path);
            $sheets = [];
            foreach ($reader->getSheets() as $index => $sheet) {
                $sheets[strtoupper(trim($sheet->getName()))] = $index;
            }
            if (! isset($sheets['KPI'])) {
                return compact('entries', 'summary');
            }
            $summary['available'] = true;
            $reader->changeSheet($sheets['KPI']);
            $headers = null;
            $byLecturer = collect($publications)->groupBy('kode_dosen');
            foreach ($reader as $number => $row) {
                if (! array_filter($row, fn ($cell) => trim((string) $cell) !== '')) {
                    continue;
                }
                if ($headers === null) {
                    $headers = array_map(fn ($cell) => strtolower(trim(preg_replace('/\s+/u', ' ', str_replace("\xC2\xA0", ' ', (string) $cell)))), $row);
                    $map = [];
                    foreach (self::HEADERS as $field => $header) {
                        $matches = array_keys($headers, $header, true);
                        if (count($matches) !== 1) {
                            $this->invalid("Header {$header} pada sheet KPI harus ada tepat satu kali.");
                        }
                        $map[$field] = $matches[0];
                    }

                    continue;
                }
                $entry = [];
                foreach ($map as $field => $index) {
                    $entry[$field] = trim((string) ($row[$index] ?? ''));
                }
                $entry['kode_dosen'] = strtoupper($entry['kode_dosen']);
                if ($entry['kode_dosen'] === '' || isset($entries[$entry['kode_dosen']])) {
                    $this->invalid("Baris KPI {$number}: Kode Dosen kosong atau duplikat.");
                }
                foreach (['rtto_non_scopus', 'rtto_scopus', 'rtto_score'] as $field) {
                    $value = str_replace(',', '.', $entry[$field]);
                    if ($value === '') {
                        $entry[$field] = null;

                        continue;
                    }
                    if (! is_numeric($value) || ! is_finite((float) $value) || (float) $value < 0
                        || ($field === 'rtto_score' && ((float) $value > 6 || floor((float) $value) !== (float) $value))) {
                        $this->invalid("Baris KPI {$number}: {$field} harus angka nol atau positif; skor KPI harus bilangan bulat 0–6. Sel rumus harus memiliki hasil tersimpan yang valid.");
                    }
                    $entry[$field] = (float) $value;
                }
                if ($entry['rtto_non_scopus'] === null || $entry['rtto_scopus'] === null || $entry['rtto_score'] === null) {
                    $summary['missing_rtto']++;
                }
                $payload = array_combine($headers, array_pad(array_slice($row, 0, count($headers)), count($headers), null));
                $computed = PublicationWorkbookMetrics::summarize($byLecturer->get($entry['kode_dosen'], collect()), (object) $entry);
                foreach (['non scopus rectorate' => 'rectorate_non_scopus', 'scopus rectorate' => 'rectorate_scopus',
                    'non scopus' => 'non_scopus', 'scopus' => 'scopus', 'jumlah first author' => 'first_author', 'score kpi' => 'score'] as $header => $field) {
                    $cached = $payload[$header] ?? null;
                    if ($cached !== null && $cached !== '' && (! is_numeric($cached) || $computed[$field] === null || abs((float) $cached - $computed[$field]) > 0.000001)) {
                        $summary['differences'][] = ['code' => $entry['kode_dosen'], 'column' => $header, 'excel' => $cached, 'calculated' => $computed[$field]];
                    }
                }
                $entry['source_row'] = (int) $number;
                $entry['source_payload'] = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                $entries[$entry['kode_dosen']] = $entry;
            }
            if (! $entries) {
                $this->invalid('Sheet KPI tidak berisi dosen. Data sebelumnya tetap tersimpan.');
            }
        } finally {
            $reader->close();
        }
        $summary['lecturers'] = count($entries);
        if ($summary['differences']) {
            $summary['warnings'][] = count($summary['differences']).' nilai turunan KPI berbeda dari hasil hitung. Dashboard menghitung ulang bobot Rectorate dan first author dari sheet publikasi, lalu mengambil maksimum per kategori dengan RTTO.';
        }
        if ($summary['missing_rtto']) {
            $summary['warnings'][] = $summary['missing_rtto'].' dosen memiliki data RTTO kosong. Nilai kosong tetap ditampilkan sebagai belum tersedia.';
        }

        return ['entries' => array_values($entries), 'summary' => $summary];
    }

    private function invalid(string $message): never
    {
        throw ValidationException::withMessages(['rectorate' => $message]);
    }
}
