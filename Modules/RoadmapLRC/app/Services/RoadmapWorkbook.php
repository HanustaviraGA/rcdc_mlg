<?php

namespace Modules\RoadmapLRC\Services;

use App\Services\Imports\SheetName;
use App\Services\Lecturers\ProdiFmRoadmap;
use Aspera\Spreadsheet\XLSX\Reader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Throwable;
use ZipArchive;

class RoadmapWorkbook
{
    /** Read the two header rows of JJP, including workbooks with leading empty columns/rows. */
    public function read(string $path): array
    {
        [$sheet, $rows] = $this->rows($path);
        $headerRow = null;
        $prodiColumn = null;
        foreach ($rows as $number => $cells) {
            if ($number > 30) {
                break;
            }
            $matches = array_keys(array_map($this->normalize(...), $cells), 'PRODI', true);
            if (count($matches) === 1) {
                $headerRow = $number;
                $prodiColumn = $matches[0];
                break;
            }
        }
        if ($headerRow === null) {
            $this->invalid('Header PRODI tidak ditemukan pada 30 baris pertama sheet JJP.');
        }

        $columns = [];
        foreach ($rows[$headerRow] as $column => $label) {
            $label = $this->normalize($label);
            if (! str_starts_with($label, 'JUMLAH FM')) {
                continue;
            }
            if (! preg_match('/^JUMLAH FM (20\d{2}|2100)$/', $label, $match)) {
                $this->invalid('Header Jumlah FM harus menyertakan tahun 2000–2100, misalnya JUMLAH FM 2026.');
            }
            $year = (int) $match[1];
            if (isset($columns[$year])) {
                $this->invalid("Kolom Jumlah FM tahun {$year} duplikat.");
            }
            if ($this->normalize($rows[$headerRow + 1][$column + 1] ?? '') !== 'S2'
                || $this->normalize($rows[$headerRow + 1][$column + 2] ?? '') !== 'S3') {
                $this->invalid("Kolom S2 dan S3 tahun {$year} harus berada di kanan Jumlah FM pada baris header berikutnya.");
            }
            $columns[$year] = ['fm' => $column, 's2' => $column + 1, 's3' => $column + 2];
        }
        if (! $columns) {
            $this->invalid('Kolom JUMLAH FM beserta tahun, S2, dan S3 tidak ditemukan pada sheet JJP.');
        }
        ksort($columns);

        $records = [];
        $programs = [];
        $warnings = [];
        foreach ($rows as $number => $cells) {
            if ($number <= $headerRow + 1) {
                continue;
            }
            $prodi = $this->normalize($cells[$prodiColumn] ?? '');
            if (in_array($prodi, ['TOTAL', 'GRAND TOTAL', 'JUMLAH'], true)) {
                // Merged totals below the program rows are calculated again by the application.
                break;
            }
            if (! array_filter($cells, fn ($cell) => $cell !== '')) {
                continue;
            }
            if (! array_key_exists($prodi, ProdiFmRoadmap::PROGRAMS)) {
                $this->invalid("Baris {$number}: kode prodi ".($prodi === '' ? '(kosong)' : $prodi).' tidak dikenal. Gunakan '.implode(', ', array_keys(ProdiFmRoadmap::PROGRAMS)).'.');
            }
            if (in_array($prodi, $programs, true)) {
                $this->invalid("Baris {$number}: prodi {$prodi} duplikat.");
            }
            $programs[] = $prodi;
            foreach ($columns as $year => $fields) {
                $record = ['prodi' => $prodi, 'year' => $year];
                foreach ($fields as $field => $column) {
                    $value = $cells[$column] ?? '';
                    if (! preg_match('/^\d+(?:\.0+)?$/', $value) || (float) $value > 9999) {
                        $label = ProdiFmRoadmap::FIELDS[$field]['label'];
                        $this->invalid("Baris {$number}, {$prodi} {$year}: {$label} wajib berupa bilangan bulat 0–9999; gunakan 0 untuk nilai nol.");
                    }
                    $record[ProdiFmRoadmap::FIELDS[$field]['column']] = (int) $value;
                }
                if ($record['jumlah_fm'] !== $record['jumlah_s2'] + $record['jumlah_s3']) {
                    $warnings[] = "{$prodi} {$year}: S2 + S3 = ".($record['jumlah_s2'] + $record['jumlah_s3']).", Jumlah FM = {$record['jumlah_fm']}.";
                }
                $records[] = $record;
            }
        }
        if (! $records) {
            $this->invalid('Sheet JJP tidak berisi data prodi.');
        }

        return [
            'records' => $records,
            'summary' => [
                'sheet' => $sheet, 'programs' => $programs, 'years' => array_keys($columns),
                'selected' => count($records), 'mismatches' => count($warnings), 'warnings' => $warnings,
            ],
        ];
    }

