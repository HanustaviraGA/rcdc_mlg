<?php

namespace App\Services\ColumnMapping;

use Aspera\Spreadsheet\XLSX\Reader;
use Illuminate\Support\Collection;

class ColumnMapperService
{
    /**
     * Baca header dari file Excel dan berikan preview data
     */
    public function preview(string $path): array
    {
        $reader = new Reader();
        $sheets = [];
        $previewData = [];

        try {
            $reader->open($path);
            
            // Ambil semua sheets
            foreach ($reader->getSheets() as $index => $sheet) {
                $sheets[$this->normalize($sheet->getName())] = [
                    'index' => $index,
                    'name' => $sheet->getName(),
                ];
            }

            // Cari sheet yang tersedia
            $sheetToRead = $this->selectBestSheet($sheets);

            if ($sheetToRead === null) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada sheet yang sesuai ditemukan',
                    'available_sheets' => array_column($sheets, 'name'),
                ];
            }

            $reader->changeSheet($sheets[$sheetToRead]['index']);
            
            $rowCount = 0;
            $headers = null;

            foreach ($reader as $number => $cells) {
                // Skip empty rows
                if (!array_filter($cells, fn($cell) => trim((string) $cell) !== '')) {
                    continue;
                }

                // Ambil headers dari baris pertama yang tidak kosong
                if ($headers === null) {
                    $headers = array_map(
                        fn($cell) => trim((string) $cell),
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
                'sheet' => $sheets[$sheetToRead]['name'],
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
    public function getMappingConfig(string $importType): array
    {
        return match ($importType) {
            'fm_file' => $this->getFmMappingConfig(),
            'mhs_file' => $this->getMhsMappingConfig(),
            'research_file' => $this->getResearchMappingConfig(),
            default => [],
        };
    }

    /**
     * Validasi mapping columns
     */
    public function validateMapping(array $mapping, string $importType): array
    {
        $config = $this->getMappingConfig($importType);
        $errors = [];
        $warnings = [];

        // Cek required fields
        foreach ($config['required'] as $field => $label) {
            if (!isset($mapping[$field]) || $mapping[$field] === '' || $mapping[$field] === null) {
                $errors[$field] = "Kolom '{$label}' wajib dipetakan";
            }
        }

        // Cek optional fields
        foreach ($config['optional'] as $field => $label) {
            if (!isset($mapping[$field]) || $mapping[$field] === '' || $mapping[$field] === null) {
                $warnings[$field] = "Kolom '{$label}' tidak ditemukan - data akan kosong";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Suggest mapping berdasarkan fuzzy matching
     */
    public function suggestMapping(array $headers, string $importType): array
    {
        $config = $this->getMappingConfig($importType);
        $mapping = [];

        // Normalize headers
        $normalizedHeaders = array_map($this->normalize(...), $headers);

        // Coba match untuk semua fields (required + optional)
        $allFields = array_merge($config['required'], $config['optional']);
        
        foreach ($allFields as $field => $label) {
            $normalizedLabel = $this->normalize($label);
            $aliases = $config['aliases'][$field] ?? [];
            
            // Exact match
            foreach ($aliases as $alias) {
                $key = array_search($this->normalize($alias), $normalizedHeaders, true);
                if ($key !== false) {
                    $mapping[$field] = $key;
                    break;
                }
            }

            // Fuzzy match jika exact match tidak ditemukan
            if (empty($mapping[$field])) {
                $bestMatch = $this->fuzzyMatchHeader($label, $headers);
                if ($bestMatch !== null) {
                    $mapping[$field] = $bestMatch;
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
    private function selectBestSheet(array $sheets): ?string
    {
        $preferences = ['malang', 'raw', 'detail', 'data'];
        
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
     * FM Publication Mapping Config
     */
    private function getFmMappingConfig(): array
    {
        return [
            'required' => [
                'request_code' => 'Request Code',
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
                'jenis' => ['jenis', 'tipe'],
                'tipe_publikasi' => ['tipe publikasi', 'publikasi', 'tipe'],
                'fm_author' => ['fm author', 'fm_author'],
                'first_author' => ['first author', 'first_author'],
                'quartile_jurnal' => ['quartile', 'quartile jurnal', 'q'],
                'publisher' => ['publisher', 'penerbit'],
                'tanggal_pelaporan' => ['tanggal', 'date', 'tgl'],
                'prodi_kpi' => ['prodi', 'prodi kpi'],
                'status' => ['status', 'st2024', 'st2025', 'st2026', 'st2027'],
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
                'nim' => 'NIM',
                'nama_mahasiswa' => 'Nama Mahasiswa',
                'judul' => 'Judul',
                'tahun' => 'Tahun',
            ],
            'optional' => [
                'prodi' => 'Program Studi',
                'dosen_pembimbing' => 'Dosen Pembimbing',
                'sumber_publikasi' => 'Sumber Publikasi',
                'link_publikasi' => 'Link Publikasi',
            ],
            'aliases' => [
                'nim' => ['nim', 'nomor induk mahasiswa'],
                'nama_mahasiswa' => ['nama', 'nama mahasiswa', 'nama_mhs'],
                'judul' => ['judul', 'title', 'nama karya'],
                'tahun' => ['tahun', 'year', 'thn'],
                'prodi' => ['prodi', 'program studi', 'program studi'],
                'dosen_pembimbing' => ['dosen', 'pembimbing', 'dosen pembimbing'],
                'sumber_publikasi' => ['sumber', 'publikasi', 'tempat publikasi'],
                'link_publikasi' => ['link', 'url', 'website'],
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
                'nidn' => 'NIDN',
                'kategori_fm_eksternal_mahasiswa' => 'Kategori FM/eksternal/Mahasiswa',
                'sumber_dana' => 'Sumber Dana',
                'dana_disetujui' => 'Dana Disetujui',
                'tanggal_mulai' => 'Tanggal Mulai',
                'tanggal_selesai' => 'Tanggal Selesai',
                'program_hibah' => 'Program Hibah',
                'skema' => 'SKEMA',
                'sdgs' => 'SDGs',
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
