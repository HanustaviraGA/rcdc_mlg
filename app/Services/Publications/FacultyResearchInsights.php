<?php

namespace App\Services\Publications;

use App\Services\Research\RectorateResearchReader;
use Illuminate\Support\Collection;

class FacultyResearchInsights
{
    public function projects(Collection $grants): Collection
    {
        return $grants->groupBy(fn ($row) => $row->project_key ?? $row->budget_year.'|'.$row->kd_prop)
            ->map(function (Collection $rows, string $key) {
                $first = $rows->first();
                $codes = $rows->filter(fn ($row) => strtoupper(trim($row->kategori_fm_eksternal_mahasiswa ?? '')) === 'FM')
                    ->pluck('kode_dosen_nim')->unique()->values()->all();
                $sdgs = $rows->flatMap(function ($row) {
                    $numbers = json_decode($row->sdg_numbers ?? '[]', true);

                    return is_array($numbers) && $numbers ? $numbers : RectorateResearchReader::sdgs($row->sdgs ?? null);
                })->filter(fn ($number) => is_numeric($number) && (int) $number == $number && $number >= 1 && $number <= 17)
                    ->map(fn ($number) => (int) $number)->unique()->sort()->values()->all();
                $roadmap = $this->values($rows, 'subtopik_research_roadmap');

                return [
                    'id' => $key, 'year' => (int) $first->budget_year, 'proposal' => $first->kd_prop,
                    'title' => $first->judul, 'codes' => $codes, 'sdgs' => $sdgs,
                    'topics' => $roadmap ?: [$first->judul],
                    'topic_source' => $roadmap ? 'Subtopik Research Roadmap' : 'Judul hibah',
                    'keywords' => $this->values($rows, 'keyword_sdgs'),
                ];
            })->filter(fn ($project) => $project['codes'] !== [])->values();
    }

    public function cluster(int $year, ?string $education, ?string $rank, Collection $papers, Collection $entries,
        Collection $grants, bool $publicationAvailable, ?object $profile = null): array
    {
        $papers = $papers->filter(fn ($row) => (int) $row->year <= $year);
        $entries = $entries->filter(fn ($row) => (int) $row->year <= $year);
        $history = $grants->filter(fn ($row) => (int) $row->budget_year <= $year);
        $scopus = $papers->filter(PublicationWorkbookMetrics::isScopusTitle(...))->unique('request_code')->count();
        $rtto = $entries->max('rtto_scopus');
        $hasScopus = $scopus > 0 || $rtto > 0;
        $qualified = $education === 'S3' || in_array($rank, ['L', 'LK', 'GB'], true);
        $chairs = $history->filter(fn ($row) => preg_match('/^ketua\b/i', trim($row->peran ?? '')));
        $external = $chairs->filter(fn ($row) => $this->funding($row) === 'external')->unique('project_key');
        $unknownFunding = $chairs->filter(fn ($row) => $this->funding($row) === 'unknown')->unique('project_key')->count();
        $rig = $this->values($history, 'rig_bdsrc_fbrc');
        $evidence = [
            'education' => $education, 'rank' => $rank, 'academic_eligible' => $qualified,
            'scopus_titles' => $scopus, 'rtto_scopus_max' => $rtto === null ? null : (float) $rtto,
            'has_scopus' => $hasScopus, 'external_chair_projects' => $external->count(),
            'unknown_chair_funding' => $unknownFunding, 'rig' => $rig,
        ];

        if (! $education && ! $rank) {
            $cluster = null;
            $reason = 'Pendidikan dan JJA belum tersedia untuk menentukan cluster.';
        } elseif ($qualified && ! $hasScopus && ! $publicationAvailable) {
            $cluster = null;
            $reason = 'Profil akademik memenuhi syarat; data publikasi sampai tahun ini belum tersedia.';
        } elseif ($qualified && $hasScopus) {
            $cluster = $external->isNotEmpty() ? 'A' : 'B';
            $reason = $cluster === 'A'
                ? 'Pendidikan/JJA memenuhi syarat, ada bukti Scopus, dan tercatat sebagai ketua hibah eksternal.'
                : 'Pendidikan/JJA memenuhi syarat dan ada bukti Scopus; ketua hibah eksternal belum tercatat.';
        } else {
            $cluster = 'C';
            $reason = 'Kriteria akademik dan publikasi untuk A/B belum terpenuhi pada data import.';
            if (! $rig) {
                $reason .= ' Keanggotaan RIG/Research Center belum tercatat; indikasi C perlu dilengkapi.';
            }
        }
        if ($unknownFunding) {
            $reason .= ' Ada hibah ketua dengan sumber dana belum dapat diklasifikasikan.';
        }
        $explicit = trim($profile->cluster ?? '');

        return [
            'cluster' => $explicit ?: $cluster, 'calculated_cluster' => $cluster,
            'source' => $explicit ? 'Penetapan' : 'Otomatis dari import',
            'reason' => $explicit ? 'Mengikuti penetapan cluster tahun '.$year.'. '.$reason : $reason,
            'evidence' => $evidence,
        ];
    }

    public function funding(object $row): string
    {
        $provider = strtolower(implode(' ', [$row->sumber_pemberi_hibah ?? '', $row->jenis_institusi_pemberi_hibah ?? '', $row->nama_pemberi_hibah ?? '']));
        // PIB is an internal scheme even when Sumber Dana says "Internasional - PIB".
        if (preg_match('/binus|hibus|bina nusantara|mandiri|pribadi|internal/', $provider)) {
            return 'internal';
        }
        if (preg_match('/nasional|internasional|eksternal|external|pemerintah|perusahaan|industri|universitas|bumn/', $provider)) {
            return 'external';
        }
        $fund = strtolower(trim($row->sumber_dana ?? ''));
        if (preg_match('/binus|hibus|mandiri|pribadi|internal|\bpib\b/', $fund)) {
            return 'internal';
        }
        if (preg_match('/nasional|internasional|eksternal|external|dikti/', $fund)) {
            return 'external';
        }

        return 'unknown';
    }

    private function values(Collection $rows, string $field): array
    {
        return $rows->pluck($field)->map(fn ($value) => trim($value ?? ''))
            ->reject(fn ($value) => in_array(strtolower($value), ['', '-', 'n/a', 'na', 'none', 'null'], true))
            ->unique()->values()->all();
    }
}
