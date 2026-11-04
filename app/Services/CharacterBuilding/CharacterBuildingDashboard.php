<?php

namespace App\Services\CharacterBuilding;

/**
 * The public CBDC dashboard (/dashboard-cbdc): the realization, target and score of each area
 * for a quarter, and every quarter of its year side by side. The data and scoring are those
 * of the Character Building module.
 */
class CharacterBuildingDashboard
{
    public function __construct(private CharacterBuildingProgress $progress) {}

    /**
     * Without a year, the latest quarter with a Publikasi FM upload; a year alone shows its Q4.
     */
    public function data(?int $year = null, ?int $quarter = null): array
    {
        [$defaultYear, $defaultQuarter] = $this->progress->defaultQuarter('publikasi');
        $year ??= $defaultYear;
        $quarter ??= $year === $defaultYear ? $defaultQuarter : 4;
        $quarters = [
            'publikasi' => collect(range(1, 4))->mapWithKeys(fn ($term) => [$term => $this->progress->publication($year, $term)]),
            'comdev' => collect(range(1, 4))->mapWithKeys(fn ($term) => [$term => $this->progress->comdev($year, $term)]),
        ];
        $years = array_values(array_unique([...$this->progress->years(), $year]));
        rsort($years);

        return [
            'year' => $year, 'quarter' => $quarter, 'label' => "{$year} Q{$quarter}", 'years' => $years,
            'publication' => $quarters['publikasi'][$quarter], 'comdev' => $quarters['comdev'][$quarter],
            'quarters' => $quarters,
            'details' => $this->details($quarters['publikasi'][$quarter], $quarters['comdev'][$quarter]),
        ];
    }

    /**
     * The lecturer details of the quarter for the page script, in the shape of the Dashboard
     * Riset (lecturers and their publications) and the Dashboard ComDev (lecturer list).
     */
    private function details(array $publication, array $comdev): array
    {
        $batch = $publication['batch'];

        return [
            'label' => $publication['label'],
            'publication' => [
                'period' => $batch ? "{$batch->year}-{$batch->month}" : null,
                'faculty' => $publication['lecturers']->map(fn ($row) => [
                    'code' => $row['code'], 'name' => $row['name'], 'rank' => $row['rank'], 'faculty' => $row['faculty'],
                    'nonScopus' => $row['non_scopus'], 'scopus' => $row['scopus'], 'firstAuthor' => $row['first_author'], 'score' => $row['score'],
                ])->values()->all(),
                'publications' => $publication['publications']->map(fn ($row) => [
                    'code' => $row['code'], 'request' => $row['request'], 'author' => $row['author'], 'name' => $row['fm_author'] ?: $row['name'],
                    'title' => $row['title'], 'firstAuthor' => $row['first_author'], 'weight' => $row['weight'], 'category' => $row['submitted'],
                    'status' => $row['status'], 'type' => $row['kind'], 'index' => $row['type'], 'source' => $row['source'],
                    'paperSource' => $row['paper_source'], 'quartile' => $row['quartile'],
                ])->values()->all(),
            ],
            'comdev' => [
                'semester' => $comdev['semester'],
                'lecturers' => $comdev['lecturers']->map(fn ($row) => collect($row)->only(['code', 'name', 'activities', 'score', 'status'])->all())->values()->all(),
            ],
        ];
    }
}
