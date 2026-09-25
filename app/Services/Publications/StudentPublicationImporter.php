<?php

namespace App\Services\Publications;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentPublicationImporter
{
    public function __construct(private ReportWorkbookReader $reader) {}

    public function import(string $path, string $filename, int $year, int $month, bool $dryRun = false): array
    {
        if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12) {
            $this->invalid('Tahun/bulan laporan tidak valid.');
        }
        $sheets = $this->reader->sheets($path, ['MALANG', 'RAW', 'TITLE', 'LIST']);
        $sheet = isset($sheets['MALANG']) ? 'MALANG' : 'RAW';
        if (! isset($sheets[$sheet])) {
            $this->invalid('Sheet MALANG atau Raw tidak ditemukan.');
        }
        $rows = $sheets[$sheet];
        $headerRow = array_key_first($rows);
        $headers = array_map($this->normalize(...), $rows[$headerRow] ?? []);
        unset($rows[$headerRow]);
        $aliases = ['request_code' => 'requestcode', 'name' => 'fm author', 'code' => 'kode dosen',
            'campus' => 'kampus', 'submitted' => 'submitted', 'type' => 'tipe publikasi',
            'title' => 'title', 'program' => 'prodi mhs', 'first_author' => 'first author'];
        $map = [];
        foreach ($aliases as $field => $header) {
            $matches = array_keys($headers, $header, true);
            if (count($matches) !== 1) {
                $this->invalid("Kolom {$header} wajib ada satu kali pada sheet {$sheet}.");
            }
            $map[$field] = $matches[0];
        }
        $summary = ['sheet' => $sheet, 'year' => $year, 'month' => $month, 'read' => count($rows),
            'excluded_campus' => 0, 'excluded_submitted' => 0, 'duplicates' => 0, 'warnings' => []];
        $records = [];
        foreach ($rows as $number => $cells) {
            $row = [];
            foreach ($map as $field => $index) {
                $row[$field] = $cells[$index] ?? '';
            }
            if (strtoupper($row['campus']) !== 'MALANG') {
                $summary['excluded_campus']++;

                continue;
            }
            if (! in_array($this->normalize($row['submitted']), ['scopus mahasiswa', 'non scopus mahasiswa'], true)
                || $this->normalize($row['code']) !== 'mahasiswa') {
                $summary['excluded_submitted']++;

                continue;
            }
            foreach (['request_code', 'name', 'title', 'program', 'type'] as $required) {
                if ($row[$required] === '') {
                    $this->invalid("Baris {$number}: {$required} wajib diisi.");
                }
            }
            $key = $row['request_code'].'|'.$this->normalize($row['name']).'|'.$this->normalize($row['program']);
            if (isset($records[$key])) {
                if (array_diff_assoc($row, $records[$key])) {
                    $this->invalid("Baris {$number}: identitas publikasi mahasiswa duplikat dengan isi berbeda.");
                }
                $summary['duplicates']++;

                continue;
            }
            $records[$key] = $row + ['source_row' => $number,
                'source_payload' => array_combine($headers, array_pad(array_slice($cells, 0, count($headers)), count($headers), ''))];
        }
        if (! $records) {
            $this->invalid('Tidak ada publikasi mahasiswa kampus MALANG.');
        }
        $scopus = array_values(array_filter($records, fn ($r) => $this->normalize($r['type']) === 'scopus' && $this->normalize($r['submitted']) === 'scopus mahasiswa'));
        $derivedList = array_map(fn ($r) => ['program' => $r['program'], 'name' => $r['name'], 'title' => $r['title']], $scopus);
        $list = $derivedList;
        if (isset($sheets['LIST'])) {
            $list = [];
            $listRows = $sheets['LIST'];
            $headerRow = array_key_first($listRows);
            $listHeaders = array_map($this->normalize(...), $listRows[$headerRow] ?? []);
            unset($listRows[$headerRow]);
            foreach (['prodi', 'mhs author', 'title'] as $header) {
                if (count(array_keys($listHeaders, $header, true)) !== 1) {
                    $this->invalid("Kolom {$header} tidak valid pada LIST.");
                }
            }
            foreach ($listRows as $number => $cells) {
                $row = [];
                foreach (['program' => 'prodi', 'name' => 'mhs author', 'title' => 'title'] as $field => $header) {
                    $row[$field] = $cells[array_search($header, $listHeaders, true)] ?? '';
                    if ($row[$field] === '') {
                        $this->invalid("LIST baris {$number}: {$header} wajib diisi.");
                    }
                }
                $list[] = $row;
            }
            $signature = fn ($r) => $this->normalize(implode('|', $r));
            if (collect($list)->map($signature)->countBy()->sortKeys()->all() !== collect($derivedList)->map($signature)->countBy()->sortKeys()->all()) {
                $summary['warnings'][] = 'Isi LIST berbeda dari baris Scopus pada MALANG. Rincian laporan menggunakan LIST; periksa sebelum mengunduh.';
            }
        } else {
            $summary['warnings'][] = 'Sheet LIST tidak ada; daftar mahasiswa dibentuk dari MALANG/Raw.';
        }
        $titleCounts = [];
        if (isset($sheets['TITLE'])) {
            $offset = null;
            foreach ($sheets['TITLE'] as $cells) {
                foreach ($cells as $i => $cell) {
                    if ($this->normalize($cell) === 'tipe publikasi' && $this->normalize($cells[$i + 1] ?? '') === 'scopus') {
                        $offset = $i;
                    }
                }
                if ($offset === null || ! is_numeric($cells[$offset + 1] ?? '')) {
                    continue;
                }
                $label = $cells[$offset] ?? '';
                $code = $this->normalize($label) === 'grand total' ? 'BINUS' : ReportPrograms::code($label);
                $titleCounts[$code] = (int) $cells[$offset + 1];
            }
            if (! $titleCounts) {
                $this->invalid('Rekap Scopus pada sheet TITLE tidak ditemukan.');
            }
        } else {
            $summary['warnings'][] = 'Sheet TITLE tidak ada; rekap dihitung dari daftar Scopus.';
        }
        $listCounts = collect($list)->countBy(fn ($r) => ReportPrograms::code($r['program']))->all();
        $listCounts['BINUS'] = count($list);
        foreach ($titleCounts as $code => $count) {
            if ($count !== ($listCounts[$code] ?? 0)) {
                $summary['warnings'][] = "{$code}: TITLE {$count}, LIST ".($listCounts[$code] ?? 0).'.';
            }
        }
        $report = $this->reader->read($path);
        foreach ($report['realization'] as $code => $row) {
            $actual = $titleCounts[$code] ?? $listCounts[$code] ?? 0;
            if ($row['realization'] !== null && abs($row['realization'] - $actual) > 0.001) {
                $summary['warnings'][] = "{$code}: Realization Template {$row['realization']}, TITLE/LIST {$actual}. Default laporan memakai TITLE/LIST; dapat diubah.";
            }
        }
        $summary += ['selected' => count($records), 'scopus' => count($scopus), 'list_count' => count($list),
            'title_counts' => $titleCounts, 'list_counts' => $listCounts, 'report' => $report];
        if ($dryRun) {
            return $summary + ['dry_run' => true];
        }
        DB::table('student_publication_imports')->upsert([[
            'year' => $year, 'month' => $month, 'filename' => basename($filename), 'sha256' => hash_file('sha256', $path),
            'summary' => json_encode($summary, JSON_THROW_ON_ERROR), 'records' => json_encode(array_values($records), JSON_THROW_ON_ERROR),
            'publication_list' => json_encode($list, JSON_THROW_ON_ERROR), 'created_at' => now(), 'updated_at' => now(),
        ]], ['year', 'month'], ['filename', 'sha256', 'summary', 'records', 'publication_list', 'updated_at']);

        return $summary;
    }

    private function normalize(string $value): string
    {
        return strtolower(trim(preg_replace('/\s+/u', ' ', str_replace("\xC2\xA0", ' ', $value))));
    }

    private function invalid(string $message): never
    {
        throw ValidationException::withMessages(['rectorate' => $message.' Data sebelumnya tetap tersimpan.']);
    }
}
