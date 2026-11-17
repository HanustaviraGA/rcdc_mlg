<?php

namespace Modules\RoadmapLRC\Database\Seeders;

use App\Services\Lecturers\ProdiFmRoadmap;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * FM count, education level and New FM Recruit (NR) roadmap per study program for 2026–2035, as agreed on
 * 16 November 2026: sheet "JJP" of the workbook "Tambahan data S3B.xlsx" with the corrections made on the
 * Roadmap LRC page, and the NR joining each year. Safe to run again: it only adds missing program years
 * and never overwrites numbers or notes edited on the page.
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

    /** Program code => year => [FM count, S2, S3, NR S2, NR S3], in the row order of the sheet. */
    private const ROADMAP = [
        'EBC' => [2026 => [33, 21, 12, 0, 0], 2027 => [34, 15, 19, 1, 0], 2028 => [35, 15, 20, 1, 0], 2029 => [36, 15, 21, 0, 1], 2030 => [37, 14, 23, 0, 1], 2031 => [38, 12, 26, 0, 1], 2032 => [39, 4, 35, 0, 2], 2033 => [41, 2, 39, 0, 1], 2034 => [41, 0, 41, 0, 2], 2035 => [42, 0, 42, 0, 1]],
        'CS' => [2026 => [21, 17, 4, 0, 0], 2027 => [22, 14, 8, 0, 1], 2028 => [23, 14, 9, 0, 1], 2029 => [24, 14, 10, 0, 1], 2030 => [25, 12, 13, 0, 1], 2031 => [26, 10, 16, 0, 1], 2032 => [27, 6, 21, 0, 1], 2033 => [28, 4, 24, 0, 1], 2034 => [29, 0, 29, 0, 1], 2035 => [30, 0, 30, 0, 1]],
        'DP' => [2026 => [2, 2, 0, 0, 0], 2027 => [2, 2, 0, 0, 0], 2028 => [2, 2, 0, 0, 0], 2029 => [3, 3, 0, 1, 0], 2030 => [3, 3, 0, 0, 0], 2031 => [3, 3, 0, 0, 0], 2032 => [3, 1, 2, 0, 0], 2033 => [4, 2, 2, 1, 0], 2034 => [4, 2, 2, 0, 0], 2035 => [4, 2, 2, 0, 0]],
        'DKV' => [2026 => [20, 18, 2, 0, 0], 2027 => [20, 17, 3, 0, 0], 2028 => [20, 16, 4, 0, 0], 2029 => [20, 16, 4, 0, 0], 2030 => [20, 14, 6, 0, 0], 2031 => [20, 13, 7, 0, 0], 2032 => [20, 7, 13, 0, 0], 2033 => [20, 6, 14, 0, 0], 2034 => [20, 2, 18, 0, 0], 2035 => [20, 1, 19, 0, 0]],
        'DI' => [2026 => [11, 11, 0, 0, 0], 2027 => [11, 10, 1, 0, 0], 2028 => [12, 10, 2, 1, 0], 2029 => [12, 10, 2, 0, 0], 2030 => [12, 10, 2, 0, 0], 2031 => [12, 9, 3, 0, 0], 2032 => [12, 4, 8, 0, 0], 2033 => [12, 3, 9, 0, 0], 2034 => [12, 2, 10, 0, 0], 2035 => [12, 2, 10, 0, 0]],
        'PR' => [2026 => [9, 9, 0, 0, 0], 2027 => [9, 7, 2, 0, 0], 2028 => [9, 7, 2, 0, 0], 2029 => [9, 6, 3, 0, 0], 2030 => [10, 7, 3, 1, 0], 2031 => [10, 6, 4, 0, 0], 2032 => [10, 4, 6, 0, 0], 2033 => [10, 2, 8, 0, 0], 2034 => [10, 1, 9, 0, 0], 2035 => [10, 1, 9, 0, 0]],
        'COMM' => [2026 => [9, 9, 0, 0, 0], 2027 => [9, 7, 2, 0, 0], 2028 => [9, 5, 4, 0, 0], 2029 => [9, 5, 4, 0, 0], 2030 => [10, 5, 5, 1, 0], 2031 => [10, 5, 5, 0, 0], 2032 => [10, 2, 8, 0, 0], 2033 => [10, 1, 9, 0, 0], 2034 => [10, 1, 9, 0, 0], 2035 => [10, 1, 9, 0, 0]],
        'CBDC' => [2026 => [10, 10, 0, 0, 0], 2027 => [10, 9, 1, 0, 0], 2028 => [10, 7, 3, 0, 0], 2029 => [10, 7, 3, 0, 0], 2030 => [10, 6, 4, 0, 0], 2031 => [10, 5, 5, 0, 0], 2032 => [11, 5, 6, 1, 0], 2033 => [11, 4, 7, 0, 0], 2034 => [11, 1, 10, 0, 0], 2035 => [11, 1, 10, 0, 0]],
        'ENG' => [2026 => [2, 2, 0, 0, 0], 2027 => [2, 2, 0, 0, 0], 2028 => [2, 2, 0, 0, 0], 2029 => [2, 2, 0, 0, 0], 2030 => [2, 2, 0, 0, 0], 2031 => [2, 2, 0, 0, 0], 2032 => [2, 2, 0, 0, 0], 2033 => [2, 1, 1, 0, 0], 2034 => [4, 2, 2, 2, 0], 2035 => [4, 2, 2, 0, 0]],
    ];

    public function run(): void
    {
        $now = now();
        $rows = [];
        foreach (array_keys(self::ROADMAP) as $index => $prodi) {
            foreach (self::ROADMAP[$prodi] as $year => [$fm, $s2, $s3, $nrS2, $nrS3]) {
                $rows[] = [
                    'prodi' => $prodi, 'program_studi' => self::PROGRAMS[$prodi], 'urutan' => $index + 1, 'year' => $year,
                    'jumlah_fm' => $fm, 'jumlah_s2' => $s2, 'jumlah_s3' => $s3, 'jumlah_nr_s2' => $nrS2, 'jumlah_nr_s3' => $nrS3,
                    'created_at' => $now, 'updated_at' => $now,
                ];
            }
        }
        DB::table(ProdiFmRoadmap::TABLE)->insertOrIgnore($rows);
    }
}
