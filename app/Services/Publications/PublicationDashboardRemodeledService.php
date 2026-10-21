<?php

namespace App\Services\Publications;

use Illuminate\Support\Collection;

/**
 * Service untuk transform data PublicationDashboard ke structure HTML baru
 * (performance_dashboard_indikator_alokasi_budget_vrtt.html)
 * 
 * Format struktur:
 * {
 *   years: { 
 *     "2026": {
 *       publication: {
 *         summary: [title1, title2, title3, title4, milestone_target, milestone_actual, fm_target, fm_actual, ...],
 *         titles: ["MILESTONE", "PUBLIKASI SCOPUS FM", ...],
 *         cards: [...],
 *         facultyByDept: { EBC: [[code, nama, ...]], ... },
 *         publicationsByDept: { EBC: [[...], ...], ... }
 *       }
 *     }
 *   }
 * }
 */
class PublicationDashboardRemodeledService
{
    /**
     * Transform data dari PublicationDashboard ke struktur HTML
     */
    public function transform(array $dashboardData): array
    {
        $years = $dashboardData['years'] ?? [];
        $faculty = collect($dashboardData['faculty'] ?? []);
        $publications = collect($dashboardData['publications'] ?? []);
        $grants = collect($dashboardData['grants'] ?? []);

        $remodeled = [
            'years' => [],
            'departments' => [],
            'indicators' => ['publication', 'vrtt', 'research', 'citation', 'ip', 'ntr'],
        ];

        // Build per-year data
        foreach ($years as $year) {
            $remodeled['years'][$year] = $this->buildYearData(
                (string) $year,
                $faculty,
                $publications,
                $dashboardData
            );
        }

        // Build department list (prodi unique)
        $depts = $faculty
            ->map(fn ($f) => $this->getProdiCode($f['program']))
            ->unique()
            ->sort()
            ->values()
            ->all();

        $remodeled['departments'] = $depts;

        return $remodeled;
    }

    /**
     * Build struktur data untuk satu tahun
     */
    private function buildYearData(
        string $year,
        Collection $faculty,
        Collection $publications,
        array $dashboardData
    ): array {
        $yearInt = (int) $year;
        $yearFaculty = $faculty->filter(fn ($f) => isset($f['annual'][$yearInt]));
        $yearPublications = $publications;

        return [
            'publication' => [
                'summary' => $this->buildSummary($yearFaculty, $yearPublications),
                'titles' => [
                    'MILESTONE',
                    'PUBLIKASI SCOPUS FM',
                    'PUBLIKASI SCOPUS MHS',
                    '% FM HAVE SCOPUS',
                ],
                'cards' => $this->buildCards($yearFaculty, $yearPublications),
                'facultyByDept' => $this->buildFacultyByDept($yearFaculty),
                'publicationsByDept' => $this->buildPublicationsByDept($yearPublications, $yearFaculty),
            ],
        ];
    }

    /**
     * Build array ringkasan (7 values: title1-4, milestone_target, milestone_actual, ...)
     */
    private function buildSummary(Collection $faculty, Collection $publications): array
    {
        // Default ringkasan
        return [
            'MILESTONE',                     // [0] title 1
            'PUBLIKASI SCOPUS FM',          // [1] title 2
            'PUBLIKASI SCOPUS MHS',         // [2] title 3
            '% FM HAVE SCOPUS',             // [3] title 4
            '',                              // [4] milestone_target
            '',                              // [5] milestone_actual
            '',                              // [6] fm_target
            '',                              // [7] fm_actual
            '',                              // [8] mhs_target
            '',                              // [9] mhs_actual
            '',                              // [10] percentage
        ];
    }

    /**
     * Build cards (grafik per prodi/dept)
     */
    private function buildCards(Collection $faculty, Collection $publications): array
    {
        $cards = [];
        $depts = $faculty
            ->map(fn ($f) => $this->getProdiCode($f['program']))
            ->unique()
            ->sort()
            ->values();

        foreach ($depts as $dept) {
            $deptFaculty = $faculty->filter(fn ($f) => $this->getProdiCode($f['program']) === $dept);
            
            $cards[] = [
                'id' => 'card-' . strtolower($dept),
                'dept' => $dept,
                'displayName' => $this->getDeptLabel($dept),
                'group' => 'FM',
                'quarter' => null,
                'score' => '',
                'fmCount' => 'Jumlah FM: ' . $deptFaculty->count() . ' FM',
                'target' => 0,
                'actual' => 0,
                'fmPercent' => 0,
                'fmScopusCount' => 0,
                'fmTotal' => $deptFaculty->count(),
                'papers' => 0,
                'journals' => 0,
                'conferences' => 0,
                'bookChapter' => 0,
                'fullBook' => 0,
                'paperTitles' => 0,
                'journalTitles' => 0,
                'conferenceTitles' => 0,
                'chapterTitles' => 0,
                'bookTitles' => 0,
            ];
        }

        return $cards;
    }

