<?php

namespace App\Services\ColumnMapping;

use Aspera\Spreadsheet\XLSX\Reader;
use Illuminate\Validation\ValidationException;

class ColumnMapperService
{
    /**
     * Baca header dari file Excel dan berikan preview data
     */
    public function preview(string $path, ?string $importType = null, ?string $sheet = null): array
    {
        $reader = new Reader;
        $sheets = [];
        $names = [];
        $previewData = [];

        try {
            $reader->open($path);

            // Ambil semua sheets
            foreach ($reader->getSheets() as $index => $entry) {
                $sheets[$this->normalize($entry->getName())] = [
                    'index' => $index,
                    'name' => $entry->getName(),
                ];
                $names[$index] = $entry->getName();
            }

            // A chosen sheet must exist; otherwise use the importer's default sheet,
            // or the first sheet so the user can pick the right one.
            if ($sheet !== null) {
                $index = array_search($sheet, $names, true);
                $selected = $index === false ? null : ['index' => $index, 'name' => $names[$index]];
            } else {
                $default = $this->selectBestSheet($sheets, $importType);
                $selected = $default !== null ? $sheets[$default]
                    : ($names ? ['index' => array_key_first($names), 'name' => reset($names)] : null);
            }

            if ($selected === null) {
                return [
                    'success' => false,
                    'message' => $sheet !== null ? "Sheet {$sheet} tidak ditemukan pada file." : 'Workbook tidak memiliki sheet.',
                    'available_sheets' => array_values($names),
                    'sheets' => array_values($names),
                ];
            }

            $reader->changeSheet($selected['index']);

            $rowCount = 0;
            $headers = null;

            foreach ($reader as $number => $cells) {
                // Skip empty rows
                if (! array_filter($cells, fn ($cell) => trim((string) $cell) !== '')) {
                    continue;
                }

                // Ambil headers dari baris pertama yang tidak kosong
                if ($headers === null) {
                    $headers = array_map(
                        fn ($cell) => trim((string) $cell),
                        $cells
                    );

                    continue;
                }

                // Ambil 5 baris pertama untuk preview
                if ($rowCount < 5) {
                    $row = [];
                    foreach ($cells as $index => $cell) {
                        $row[$index] = trim((string) $cell);
                    }
                    $previewData[] = $row;
                    $rowCount++;
                }

                if ($rowCount >= 5) {
                    break;
                }
            }

            return [
                'success' => true,
                'sheet' => $selected['name'],
                'sheets' => array_values($names),
                'headers' => $headers ?? [],
                'preview_data' => $previewData,
                'total_columns' => count($headers ?? []),
            ];
        } finally {
            $reader->close();
        }
    }

    /**
     * Dapatkan mapping configuration untuk tipe import tertentu
     */
    public function getMappingConfig(string $importType, ?int $year = null): array
    {
        $config = match ($importType) {
            'fm_file' => $this->getFmMappingConfig(),
            'mhs_file' => $this->getMhsMappingConfig(),
            'research_file' => $this->getResearchMappingConfig(),
            'fm_kpi', 'mhs_list', 'mhs_title', 'report_realization', 'report_scoring' => $this->getSupportingSheetConfig($importType),
            'pkm_ratio' => $this->getPkmRatioMappingConfig(),
            'pkm_involvement' => $this->getPkmInvolvementMappingConfig(),
            'pkm_partners' => [
                'required' => ['name' => 'Nama Usaha', 'cluster' => 'Cluster', 'scale' => 'Skala', 'area' => 'Area'],
                'optional' => ['joined_year' => 'Tahun Bergabung'],
                'aliases' => [
                    'name' => ['nama umkm', 'nama mitra', 'nama usaha / mitra'],
                    'cluster' => ['kluster', 'kategori kluster', 'cluster umkm'],
                    'scale' => ['skala usaha', 'skala umkm'],
                    'area' => ['wilayah', 'kabupaten kota', 'kota / kabupaten'],
                    'joined_year' => ['tahun gabung', 'tahun bergabung umkm'],
                ],
            ],
            default => [],
        };
        foreach (['required', 'optional'] as $group) {
            foreach ($config[$group] ?? [] as $field => $label) {
                $config[$group][$field] = str_replace('{tahun}', (string) ($year ?? now()->year), $label);
            }
        }
        foreach ($config['aliases'] ?? [] as $field => $aliases) {
            $config['aliases'][$field] = array_map(fn ($alias) => str_replace('{tahun}', (string) ($year ?? now()->year), $alias), $aliases);
        }

        return $config;
    }

