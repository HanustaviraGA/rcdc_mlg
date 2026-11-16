<?php

namespace App\Services\LrcDashboard;

use App\Services\Lecturers\ProdiFmRoadmap;
use Illuminate\Support\Collection;

/**
 * Projects the S3 FM of every roadmap year the way LRC plans it:
 *
 *     FM S3 = existing S3 + (lecturers graduating S3 that year + new recruits S3)
 *
 * The Data Center, with the latest education statuses, gives the existing S3 and the graduates:
 * S2 and On Going S3 lecturers by their proyeksi lulus S3. The FM a program's roadmap has beyond its
 * lecturers are New FM Recruits (NR): S3 as far as the roadmap's S3 exceeds the projected S3, the rest S2.
 * So a program whose roadmap FM equals its Data Center FM has no NR, and its projected S3 may differ
 * from the roadmap's. The existing S3 of a year are the S3 of the year before, graduates and recruits included.
 */
class FmS3Projection
{
    public function __construct(private ProdiFmRoadmap $roadmap) {}

    /**
     * @param  Collection<int, array>  $lecturers  roster rows: gugus, education, pendidikan, statusPendidikan, proyeksiLulusS3, code, name
     * @param  list<string>|null  $programs  study programs (gugus binaan) to project, or all of the roadmap
     */
    public function build(Collection $lecturers, ?array $programs = null): array
    {
        $dataCenter = [];
        $programYears = [];
        foreach ($this->roadmap->records($programs)->groupBy('prodi') as $prodi => $rows) {
            $program = $rows->first()->program_studi;
            $members = $program === null ? collect() : $lecturers->where('gugus', $program)->values();
            $dataCenter[$prodi] = $this->dataCenter($program, $members);
            // Cumulative recruits needed by each year, per level.
            $recruited = ['S3' => [], 'S2' => []];
            $previous = ['s3' => $dataCenter[$prodi]['s3'], 'recruitsS3' => 0];
            foreach ($rows as $row) {
                $year = (int) $row->year;
                $s3 = $dataCenter[$prodi]['s3'] + $members->filter(fn (array $member) => $member['education'] !== 'S3'
                    && $member['proyeksiLulusS3'] !== null && $member['proyeksiLulusS3'] <= $year)->count();
                // Recruits fill the FM the roadmap has beyond the Data Center: S3 first, as far as S3 falls short, then S2.
                $gap = max(0, (int) $row->jumlah_fm - $members->count());
                $recruited['S3'][$year] = min($gap, max(0, (int) $row->jumlah_s3 - $s3));
                $recruited['S2'][$year] = $gap - $recruited['S3'][$year];
                $programYears[$year][] = [
                    'prodi' => (string) $prodi,
                    'roadmap' => ['fm' => (int) $row->jumlah_fm, 's2' => (int) $row->jumlah_s2, 's3' => (int) $row->jumlah_s3],
                    'existing' => $previous['s3'] + $previous['recruitsS3'],
                    'graduates' => $s3 - $previous['s3'],
                    'recruits' => $recruited['S3'][$year] - $previous['recruitsS3'],
                    'projectedS3' => $s3 + $recruited['S3'][$year],
                    'newRecruits' => [...$this->recruitRows($recruited['S3'], 'S3'), ...$this->recruitRows($recruited['S2'], 'S2')],
                ];
                $previous = ['s3' => $s3, 'recruitsS3' => $recruited['S3'][$year]];
            }
        }
        ksort($programYears);

        return [
            'prodi' => array_map('strval', array_keys($dataCenter)),
            'years' => array_map(fn (array $entries, int $year) => $this->year($year, $entries), $programYears, array_keys($programYears)),
            'dataCenter' => $dataCenter,
        ];
    }

    /** The totals of a year over its programs, with the programs themselves. */
    private function year(int $year, array $entries): array
    {
        $sum = fn (callable $value) => array_sum(array_map($value, $entries));
        $fm = $sum(fn (array $entry) => $entry['roadmap']['fm']);
        $s3 = $sum(fn (array $entry) => $entry['roadmap']['s3']);

        return [
            'year' => $year, 's3' => $s3, 'fm' => $fm, 'percent' => $fm > 0 ? round($s3 / $fm * 100, 1) : null,
            'existing' => $sum(fn (array $entry) => $entry['existing']),
            'graduates' => $sum(fn (array $entry) => $entry['graduates']),
            'recruits' => $sum(fn (array $entry) => $entry['recruits']),
            'projectedS3' => $sum(fn (array $entry) => $entry['projectedS3']),
            'newRecruits' => $sum(fn (array $entry) => array_sum(array_column($entry['newRecruits'], 'count'))),
            'programs' => $entries,
        ];
    }

    /** The current lecturers of a program and their counts per education. */
    private function dataCenter(?string $program, Collection $members): array
    {
        return [
            'programStudi' => $program,
            'fm' => $members->count(),
            's2' => $members->where('education', 'S2')->count(),
            'ongoing' => $members->where('education', 'On Going S3')->count(),
            's3' => $members->where('education', 'S3')->count(),
            'lecturers' => $members->sortBy('name')->map(fn (array $member) => [
                'code' => $member['code'], 'name' => $member['name'],
                'pendidikan' => $member['pendidikan'], 'statusPendidikan' => $member['statusPendidikan'],
                'education' => $member['education'],
                'proyeksiLulusS3' => $member['education'] === 'S3' ? null : $member['proyeksiLulusS3'],
            ])->values()->all(),
        ];
    }

    /**
     * The recruits of a level by the year each is first needed.
     *
     * @param  array<int, int>  $cumulative  year => recruits needed by then, up to the year shown
     * @return list<array{level: string, year: int, count: int}>
     */
    private function recruitRows(array $cumulative, string $level): array
    {
        $needed = (int) end($cumulative);
        $counts = [];
        for ($index = 1; $index <= $needed; $index++) {
            $year = min(array_keys(array_filter($cumulative, fn (int $count) => $count >= $index)));
            $counts[$year] = ($counts[$year] ?? 0) + 1;
        }
        ksort($counts);

        return array_map(fn (int $year, int $count) => ['level' => $level, 'year' => $year, 'count' => $count], array_keys($counts), $counts);
    }
}
