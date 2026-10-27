<?php

namespace App\Services\CommunityDevelopment;

use App\Services\ColumnMapping\ColumnMapperService;
use App\Services\Imports\ImportPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class CommunityPartnerWorkbook
{
    public function __construct(private PartnerWorkbookReader $reader, private ColumnMapperService $mapper) {}

    public function preview(string $path, ?string $sheet = null, ?int $headerRow = null): array
    {
        $book = $this->reader->read($path, $sheet, $headerRow === null ? 55 : $headerRow + 5);
        $rows = $book['rows'];
        $config = $this->mapper->getMappingConfig('pkm_partners');
        if ($rows === [] && $headerRow === null) {
            return ['success' => true, 'sheet' => $book['sheet'], 'sheets' => $book['sheets'],
                'header_row' => 1, 'headers' => [], 'mapping_config' => $config, 'suggested_mapping' => [], 'preview_data' => []];
        }
        if ($headerRow === null) {
            $best = -1;
            foreach (array_slice($rows, 0, 50, true) as $number => $cells) {
                $score = count(array_intersect_key($this->mapper->suggestMapping($cells, 'pkm_partners'), $config['required']));
                if ($score > $best) {
                    $best = $score;
                    $headerRow = $number;
                }
            }
        }
        if (! isset($rows[$headerRow])) {
            $this->invalid('Baris header tidak tersedia pada sheet ini.', 'header_row');
        }
        $headers = $rows[$headerRow];

        return ['success' => true, 'sheet' => $book['sheet'], 'sheets' => $book['sheets'],
            'header_row' => $headerRow, 'headers' => $headers, 'mapping_config' => $config,
            'suggested_mapping' => $this->mapper->suggestMapping($headers, 'pkm_partners'),
            'preview_data' => array_values(array_slice(array_filter($rows, fn ($number) => $number > $headerRow, ARRAY_FILTER_USE_KEY), 0, 5))];
    }

    public function import(string $path, int $year, string $sheet, int $headerRow, array $mapping, string $filename): array
    {
        if ($year < 2000 || $year > 2100) {
            $this->invalid('Tahun data harus antara 2000 dan 2100.', 'data_year');
        }
        $book = $this->reader->read($path, $sheet);
        if ($headerRow < 1 || $headerRow > 1000 || ! isset($book['rows'][$headerRow])) {
            $this->invalid('Baris header tidak valid.', 'header_row');
        }
        try {
            $mapping = $this->mapper->resolveMapping($book['rows'][$headerRow], 'pkm_partners', $mapping);
        } catch (ValidationException $exception) {
            $this->invalid(implode(' ', array_merge(...array_values($exception->errors()))), 'column_mapping');
        }
        $policy = new ImportPolicy('pkm', 'partners');
        $records = [];
        $seen = [];
        $duplicates = 0;
        foreach ($book['rows'] as $number => $cells) {
            if ($number <= $headerRow) {
                continue;
            }
            $record = [];
            foreach ($mapping as $field => $index) {
                $record[$field] = $index === null ? '' : trim($cells[$index] ?? '');
            }
            // Ignore rows containing only unmapped cells, for example a footer or serial number.
            if (! array_filter($record, fn ($value) => $value !== '')) {
                continue;
            }
            $invalid = false;
            foreach (['name', 'cluster', 'area'] as $field) {
                if ($record[$field] === '' || mb_strlen($record[$field]) > 255 || $record[$field] === '#ERROR!') {
                    $invalid = true;
                }
            }
            if ($invalid || mb_strlen($record['scale']) > 255 || $record['scale'] === '#ERROR!') {
                $policy->skip("Baris {$number}: nama, kluster, dan area wajib terisi dengan nilai valid (maksimal 255 karakter).");

                continue;
            }
            $record['cluster'] = $this->category($record['cluster'], [
                'f&b' => 'F&B', 'food & beverage' => 'F&B', 'kriya' => 'Kriya', 'holti' => 'Holti',
                'batik & ecoprint' => 'Batik & Ecoprint', 'non umkm' => 'NON UMKM', 'non-umkm' => 'NON UMKM',
            ]);
            $record['scale'] = $record['scale'] === '' ? ($record['cluster'] === 'NON UMKM' ? 'Lainnya' : 'Belum diketahui')
                : $this->category($record['scale'], ['mikro' => 'Mikro', 'kecil' => 'Kecil', 'menengah' => 'Menengah', 'lainnya' => 'Lainnya']);
            $record['area'] = $this->category($record['area'], [
                'kab. malang' => 'Kab. Malang', 'kab malang' => 'Kab. Malang', 'kabupaten malang' => 'Kab. Malang', 'kota malang' => 'Kota Malang',
            ]);
            $joined = $record['joined_year'];
            if ($joined !== '' && (! ctype_digit($joined) || (int) $joined < 1900 || (int) $joined > 2100)) {
                $policy->accept("Baris {$number}: tahun bergabung tidak valid; nilai dikosongkan.");
                $joined = '';
            }
            $record['joined_year'] = $joined === '' ? null : (int) $joined;
            $signature = json_encode($record, JSON_THROW_ON_ERROR);
            if (isset($seen[$signature])) {
                $duplicates++;

                continue;
            }
            $seen[$signature] = true;
            $records[] = $record + ['source_row' => $number];
        }
        if ($records === []) {
            $this->invalid('Tidak ada mitra yang dapat diimpor. Data sebelumnya tetap tersimpan.');
        }
        foreach (['community_partner_imports', 'community_partners'] as $table) {
            if (! Schema::hasTable($table)) {
                $this->invalid('Migrasi data mitra Community Development belum dijalankan.');
            }
        }

        return DB::transaction(function () use ($year, $sheet, $headerRow, $mapping, $filename, $records, $duplicates, $policy) {
            $batch = DB::table('community_partner_imports')->where('data_year', $year)->lockForUpdate()->first();
            $values = ['filename' => $filename, 'sheet' => $sheet, 'header_row' => $headerRow,
                'column_mapping' => json_encode($mapping, JSON_THROW_ON_ERROR), 'partner_count' => count($records), 'updated_at' => now()];
            if ($batch) {
                $id = $batch->id;
                DB::table('community_partner_imports')->where('id', $id)->update($values);
                DB::table('community_partners')->where('import_id', $id)->delete();
            } else {
                $id = DB::table('community_partner_imports')->insertGetId($values + ['data_year' => $year, 'created_at' => now()]);
            }
            foreach (array_chunk($records, 100) as $chunk) {
                DB::table('community_partners')->insert(array_map(fn ($record) => $record + ['import_id' => $id], $chunk));
            }

            return $policy->summary(['import_id' => $id, 'data_year' => $year, 'selected' => count($records),
                'duplicates' => $duplicates, 'replaced' => $batch !== null, 'warnings' => []]);
        });
    }

    private function category(string $value, array $known): string
    {
        return $known[mb_strtolower(preg_replace('/\s+/u', ' ', $value))] ?? $value;
    }

    private function invalid(string $message, string $field = 'partners'): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