    /**
     * Build faculty data grouped by department
     * Format: [[code, nama, prodi, jja, faculty_type, non_scopus, scopus, first_author, score_kpi], ...]
     */
    private function buildFacultyByDept(Collection $faculty): array
    {
        $result = [];

        foreach ($faculty as $f) {
            $dept = $this->getProdiCode($f['program']);
            if (!isset($result[$dept])) {
                $result[$dept] = [];
            }

            $result[$dept][] = [
                $f['code'],                                      // [0] Kode Dosen
                $f['name'],                                      // [1] Nama Dosen
                $f['program'],                                   // [2] Jurusan Binaan
                $f['rank'] ?? '',                                // [3] JJA
                $f['faculty'] ?? '',                             // [4] Faculty Type
                0,                                               // [5] Non Scopus
                0,                                               // [6] Scopus
                0,                                               // [7] Jumlah First Author
                '',                                              // [8] Score KPI
            ];
        }

        return $result;
    }

    /**
     * Build publications grouped by department
     */
    private function buildPublicationsByDept(Collection $publications, Collection $faculty): array
    {
        $result = [];

        foreach ($publications as $pub) {
            $code = $pub['request_code'] ?? '';
            
            // Find faculty by code
            $faculty_record = $faculty->firstWhere('code', $pub['kode_dosen'] ?? '');
            if (!$faculty_record) {
                continue;
            }

            $dept = $this->getProdiCode($faculty_record['program']);
            if (!isset($result[$dept])) {
                $result[$dept] = [];
            }

            // Build publication row
            $result[$dept][] = [
                $pub['author'] ?? '',              // [0] Author
                $pub['fm_author'] ?? '',           // [1] FM Author
                $pub['kode_dosen'] ?? '',          // [2] Kode Dosen
                $pub['s_f'] ?? '',                 // [3] S/F
                $pub['dept'] ?? '',                // [4] Dept
                $pub['kampus'] ?? '',              // [5] Kampus
                $pub['first_author'] ?? '',        // [6] First Author
                $pub['sumber_paper'] ?? '',        // [7] Sumber paper
                $pub['bobot'] ?? 0,                // [8] Bobot
                $pub['submitted'] ?? '',           // [9] Submitted
                '',                                // [10] St2024
                $pub['jenis'] ?? '',               // [11] Jenis
                $pub['tipe_publikasi'] ?? '',      // [12] Tipe Publikasi
                $pub['title'] ?? '',               // [13] Title
                $pub['year'] ?? '',                // [14] Scopus Year
                '',                                // [15] Tanggal Pelaporan
                $pub['source_title'] ?? '',        // [16] Source title
                $pub['quartile_jurnal'] ?? '',     // [17] Quartile Jurnal
                '',                                // [18] Prodi MHS
            ];
        }

        return $result;
    }

    /**
     * Get prodi code dari nama
     */
    private function getProdiCode(string $programName): string
    {
        $codes = [
            'Computer Science' => 'CS',
            'Visual Communication Design' => 'DKV',
            'Interior Design' => 'DI',
            'Business Creation' => 'BC',
            'Communication' => 'ILKOM',
            'Public Relation' => 'PR',
            'Psychology' => 'PSY',
            'Character Building Development Center' => 'CBDC',
            'Language Center' => 'LC',
        ];

        foreach ($codes as $name => $code) {
            if (str_contains(strtolower($programName), strtolower($name))) {
                return $code;
            }
        }

        // Fallback: use ReportPrograms service
        return \App\Services\Publications\ReportPrograms::code($programName);
    }

    /**
     * Get label untuk kode prodi
     */
    private function getDeptLabel(string $code): string
    {
        $labels = \App\Services\Publications\ReportPrograms::LABELS;
        return $labels[$code] ?? $code;
    }
}
