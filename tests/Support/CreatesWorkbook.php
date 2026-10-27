<?php

namespace Tests\Support;

use ZipArchive;

trait CreatesWorkbook
{
    private array $workbooks = [];

    private function makeWorkbook(array $sheets): string
    {
        $file = tempnam(sys_get_temp_dir(), 'monthly-workbook-');
        $this->workbooks[] = $file;
        $zip = new ZipArchive;
        $zip->open($file, ZipArchive::OVERWRITE);
        $ns = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
        $rels = 'http://schemas.openxmlformats.org/package/2006/relationships';
        $doc = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
        $zip->addFromString('[Content_Types].xml', '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<Relationships xmlns="'.$rels.'"><Relationship Id="rId1" Type="'.$doc.'/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $book = '<workbook xmlns="'.$ns.'" xmlns:r="'.$doc.'"><sheets>';
        $links = '<Relationships xmlns="'.$rels.'">';
        $index = 0;
        foreach ($sheets as $name => $rows) {
            $index++;
            $book .= '<sheet name="'.htmlspecialchars($name, ENT_XML1).'" sheetId="'.$index.'" r:id="rId'.$index.'"/>';
            $links .= '<Relationship Id="rId'.$index.'" Type="'.$doc.'/worksheet" Target="worksheets/sheet'.$index.'.xml"/>';
            $xml = '<worksheet xmlns="'.$ns.'"><sheetData>';
            foreach ($rows as $r => $cells) {
                $xml .= '<row r="'.($r + 1).'">';
                foreach ($cells as $c => $value) {
                    $column = '';
                    for ($n = $c + 1; $n > 0; $n = intdiv($n - 1, 26)) {
                        $column = chr(65 + ($n - 1) % 26).$column;
                    }
                    $xml .= '<c r="'.$column.($r + 1).'" t="inlineStr"><is><t>'.htmlspecialchars((string) $value, ENT_XML1).'</t></is></c>';
                }
                $xml .= '</row>';
            }
            $zip->addFromString('xl/worksheets/sheet'.$index.'.xml', $xml.'</sheetData></worksheet>');
        }
        $zip->addFromString('xl/workbook.xml', $book.'</sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', $links.'</Relationships>');
        $zip->close();

        return $file;
    }

    private function removeWorkbooks(): void
    {
        foreach ($this->workbooks as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    /** Build a small BIFF12 workbook without using the production reader. */
    private function makeXlsbWorkbook(array $sheets): string
    {
        $file = tempnam(sys_get_temp_dir(), 'binary-workbook-');
        $this->workbooks[] = $file;
        $zip = new ZipArchive;
        $zip->open($file, ZipArchive::OVERWRITE);
        $record = static function (int $type, string $data = ''): string {
            $header = $type < 128 ? chr($type) : chr(($type & 127) | 128).chr($type >> 7);
            $length = strlen($data);
            do {
                $header .= chr(($length & 127) | ($length >= 128 ? 128 : 0));
                $length >>= 7;
            } while ($length > 0);

            return $header.$data;
        };
        $wide = static function (string $value): string {
            $encoded = mb_convert_encoding($value, 'UTF-16LE', 'UTF-8');

            return pack('V', strlen($encoded) / 2).$encoded;
        };
        $book = $record(131).$record(143);
        $links = '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        $strings = [];
        $index = 0;
        foreach ($sheets as $name => $rows) {
            $index++;
            $book .= $record(156, pack('V2', 0, $index).$wide('rId'.$index).$wide($name));
            $links .= '<Relationship Id="rId'.$index.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet'.$index.'.bin"/>';
            $binary = $record(129).$record(145);
            foreach ($rows as $r => $cells) {
                $binary .= $record(0, pack('V', $r).str_repeat("\0", 13));
                foreach ($cells as $c => $value) {
                    $cell = pack('V2', $c, 0);
                    if (is_array($value)) {
                        $binary .= $record($value['type'], $cell.$value['payload']);
                    } elseif (is_int($value) || is_float($value)) {
                        $binary .= $record(5, $cell.pack('e', $value));
                    } else {
                        $strings[] = (string) $value;
                        $binary .= $record(7, $cell.pack('V', count($strings) - 1));
                    }
                }
            }
            $zip->addFromString('xl/worksheets/sheet'.$index.'.bin', $binary.$record(146).$record(130));
        }
        $zip->addFromString('xl/workbook.bin', $book.$record(144).$record(132));
        $zip->addFromString('xl/_rels/workbook.bin.rels', $links.'</Relationships>');
        $shared = $record(159, pack('V2', count($strings), count($strings)));
        foreach ($strings as $string) {
            $shared .= $record(19, "\0".$wide($string));
        }
        $zip->addFromString('xl/sharedStrings.bin', $shared.$record(160));
        $zip->close();

        return $file;
    }
}
