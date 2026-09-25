<?php

namespace App\Services\Publications;

class ReportPrograms
{
    public const LABELS = ['BINUS' => 'Binus @ Malang', 'BC' => 'Entre. - Buss. Creation',
        'CS' => 'Computer Science', 'DKV' => 'Visual Communication Design', 'DI' => 'Interior Design',
        'ILKOM' => 'Communication', 'PR' => 'Public Relation', 'CBDC' => 'CBDC', 'LC' => 'LC', 'PSY' => 'Psychology'];

    public static function code(?string $name): string
    {
        $name = trim($name ?? '');
        $value = strtolower($name);
        if (in_array(strtoupper($name), array_keys(self::LABELS), true)) {
            return strtoupper($name);
        }

        return match (true) {
            str_contains($value, 'creation'), str_contains($value, 'entrepreneur') => 'BC',
            str_contains($value, 'computer science'), str_contains($value, 'informatics') => 'CS',
            str_contains($value, 'visual'), str_contains($value, 'komunikasi visual') => 'DKV',
            str_contains($value, 'interior') => 'DI',
            str_contains($value, 'public relation') => 'PR',
            str_contains($value, 'communication'), str_contains($value, 'komunikasi') => 'ILKOM',
            str_contains($value, 'character'), str_contains($value, 'cbdc') => 'CBDC',
            str_contains($value, 'language'), $value === 'english literature' => 'LC',
            str_contains($value, 'psycholog') => 'PSY',
            str_contains($value, 'malang') => 'BINUS',
            default => $name,
        };
    }
}
