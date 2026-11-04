<?php

namespace App\Services\ColumnMapping;

use Aspera\Spreadsheet\XLSX\Reader;
use Aspera\Spreadsheet\XLSX\ReaderConfiguration;
use DateTimeInterface;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

/**
 * The sheets each rectorate upload reads. The main sheet (MALANG/Raw/Detail) has its own
 * preview; each supporting sheet can be replaced by any sheet of the file, with its own
 * column mapping, or left unused. Without a selection the importers keep finding the
 * supporting sheets by name.
 */
class WorkbookSheetRoles
{
    private const MAIN = [
        'fm_file' => ['label' => 'Publikasi FM', 'expected' => 'MALANG atau Raw',
            'description' => 'Daftar publikasi per dosen. Wajib.'],
        'mhs_file' => ['label' => 'Publikasi MHS', 'expected' => 'MALANG atau Raw',
            'description' => 'Daftar publikasi mahasiswa. Wajib.'],
        'research_file' => ['label' => 'Hibah penelitian', 'expected' => 'MALANG atau Detail',
            'description' => 'Daftar peneliti per proposal hibah. Wajib; Hibah tidak memakai sheet lain.'],
    ];

    private const REALIZATION = ['label' => 'Realization Template', 'expected' => 'Realization Template',
        'sheets' => ['realization template'], 'config' => 'report_realization',
        'description' => 'Target dan realisasi per unit Malang untuk rekap laporan. Opsional.'];

    private const SCORING = ['label' => 'Scoring PI', 'expected' => 'Scoring PI',
        'sheets' => ['scoring pi'], 'config' => 'report_scoring',
        'description' => 'Rentang Score 1–6 per unit Malang untuk rekap laporan. Opsional.'];

    private const SUPPORTING = [
        'fm_file' => [
            'kpi' => ['label' => 'KPI', 'expected' => 'KPI', 'sheets' => ['kpi'], 'config' => 'fm_kpi',
                'description' => 'Daftar dosen dengan nilai RTTO dan Score KPI RTTO. Opsional; tanpa sheet ini skor memakai perhitungan sistem.'],
            'realization' => self::REALIZATION,
            'scoring' => self::SCORING,
        ],
        'mhs_file' => [
            'list' => ['label' => 'LIST', 'expected' => 'LIST', 'sheets' => ['list'], 'config' => 'mhs_list',
                'description' => 'Daftar publikasi Scopus mahasiswa. Opsional; tanpa sheet ini daftar dibentuk dari sheet publikasi.'],
            'title' => ['label' => 'TITLE', 'expected' => 'TITLE', 'sheets' => ['title'], 'config' => 'mhs_title',
                'description' => 'Rekap jumlah Scopus per prodi (blok Tipe Publikasi → Scopus). Opsional; tanpa sheet ini rekap dihitung dari daftar.'],
            'realization' => self::REALIZATION,
            'scoring' => self::SCORING,
        ],
        'research_file' => [],
    ];

    public function __construct(private ColumnMapperService $mapper) {}

    public function main(string $type): array
    {
        return self::MAIN[$type];
    }

    /** @return array<string, array> role => definition, in reading order */
    public function supporting(string $type): array
    {
        return self::SUPPORTING[$type] ?? [];
    }

    /**
     * Previews every supporting sheet: the sheet chosen for a role ('' or null: not used),
     * otherwise the sheet named like the role when the file has one.
     *
     * @param  array<string, ?string>  $chosen
     */
    public function preview(string $path, string $type, array $chosen = [], ?int $year = null): array
    {
        $roles = $this->supporting($type);
        if (! $roles) {
            return [];
        }
        $result = [];
        $this->withReader($path, function (Reader $reader, array $names) use ($roles, $chosen, $year, &$result) {
            foreach ($roles as $role => $definition) {
                $config = $this->mapper->getMappingConfig($definition['config'], $year);
                $entry = Arr::except($definition, ['sheets', 'config']) + [
                    'sheet' => null, 'header_row' => null, 'headers' => [], 'preview_data' => [],
                    'mapping_config' => $config, 'suggested_mapping' => [],
                ];
                $index = $this->sheetIndex($names, $definition, $chosen, $role);
                if ($index !== null) {
                    $rows = $this->rows($reader, $index, 40);
                    $entry['sheet'] = $names[$index];
                    if ($rows) {
                        [$headerRow, $headers] = $this->header($definition['config'], $rows);
                        $entry['header_row'] = $headerRow;
                        $entry['headers'] = $headers;
                        $entry['preview_data'] = array_values(array_map(
                            fn ($cells) => array_map($this->text(...), $cells),
                            array_slice(array_filter($rows, fn ($number) => $number > $headerRow, ARRAY_FILTER_USE_KEY), 0, 5)
                        ));
                        $entry['suggested_mapping'] = $this->mapper->suggestMapping($headers, $definition['config'], $year);
                    }
                }
                $result[$role] = $entry;
            }
        });

        return $result;
    }

    /**
     * Validates the sheet choices sent with a preview: role => sheet name ('' or null: not used).
     */
    public function previewChoice(string $type, ?string $raw, string $field): array
    {
        $decoded = $this->decode($raw, $field);
        foreach ($decoded as $role => $sheet) {
            $this->assertRole($type, $role, $field);
            if ($sheet !== null && (! is_string($sheet) || mb_strlen($sheet) > 255)) {
                $this->fail($field, "Pilihan sheet {$role} tidak valid.");
            }
        }

        return $decoded;
    }

