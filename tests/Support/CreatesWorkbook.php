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
}
