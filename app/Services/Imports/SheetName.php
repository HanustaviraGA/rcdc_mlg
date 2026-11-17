<?php

namespace App\Services\Imports;

use Illuminate\Support\Str;

/**
 * Finds the sheet chosen in a mapping preview. Laravel's TrimStrings middleware trims
 * request input, so a sheet named "Malang " comes back as "Malang". The exact name wins;
 * otherwise the one sheet whose trimmed name matches.
 */
final class SheetName
{
    /**
     * @param  array<int, string>  $names  sheet names keyed by sheet index
     */
    public static function find(array $names, string $requested): int|false
    {
        $names = array_map(fn ($name) => (string) $name, $names);
        $index = array_search($requested, $names, true);
        if ($index !== false) {
            return $index;
        }
        $matches = array_keys(array_map(Str::trim(...), $names), Str::trim($requested), true);

        return count($matches) === 1 ? $matches[0] : false;
    }
}