    /**
     * Validasi mapping columns
     */
    public function validateMapping(array $mapping, string $importType, ?int $headerCount = null, ?int $year = null): array
    {
        $config = $this->getMappingConfig($importType, $year);
        if ($config === []) {
            return ['valid' => false, 'errors' => ['type' => 'Tipe import tidak dikenal.'], 'warnings' => []];
        }
        $errors = [];
        $warnings = [];
        $fields = array_merge($config['required'], $config['optional']);
        $required = $config['required'];

        foreach ($mapping as $field => $index) {
            if (! array_key_exists($field, $fields)) {
                $errors[$field] = "Kolom sistem '{$field}' tidak dikenal.";

                continue;
            }
            if ($index === '' || $index === null) {
                continue;
            }
            if (! (is_int($index) || (is_string($index) && ctype_digit($index))) || (int) $index < 0) {
                $errors[$field] = "Pemetaan '{$fields[$field]}' harus berupa indeks kolom yang valid.";

                continue;
            }
            if ($headerCount !== null && (int) $index >= $headerCount) {
                $errors[$field] = "Pemetaan '{$fields[$field]}' berada di luar kolom file.";
            }
        }

        // One source column may feed several target fields.
        foreach ($required as $field => $label) {
            if (! array_key_exists($field, $mapping) || $mapping[$field] === '' || $mapping[$field] === null) {
                $errors[$field] = "Kolom '{$label}' wajib dipetakan";
            }
        }
        foreach ($config['optional'] as $field => $label) {
            if (! array_key_exists($field, $mapping) || $mapping[$field] === '' || $mapping[$field] === null) {
                $warnings[$field] = "Kolom '{$label}' tidak dipetakan - data akan kosong";
            }
        }

        return ['valid' => empty($errors), 'errors' => $errors, 'warnings' => $warnings];
    }

