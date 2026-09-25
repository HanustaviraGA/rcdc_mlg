<?php

namespace App\Console\Commands;

use App\Services\Research\RectorateResearchImporter;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

class ImportRectorateResearch extends Command
{
    protected $signature = 'research:import-rectorate {file} {--year=} {--month=} {--dry-run : Validate without changing data}';

    protected $description = 'Import hibah bulanan dari sheet MALANG (fallback Detail), Lokasi Kampus Binus @Malang';

    public function handle(RectorateResearchImporter $importer): int
    {
        try {
            $summary = $importer->import($this->argument('file'), basename($this->argument('file')), (bool) $this->option('dry-run'),
                $this->option('year') !== null ? (int) $this->option('year') : null,
                $this->option('month') !== null ? (int) $this->option('month') : null);
            $this->line(json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        } catch (ValidationException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
