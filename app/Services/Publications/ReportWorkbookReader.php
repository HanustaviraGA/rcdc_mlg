<?php

namespace App\Services\Publications;

use App\Services\ColumnMapping\WorkbookSheetRoles;
use Aspera\Spreadsheet\XLSX\Reader;
use Aspera\Spreadsheet\XLSX\ReaderConfiguration;

class ReportWorkbookReader
{
    public function sheets(string $path, array $names): array
    {
        $reader = new Reader((new ReaderConfiguration)->setReturnUnformatted(true));
        $result = [];
        try {
            $reader->open($path);
            foreach ($reader->getSheets() as $index => $sheet) {
                $name = strtoupper(trim($sheet->getName()));
                if (! in_array($name, $names, true)) {
                    continue;
                }
                $reader->changeSheet($index);
                $result[$name] = [];
                foreach ($reader as $number => $cells) {
                    $cells = array_map(fn ($value) => trim((string) $value), $cells);
                    if (array_filter($cells, fn ($value) => $value !== '')) {
                        $result[$name][$number] = $cells;
                    }
                }
            }
        } finally {
            $reader->close();
        }

        return $result;
    }

    /**
     * Realization Template and Scoring PI. A role missing from $selections (or no selections)
     * is the sheet with that name read by its headers; a selection from the mapping preview
     * names another sheet (or none) and its columns.
     */
    public function read(string $path, ?array $selections = null, string $type = 'fm_file'): array
    {
        $named = array_filter(['REALIZATION TEMPLATE' => 'realization', 'SCORING PI' => 'scoring'],
            fn ($role) => ($selections[$role] ?? null) === null);
        $sheets = [];
        foreach ($named ? $this->sheets($path, array_keys($named)) : [] as $name => $rows) {
            $headers = array_map('strtolower', array_shift($rows) ?? []);
            $sheets[$named[$name]] = array_map(fn ($cells) => array_combine($headers, array_pad(array_slice($cells, 0, count($headers)), count($headers), '')), $rows);
        }
        $scores = range(1, 6);
        // Header name of the named sheets => mapped field.
        $columns = [
            'realization' => ['unit name' => 'unit', 'target' => 'target', 'realization' => 'realization'],
            'scoring' => ['unit name' => 'unit'] + array_combine(array_map(fn ($score) => "score {$score}", $scores), array_map(fn ($score) => "score_{$score}", $scores)),
        ];
        foreach ($columns as $role => $fields) {
            if (($selections[$role] ?? null) === null) {
                continue;
            }
            $table = app(WorkbookSheetRoles::class)->read($path, $type, $role, $selections[$role]);
            if ($table !== null) {
                $sheets[$role] = array_map(fn ($cells) => array_map(
                    fn ($field) => $table['map'][$field] === null ? '' : trim((string) ($cells[$table['map'][$field]] ?? '')),
                    $fields
                ), $table['rows']);
            }
        }
        $realization = [];
        $ranges = [];
        foreach ($sheets as $role => $rows) {
            foreach ($rows as $row) {
                $unit = $row['unit name'] ?? '';
                if (! str_contains(strtolower($unit), 'malang')) {
                    continue;
                }
                $code = ReportPrograms::code($unit);
                // The campus row is authoritative; avoid a second institutional subtotal.
                if ($code === 'BINUS' && ! str_contains(strtolower($unit), 'campus')) {
                    continue;
                }
                if ($role === 'realization') {
                    $realization[$code] = ['target' => self::number($row['target'] ?? ''),
                        'realization' => self::number($row['realization'] ?? ''), 'unit' => $unit];
                } else {
                    for ($score = 1; $score <= 6; $score++) {
                        $bounds = preg_split('/\s+-\s+/', trim($row['score '.$score] ?? ''));
                        if (count($bounds) === 2 && self::number($bounds[0]) !== null && self::number($bounds[1]) !== null) {
                            $ranges[$code][$score] = array_map(self::number(...), $bounds);
                        }
                    }
                }
            }
        }

        return ['realization' => $realization, 'ranges' => $ranges];
    }

    public static function number(string $value): ?float
    {
        $value = preg_replace('/\s+/u', '', $value);
        if (str_contains($value, ',')) {
            $value = str_replace(['.', ','], ['', '.'], $value);
        }

        return is_numeric($value) ? (float) $value : null;
    }

    public static function score(?float $value, array $ranges): ?int
    {
        if ($value === null) {
            return null;
        }
        foreach ($ranges as $score => [$min, $max]) {
            if (round($value, 2) >= $min && round($value, 2) <= $max) {
                return (int) $score;
            }
        }

        return null;
    }
}
