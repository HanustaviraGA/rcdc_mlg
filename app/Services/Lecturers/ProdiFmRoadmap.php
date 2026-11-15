<?php

namespace App\Services\Lecturers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * The planned FM count of each study program per year and its education levels (S2 and S3),
 * laid out like sheet JJP: a row per program, three columns per year and a total row.
 * Each number is edited on its own, with a note.
 */
class ProdiFmRoadmap
{
    public const TABLE = 'prodi_fm_roadmaps';

    /** Editable numbers of a program year: field => [column, note column, label]. */
    public const FIELDS = [
        'fm' => ['column' => 'jumlah_fm', 'note' => 'catatan_fm', 'label' => 'Jumlah FM'],
        's2' => ['column' => 'jumlah_s2', 'note' => 'catatan_s2', 'label' => 'S2'],
        's3' => ['column' => 'jumlah_s3', 'note' => 'catatan_s3', 'label' => 'S3'],
    ];

    public function available(): bool
    {
        return Schema::hasTable(self::TABLE);
    }

    /**
     * @return array{
     *     years: list<int>,
     *     rows: list<array{prodi: string, program_studi: ?string, years: array<int, array{id: int, fm: int, s2: int, s3: int, notes: array<string, ?string>, mismatch: bool}>}>,
     *     totals: array<int, array{fm: int, s2: int, s3: int, mismatch: bool}>,
     *     mismatches: int,
     * }
     */
    public function table(): array
    {
        $records = $this->available()
            ? DB::table(self::TABLE)->orderBy('urutan')->orderBy('prodi')->orderBy('year')->get()
            : collect();
        $years = $records->pluck('year')->map(fn ($year) => (int) $year)->unique()->sort()->values()->all();
        $rows = $records->groupBy('prodi')->map(fn ($group, $prodi) => [
            'prodi' => (string) $prodi,
            'program_studi' => $group->first()->program_studi,
            'years' => $group->mapWithKeys(fn ($record) => [(int) $record->year => [
                'id' => (int) $record->id,
                ...$this->cell((int) $record->jumlah_fm, (int) $record->jumlah_s2, (int) $record->jumlah_s3),
                'notes' => array_map(fn (array $field) => $record->{$field['note']}, self::FIELDS),
            ]])->all(),
        ])->values()->all();
        $totals = [];
        foreach ($years as $year) {
            $cells = array_filter(array_map(fn (array $row) => $row['years'][$year] ?? null, $rows));
            $totals[$year] = $this->cell(...array_map(fn ($key) => array_sum(array_column($cells, $key)), ['fm', 's2', 's3']));
        }

        return [
            'years' => $years, 'rows' => $rows, 'totals' => $totals,
            'mismatches' => collect($rows)->sum(fn (array $row) => collect($row['years'])->where('mismatch', true)->count()),
        ];
    }

    /**
     * The planned share of S3 FM per year, summed over the given programs (Data Center names) or over all of them.
     *
     * @param  list<string>|null  $programs
     * @return array{prodi: list<string>, years: list<array{year: int, s3: int, fm: int, percent: ?float}>}
     */
    public function projection(?array $programs = null): array
    {
        $records = $this->available()
            ? DB::table(self::TABLE)->when($programs !== null, fn ($query) => $query->whereIn('program_studi', $programs))
                ->orderBy('urutan')->orderBy('prodi')->orderBy('year')->get()
            : collect();

        return [
            'prodi' => $records->pluck('prodi')->unique()->values()->all(),
            'years' => $records->groupBy('year')->sortKeys()->map(function ($group, $year) {
                $s3 = (int) $group->sum('jumlah_s3');
                $fm = (int) $group->sum('jumlah_fm');

                return ['year' => (int) $year, 's3' => $s3, 'fm' => $fm, 'percent' => $fm > 0 ? round($s3 / $fm * 100, 1) : null];
            })->values()->all(),
        ];
    }

    /**
     * Sets one number of a program year and its note; an empty note clears it.
     *
     * @return object the saved row
     */
    public function update(array $input): object
    {
        if (! $this->available()) {
            throw ValidationException::withMessages(['id' => 'Migrasi roadmap jumlah FM belum dijalankan.']);
        }
        $values = Validator::make($input, [
            'id' => ['required', 'integer'],
            'field' => ['required', 'in:'.implode(',', array_keys(self::FIELDS))],
            'nilai' => ['required', 'integer', 'min:0', 'max:9999'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ], [
            'field.in' => 'Kolom yang diubah harus Jumlah FM, S2, atau S3.',
            'required' => ':Attribute wajib diisi.',
            'integer' => ':Attribute harus berupa bilangan bulat.',
            'min' => ':Attribute minimal :min.',
            'nilai.max' => 'Nilai maksimal :max.',
            'catatan.max' => 'Catatan maksimal :max karakter.',
            'string' => ':Attribute harus berupa teks.',
        ], ['id' => 'baris roadmap', 'field' => 'kolom', 'nilai' => 'nilai', 'catatan' => 'catatan'])->validate();
        $row = DB::table(self::TABLE)->find((int) $values['id']);
        if (! $row) {
            throw ValidationException::withMessages(['id' => 'Baris roadmap tidak ditemukan.']);
        }
        $field = self::FIELDS[$values['field']];
        $note = trim((string) ($values['catatan'] ?? ''));
        DB::table(self::TABLE)->where('id', $row->id)->update([
            $field['column'] => (int) $values['nilai'], $field['note'] => $note === '' ? null : $note, 'updated_at' => now(),
        ]);

        return DB::table(self::TABLE)->find($row->id);
    }

    /** One year of a prodi; mismatch when S2 + S3 differs from the FM count. */
    private function cell(int $fm, int $s2, int $s3): array
    {
        return ['fm' => $fm, 's2' => $s2, 's3' => $s3, 'mismatch' => $s2 + $s3 !== $fm];
    }
}