    public function import(string $path): array
    {
        $parsed = $this->read($path);
        if (! Schema::hasTable(ProdiFmRoadmap::TABLE)) {
            $this->invalid('Migrasi roadmap jumlah FM belum dijalankan.');
        }

        return DB::transaction(function () use ($parsed) {
            $existing = DB::table(ProdiFmRoadmap::TABLE)
                ->whereIn('prodi', $parsed['summary']['programs'])->whereIn('year', $parsed['summary']['years'])
                ->lockForUpdate()->get()->keyBy(fn ($row) => $row->prodi.'-'.$row->year);
            $counts = ['inserted' => 0, 'updated' => 0, 'unchanged' => 0];
            $order = array_flip(array_keys(ProdiFmRoadmap::PROGRAMS));
            foreach ($parsed['records'] as $record) {
                $row = $existing->get($record['prodi'].'-'.$record['year']);
                $values = array_intersect_key($record, array_flip(['jumlah_fm', 'jumlah_s2', 'jumlah_s3']));
                if ($row) {
                    if (array_intersect_key((array) $row, $values) == $values) {
                        $counts['unchanged']++;

                        continue;
                    }
                    DB::table(ProdiFmRoadmap::TABLE)->where('id', $row->id)->update($values + ['updated_at' => now()]);
                    $counts['updated']++;
                } else {
                    // NR defaults to zero; notes are empty for new rows. Existing NR and notes are untouched.
                    DB::table(ProdiFmRoadmap::TABLE)->insert($record + [
                        'program_studi' => ProdiFmRoadmap::PROGRAMS[$record['prodi']], 'urutan' => $order[$record['prodi']] + 1,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                    $counts['inserted']++;
                }
            }

            return $parsed['summary'] + $counts;
        });
    }

    private function rows(string $path): array
    {
        // Reject incomplete XLSX packages before the reader starts extracting their contents.
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            $this->invalid('File Excel tidak dapat dibaca. Pastikan file .xlsx valid dan tidak dilindungi kata sandi.');
        }
        try {
            foreach (['[Content_Types].xml', '_rels/.rels', 'xl/workbook.xml', 'xl/_rels/workbook.xml.rels'] as $part) {
                if ($zip->locateName($part) === false) {
                    $this->invalid('File Excel tidak dapat dibaca. Struktur file .xlsx tidak lengkap.');
                }
            }
        } finally {
            $zip->close();
        }
        $reader = new Reader;
        try {
            $reader->open($path);
            $names = [];
            foreach ($reader->getSheets() as $index => $sheet) {
                $names[$index] = $sheet->getName();
            }
            $index = SheetName::find($names, 'JJP');
            if ($index === false) {
                $this->invalid('Sheet JJP tidak ditemukan atau namanya ambigu pada file Excel.');
            }
            $reader->changeSheet($index);
            $rows = [];
            foreach ($reader as $number => $cells) {
                if ($number > 1000 || count($cells) > 400) {
                    $this->invalid('Sheet JJP melebihi batas 1000 baris atau 400 kolom.');
                }
                $rows[(int) $number] = array_map(fn ($cell) => is_scalar($cell) ? trim((string) $cell) : '', $cells);
            }

            return [$names[$index], $rows];
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);
            $this->invalid('File Excel tidak dapat dibaca. Pastikan file .xlsx valid dan tidak dilindungi kata sandi.');
        } finally {
            $reader->close();
        }
    }

    private function normalize(string $value): string
    {
        return mb_strtoupper(trim(preg_replace('/\s+/u', ' ', str_replace("\xC2\xA0", ' ', $value))));
    }

    private function invalid(string $message): never
    {
        throw ValidationException::withMessages(['roadmap' => $message.' Data sebelumnya tetap tersimpan.']);
    }
}
