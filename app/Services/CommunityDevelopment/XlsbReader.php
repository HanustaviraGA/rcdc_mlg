<?php

namespace App\Services\CommunityDevelopment;

use Generator;
use RuntimeException;
use ZipArchive;

/**
 * Read BIFF12 cell values, including cached formula results (MS-XLSB).
 * Formatting, macros and formula evaluation are intentionally outside this reader.
 *
 * @see https://learn.microsoft.com/en-us/openspecs/office_file_formats/ms-xlsb/acc8aa92-1f02-4167-99f5-84f9f676b95a
 */
class XlsbReader
{
    public function read(ZipArchive $zip, ?string $sheet, ?int $limit): array
    {
        $xml = $zip->getFromName('xl/_rels/workbook.bin.rels');
        $relationships = $xml === false ? false : @simplexml_load_string($xml, options: LIBXML_NONET);
        if ($relationships === false) {
            throw new RuntimeException('Relasi workbook XLSB tidak valid.');
        }
        $targets = [];
        foreach ($relationships->children('http://schemas.openxmlformats.org/package/2006/relationships') as $relationship) {
            $attributes = $relationship->attributes();
            if ((string) $attributes['TargetMode'] !== 'External') {
                $target = (string) $attributes['Target'];
                $targets[(string) $attributes['Id']] = str_starts_with($target, '/') ? ltrim($target, '/') : 'xl/'.$target;
            }
        }
        $sheets = [];
        foreach ($this->records($this->part($zip, 'xl/workbook.bin')) as [$id, $record]) {
            if ($id === 0x019C) { // BrtBundleSh
                $offset = 8;
                $relation = $this->wideString($record, $offset);
                $name = $this->wideString($record, $offset);
                if (isset($targets[$relation]) && str_starts_with($targets[$relation], 'xl/worksheets/')) {
                    $sheets[$name] = $targets[$relation];
                }
            }
        }
        $sheet ??= array_key_first($sheets);
        if ($sheet === null || ! isset($sheets[$sheet])) {
            throw new RuntimeException('Sheet yang dipilih tidak tersedia.');
        }
        $strings = [];
        if ($zip->locateName('xl/sharedStrings.bin') !== false) {
            foreach ($this->records($this->part($zip, 'xl/sharedStrings.bin')) as [$id, $record]) {
                if ($id === 0x13) { // BrtSSTItem: rich-string flags, followed by XLWideString
                    $offset = 1;
                    $strings[] = $this->wideString($record, $offset);
                }
            }
        }
        $rows = [];
        $row = null;
        $cells = [];
        $complete = false;
        foreach ($this->records($this->part($zip, $sheets[$sheet])) as [$id, $record]) {
            if ($id === 0 || $id === 0x0192) { // BrtRowHdr / BrtEndSheetData
                if ($row !== null && array_filter($cells, fn ($value) => $value !== '')) {
                    if (count($rows) >= 100000) {
                        throw new RuntimeException('Maksimal 100.000 baris per sheet.');
                    }
                    $rows[$row] = array_replace(array_fill(0, max(array_keys($cells)) + 1, ''), $cells);
                    if ($limit !== null && count($rows) >= $limit) {
                        $complete = true;
                        break;
                    }
                }
                $cells = [];
                $row = $id === 0 ? $this->uint($record, 0) + 1 : null;
                if ($id === 0x0192) {
                    $complete = true;
                    break;
                }

                continue;
            }
            if ($row === null || ($id !== 62 && ($id < 1 || $id > 11))) {
                continue;
            }
            $column = $this->uint($record, 0);
            if ($column >= 256) {
                throw new RuntimeException('Maksimal 256 kolom per sheet.');
            }
            $offset = $id === 62 ? 9 : 8;
            $value = match ($id) {
                1 => '',
                2 => $this->rk($this->uint($record, 8)),
                3, 11 => '#ERROR!',
                4, 10 => ord($this->bytes($record, 8, 1)) ? '1' : '0',
                5, 9 => unpack('e', $this->bytes($record, 8, 8))[1],
                6, 8, 62 => $this->wideString($record, $offset),
                7 => $strings[$this->uint($record, 8)] ?? throw new RuntimeException('Indeks shared string tidak valid.'),
            };
            $cells[$column] = trim((string) $value);
        }
        if (! $complete) {
            throw new RuntimeException('Akhir data sheet XLSB tidak ditemukan.');
        }

        return ['sheet' => $sheet, 'sheets' => array_keys($sheets), 'rows' => $rows];
    }

    private function records(string $binary): Generator
    {
        $offset = 0;
        $size = strlen($binary);
        while ($offset < $size) {
            $id = $this->variableInteger($binary, $offset, false);
            $length = $this->variableInteger($binary, $offset, true);
            $record = $this->bytes($binary, $offset, $length);
            $offset += $length;
            yield [$id, $record];
        }
    }

    private function variableInteger(string $binary, int &$offset, bool $length): int
    {
        $value = 0;
        for ($i = 0; $i < 4; $i++) {
            $byte = ord($this->bytes($binary, $offset++, 1));
            $value |= ($length ? $byte & 0x7F : $byte) << (($length ? 7 : 8) * $i);
            if (($byte & 0x80) === 0) {
                return $value;
            }
        }
        throw new RuntimeException('Record BIFF12 tidak valid.');
    }

    private function wideString(string $record, int &$offset): string
    {
        $length = $this->uint($record, $offset);
        $offset += 4;
        if ($length === 0xFFFFFFFF) {
            return '';
        }
        $value = mb_convert_encoding($this->bytes($record, $offset, $length * 2), 'UTF-8', 'UTF-16LE');
        $offset += $length * 2;

        return $value;
    }

    private function rk(int $raw): float
    {
        $signed = $raw > 0x7FFFFFFF ? $raw - 0x100000000 : $raw;
        $value = ($raw & 2) ? $signed >> 2 : unpack('e', pack('V2', 0, $raw & 0xFFFFFFFC))[1];

        return ($raw & 1) ? $value / 100 : (float) $value;
    }

    private function uint(string $record, int $offset): int
    {
        return unpack('V', $this->bytes($record, $offset, 4))[1];
    }

    private function bytes(string $data, int $offset, int $length): string
    {
        if ($offset < 0 || $length < 0 || $offset + $length > strlen($data)) {
            throw new RuntimeException('Data XLSB terpotong atau rusak.');
        }

        return substr($data, $offset, $length);
    }

    private function part(ZipArchive $zip, string $name): string
    {
        return $zip->getFromName($name) ?: throw new RuntimeException('Bagian workbook tidak ditemukan: '.$name);
    }
}
