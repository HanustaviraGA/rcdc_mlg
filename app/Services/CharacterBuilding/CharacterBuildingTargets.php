<?php

namespace App\Services\CharacterBuilding;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Targets of Character Building (CBDC): each period has a target (the achievement to reach)
 * and the score it earns. The targets of a year (its four quarters, or both semesters of an
 * academic year) form the score ranges of that year. A realization earns the score of the
 * highest target it reaches: with 3 → 2, 6 → 3, 9 → 4 and 11 → 6, a realization of 7 or 8
 * earns 3 and anything from 11 up earns the maximum 6.
 */
class CharacterBuildingTargets
{
    public const TABLE = 'character_building_targets';

    public const CATEGORIES = [
        'publikasi' => ['label' => 'Publikasi', 'period' => 'quarter', 'unit' => 'judul Scopus', 'score_max' => 6,
            'hint' => 'Per kuartal. Target berupa jumlah judul Scopus dosen CBDC.'],
        'pengajaran' => ['label' => 'Pengajaran', 'period' => 'semester', 'unit' => null, 'score_max' => 100,
            'hint' => 'Per semester pada tahun akademik. Data realisasi pengajaran segera tersedia.'],
        'comdev' => ['label' => 'Community Development', 'period' => 'quarter', 'unit' => 'kegiatan', 'score_max' => 6,
            'hint' => 'Per kuartal. Target berupa jumlah kegiatan comdev CBDC (sheet Rasio).'],
        'pengembangan_diri' => ['label' => 'Pengembangan Diri', 'period' => 'quarter', 'unit' => null, 'score_max' => 100,
            'hint' => 'Per kuartal. Data realisasi pengembangan diri segera tersedia.'],
    ];

    public const SEMESTERS = [1 => 'Ganjil', 2 => 'Genap'];

    public function available(): bool
    {
        return Schema::hasTable(self::TABLE);
    }

    /** @return array<string, Collection> category => targets, newest year first, in period order */
    public function grouped(): array
    {
        $rows = $this->available()
            ? DB::table(self::TABLE)->orderByDesc('year')->orderBy('term')->get()->map($this->present(...))
            : collect();

        return array_map(fn ($category) => $rows->where('category', $category)->values(),
            array_combine(array_keys(self::CATEGORIES), array_keys(self::CATEGORIES)));
    }

    /** The target of one period, if set. */
    public function target(string $category, int $year, int $term): ?object
    {
        $row = $this->available() ? DB::table(self::TABLE)->where(compact('category', 'year', 'term'))->first() : null;

        return $row ? $this->present($row) : null;
    }

    /** The score ranges of a year: its targets from the lowest, periods sharing a target once. */
    public function ladder(string $category, int $year): Collection
    {
        return $this->available()
            ? DB::table(self::TABLE)->where(compact('category', 'year'))->orderBy('target')->orderBy('term')->get()
                ->map($this->present(...))->unique('target')->values()
            : collect();
    }

    /** The range a realization reaches: the one with the highest target at or below it. */
    public static function reached(Collection $ladder, ?float $realization): ?object
    {
        return $realization === null ? null : $ladder->sortByDesc('target')->first(fn ($tier) => $realization >= $tier->target);
    }

    /**
     * Saves a target from the form. Without an id it sets the target of its period, replacing
     * a saved one; with one, that target moves to the submitted period and values. Within a
     * year, a higher target must earn a higher score.
     */
    public function save(array $input, ?int $id = null): object
    {
        if (! $this->available()) {
            throw ValidationException::withMessages(['target' => 'Migrasi target Character Building belum dijalankan.']);
        }
        $existing = $id === null ? null : DB::table(self::TABLE)->find($id);
        if ($id !== null && ! $existing) {
            throw ValidationException::withMessages(['id' => 'Target tidak ditemukan.']);
        }
        $values = $this->validated($existing ? ['category' => $existing->category] + $input : $input);
        $period = ['category' => $values['category'], 'year' => $values['year'], 'term' => $values['term']];
        $current = DB::table(self::TABLE)->where($period)->first();
        if ($existing && $current && (int) $current->id !== (int) $existing->id) {
            throw ValidationException::withMessages([self::CATEGORIES[$values['category']]['period'] => 'Target '.self::periodLabel(...array_values($period))
                .' sudah ada. Ubah target tersebut atau hapus terlebih dahulu.']);
        }
        $replaced = $existing ?? $current;
        $others = DB::table(self::TABLE)->where('category', $values['category'])->where('year', $values['year'])
            ->when($replaced, fn ($query) => $query->where('id', '!=', $replaced->id))->orderBy('term')->get()->map($this->present(...));
        foreach ($others as $row) {
            if (($row->target < $values['target'] && $row->score >= $values['score'])
                || ($row->target > $values['target'] && $row->score <= $values['score'])
                || ($row->target === $values['target'] && $row->score !== $values['score'])) {
                throw ValidationException::withMessages(['score' => "Skor harus naik mengikuti target. Pada {$row->label}, target "
                    .self::number($row->target).' memiliki skor '.self::number($row->score).'.']);
            }
        }
        $data = $period + ['target' => $values['target'], 'score' => $values['score'], 'updated_at' => now()];
        if ($replaced) {
            DB::table(self::TABLE)->where('id', $replaced->id)->update($data);
        }

        return $this->present(DB::table(self::TABLE)->find($replaced->id ?? DB::table(self::TABLE)->insertGetId($data + ['created_at' => now()])));
    }

