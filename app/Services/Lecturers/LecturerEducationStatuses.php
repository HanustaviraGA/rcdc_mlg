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

    public const FIELDS = ['status_pendidikan', 'roadmap_s3', 'proyeksi_lulus_s3', 'pembiayaan', 'universitas_tujuan'];

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
        return $this->detailsAsOf($year, $month)->map(fn (array $details) => $details['status_pendidikan']);
    }

    /** Lecturer code => the education status and S3 planning for that period. */
    public function detailsAsOf(int $year, int $month): Collection
    {
        if (! $this->available()) {
            return collect();
        }

        $fields = array_values(array_intersect(self::FIELDS, Schema::getColumnListing(self::TABLE)));

        return DB::table(self::TABLE)
            ->where(fn ($query) => $query->where('year', '<', $year)
                ->orWhere(fn ($query) => $query->where('year', $year)->where('month', '<=', $month)))
            ->orderBy('year')->orderBy('month')
            ->get(['kode_dosen', ...$fields])
            // Ordered by period, so a later upload replaces an earlier one.
            ->mapWithKeys(fn ($row) => [$this->code($row->kode_dosen) => array_intersect_key((array) $row, array_flip($fields))]);
    }

    /**
     * Gives loaded lecturer rows the uploaded status of the period (by default the current
     * roster). Lecturers without an uploaded status keep the value on their row.
     */
    public function apply(iterable $lecturers, ?int $year = null, ?int $month = null): void
    {
        $statuses = $this->detailsAsOf($year ?? (int) now()->year, $month ?? 12);
        foreach ($lecturers as $lecturer) {
            $code = $this->code($lecturer->kode_dosen ?? '');
            if ($statuses->has($code)) {
                foreach ($statuses->get($code) as $field => $value) {
                    $lecturer->{$field} = $value;
                }
            }
        }
    }

    /** @param  array<string, array>  $statuses  lecturer code => education fields */
    public function store(array $statuses, int $year, int $month): void
    {
        $rows = [];
        foreach ($statuses as $code => $details) {
            $rows[] = ['kode_dosen' => $this->code($code), 'year' => $year, 'month' => $month,
                ...$details, 'created_at' => now(), 'updated_at' => now()];
        }
        foreach (array_chunk($rows, 500) as $chunk) {
            $fields = array_values(array_intersect(self::FIELDS, array_keys($chunk[0])));
            DB::table(self::TABLE)->upsert($chunk, ['kode_dosen', 'year', 'month'], [...$fields, 'updated_at']);
        }
    }

    private function code(mixed $code): string
    {
        return strtoupper(trim((string) $code));
    }
}
