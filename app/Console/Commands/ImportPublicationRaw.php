<?php

namespace App\Console\Commands;

use App\Services\Publications\PublicationImporter;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

class ImportPublicationRaw extends Command
{
    protected $signature = 'publications:import-raw {file} {--year=} {--month=} {--dry-run : Validate and summarize without changing data}';

    protected $description = 'Import sheet Raw untuk FM MALANG (Non Scopus FM / Scopus FM)';

    public function handle(PublicationImporter $importer): int
    {
        $month = (int) $this->option('month');
        try {
            $summary = $importer->import($this->argument('file'), basename($this->argument('file')),
                (int) $this->option('year'), $month, (int) ceil($month / 3), (bool) $this->option('dry-run'));
            $this->line(json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        } catch (ValidationException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