    public function delete(int $id): object
    {
        $row = $this->available() ? DB::table(self::TABLE)->find($id) : null;
        if (! $row) {
            throw ValidationException::withMessages(['id' => 'Target tidak ditemukan.']);
        }
        DB::table(self::TABLE)->where('id', $id)->delete();

        return $this->present($row);
    }

    public static function periodLabel(string $category, int $year, int $term): string
    {
        return self::yearLabel($category, $year).' '.self::termLabel($category, $term);
    }

    /** The year whose targets share score ranges: a calendar year, or an academic year. */
    public static function yearLabel(string $category, int $year): string
    {
        return self::CATEGORIES[$category]['period'] === 'semester' ? $year.'/'.($year + 1) : (string) $year;
    }

    public static function termLabel(string $category, int $term): string
    {
        return self::CATEGORIES[$category]['period'] === 'semester' ? self::SEMESTERS[$term] : 'Q'.$term;
    }

    public static function number(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, ',', '.'), '0'), ',');
    }

    /** Years offered by the forms and filters: the current year, its neighbours and saved targets. */
    public function years(array $extra = []): array
    {
        $years = array_merge(range((int) now()->year + 1, (int) now()->year - 4), $extra,
            $this->available() ? DB::table(self::TABLE)->pluck('year')->map(fn ($year) => (int) $year)->all() : []);
        $years = array_values(array_unique($years));
        rsort($years);

        return $years;
    }

    private function validated(array $input): array
    {
        $category = $input['category'] ?? null;
        $definition = self::CATEGORIES[$category] ?? null;
        $semester = ($definition['period'] ?? null) === 'semester';
        $values = Validator::make($input, [
            'category' => ['required', 'in:'.implode(',', array_keys(self::CATEGORIES))],
            'year' => $semester ? ['prohibited'] : ['required', 'integer', 'between:2000,2100'],
            'quarter' => $semester ? ['prohibited'] : ['required', 'integer', 'between:1,4'],
            'academic_year' => $semester ? ['required', 'regex:/^(20\d{2})\/(20\d{2}|2100)$/'] : ['prohibited'],
            'semester' => $semester ? ['required', 'in:Ganjil,Genap'] : ['prohibited'],
            'target' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999'],
            'score' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:'.($definition['score_max'] ?? 100)],
        ], [
            'academic_year.regex' => 'Tahun akademik harus berurutan, misalnya 2025/2026.',
            'target.max' => 'Target maksimal :max.',
            'score.max' => 'Skor :attribute maksimal :max.',
            'decimal' => ':Attribute maksimal dua angka desimal.',
        ], ['target' => 'target', 'score' => $definition['label'] ?? 'skor', 'quarter' => 'kuartal', 'year' => 'tahun',
            'academic_year' => 'tahun akademik'])->validate();
        $result = ['category' => $category, 'target' => round((float) $values['target'], 2), 'score' => round((float) $values['score'], 2)];
        if ($semester) {
            [$start, $end] = array_map('intval', explode('/', $values['academic_year']));
            if ($end !== $start + 1) {
                throw ValidationException::withMessages(['academic_year' => 'Tahun akademik harus berurutan, misalnya 2025/2026.']);
            }

            return $result + ['year' => $start, 'term' => array_search($values['semester'], self::SEMESTERS, true)];
        }

        return $result + ['year' => (int) $values['year'], 'term' => (int) $values['quarter']];
    }

    private function present(object $row): object
    {
        $row->id = (int) $row->id;
        $row->year = (int) $row->year;
        $row->term = (int) $row->term;
        $row->target = round((float) $row->target, 2);
        $row->score = round((float) $row->score, 2);
        $row->label = self::periodLabel($row->category, $row->year, $row->term);

        return $row;
    }
}
