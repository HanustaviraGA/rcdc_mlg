<?php

namespace App\Services\Publications;

use App\Services\ColumnMapping\WorkbookSheetRoles;
use App\Services\Imports\ImportPolicy;
use Aspera\Spreadsheet\XLSX\Reader;
use Aspera\Spreadsheet\XLSX\ReaderConfiguration;

class WorkbookKpiReader
{
    private const HEADERS = [
        'kode_dosen' => 'kode dosen', 'name' => 'nama dosen', 'program' => 'jurusan binaan',
        'academic_rank' => 'jja', 'faculty_type' => 'faculty type',
        'rtto_non_scopus' => 'non scopus rtto', 'rtto_scopus' => 'scopus rtto', 'rtto_score' => 'score kpi rtto',
    ];

    /** Values Excel already calculated (header => metric), compared with the system calculation. */
    private const CACHED = [
        'non scopus rectorate' => 'rectorate_non_scopus', 'scopus rectorate' => 'rectorate_scopus',
        'non scopus' => 'non_scopus', 'scopus' => 'scopus', 'jumlah first author' => 'first_author', 'score kpi' => 'score',
    ];

    /**
     * Without a selection the sheet named KPI is read by its header names; a selection from the
     * mapping preview names the sheet (or none) and its columns.
     */
    public function read(string $path, array $publications, ?array $selection = null, ?int $year = null): array
    {
        $entries = [];
        $summary = ['available' => false, 'lecturers' => 0, 'missing_rtto' => 0, 'differences' => [], 'warnings' => []];
        $policy = new ImportPolicy('rectorate_fm');
        if ($selection !== null) {
            $table = app(WorkbookSheetRoles::class)->read($path, 'fm_file', 'kpi', $selection, $year);
            if ($table === null) {
                return compact('entries', 'summary');
            }
            $headers = array_map($this->normalize(...), $table['headers']);
            $map = array_intersect_key($table['map'], self::HEADERS);
            $cached = [];
            foreach (self::CACHED as $header => $field) {
                $index = $table['map'][$field] ?? null;
                if ($index !== null) {
                    $cached[$field] = ['index' => $index, 'column' => $headers[$index] ?: $header];
                }
            }
            $rows = $table['rows'];
            $summary['sheet'] = $table['sheet'];
        } else {
            $sheet = $this->namedSheet($path);
            if ($sheet === null) {
                return compact('entries', 'summary');
            }
            [$name, $headerCells, $rows] = $sheet;
            if ($headerCells === null) {
                $policy->accept('Sheet KPI tidak berisi dosen.');

                return ['entries' => [], 'summary' => $policy->summary($summary)];
            }
            $headers = array_map(fn ($cell) => $this->normalize((string) $cell), $headerCells);
            $map = [];
            foreach (self::HEADERS as $field => $header) {
                $matches = array_keys($headers, $header, true);
                if (count($matches) !== 1) {
                    $policy->accept("Header {$header} pada sheet KPI harus ada tepat satu kali. Sheet KPI dilewati.");

                    return ['entries' => [], 'summary' => $policy->summary($summary)];
                }
                $map[$field] = $matches[0];
            }
            $cached = [];
            foreach (self::CACHED as $header => $field) {
                $matches = array_keys($headers, $header, true);
                if ($matches) {
                    // Like the audit payload, a repeated header keeps its last column.
                    $cached[$field] = ['index' => end($matches), 'column' => $header];
                }
            }
            $summary['sheet'] = $name;
        }
        $summary['available'] = true;
        $byLecturer = collect($publications)->groupBy('kode_dosen');
        foreach ($rows as $number => $row) {
            $entry = [];
            foreach ($map as $field => $index) {
                $entry[$field] = trim((string) ($row[$index] ?? ''));
            }
            $entry['kode_dosen'] = strtoupper($entry['kode_dosen']);
            if ($entry['kode_dosen'] === '' || isset($entries[$entry['kode_dosen']])) {
                $policy->skip("Baris KPI {$number}: Kode Dosen kosong atau duplikat.");

                continue;
            }
            foreach (['rtto_non_scopus', 'rtto_scopus', 'rtto_score'] as $field) {
                $value = str_replace(',', '.', $entry[$field]);
                if ($value === '') {
                    $entry[$field] = null;

                    continue;
                }
                if (! is_numeric($value) || ! is_finite((float) $value) || (float) $value < 0
                    || ($field === 'rtto_score' && ((float) $value > 6 || floor((float) $value) !== (float) $value))) {
                    $policy->accept("Baris KPI {$number}: {$field} harus angka nol atau positif; skor KPI harus bilangan bulat 0–6. Nilai tidak valid dikosongkan.");
                    $entry[$field] = null;

                    continue;
                }
                $entry[$field] = (float) $value;
            }
            if ($entry['rtto_non_scopus'] === null || $entry['rtto_scopus'] === null || $entry['rtto_score'] === null) {
                $summary['missing_rtto']++;
            }
            $payload = array_combine($headers, array_pad(array_slice($row, 0, count($headers)), count($headers), null));
            $computed = PublicationWorkbookMetrics::summarize($byLecturer->get($entry['kode_dosen'], collect()), (object) $entry);
            foreach ($cached as $field => ['index' => $index, 'column' => $column]) {
                $value = $row[$index] ?? null;
                if ($value !== null && $value !== '' && (! is_numeric($value) || $computed[$field] === null || abs((float) $value - $computed[$field]) > 0.000001)) {
                    $summary['differences'][] = ['code' => $entry['kode_dosen'], 'column' => $column, 'excel' => $value, 'calculated' => $computed[$field]];
                }
            }
            $entry['source_row'] = (int) $number;
            $entry['source_payload'] = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            $entries[$entry['kode_dosen']] = $entry;
        }
        if (! $entries) {
            $policy->accept('Sheet KPI tidak berisi dosen.');
            $summary['available'] = false;
        }
        $summary['lecturers'] = count($entries);
        if ($summary['differences']) {
            $summary['warnings'][] = count($summary['differences']).' nilai turunan KPI berbeda dari hasil hitung. Dashboard menghitung ulang bobot Rectorate dan first author dari sheet publikasi, lalu mengambil maksimum per kategori dengan RTTO.';
        }
        if ($summary['missing_rtto']) {
            $summary['warnings'][] = $summary['missing_rtto'].' dosen memiliki data RTTO kosong. Nilai kosong tetap ditampilkan sebagai belum tersedia.';
        }

        return ['entries' => array_values($entries), 'summary' => $policy->summary($summary)];
    }

    /** The sheet named KPI: its name, header row and the following non-empty rows. */
    private function namedSheet(string $path): ?array
    {
        $reader = new Reader((new ReaderConfiguration)->setReturnUnformatted(true));
        try {
            $reader->open($path);
            $index = null;
            foreach ($reader->getSheets() as $sheetIndex => $sheet) {
                if (strtoupper(trim($sheet->getName())) === 'KPI') {
                    [$index, $name] = [$sheetIndex, $sheet->getName()];
                }
            }
            if ($index === null) {
                return null;
            }
            $reader->changeSheet($index);
            $rows = [];
            foreach ($reader as $number => $row) {
                if (array_filter($row, fn ($cell) => trim((string) $cell) !== '')) {
                    $rows[(int) $number] = $row;
                }
            }
        } finally {
            $reader->close();
        }
        $headerRow = array_key_first($rows);
        if ($headerRow === null) {
            return [$name, null, []];
        }
        $headers = $rows[$headerRow];
        unset($rows[$headerRow]);

        return [$name, $headers, $rows];
    }

    private function normalize(string $value): string
    {
        return strtolower(trim(preg_replace('/\s+/u', ' ', str_replace("\xC2\xA0", ' ', $value))));
    }
}
