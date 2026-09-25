<?php

namespace App\Services\Reports;

use App\Services\Publications\ReportWorkbookReader;
use DOMDocument;
use DOMElement;
use DOMXPath;
use RuntimeException;
use ZipArchive;

class PublicationDocx
{
    public const TABLE_IDS = ['fm_summary', 'mhs_summary', 'fm_BC', 'fm_CS', 'fm_DKV', 'fm_DI', 'fm_ILKOM', 'fm_PR', 'fm_CBDC', 'fm_LC', 'fm_PSY', 'mhs_BC', 'mhs_CS', 'mhs_ILKOM', 'mhs_PR', 'mhs_DKV', 'mhs_DI'];

    private const W = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    public function render(array $report): string
    {
        $path = tempnam(sys_get_temp_dir(), 'publication-report-');
        try {
            if (! copy(resource_path('reports/publication-template.docx'), $path)) {
                throw new RuntimeException('Template laporan tidak tersedia.');
            }
            $zip = new ZipArchive;
            if ($zip->open($path) !== true) {
                throw new RuntimeException('Template Word tidak dapat dibuka.');
            }
            $doc = new DOMDocument;
            $doc->loadXML($zip->getFromName('word/document.xml'), LIBXML_NONET);
            $xp = new DOMXPath($doc);
            $xp->registerNamespace('w', self::W);
            $tables = iterator_to_array($xp->query('/w:document/w:body/w:tbl'));
            $data = collect($report['tables'])->keyBy('id');
            $fmPrototype = $tables[2]->cloneNode(true);
            $mhsPrototype = $tables[11]->cloneNode(true);
            foreach (self::TABLE_IDS as $index => $id) {
                $this->fillTable($tables[$index], $data->get($id)['rows'] ?? [], $xp);
            }
            $body = $xp->query('/w:document/w:body')->item(0);
            $section = $xp->query('./w:sectPr', $body)->item(0);
            $facultyEnd = $tables[10]->nextSibling;
            foreach ($data->except(self::TABLE_IDS) as $table) {
                $isFaculty = str_starts_with($table['id'], 'fm_');
                $anchor = $isFaculty ? $facultyEnd : $section;
                $heading = $doc->createElementNS(self::W, 'w:p');
                $run = $doc->createElementNS(self::W, 'w:r');
                $text = $doc->createElementNS(self::W, 'w:t');
                $text->appendChild($doc->createTextNode($table['label']));
                $run->appendChild($text);
                $heading->appendChild($run);
                $body->insertBefore($heading, $anchor);
                $node = ($isFaculty ? $fmPrototype : $mhsPrototype)->cloneNode(true);
                $this->fillTable($node, $table['rows'], $xp);
                $body->insertBefore($node, $anchor);
            }
            foreach ($xp->query('/w:document/w:body/w:p') as $paragraph) {
                $text = $paragraph->textContent;
                if (str_contains($text, '{{REPORT_TITLE}}')) {
                    $this->setText($paragraph, $report['title'], $xp);
                }
                if (str_contains($text, '{{REPORT_PERIOD}}')) {
                    $this->setText($paragraph, $report['period_label'], $xp);
                }
            }
            $zip->addFromString('word/document.xml', $doc->saveXML());
            // The supplied Word document places MHS in chart1 and FM in chart2.
            $zip->addFromString('word/charts/chart1.xml', $this->chart($data['mhs_summary']['rows']));
            $zip->addFromString('word/charts/chart2.xml', $this->chart($data['fm_summary']['rows']));
            $zip->close();

            return $path;
        } catch (\Throwable $e) {
            @unlink($path);
            throw $e;
        }
    }

    private function fillTable(DOMElement $table, array $rows, DOMXPath $xp): void
    {
        $original = iterator_to_array($xp->query('./w:tr', $table));
        $prototype = ($original[1] ?? $original[0])->cloneNode(true);
        foreach (array_slice($original, 1) as $row) {
            $table->removeChild($row);
        }
        foreach ($rows as $values) {
            $row = $prototype->cloneNode(true);
            foreach ($xp->query('./w:tc', $row) as $i => $cell) {
                $this->setText($cell, (string) ($values[$i] ?? ''), $xp);
            }
            $table->appendChild($row);
        }
    }

