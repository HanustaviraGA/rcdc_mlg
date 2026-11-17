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
 * S2 and On Going S3 lecturers by their proyeksi lulus S3. The roadmap gives the New FM Recruits (NR):
 * people from outside joining each year as S2 or S3; a lecturer moving up to S3 is a graduate, never a recruit.
 * The existing S3 of a year are the S3 of the year before, graduates and recruits included. The projected
 * S3 and FM (Data Center + recruits so far) can differ from the roadmap's; the dashboard shows where.
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
            // Recruits joined so far: [level, year, count], S3 before S2.
            $joined = ['S3' => [], 'S2' => []];
            $previous = ['s3' => $dataCenter[$prodi]['s3'], 'recruitsS3' => 0];
            foreach ($rows as $row) {
                $year = (int) $row->year;
                $s3 = $dataCenter[$prodi]['s3'] + $members->filter(fn (array $member) => $member['education'] !== 'S3'
                    && $member['proyeksiLulusS3'] !== null && $member['proyeksiLulusS3'] <= $year)->count();
                $recruits = ['S3' => (int) ($row->jumlah_nr_s3 ?? 0), 'S2' => (int) ($row->jumlah_nr_s2 ?? 0)];
                foreach ($recruits as $level => $count) {
                    if ($count > 0) {
                        $joined[$level][] = ['level' => $level, 'year' => $year, 'count' => $count];
                    }
                }
                $recruitsS3 = $previous['recruitsS3'] + $recruits['S3'];
                $newRecruits = [...$joined['S3'], ...$joined['S2']];
                $programYears[$year][] = [
                    'prodi' => (string) $prodi,
                    'roadmap' => ['fm' => (int) $row->jumlah_fm, 's2' => (int) $row->jumlah_s2, 's3' => (int) $row->jumlah_s3,
                        'nrS2' => $recruits['S2'], 'nrS3' => $recruits['S3']],
                    'existing' => $previous['s3'] + $previous['recruitsS3'],
                    'graduates' => $s3 - $previous['s3'],
                    'recruits' => $recruits['S3'],
                    'projectedS3' => $s3 + $recruitsS3,
                    'projectedFm' => $members->count() + array_sum(array_column($newRecruits, 'count')),
                    'newRecruits' => $newRecruits,
                ];
                $previous = ['s3' => $s3, 'recruitsS3' => $recruitsS3];
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
            'projectedFm' => $sum(fn (array $entry) => $entry['projectedFm']),
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
}
