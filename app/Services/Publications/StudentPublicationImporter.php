<?php

namespace App\Services\Publications;

use App\Services\ColumnMapping\ColumnMapperService;
use App\Services\ColumnMapping\WorkbookSheetRoles;
use App\Services\Imports\ImportPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentPublicationImporter
{
    public function __construct(private ReportWorkbookReader $reader) {}

    /**
     * @param  ?array  $extraSheets  Supporting sheets confirmed in the preview (LIST, TITLE,
     *                               Realization Template, Scoring PI); null finds them by name.
     */
    public function import(string $path, string $filename, int $year, int $month, bool $dryRun = false, ?array $columnMapping = null, ?string $sheet = null, ?array $extraSheets = null): array
    {
        if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12) {
            $this->invalid('Tahun/bulan laporan tidak valid.');
        }
        // A sheet chosen in the mapping preview replaces the MALANG/Raw default. LIST and TITLE
        // are found by name unless the preview confirmed another sheet (or none) for them.
        $chosen = $sheet === null ? null : strtoupper(trim($sheet));
        $listSelection = $extraSheets['list'] ?? null;
        $titleSelection = $extraSheets['title'] ?? null;
        $named = array_keys(array_filter(['TITLE' => $titleSelection, 'LIST' => $listSelection], fn ($selection) => $selection === null));
        $sheets = $this->reader->sheets($path, array_merge(['MALANG', 'RAW'], $named, $chosen === null ? [] : [$chosen]));
        $key = $chosen ?? (isset($sheets['MALANG']) ? 'MALANG' : 'RAW');
        if (! isset($sheets[$key])) {
            $this->invalid($chosen === null ? 'Sheet MALANG atau Raw tidak ditemukan. Pilih sheet publikasi pada pratinjau.' : "Sheet {$sheet} tidak ditemukan atau kosong.");
        }
        $rows = $sheets[$key];
        $sheet ??= $key;
        $headerRow = array_key_first($rows);
        $sourceHeaders = $rows[$headerRow] ?? [];
        $headers = array_map($this->normalize(...), $rows[$headerRow] ?? []);
        unset($rows[$headerRow]);
        $mapper = app(ColumnMapperService::class);
        $map = $mapper->resolveMapping($headers, 'mhs_file', $columnMapping, $year);
        $summary = ['sheet' => $sheet, 'year' => $year, 'month' => $month, 'read' => count($rows),
            'excluded_campus' => 0, 'excluded_submitted' => 0, 'duplicates' => 0, 'warnings' => []];
        $records = [];
        $policy = new ImportPolicy('rectorate_mhs');
        foreach ($rows as $number => $cells) {
            $row = [];
            foreach ($map as $field => $index) {
                $row[$field] = $index === null ? '' : ($cells[$index] ?? '');
            }
            if (strtoupper($row['campus']) !== 'MALANG') {
                $summary['excluded_campus']++;

                continue;
            }
            if (! in_array($this->normalize($row['submitted']), ['scopus mahasiswa', 'non scopus mahasiswa'], true)
                || ($row['code'] !== '' && $this->normalize($row['code']) !== 'mahasiswa')) {
                $summary['excluded_submitted']++;

                continue;
            }
            $validation = $mapper->validateRow($row, 'mhs_file', (int) $number, $map, $sourceHeaders, $year);
            if (! $validation['valid']) {
                $policy->accept(implode(' ', $validation['errors']));
            }
            $key = PublicationWorkbookMetrics::publicationKey($row + ['tipe_publikasi' => $row['type'], 'source_row' => $number])
                .'|'.$this->normalize($row['name']).'|'.$this->normalize($row['program']);
            if (isset($records[$key])) {
                if (array_diff_assoc($row, $records[$key])) {
                    $policy->skip("Baris {$number}: identitas publikasi mahasiswa duplikat dengan isi berbeda; data pertama dipakai.");

                    continue;
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
        $roles = app(WorkbookSheetRoles::class);
        if ($listSelection === null ? isset($sheets['LIST']) : $this->used($listSelection)) {
            try {
                $list = [];
                // Field => [column, name shown in messages].
                $columns = [];
                if ($listSelection === null) {
                    $listRows = $sheets['LIST'];
                    $headerRow = array_key_first($listRows);
                    $listHeaders = array_map($this->normalize(...), $listRows[$headerRow] ?? []);
                    unset($listRows[$headerRow]);
                    foreach (['program' => 'prodi', 'name' => 'mhs author', 'title' => 'title'] as $field => $header) {
                        if (count(array_keys($listHeaders, $header, true)) !== 1) {
                            $this->invalid("Kolom {$header} tidak valid pada LIST.");
                        }
                        $columns[$field] = [array_search($header, $listHeaders, true), $header];
                    }
                } else {
                    $table = $roles->read($path, 'mhs_file', 'list', $listSelection, $year);
                    $labels = app(ColumnMapperService::class)->getMappingConfig('mhs_list')['required'];
                    $listRows = array_map(fn ($cells) => array_map(fn ($value) => trim((string) $value), $cells), $table['rows']);
                    foreach (['program', 'name', 'title'] as $field) {
                        $columns[$field] = [$table['map'][$field], $labels[$field]];
                    }
                }
                foreach ($listRows as $number => $cells) {
                    $row = [];
                    foreach ($columns as $field => [$index, $header]) {
                        $row[$field] = $cells[$index] ?? '';
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
            } catch (ValidationException $exception) {
                $policy->accept($exception->getMessage().' LIST dibentuk dari data MALANG/Raw.');
                $list = $derivedList;
            }
        } else {
            $summary['warnings'][] = $listSelection === null
                ? 'Sheet LIST tidak ada; daftar mahasiswa dibentuk dari MALANG/Raw.'
                : 'Sheet LIST tidak digunakan; daftar mahasiswa dibentuk dari sheet publikasi.';
        }
        $titleCounts = [];
        if ($titleSelection !== null) {
            if ($this->used($titleSelection)) {
                $table = $roles->read($path, 'mhs_file', 'title', $titleSelection, $year);
                // The recap rows below the confirmed header: a label and its Scopus count.
                foreach ($table['rows'] as $cells) {
                    $count = trim((string) ($cells[$table['map']['count']] ?? ''));
                    if (! is_numeric($count)) {
                        continue;
                    }
                    $label = trim((string) ($cells[$table['map']['label']] ?? ''));
                    $titleCounts[$this->normalize($label) === 'grand total' ? 'BINUS' : ReportPrograms::code($label)] = (int) $count;
                }
                if (! $titleCounts) {
                    $policy->accept("Rekap Scopus pada sheet {$table['sheet']} tidak ditemukan; rekap dihitung dari daftar Scopus.");
                }
            } else {
                $summary['warnings'][] = 'Sheet TITLE tidak digunakan; rekap dihitung dari daftar Scopus.';
            }
        } elseif (isset($sheets['TITLE'])) {
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
                $policy->accept('Rekap Scopus pada sheet TITLE tidak ditemukan; rekap dihitung dari daftar Scopus.');
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
        $report = $this->reader->read($path, $extraSheets, 'mhs_file');
        foreach ($report['realization'] as $code => $row) {
            $actual = $titleCounts[$code] ?? $listCounts[$code] ?? 0;
            if ($row['realization'] !== null && abs($row['realization'] - $actual) > 0.001) {
                $summary['warnings'][] = "{$code}: Realization Template {$row['realization']}, TITLE/LIST {$actual}. Default laporan memakai TITLE/LIST; dapat diubah.";
            }
        }
        $summary += ['selected' => count($records), 'scopus' => count($scopus), 'list_count' => count($list),
            'title_counts' => $titleCounts, 'list_counts' => $listCounts, 'report' => $report];
        if ($extraSheets !== null) {
            $summary['supporting_sheets'] = array_map(fn ($selection) => $this->used($selection) ? $selection['sheet'] : null, $extraSheets);
        }
        $summary = $policy->summary($summary);
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

    private function used(array $selection): bool
    {
        return ($selection['sheet'] ?? null) !== null && $selection['sheet'] !== '';
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