    /**
     * Validates the supporting sheets confirmed for an upload:
     * role => ['sheet' => ?string, 'column_mapping' => ?array]. Null keeps the name-based defaults.
     */
    public function selection(string $type, ?string $raw, string $field): ?array
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }
        $decoded = $this->decode($raw, $field);
        foreach ($decoded as $role => $entry) {
            $this->assertRole($type, $role, $field);
            $sheet = is_array($entry) ? ($entry['sheet'] ?? null) : false;
            $mapping = is_array($entry) ? ($entry['column_mapping'] ?? null) : false;
            if (! is_array($entry) || ($sheet !== null && (! is_string($sheet) || mb_strlen($sheet) > 255))
                || ($mapping !== null && (! is_array($mapping) || (array_is_list($mapping) && $mapping !== [])))) {
                $this->fail($field, "Sheet pendukung {$role} harus berisi nama sheet dan pemetaan kolom yang valid.");
            }
        }

        return $decoded;
    }

    /**
     * One supporting sheet for an importer, or null when it is not used. Without a column
     * mapping the headers are matched by name.
     *
     * @return array{sheet: string, header_row: int, headers: array, map: array, rows: array<int, array>}|null
     */
    public function read(string $path, string $type, string $role, array $selection, ?int $year = null): ?array
    {
        $definition = $this->supporting($type)[$role];
        $sheet = $selection['sheet'] ?? null;
        if ($sheet === null || $sheet === '') {
            return null;
        }
        $label = "Sheet {$definition['label']} ({$sheet})";
        $rows = [];
        $this->withReader($path, function (Reader $reader, array $names) use ($sheet, $label, &$rows) {
            $index = array_search($sheet, $names, true);
            if ($index === false) {
                $this->fail('rectorate', "{$label} tidak ditemukan pada file. Data sebelumnya tetap tersimpan.");
            }
            $rows = $this->rows($reader, $index);
        });
        if (! $rows) {
            $this->fail('rectorate', "{$label} tidak berisi data. Data sebelumnya tetap tersimpan.");
        }
        [$headerRow, $headers] = $this->header($definition['config'], $rows);
        try {
            $map = $this->mapper->resolveMapping($headers, $definition['config'], $selection['column_mapping'] ?? null, $year);
        } catch (ValidationException $exception) {
            $this->fail('rectorate', "{$label}: ".implode(' ', Arr::flatten($exception->errors())).' Data sebelumnya tetap tersimpan.');
        }

        return ['sheet' => $sheet, 'header_row' => $headerRow, 'headers' => $headers, 'map' => $map,
            'rows' => array_filter($rows, fn ($number) => $number > $headerRow, ARRAY_FILTER_USE_KEY)];
    }

    private function withReader(string $path, callable $callback): void
    {
        $reader = new Reader((new ReaderConfiguration)->setReturnUnformatted(true));
        try {
            $reader->open($path);
            $names = [];
            foreach ($reader->getSheets() as $index => $sheet) {
                $names[$index] = $sheet->getName();
            }
            $callback($reader, $names);
        } finally {
            $reader->close();
        }
    }

    private function sheetIndex(array $names, array $definition, array $chosen, string $role): ?int
    {
        if (array_key_exists($role, $chosen)) {
            if ($chosen[$role] === null || $chosen[$role] === '') {
                return null;
            }
            $index = array_search($chosen[$role], $names, true);
            if ($index === false) {
                $this->fail('extra_sheets', "Sheet {$chosen[$role]} untuk {$definition['label']} tidak ditemukan pada file.");
            }

            return $index;
        }
        foreach ($names as $index => $name) {
            if (in_array($this->normalize($name), $definition['sheets'], true)) {
                return $index;
            }
        }

        return null;
    }

    /** Non-empty rows keyed by sheet row number, with the raw cell values. */
    private function rows(Reader $reader, int $index, ?int $limit = null): array
    {
        $reader->changeSheet($index);
        $rows = [];
        foreach ($reader as $number => $cells) {
            if (! array_filter($cells, fn ($cell) => $this->text($cell) !== '')) {
                continue;
            }
            $rows[(int) $number] = array_values($cells);
            if ($limit !== null && count($rows) >= $limit) {
                break;
            }
        }

        return $rows;
    }

    /**
     * The first non-empty row, except TITLE: its recap starts at the row with
     * "Tipe Publikasi" directly followed by "Scopus".
     */
    private function header(string $config, array $rows): array
    {
        $headerRow = array_key_first($rows);
        if ($config === 'mhs_title') {
            foreach ($rows as $number => $cells) {
                $normalized = array_map(fn ($cell) => $this->normalize($this->text($cell)), $cells);
                foreach ($normalized as $index => $cell) {
                    if ($cell === 'tipe publikasi' && ($normalized[$index + 1] ?? '') === 'scopus') {
                        $headerRow = $number;
                        break 2;
                    }
                }
            }
        }

        return [$headerRow, array_map($this->text(...), $rows[$headerRow])];
    }

    private function text(mixed $value): string
    {
        return $value instanceof DateTimeInterface ? $value->format('Y-m-d') : trim((string) $value);
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', str_replace("\xC2\xA0", ' ', $value))));
    }

    private function decode(?string $raw, string $field): array
    {
        if ($raw === null || trim($raw) === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        if (! is_array($decoded) || ! str_starts_with(ltrim($raw), '{')) {
            $this->fail($field, 'Pilihan sheet pendukung harus berupa objek JSON.');
        }

        return $decoded;
    }

    private function assertRole(string $type, int|string $role, string $field): void
    {
        if (! isset($this->supporting($type)[$role])) {
            $this->fail($field, "Sheet pendukung {$role} tidak dikenal untuk jenis file ini.");
        }
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
