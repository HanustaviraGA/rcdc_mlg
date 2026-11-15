<?php

namespace Modules\RoadmapLRC\Database\Seeders;

use App\Services\Lecturers\ProdiFmRoadmap;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * FM count and education level roadmap per study program for 2026–2035, from sheet "JJP" of the
 * workbook "Tambahan data S3B.xlsx". Seven numbers of the sheet are corrected so that S2 + S3 equals
 * the FM count; each keeps the sheet's number in its note. Safe to run again: it only adds missing
 * program years and never overwrites numbers or notes edited on the page.
 */
class ProdiFmRoadmapSeeder extends Seeder
{
    /** Program code on the sheet => program (nama_gugus_binaan) in the Data Center. */
    private const PROGRAMS = [
        'EBC' => 'Entrepreneurship',
        'CS' => 'Computer Science',
        'DP' => 'Psychology',
        'DKV' => 'Visual Communication Design',
        'DI' => 'Interior Design',
        'PR' => 'Public Relations',
        'COMM' => 'Communications',
        'CBDC' => 'Character Building',
        'ENG' => 'English Literature',
    ];

    /** Program code => year => [FM count, S2, S3], in the row order of the sheet. */
    private const ROADMAP = [
        'EBC' => [2026 => [33, 22, 11], 2027 => [34, 16, 18], 2028 => [35, 15, 20], 2029 => [36, 15, 21], 2030 => [37, 14, 23], 2031 => [38, 12, 26], 2032 => [39, 4, 35], 2033 => [40, 2, 38], 2034 => [41, 0, 41], 2035 => [42, 0, 42]],
        'CS' => [2026 => [21, 18, 3], 2027 => [22, 14, 8], 2028 => [23, 14, 9], 2029 => [24, 14, 10], 2030 => [25, 12, 13], 2031 => [26, 10, 16], 2032 => [27, 6, 21], 2033 => [28, 4, 24], 2034 => [29, 0, 29], 2035 => [30, 0, 30]],
        'DP' => [2026 => [2, 2, 0], 2027 => [2, 2, 0], 2028 => [2, 2, 0], 2029 => [3, 3, 0], 2030 => [3, 3, 0], 2031 => [3, 3, 0], 2032 => [3, 1, 2], 2033 => [4, 2, 2], 2034 => [4, 2, 2], 2035 => [4, 2, 2]],
        'DKV' => [2026 => [20, 18, 2], 2027 => [20, 17, 3], 2028 => [20, 16, 4], 2029 => [20, 16, 4], 2030 => [20, 14, 6], 2031 => [20, 13, 7], 2032 => [20, 7, 13], 2033 => [20, 6, 14], 2034 => [20, 2, 18], 2035 => [20, 1, 19]],
        'DI' => [2026 => [11, 11, 0], 2027 => [12, 11, 1], 2028 => [12, 10, 2], 2029 => [12, 10, 2], 2030 => [12, 10, 2], 2031 => [12, 9, 3], 2032 => [12, 4, 8], 2033 => [12, 3, 9], 2034 => [12, 2, 10], 2035 => [12, 2, 10]],
        'PR' => [2026 => [9, 9, 0], 2027 => [9, 7, 2], 2028 => [9, 7, 2], 2029 => [9, 6, 3], 2030 => [10, 7, 3], 2031 => [10, 6, 4], 2032 => [10, 4, 6], 2033 => [10, 2, 8], 2034 => [10, 1, 9], 2035 => [10, 1, 9]],
        'COMM' => [2026 => [9, 9, 0], 2027 => [9, 7, 2], 2028 => [9, 5, 4], 2029 => [9, 5, 4], 2030 => [10, 5, 5], 2031 => [10, 5, 5], 2032 => [10, 2, 8], 2033 => [10, 1, 9], 2034 => [10, 1, 9], 2035 => [10, 1, 9]],
        'CBDC' => [2026 => [10, 10, 0], 2027 => [10, 9, 1], 2028 => [10, 7, 3], 2029 => [10, 7, 3], 2030 => [10, 6, 4], 2031 => [11, 6, 5], 2032 => [11, 5, 6], 2033 => [11, 4, 7], 2034 => [11, 1, 10], 2035 => [11, 1, 10]],
        'ENG' => [2026 => [2, 2, 0], 2027 => [2, 2, 0], 2028 => [2, 2, 0], 2029 => [2, 2, 0], 2030 => [2, 2, 0], 2031 => [2, 2, 0], 2032 => [2, 2, 0], 2033 => [2, 1, 1], 2034 => [2, 0, 2], 2035 => [2, 0, 2]],
    ];

    /**
     * The numbers on the sheet whose S2 + S3 differed from the FM count: program => year => field => sheet number.
     * The FM count is kept and S2 or S3 follows the trend of the neighbouring years.
     */
    private const CORRECTIONS = [
        'CS' => [2026 => ['s3' => 4]],
        'CBDC' => [2031 => ['s2' => 5]],
        'EBC' => [2033 => ['s3' => 39], 2034 => ['s3' => 40], 2035 => ['s3' => 41]],
        'ENG' => [2034 => ['s2' => 2], 2035 => ['s2' => 2]],
    ];

    public function run(): void
    {
        $now = now();
        $rows = [];
        foreach (array_keys(self::ROADMAP) as $index => $prodi) {
            foreach (self::ROADMAP[$prodi] as $year => [$fm, $s2, $s3]) {
                $notes = array_map(fn (int $sheet) => "Sheet JJP: {$sheet}. Disesuaikan agar S2 + S3 sama dengan Jumlah FM ({$fm}).",
                    self::CORRECTIONS[$prodi][$year] ?? []);
                $rows[] = [
                    'prodi' => $prodi, 'program_studi' => self::PROGRAMS[$prodi], 'urutan' => $index + 1, 'year' => $year,
                    'jumlah_fm' => $fm, 'jumlah_s2' => $s2, 'jumlah_s3' => $s3,
                    'catatan_fm' => $notes['fm'] ?? null, 'catatan_s2' => $notes['s2'] ?? null, 'catatan_s3' => $notes['s3'] ?? null,
                    'created_at' => $now, 'updated_at' => $now,
                ];
            }
        }
        DB::table(ProdiFmRoadmap::TABLE)->insertOrIgnore($rows);
    }
}
