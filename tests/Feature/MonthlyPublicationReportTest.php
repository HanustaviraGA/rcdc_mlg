<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Reports\MonthlyPublicationReport;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use ZipArchive;

class MonthlyPublicationReportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Run PHP with -d extension=pdo_sqlite.');
        }
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Schema::create('publication_imports', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('month');
            $table->string('filename');
            $table->json('summary');
        });
        Schema::create('rectorate_dosen', function (Blueprint $table) {
            $table->id();
            $table->integer('publication_import_id');
            foreach (['kode_dosen', 'fm_author', 'prodi_kpi', 'dept', 'tipe_publikasi', 'submitted', 'first_author'] as $field) {
                $table->string($field);
            }
            $table->double('bobot_asli');
        });
        Schema::create('publication_kpi_entries', function (Blueprint $table) {
            $table->integer('publication_import_id');
            foreach (['kode_dosen', 'name', 'program', 'academic_rank', 'faculty_type'] as $field) {
                $table->string($field);
            }
            $table->double('rtto_scopus');
            $table->double('rtto_non_scopus');
            $table->integer('rtto_score');
        });
        DB::table('publication_imports')->insert([
            ['id' => 1, 'year' => 2026, 'month' => 8, 'filename' => 'august.xlsx', 'summary' => '{}'],
            ['id' => 2, 'year' => 2026, 'month' => 9, 'filename' => 'september.xlsx', 'summary' => '{}'],
        ]);
        foreach ([1 => 0.5, 2 => 2.5] as $batch => $weight) {
            DB::table('rectorate_dosen')->insert(['publication_import_id' => $batch, 'kode_dosen' => 'D001', 'fm_author' => 'Test Lecturer',
                'prodi_kpi' => 'CS', 'dept' => 'CS', 'tipe_publikasi' => 'Scopus', 'submitted' => 'Scopus FM', 'first_author' => 'Y', 'bobot_asli' => $weight]);
        }
        DB::table('publication_kpi_entries')->insert(['publication_import_id' => 1, 'kode_dosen' => 'D001', 'name' => 'Test Lecturer',
            'program' => 'CS', 'academic_rank' => 'L200', 'faculty_type' => 'Functional', 'rtto_scopus' => 1.5, 'rtto_non_scopus' => 0, 'rtto_score' => 4]);
    }

    public function test_generation_uses_exact_month_and_workbook_score(): void
    {
        $report = app(MonthlyPublicationReport::class)->generate(2026, 8);
        $rows = collect($report['tables'])->keyBy('id')['fm_CS']['rows'];
        $this->assertSame('1,50', $rows[0][6]);
        $this->assertSame('4', $rows[0][8]);
        $this->assertSame('1', $rows[0][7]);
        $this->assertSame('august.xlsx', $report['sources']['FM']);
        $this->assertNull($report['sources']['MHS']);
    }

    public function test_generate_edit_download_preserves_database_and_updates_word_charts(): void
    {
        $this->actingAs((new User)->forceFill(['id' => 1, 'name' => 'Editor']));
        $draft = $this->postJson(route('exportreport.generate'), ['year' => 2026, 'month' => 8])->assertOk()->json();
        $tables = $draft['report']['tables'];
        foreach ($tables as &$table) {
            if ($table['id'] === 'fm_summary') {
                $table['rows'][0] = ['Edited Campus', '100', '125', '6'];
            }
            if ($table['id'] === 'fm_CS') {
                $table['rows'][0][1] = 'Edited <Name> & Co';
            }
        }
        unset($table);
        $tables[] = ['id' => 'fm_Additional', 'label' => 'FM additional program', 'headers' => MonthlyPublicationReport::FM_HEADERS,
            'rows' => [['D99', 'Additional Lecturer', 'Additional', 'TP', 'Functional', '0', '0', '0', '', 'Belum']]];
        // Extra programs are normally produced by Generate from master data.
        $drafts = session('publication_report_drafts');
        $drafts[$draft['id']]['report']['tables'][] = end($tables);
        session(['publication_report_drafts' => $drafts]);
        $payload = ['id' => $draft['id'], 'title' => 'Edited report', 'period_label' => '2026 (Agustus)', 'tables' => $tables];
        $this->postJson(route('exportreport.save'), $payload)->assertOk();
        $response = $this->postJson(route('exportreport.download'), $payload)->assertOk();
        $path = $response->baseResponse->getFile()->getPathname();
        try {
            $zip = new ZipArchive;
            $this->assertTrue($zip->open($path));
            $xml = $zip->getFromName('word/document.xml');
            $this->assertStringContainsString('Edited &lt;Name&gt; &amp; Co', $xml);
            $this->assertStringContainsString('Edited report', $xml);
            $this->assertLessThan(strpos($xml, 'SCOPUS MAHASISWA'), strpos($xml, 'Additional Lecturer'));
            $this->assertStringNotContainsString('{{REPORT_', $xml);
            $this->assertStringContainsString('125,0%', $zip->getFromName('word/charts/chart2.xml'));
            $this->assertStringNotContainsString('externalData', $zip->getFromName('word/charts/chart2.xml'));
            for ($i = 0; $i < $zip->numFiles; $i++) {
                if (str_ends_with($zip->getNameIndex($i), '.xml')) {
                    $this->assertTrue((new \DOMDocument)->loadXML($zip->getFromIndex($i), LIBXML_NONET), $zip->getNameIndex($i));
                }
            }
            $zip->close();
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
        $this->assertSame('Test Lecturer', DB::table('rectorate_dosen')->value('fm_author'));
    }

    public function test_guests_invalid_cells_and_another_users_draft_are_rejected(): void
    {
        $this->postJson(route('exportreport.generate'), ['year' => 2026, 'month' => 8])->assertForbidden();
        $this->actingAs((new User)->forceFill(['id' => 1, 'name' => 'Editor']));
        $draft = $this->postJson(route('exportreport.generate'), ['year' => 2026, 'month' => 8])->json();
        $payload = ['id' => $draft['id'], 'title' => 'Title', 'period_label' => 'Period', 'tables' => $draft['report']['tables']];
        $payload['tables'][0]['rows'][0][2] = 'invalid';
        $this->postJson(route('exportreport.save'), $payload)->assertUnprocessable();
        $this->actingAs((new User)->forceFill(['id' => 2, 'name' => 'Another editor']));
        $this->postJson(route('exportreport.save'), $payload)->assertNotFound();
    }
}
