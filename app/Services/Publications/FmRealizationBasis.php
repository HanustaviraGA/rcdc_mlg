<?php

namespace App\Services\Publications;

use Illuminate\Support\Collection;

/**
 * The Scopus FM realization that /kpi-publikasi and the monthly report show (config
 * publications.fm_basis, env PUBLICATION_FM_BASIS):
 * - realization: the Realization Template of the workbook. The campus total is its
 *   "BINUS @ Malang Campus" row and only the Malang units it lists are counted, so the
 *   programs and lecturers of other units (CBDC, English Literature, ...) are left out.
 * - campus: the original weight of every Scopus FM row of the MALANG sheet, per Prodi KPI,
 *   like the pivot per Kampus. Targets and score ranges still come from the template.
 * Without a Realization Template both count every program by its original weight.
 */
class FmRealizationBasis
{
    public const REALIZATION = 'realization';

    public const CAMPUS = 'campus';

    public readonly string $basis;

    /** Realization Template rows by program code; BINUS is the campus row. */
    private array $template;

    public function __construct(array $metadata, ?string $basis = null)
    {
        $this->basis = $basis ?? self::configured();
        $this->template = $metadata['realization'] ?? [];
    }

    public static function configured(): string
    {
        return strtolower(trim((string) config('publications.fm_basis'))) === self::CAMPUS ? self::CAMPUS : self::REALIZATION;
    }

    /** Program codes counted in the campus realization; null counts every program. */
    public function programs(): ?array
    {
        $programs = array_values(array_diff(array_keys($this->template), ['BINUS']));

        return $this->basis === self::REALIZATION && $programs ? $programs : null;
    }

    public function counts(?string $program): bool
    {
        $programs = $this->programs();

        return $programs === null || in_array($program, $programs, true);
    }

    /** Realization of one program; $rows are its publication rows. */
    public function program(string $program, Collection $rows): float
    {
        return $this->official($program) ?? self::weight($rows);
    }

    /** Campus realization; $rows are the publication rows of the counted programs. */
    public function campus(Collection $rows): float
    {
        return $this->official('BINUS') ?? self::weight($rows);
    }

    public function source(): string
    {
        return match (true) {
            $this->official('BINUS') !== null => 'Realization Template (unit BINUS @Malang)',
            $this->basis === self::CAMPUS => 'Bobot asli seluruh publikasi kampus MALANG',
            default => 'Bobot asli publikasi',
        };
    }

    /** Original weight of the Scopus FM titles among $rows. */
    public static function weight(Collection $rows): float
    {
        return (float) $rows->filter(PublicationWorkbookMetrics::isScopusTitle(...))->sum('bobot_asli');
    }

    private function official(string $program): ?float
    {
        $value = $this->basis === self::REALIZATION ? ($this->template[$program]['realization'] ?? null) : null;

        return $value === null ? null : (float) $value;
    }
}