    /**
     * Validate mapped row values using the same required fields as the preview.
     */
    public function validateRow(array $record, string $importType, int $rowNumber, array $mapping = [], array $headers = [], ?int $year = null): array
    {
        $config = $this->getMappingConfig($importType, $year);
        if ($config === []) {
            return ['valid' => false, 'errors' => ['type' => 'Tipe import tidak dikenal.']];
        }
        $errors = [];
        foreach ($config['required'] as $field => $label) {
            if (trim((string) ($record[$field] ?? '')) !== '') {
                continue;
            }
            $source = '';
            $index = $mapping[$field] ?? null;
            if ($index !== null && $index !== '' && is_numeric($index) && (int) $index >= 0) {
                $column = '';
                for ($number = (int) $index + 1; $number > 0; $number = intdiv($number - 1, 26)) {
                    $column = chr(65 + ($number - 1) % 26).$column;
                }
                $header = trim((string) ($headers[(int) $index] ?? ''));
                $source = ' pada kolom '.$column.($header === '' ? '' : " ({$header})");
            }
            $errors[$field] = "Baris {$rowNumber}: {$label} ({$field}) kosong{$source}.";
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }

    /**
     * Resolve a confirmed mapping against a workbook header row.
     * A supplied mapping is authoritative: an omitted optional field stays null
     * rather than silently switching back to automatic header detection.
     */
    public function resolveMapping(array $headers, string $importType, ?array $mapping = null, ?int $year = null): array
    {
        $config = $this->getMappingConfig($importType, $year);
        if ($config === []) {
            throw ValidationException::withMessages(['rectorate' => 'Tipe import tidak dikenal.']);
        }
        $normalized = array_map(fn ($header) => $this->normalize((string) $header), $headers);
        $fields = array_merge($config['required'], $config['optional']);
        $resolved = [];
        $source = $mapping === null ? null : array_map(
            fn ($index) => $index === '' ? null : $index,
            $mapping
        );
        if ($source !== null) {
            $validation = $this->validateMapping($source, $importType, count($headers), $year);
            if (! $validation['valid']) {
                throw ValidationException::withMessages(['rectorate' => implode(' ', $validation['errors'])]);
            }
        }
        foreach ($fields as $field => $label) {
            if ($source !== null && array_key_exists($field, $source)) {
                // The complete user mapping was validated before normalization.
                $index = $source[$field];
                $resolved[$field] = $index === null ? null : (int) $index;

                continue;
            }
            if ($source !== null) {
                $resolved[$field] = null;

                continue;
            }
            $matches = [];
            foreach (array_unique(array_merge([$label], $config['aliases'][$field] ?? [])) as $alias) {
                foreach (array_keys($normalized, $this->normalize((string) $alias), true) as $index) {
                    $matches[$index] = true;
                }
            }
            if (count($matches) > 1) {
                throw ValidationException::withMessages(['rectorate' => "Kolom '{$label}' ambigu atau duplikat pada file."]);
            }
            $resolved[$field] = $matches === [] ? null : (int) array_key_first($matches);
        }

        $validation = $this->validateMapping($resolved, $importType, count($headers), $year);
        if (! $validation['valid']) {
            throw ValidationException::withMessages(['rectorate' => implode(' ', $validation['errors'])]);
        }

        return $resolved;
    }

    /**
     * Suggest mapping berdasarkan fuzzy matching
     */
    public function suggestMapping(array $headers, string $importType, ?int $year = null): array
    {
        $config = $this->getMappingConfig($importType, $year);
        $mapping = [];
        if ($config === []) {
            return [];
        }
        // Normalize headers
        $normalizedHeaders = array_map($this->normalize(...), $headers);

        // Coba match untuk semua fields (required + optional)
        $allFields = array_merge($config['required'], $config['optional']);

        // Reserve exact matches first, so an optional fuzzy guess never steals
        // the source column of a required field (and column zero stays valid).
        $used = [];
        foreach ($allFields as $field => $label) {
            $aliases = array_unique(array_merge([$label], $config['aliases'][$field] ?? []));
            foreach ($aliases as $alias) {
                $key = array_search($this->normalize($alias), $normalizedHeaders, true);
                if ($key !== false && ! isset($used[$key])) {
                    $mapping[$field] = $key;
                    $used[$key] = true;
                    break;
                }
            }
        }
        foreach ($allFields as $field => $label) {
            if (! array_key_exists($field, $mapping) && $field !== 'status') {
                $bestMatch = $this->fuzzyMatchHeader($label, array_diff_key($headers, $used));
                if ($bestMatch !== null) {
                    $mapping[$field] = $bestMatch;
                    $used[$bestMatch] = true;
                }
            }
        }

        return $mapping;
    }

    /**
     * Fuzzy match untuk header
     */
    private function fuzzyMatchHeader(string $target, array $headers): ?int
    {
        $targetNorm = $this->normalize($target);
        $bestScore = 0;
        $bestIndex = null;

        foreach ($headers as $index => $header) {
            $headerNorm = $this->normalize($header);
            $score = $this->levenshteinSimilarity($targetNorm, $headerNorm);

            if ($score > $bestScore && $score >= 0.6) {
                $bestScore = $score;
                $bestIndex = $index;
            }
        }

        return $bestIndex;
    }

    /**
     * Hitung similarity menggunakan Levenshtein distance
     */
    private function levenshteinSimilarity(string $a, string $b): float
    {
        $distance = levenshtein($a, $b);
        $maxLength = max(strlen($a), strlen($b));

        if ($maxLength === 0) {
            return 1.0;
        }

        return 1.0 - ($distance / $maxLength);
    }

    /**
     * Pilih sheet terbaik berdasarkan nama
     */
    private function selectBestSheet(array $sheets, ?string $importType = null): ?string
    {
        $preferences = match ($importType) {
            'research_file' => ['malang', 'detail'],
            'fm_file', 'mhs_file' => ['malang', 'raw'],
            default => ['malang', 'raw', 'detail', 'data'],
        };

        foreach ($preferences as $pref) {
            if (isset($sheets[$pref])) {
                return $pref;
            }
        }

        return null;
    }

    /**
     * Normalize string untuk comparison
     */
    private function normalize(string $value): string
    {
        return strtolower(trim(preg_replace('/\s+/u', ' ', str_replace("\xC2\xA0", ' ', $value))));
    }

    /**
     * Columns of the supporting sheets read next to MALANG/Raw (see WorkbookSheetRoles).
     * Labels follow the headers those sheets normally carry.
     */
    private function getSupportingSheetConfig(string $importType): array
    {
        return match ($importType) {
            'fm_kpi' => [
                'required' => [
                    'kode_dosen' => 'Kode Dosen', 'name' => 'Nama Dosen', 'program' => 'Jurusan Binaan',
                    'academic_rank' => 'JJA', 'faculty_type' => 'Faculty Type', 'rtto_non_scopus' => 'Non Scopus RTTO',
                    'rtto_scopus' => 'Scopus RTTO', 'rtto_score' => 'Score KPI RTTO',
                ],
                // Values Excel already calculated; only compared with the system calculation.
                'optional' => [
                    'rectorate_non_scopus' => 'Non Scopus Rectorate', 'rectorate_scopus' => 'Scopus Rectorate',
                    'non_scopus' => 'Non Scopus', 'scopus' => 'Scopus', 'first_author' => 'Jumlah First Author', 'score' => 'Score KPI',
                ],
                'aliases' => ['kode_dosen' => ['kode_dosen'], 'program' => ['jurusan', 'prodi'], 'faculty_type' => ['tipe faculty']],
            ],
            'mhs_list' => [
                'required' => ['program' => 'Prodi', 'name' => 'MHS Author', 'title' => 'Title'],
                'optional' => [],
                'aliases' => ['program' => ['prodi mhs', 'program studi'], 'name' => ['nama mahasiswa', 'fm author'], 'title' => ['judul']],
            ],
            'mhs_title' => [
                'required' => ['label' => 'Prodi (label baris rekap)', 'count' => 'Jumlah Scopus'],
                'optional' => [],
                'aliases' => ['label' => ['tipe publikasi', 'row labels', 'prodi'], 'count' => ['scopus', 'count of title']],
            ],
            'report_realization' => [
                'required' => ['unit' => 'Unit Name'],
                'optional' => ['target' => 'Target', 'realization' => 'Realization'],
                'aliases' => ['unit' => ['unit', 'nama unit'], 'realization' => ['realisasi']],
            ],
            'report_scoring' => [
                'required' => ['unit' => 'Unit Name'],
                'optional' => array_combine(
                    array_map(fn ($score) => "score_{$score}", range(1, 6)),
                    array_map(fn ($score) => "Score {$score}", range(1, 6))
                ),
                'aliases' => ['unit' => ['unit', 'nama unit']],
            ],
        };
    }

    private function getPkmRatioMappingConfig(): array
    {
        return [
            'required' => ['program' => 'Prodi', 'faculty' => 'Jumlah FM', 'activities' => 'Jumlah Kegiatan', 'ratio' => 'Rasio'],
            // 'optional' => ['ratio' => 'Rasio'],
            'optional' => [],
            'aliases' => ['program' => ['program studi', 'jurusan', 'prodi'], 'faculty' => ['jumlah dosen'],
                'activities' => ['jumlah kegiatan'], 'ratio' => ['rasio']],
        ];
    }

    private function getPkmInvolvementMappingConfig(): array
    {
        return [
            'required' => [
                'kode_dosen' => 'Kode Dosen',
                'reported_activities' => 'Jumlah kegiatan yang dilaporkan ke binusmaya',
                'score' => 'Skor KPI comdev FM', 'status' => 'Keterangan : Minimal 1 Kegiatan PkM',
            ],
            // 'optional' => ['score' => 'Skor KPI comdev FM', 'status' => 'Keterangan : Minimal 1 Kegiatan PkM'],
            'optional' => [],
            'aliases' => [
                'kode_dosen' => ['kode dosen', 'kode_dosen'],
                'reported_activities' => ['jumlah laporan binusmaya', 'jumlah kegiatan binusmaya'],
                'score' => ['skor kpi', 'skor kpi comdev fm'],
                'status' => ['status', 'keterangan', 'keterangan: minimal 1 kegiatan pkm'],
            ],
        ];
    }

    /**
     * FM Publication Mapping Config
     */
    private function getFmMappingConfig(): array
    {
        return [
            'required' => [
                'kode_dosen' => 'Kode Dosen',
                'kampus' => 'Kampus',
                'submitted' => 'Submitted',
                'bobot_asli' => 'Bobot',
                'title' => 'Title',
                'jenis' => 'Jenis',
                'tipe_publikasi' => 'Tipe Publikasi',
                'status' => 'Status (Status / St{tahun})',
            ],
            'optional' => [
                'request_code' => 'Request Code',
                'author' => 'Author',
                's_f' => 'S/F',
                'dept' => 'Dept',
                'sumber_paper' => 'Sumber Paper',
                'scopus_year' => 'Scopus Year',
                'source_title' => 'Source Title',
                'notes' => 'Notes',
                'fm_author' => 'FM Author',
                'jja' => 'JJA',
                'pendidikan' => 'Pendidikan',
                'type' => 'Type',
                'first_author' => 'First Author',
                'quartile_jurnal' => 'Quartile Jurnal',
                'publisher' => 'Publisher',
                'tanggal_pelaporan' => 'Tanggal Pelaporan',
                'prodi_kpi' => 'Prodi KPI',
            ],
            'aliases' => [
                'request_code' => ['requestcode', 'request code'],
                'kode_dosen' => ['kode dosen', 'kode_dosen'],
                'kampus' => ['kampus', 'campus'],
                'submitted' => ['submitted', 'tipe laporan'],
                'bobot_asli' => ['bobot', 'bobot asli', 'bobot kontribusi'],
                'title' => ['title', 'judul'],
                'jenis' => ['jenis'],
                'tipe_publikasi' => ['tipe publikasi', 'publikasi'],
                'type' => ['type'],
                'author' => ['author'],
                's_f' => ['s/f'],
                'dept' => ['dept'],
                'sumber_paper' => ['skema', 'sumber paper'],
                'scopus_year' => ['scopus year'],
                'source_title' => ['source title'],
                'notes' => ['notes'],
                'fm_author' => ['fm author', 'fm_author'],
                'first_author' => ['first author', 'first_author'],
                'quartile_jurnal' => ['quartile', 'quartile jurnal', 'q'],
                'publisher' => ['publisher', 'penerbit'],
                'tanggal_pelaporan' => ['tanggal pelaporan', 'tanggal', 'date', 'tgl'],
                'prodi_kpi' => ['prodi', 'prodi kpi'],
                'status' => ['status', 'st{tahun}'],
            ],
        ];
    }

    /**
     * Student Publication Mapping Config
     */
    private function getMhsMappingConfig(): array
    {
        return [
            'required' => [
                'name' => 'Nama Mahasiswa / FM Author',
                'campus' => 'Kampus',
                'submitted' => 'Submitted',
                'type' => 'Tipe Publikasi',
                'title' => 'Judul / Title',
                'program' => 'Program Studi Mahasiswa',
            ],
            'optional' => [
                'code' => 'Kode Dosen (Mahasiswa)',
                'request_code' => 'Request Code',
                'first_author' => 'First Author',
            ],
            'aliases' => [
                'request_code' => ['requestcode', 'request code'],
                'name' => ['fm author', 'mhs author', 'nama mahasiswa', 'nama'],
                'code' => ['kode dosen', 'kode_dosen'],
                'campus' => ['kampus', 'campus'],
                'submitted' => ['submitted', 'tipe laporan'],
                'type' => ['tipe publikasi'],
                'title' => ['title', 'judul', 'nama karya'],
                'program' => ['prodi mhs', 'prodi', 'program studi'],
                'first_author' => ['first author', 'first_author'],
            ],
        ];
    }

    /**
     * Research Grant Mapping Config
     */
    private function getResearchMappingConfig(): array
    {
        return [
            'required' => [
                'tahun_anggaran' => 'Tahun Anggaran',
                'kd_prop' => 'Kd. Prop',
                'kode_dosen_nim' => 'Kode Dosen/NIM',
                'nama' => 'Nama',
                'peran' => 'Peran',
                'judul' => 'JUDUL',
                'lokasi_kampus' => 'Lokasi Kampus',
            ],
            'optional' => [
                ...array_diff_key(\App\Services\Research\RectorateResearchReader::HEADERS, array_flip([
                    'tahun_anggaran', 'kd_prop', 'kode_dosen_nim', 'nama', 'peran', 'judul', 'lokasi_kampus',
                ])),
            ],
            'aliases' => [
                'tahun_anggaran' => ['tahun anggaran', 'tahun', 'budget year'],
                'kd_prop' => ['kd prop', 'kd. prop', 'kode prop'],
                'kode_dosen_nim' => ['kode dosen/nim', 'kode dosen', 'kode'],
                'nama' => ['nama', 'nama peneliti', 'name'],
                'peran' => ['peran', 'role', 'posisi'],
                'judul' => ['judul', 'title'],
                'lokasi_kampus' => ['lokasi kampus', 'kampus', 'location'],
                'nidn' => ['nidn', 'nip'],
                'kategori_fm_eksternal_mahasiswa' => ['kategori', 'category'],
                'sumber_dana' => ['sumber dana', 'sumber', 'funding'],
                'dana_disetujui' => ['dana disetujui', 'dana', 'approved amount'],
                'tanggal_mulai' => ['tanggal mulai', 'start date', 'dari'],
                'tanggal_selesai' => ['tanggal selesai', 'end date', 'sampai'],
                'program_hibah' => ['program hibah', 'program', 'scheme'],
                'skema' => ['skema', 'scheme', 'tipe hibah'],
                'sdgs' => ['sdgs', 'sustainable development goals'],
            ],
        ];
    }
}
