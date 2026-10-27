<?php

namespace App\Services\CommunityDevelopment;

class CommunityDevelopmentDemo
{
    /**
     * Sample data from dashboard_community_development_fm.html.
     * Lecturer examples shown before the first PKM recap import.
     */
    public function data(): array
    {
        $lecturers = [
            'ebc' => [
                ['name' => 'Dr. Kukuh Lukiyanto, S.T., M.M., M.T.', 'activities' => 3, 'score' => 6],
                ['name' => 'Yoseph Benny Kusuma, S.M., M.SM.', 'activities' => 2, 'score' => 6],
                ['name' => 'Etsa Astridya Setiyati, S.E., M.Comm.', 'activities' => 1, 'score' => 5],
            ],
            'cs' => [
                ['name' => 'Dr. Choirul Huda, S.Kom., M.M.', 'activities' => 2, 'score' => 6],
                ['name' => 'Alvin Chandra, S.Kom., M.M.', 'activities' => 1, 'score' => 5],
                ['name' => 'Dr. Anita Rahayu, S.Si., M.Si.', 'activities' => 4, 'score' => 6],
            ],
            'pr' => [
                ['name' => 'Galuh Ayu Savitri, S.I.Kom., M.I.Kom.', 'activities' => 4, 'score' => 6],
                ['name' => 'Nisrin Husna, S.I.Kom., M.I.Kom.', 'activities' => 1, 'score' => 5],
            ],
            'ilkom' => [['name' => 'Windaningsih, S.Sos., M.I.Kom', 'activities' => 1, 'score' => 5]],
            'di' => [['name' => 'Ira Audia Agustina, S.T., M.Ds.', 'activities' => 1, 'score' => 5]],
            'dkv' => [['name' => 'Hindam Basith Rafiqi, S.Sn., M.Sn.', 'activities' => 5, 'score' => 6]],
            'cbdc' => [['name' => 'Romanus Piter, S.Fil., M.Fil.', 'activities' => 3, 'score' => 6]],
            'dllc' => [['name' => 'Asih Zunaidah, S.S., M.LI.', 'activities' => 2, 'score' => 6]],
            'psychology' => [['name' => 'Natasha Gandhi, S.Psi., M.Psi.', 'activities' => 3, 'score' => 6]],
        ];
        $programs = [];
        foreach ([
            ['ebc', 'Prodi EBC', 'Entrepreneurship', 30, 60],
            ['cs', 'Prodi CS', 'Computer Science', 21, 37],
            ['pr', 'Prodi PR', 'Public Relations', 8, 14],
            ['ilkom', 'Prodi Digicomm', 'Digital Communication', 8, 10],
            ['di', 'Prodi DI', 'Interior Design · DI', 10, 18],
            ['dkv', 'Prodi DKV', 'Visual Communication Design', 19, 36],
            ['cbdc', 'CBDC', 'Character Building', 10, 20],
            ['dllc', 'DLLC', 'Language Center', 2, 3],
            ['psychology', 'Psychology', 'Psychology', 2, 8],
        ] as [$key, $code, $label, $faculty, $activities]) {
            $programs[] = compact('key', 'code', 'label', 'faculty', 'activities') + [
                'ratio' => $activities / $faculty,
                'lecturers' => $lecturers[$key],
            ];
        }

        $faculty = array_sum(array_column($programs, 'faculty'));
        $activities = array_sum(array_column($programs, 'activities'));

        return [
            'isDemo' => true,
            'programs' => $programs,
            // The reference total is 109, but its nine rows sum to 110. Derive totals from the rows.
            'totals' => compact('faculty', 'activities') + ['ratio' => $activities / $faculty],
        ];
    }
}
