<?php

namespace App\Services\Publications;

use Illuminate\Support\Collection;

class PublicationScore
{
    public function education(?string $value): ?string
    {
        return preg_match('/\bS[123]\b/i', $value ?? '', $match) ? strtoupper($match[0]) : null;
    }

    public function rank(?string $value): ?string
    {
        $value = strtoupper(trim($value ?? ''));
        foreach (['LEKTOR KEPALA' => 'LK', 'ASISTEN AHLI' => 'AA', 'GURU BESAR' => 'GB', 'LEKTOR' => 'L', 'TENAGA PENGAJAR' => 'TP'] as $label => $code) {
            if (str_starts_with($value, $label)) {
                return $code;
            }
        }

        return preg_match('/^(LK|AA|GB|TP|L)(?:\d|\b)/', $value, $match) ? $match[1] : null;
    }

    public function faculty(?string $value): ?string
    {
        return preg_match('/^(Functional|Professional)/i', trim($value ?? ''), $match) ? ucfirst(strtolower($match[1])) : null;
    }

    public function rule(?string $faculty, ?string $rank, ?string $education): ?array
    {
        if (! $faculty || ! $rank || ! $education) {
            return null;
        }
        $suffix = $faculty === 'Functional' ? 'Func' : 'Prof';
        $rule = match (true) {
            $rank === 'TP' && in_array($education, ['S1', 'S2']) => ['TP12', null],
            $rank === 'AA' && $education === 'S2' => ['AA2', $suffix === 'Func' ? 1 : 0.5],
            $rank === 'L' && $education === 'S2' => ['L2', $suffix === 'Func' ? 1 : 0.5],
            in_array($rank, ['AA', 'TP']) && $education === 'S3',
            $rank === 'LK' && $education === 'S2',
            $rank === 'LK' && $suffix === 'Prof' => ['AA3TP3LK2', $suffix === 'Func' ? 2 : 1],
            in_array($rank, ['L', 'LK']) && $education === 'S3' => [$suffix === 'Func' ? 'L3LK3' : 'L3', $suffix === 'Func' ? 4 : 2],
            $rank === 'GB' && $suffix === 'Func' => ['GB', 6],
            default => null,
        };

        return $rule ? ['function' => $rule[0].$suffix, 'threshold' => $rule[1], 'label' => $faculty.' · '.$rank.' '.$education] : null;
    }

    public function calculate(string $code, ?array $rule, Collection $publications): ?int
    {
        // An absent report is unknown, not proof of zero productivity.
        if (! $rule || $publications->isEmpty() || $publications->contains(fn ($row) => ! is_numeric($row->bobot_asli ?? null))) {
            return null;
        }

        return (int) ($rule['function'])($code, $publications)['kpi'];
    }
}
