<?php

namespace App\Services\Publications;

use Illuminate\Support\Collection;

class PublicationWorkbookMetrics
{
    public static function publicationKey(object|array $row): string
    {
        $requestCode = trim((string) data_get($row, 'request_code'));
        if ($requestCode !== '') {
            return 'request:'.$requestCode;
        }
        if (trim((string) data_get($row, 'title')) === '') {
            return 'row:'.(data_get($row, 'source_row') ?? data_get($row, 'id_rectorate') ?? 'unknown');
        }
        // A missing optional Request Code must not merge all papers by one lecturer.
        $identity = array_map(fn ($field) => mb_strtolower(trim(preg_replace('/\s+/u', ' ',
            str_replace("\xC2\xA0", ' ', (string) data_get($row, $field))))), ['title', 'jenis', 'tipe_publikasi']);

        return 'publication:'.hash('sha256', json_encode($identity, JSON_THROW_ON_ERROR));
    }

    public static function isScopusTitle(object|array $row): bool
    {
        return strcasecmp(trim((string) data_get($row, 'tipe_publikasi')), 'Scopus') === 0
            && strcasecmp(trim((string) data_get($row, 'submitted')), 'Scopus FM') === 0;
    }

    public static function summarize(Collection $papers, ?object $entry = null): array
    {
        $scopus = $papers->filter(fn ($row) => strcasecmp(trim((string) data_get($row, 'tipe_publikasi')), 'Scopus') === 0)->sum('bobot_asli');
        $nonScopus = $papers->filter(fn ($row) => strcasecmp(trim((string) data_get($row, 'tipe_publikasi')), 'Nscopus') === 0)->sum('bobot_asli');
        $titles = $papers->filter(self::isScopusTitle(...));
        $rttoScopus = $entry->rtto_scopus ?? null;
        $rttoNonScopus = $entry->rtto_non_scopus ?? null;

        return [
            'in_kpi' => $entry !== null,
            'program' => $entry->program ?? null,
            'rank' => $entry->academic_rank ?? null,
            'faculty' => $entry->faculty_type ?? null,
            'score' => isset($entry->rtto_score) ? (int) $entry->rtto_score : null,
            'titles' => $titles->count(),
            'first_author' => $titles->filter(fn ($row) => strtoupper(trim((string) data_get($row, 'first_author'))) === 'Y')->count(),
            'rectorate_scopus' => (float) $scopus,
            'rectorate_non_scopus' => (float) $nonScopus,
            'rtto_scopus' => $rttoScopus === null ? null : (float) $rttoScopus,
            'rtto_non_scopus' => $rttoNonScopus === null ? null : (float) $rttoNonScopus,
            'scopus' => max((float) $scopus, (float) $rttoScopus),
            'non_scopus' => max((float) $nonScopus, (float) $rttoNonScopus),
            'scopus_source' => self::source((float) $scopus, $rttoScopus),
            'non_scopus_source' => self::source((float) $nonScopus, $rttoNonScopus),
        ];
    }

    private static function source(float $rectorate, mixed $rtto): string
    {
        if ($rtto === null) {
            return 'Rectorate';
        }

        return abs($rectorate - (float) $rtto) < 0.000000001 ? 'Sama' : ($rectorate > (float) $rtto ? 'Rectorate' : 'RTTO');
    }
}