    private function setText(DOMElement $node, string $value, DOMXPath $xp): void
    {
        $texts = iterator_to_array($xp->query('.//w:t', $node));
        if ($texts) {
            $texts[0]->nodeValue = '';
            $texts[0]->appendChild($node->ownerDocument->createTextNode($value));
            foreach (array_slice($texts, 1) as $text) {
                $text->parentNode->removeChild($text);
            }
        } else {
            $p = $xp->query('.//w:p', $node)->item(0) ?? $node;
            $run = $node->ownerDocument->createElementNS(self::W, 'w:r');
            $text = $node->ownerDocument->createElementNS(self::W, 'w:t');
            $text->appendChild($node->ownerDocument->createTextNode($value));
            $run->appendChild($text);
            $p->appendChild($run);
        }
    }

    public function chart(array $rows): string
    {
        $rows = array_values(array_filter($rows, fn ($r) => ($target = ReportWorkbookReader::number($r[1])) !== null && $target > 0 && ReportWorkbookReader::number($r[2]) !== null));
        $escape = fn ($v) => htmlspecialchars((string) $v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $categories = '<c:strLit><c:ptCount val="'.count($rows).'"/>';
        foreach ($rows as $i => $row) {
            $categories .= '<c:pt idx="'.$i.'"><c:v>'.$escape($row[0]).'</c:v></c:pt>';
        }
        $categories .= '</c:strLit>';
        $series = '';
        $hideAutoLabels = '<c:showLegendKey val="0"/><c:showVal val="0"/><c:showCatName val="0"/><c:showSerName val="0"/><c:showPercent val="0"/><c:showBubbleSize val="0"/>';
        foreach (['Realisasi' => '4472C4', 'Sisa target' => 'ED7D31'] as $name => $color) {
            $index = $name === 'Realisasi' ? 0 : 1;
            $values = '<c:numLit><c:formatCode>0.00</c:formatCode><c:ptCount val="'.count($rows).'"/>';
            $labels = '';
            foreach ($rows as $i => $row) {
                $target = ReportWorkbookReader::number($row[1]);
                $real = ReportWorkbookReader::number($row[2]);
                $value = $index === 0 ? $real : max(0, $target - $real);
                $values .= '<c:pt idx="'.$i.'"><c:v>'.$value.'</c:v></c:pt>';
                if ($index === 0) {
                    $labels .= '<c:dLbl><c:idx val="'.$i.'"/><c:tx><c:rich><a:bodyPr/><a:lstStyle/><a:p><a:r><a:rPr sz="800"/><a:t>'.$escape(number_format($real / $target * 100, 1, ',', '').'%').'</a:t></a:r></a:p></c:rich></c:tx><c:dLblPos val="ctr"/>'.$hideAutoLabels.'</c:dLbl>';
                }
            }
            $values .= '</c:numLit>';
            $series .= '<c:ser><c:idx val="'.$index.'"/><c:order val="'.$index.'"/><c:tx><c:v>'.$name.'</c:v></c:tx><c:spPr><a:solidFill><a:srgbClr val="'.$color.'"/></a:solidFill></c:spPr><c:dLbls>'.$labels.$hideAutoLabels.'</c:dLbls><c:cat>'.$categories.'</c:cat><c:val>'.$values.'</c:val></c:ser>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><c:chartSpace xmlns:c="http://schemas.openxmlformats.org/drawingml/2006/chart" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"><c:chart><c:autoTitleDeleted val="1"/><c:plotArea><c:layout/><c:barChart><c:barDir val="col"/><c:grouping val="percentStacked"/>'.$series.'<c:gapWidth val="65"/><c:overlap val="100"/><c:axId val="1"/><c:axId val="2"/></c:barChart><c:catAx><c:axId val="1"/><c:scaling><c:orientation val="minMax"/></c:scaling><c:delete val="0"/><c:axPos val="b"/><c:tickLblPos val="nextTo"/><c:crossAx val="2"/><c:crosses val="autoZero"/></c:catAx><c:valAx><c:axId val="2"/><c:scaling><c:orientation val="minMax"/><c:max val="1"/><c:min val="0"/></c:scaling><c:delete val="0"/><c:axPos val="l"/><c:numFmt formatCode="0%" sourceLinked="0"/><c:tickLblPos val="nextTo"/><c:crossAx val="1"/><c:crosses val="autoZero"/></c:valAx></c:plotArea><c:legend><c:legendPos val="b"/><c:overlay val="0"/></c:legend><c:plotVisOnly val="1"/></c:chart></c:chartSpace>';
    }
}
