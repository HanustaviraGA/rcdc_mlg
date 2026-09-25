<?php

namespace App\Services\Reports;

use App\Services\Publications\PublicationWorkbookMetrics;
use App\Services\Publications\ReportPrograms;
use App\Services\Publications\ReportWorkbookReader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MonthlyPublicationReport
{
    public const FM_HEADERS = ['Kode Dosen', 'Nama Dosen', 'Jurusan Binaan', 'JJA', 'Faculty Type', 'Non Scopus', 'Scopus', 'Jumlah First Author', 'Score KPI', 'Scopus First Author'];

    public function generate(int $year, int $month): array
    {
        $warnings = [];
        $fm = $this->batch('publication_imports', $year, $month);
        $mhs = $this->batch('student_publication_imports', $year, $month);
        $hibah = Schema::hasColumn('research_imports', 'month') ? $this->batch('research_imports', $year, $month) : null;
        foreach (['FM' => $fm, 'MHS' => $mhs, 'hibah' => $hibah] as $label => $batch) {
            if (! $batch) {
                $warnings[] = "File {$label} untuk {$month}/{$year} belum diupload.";
            }
        }
        $fmSummary = json_decode($fm->summary ?? '{}', true);
        $mhsSummary = json_decode($mhs->summary ?? '{}', true);
        $warnings = array_merge($warnings, $fmSummary['warnings'] ?? [], $mhsSummary['warnings'] ?? []);
        $papers = $fm ? DB::table('rectorate_dosen')->where('publication_import_id', $fm->id)->get() : collect();
        $entries = $fm && Schema::hasTable('publication_kpi_entries') ? DB::table('publication_kpi_entries')->where('publication_import_id', $fm->id)->get()->keyBy('kode_dosen') : collect();
        $master = Schema::hasTable('database_dosen_new') ? DB::table('database_dosen_new')->get()->keyBy('kode_dosen') : collect();
        $old = Schema::hasTable('database_dosen') ? DB::table('database_dosen')->get()->keyBy('kode_dosen') : collect();
        $codes = ($master->isNotEmpty() ? $master : $old)->keys()->merge($entries->keys())->merge($papers->pluck('kode_dosen'))->unique();
        $faculty = [];
        foreach ($codes as $code) {
            $profile = $master->get($code);
            $legacy = $old->get($code);
            $entry = $entries->get($code);
            $rows = $papers->where('kode_dosen', $code);
            $metrics = PublicationWorkbookMetrics::summarize($rows, $entry);
            $program = ReportPrograms::code($entry->program ?? $profile->nama_gugus_binaan ?? $legacy->jurusan_dosen ?? $rows->first()->prodi_kpi ?? $rows->first()->dept ?? 'Lainnya');
            $faculty[$program][] = [$code, $entry->name ?? $profile->nama_dosen ?? $legacy->nama_dosen ?? $rows->first()->fm_author ?? $code,
                $program, $entry->academic_rank ?? $profile->jja ?? $legacy->jja_dosen ?? '',
                $entry->faculty_type ?? $profile->tipe_faculty ?? $legacy->ft_dosen ?? '',
                $fm ? self::number($metrics['non_scopus']) : '', $fm ? self::number($metrics['scopus']) : '',
                $fm ? (string) $metrics['first_author'] : '', $metrics['score'] === null ? '' : (string) $metrics['score'],
                $fm ? ($metrics['first_author'] > 0 ? 'Sudah' : 'Belum') : ''];
        }
        $list = json_decode($mhs->publication_list ?? '[]', true);
        $students = [];
        foreach ($list as $row) {
            $students[ReportPrograms::code($row['program'])][] = [$row['name'], $row['title']];
        }
        $fmPrograms = ['BC', 'CS', 'DKV', 'DI', 'ILKOM', 'PR', 'CBDC', 'LC', 'PSY'];
        $mhsPrograms = ['BC', 'CS', 'ILKOM', 'PR', 'DKV', 'DI'];
        $summaries = [];
        foreach (['fm' => $fmSummary, 'mhs' => $mhsSummary] as $kind => $summary) {
            $rows = [];
            $metadata = $summary['report'] ?? [];
            if (! ($metadata['realization'] ?? [])) {
                $warnings[] = strtoupper($kind).': target/rentang skor belum tersedia. Upload ulang workbook yang memiliki Realization Template dan Scoring PI atau isi manual.';
            }
            foreach (['BINUS', 'BC', 'CS', 'DKV', 'DI', 'ILKOM', 'PR', ...($kind === 'fm' ? ['CBDC', 'LC'] : [])] as $code) {
                $target = $metadata['realization'][$code]['target'] ?? null;
                if ($kind === 'mhs') {
                    $value = $mhs ? ($summary['title_counts'][$code] ?? $summary['list_counts'][$code] ?? 0) : null;
                } else {
                    $value = $metadata['realization'][$code]['realization'] ?? ($fm ? $papers->filter(fn ($r) => PublicationWorkbookMetrics::isScopusTitle($r)
                        && ($code === 'BINUS' || ReportPrograms::code($r->prodi_kpi ?? $r->dept) === $code))->sum('bobot_asli') : null);
                }
                $score = ReportWorkbookReader::score($value, $metadata['ranges'][$code] ?? []);
                $rows[] = [$code === 'BINUS' ? 'Binus @ Malang' : $code, self::number($target), self::number($value), $score === null ? '' : (string) $score];
            }
            $summaries[] = ['id' => $kind.'_summary', 'label' => 'Scopus '.strtoupper($kind),
                'headers' => ['Prodi', 'Target', 'Realization', 'Score'], 'rows' => $rows];
        }
        $tables = $summaries;
        foreach (['fm' => array_unique(array_merge($fmPrograms, array_keys($faculty))), 'mhs' => array_unique(array_merge($mhsPrograms, array_keys($students)))] as $kind => $programs) {
            foreach ($programs as $program) {
                $rows = $kind === 'fm' ? ($faculty[$program] ?? []) : ($students[$program] ?? []);
                usort($rows, fn ($a, $b) => strcasecmp($a[$kind === 'fm' ? 1 : 0], $b[$kind === 'fm' ? 1 : 0]));
                $tables[] = ['id' => $kind.'_'.$program, 'label' => strtoupper($kind).' — '.(ReportPrograms::LABELS[$program] ?? $program),
                    'headers' => $kind === 'fm' ? self::FM_HEADERS : ['MHS Author', 'Title'], 'rows' => $rows];
            }
        }
        if ($entries->isEmpty()) {
            $warnings[] = 'Skor KPI dosen kosong karena sheet KPI belum tersedia untuk periode ini; isi manual bila sudah memiliki skor yang disahkan.';
        }

        return ['year' => $year, 'month' => $month, 'title' => 'LAPORAN PENCAPAIAN PUBLIKASI SCOPUS FM DAN MAHASISWA',
            'period_label' => $year.' ('.\Carbon\Carbon::create($year, $month, 1)->locale('id')->translatedFormat('F').')',
            'warnings' => array_values(array_unique($warnings)), 'sources' => ['FM' => $fm->filename ?? null, 'MHS' => $mhs->filename ?? null, 'Hibah' => $hibah->filename ?? null],
            'tables' => $tables];
    }

    private function batch(string $table, int $year, int $month): ?object
    {
        return Schema::hasTable($table) ? DB::table($table)->where(compact('year', 'month'))->first() : null;
    }

    private static function number(?float $value): string
    {
        return $value === null ? '' : number_format($value, 2, ',', '');
    }
}
