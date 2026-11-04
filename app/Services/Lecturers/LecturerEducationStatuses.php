<?php

namespace App\Services\Lecturers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Uploaded education statuses, stored apart from the Data Center rows: a Data Center
 * upload replaces its whole period, so a status kept on those rows would be erased.
 * A status applies from its upload period until a later period uploads another one.
 */
class LecturerEducationStatuses
{
    public const TABLE = 'lecturer_education_statuses';

    public function available(): bool
    {
        return Schema::hasTable(self::TABLE);
    }

    /**
     * Lecturer code => the latest uploaded status at or before the period. A blank
     * upload is kept as null, so it still overrides an older status.
     */
    public function asOf(int $year, int $month): Collection
    {
        if (! $this->available()) {
            return collect();
        }

        return DB::table(self::TABLE)
            ->where(fn ($query) => $query->where('year', '<', $year)
                ->orWhere(fn ($query) => $query->where('year', $year)->where('month', '<=', $month)))
            ->orderBy('year')->orderBy('month')
            ->get(['kode_dosen', 'status_pendidikan'])
            // Ordered by period, so a later upload replaces an earlier one.
            ->mapWithKeys(fn ($row) => [$this->code($row->kode_dosen) => $row->status_pendidikan]);
    }

    /**
     * Gives loaded lecturer rows the uploaded status of the period (by default the current
     * roster). Lecturers without an uploaded status keep the value on their row.
     */
    public function apply(iterable $lecturers, ?int $year = null, ?int $month = null): void
    {
        $statuses = $this->asOf($year ?? (int) now()->year, $month ?? 12);
        foreach ($lecturers as $lecturer) {
            $code = $this->code($lecturer->kode_dosen ?? '');
            if ($statuses->has($code)) {
                $lecturer->status_pendidikan = $statuses->get($code);
            }
        }
    }

    /** @param  array<string, ?string>  $statuses  lecturer code => status */
    public function store(array $statuses, int $year, int $month): void
    {
        $rows = [];
        foreach ($statuses as $code => $status) {
            $rows[] = ['kode_dosen' => $this->code($code), 'year' => $year, 'month' => $month,
                'status_pendidikan' => $status, 'created_at' => now(), 'updated_at' => now()];
        }
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table(self::TABLE)->upsert($chunk, ['kode_dosen', 'year', 'month'], ['status_pendidikan', 'updated_at']);
        }
    }

    private function code(mixed $code): string
    {
        return strtoupper(trim((string) $code));
    }
}
