<?php

namespace App\Services\CommunityDevelopment;

use Aspera\Spreadsheet\XLSX\Reader;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use ZipArchive;

class PartnerWorkbookReader
{
    public function read(string $path, ?string $sheet = null, ?int $limit = null): array
    {
        $zip = new ZipArchive;
        $reader = null;
        $opened = false;
        try {
            if ($zip->open($path) !== true) {
                throw new RuntimeException('File bukan workbook Excel yang valid.');
            }
            $opened = true;
            $expanded = 0;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $expanded += $zip->statIndex($i)['size'];
                if ($expanded > 80 * 1024 * 1024) {
                    throw new RuntimeException('Ukuran isi workbook melebihi 80 MB.');
                }
            }
            if ($zip->locateName('xl/workbook.bin') !== false) {
                return (new XlsbReader)->read($zip, $sheet, $limit);
            }
            if ($zip->locateName('xl/workbook.xml') === false) {
                throw new RuntimeException('Workbook XLSX/XLSB tidak ditemukan.');
            }
            $reader = new Reader;
            $reader->open($path);
            $sheets = [];
            foreach ($reader->getSheets() as $index => $entry) {
                $sheets[$entry->getName()] = $index;
            }
            $sheet ??= array_key_first($sheets);
            if ($sheet === null || ! isset($sheets[$sheet])) {
                throw new RuntimeException('Sheet yang dipilih tidak tersedia.');
            }
            $reader->changeSheet($sheets[$sheet]);
            $rows = [];
            foreach ($reader as $number => $cells) {
                $cells = array_map(fn ($value) => trim((string) $value), $cells);
                if (! array_filter($cells, fn ($value) => $value !== '')) {
                    continue;
                }
                if (count($cells) > 256 || count($rows) >= 100000) {
                    throw new RuntimeException('Maksimal 256 kolom dan 100.000 baris per sheet.');
                }
                $rows[(int) $number] = $cells;
                if ($limit !== null && count($rows) >= $limit) {
                    break;
                }
            }

            return ['sheet' => $sheet, 'sheets' => array_keys($sheets), 'rows' => $rows];
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages(['partners' => 'File Excel tidak dapat dibaca. '.$exception->getMessage()]);
        } finally {
            $reader?->close();
            if ($opened) {
                $zip->close();
            }
        }
    }
}
