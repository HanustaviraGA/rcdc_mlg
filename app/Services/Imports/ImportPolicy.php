<?php

namespace App\Services\Imports;

use Illuminate\Validation\ValidationException;

class ImportPolicy
{
    public readonly bool $force;

    private array $warnings = [];

    private int $skippedRows = 0;

    public function __construct(string $feature, private string $errorField = 'rectorate')
    {
        $this->force = filter_var(config("imports.force.$feature", false), FILTER_VALIDATE_BOOLEAN);
    }

    public function accept(string $message): void
    {
        if (! $this->force) {
            throw ValidationException::withMessages([$this->errorField => $message.' Data sebelumnya tetap tersimpan.']);
        }
        $this->warnings[] = $message;
    }

    public function skip(string $message): void
    {
        $this->accept($message.' Baris dilewati.');
        $this->skippedRows++;
    }

    public function summary(array $summary): array
    {
        return array_replace($summary, [
            'force_import' => $this->force,
            'skipped_rows' => $this->skippedRows,
            'warnings' => array_values(array_unique(array_merge($summary['warnings'] ?? [], $this->warnings))),
        ]);
    }
}
