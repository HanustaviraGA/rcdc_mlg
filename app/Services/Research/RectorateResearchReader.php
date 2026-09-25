<?php

namespace App\Services\Research;

use Aspera\Spreadsheet\XLSX\Reader;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Validation\ValidationException;

class RectorateResearchReader
{
    public const CAMPUS = 'BINUS @Malang';

    public const HEADERS = [
        'no' => 'No', 'pengakuan_kpi_research_program' => 'Pengakuan KPI Research Program',
        'pengakuan_kpi_non_tuition' => 'Pengakuan KPI non Tuition', 'sumber_dana' => 'Sumber Dana',
        'bobot_sumber_dana' => 'Bobot sumber dana', 'tahun_anggaran' => 'Tahun Anggaran',
        'kd_prop' => 'Kd. Prop', 'kode_dosen_nim' => 'Kode Dosen/NIM', 'nidn' => 'NIDN',
        'nama' => 'Nama', 'peran' => 'Peran', 'kategori_fm_eksternal_mahasiswa' => 'Kategori FM/eksternal/Mahasiswa',
        'rig_bdsrc_fbrc' => 'RIG/BDSRC/FBRC', 'bobot_peran' => 'Bobot Peran',
        'bobot_sumber_x_bobot_peran' => 'Bobot sumber x bobot peran', 'prodi_di_kpi' => 'Prodi di KPI',
        'fakultas_kpi' => 'Fakultas KPI', 'prodi_jur_binaan' => 'Prodi/Jur Binaan', 'prodi_pdpt' => 'Prodi PDPT',
        'lokasi_kampus' => 'Lokasi Kampus', 'tahun_ke' => 'Tahun ke-', 'total_tahun_penelitian' => 'Total tahun Penelitian',
        'tanggal_mulai' => 'Tanggal Mulai', 'tanggal_selesai' => 'Tanggal Selesai',
        'sumber_pemberi_hibah' => 'Sumber Pemberi Hibah', 'jenis_institusi_pemberi_hibah' => 'Jenis Institusi pemberi Hibah',
        'nama_pemberi_hibah' => 'Nama Pemberi hibah', 'program_hibah' => 'Program Hibah', 'skema' => 'SKEMA',
        'status_usulan' => 'Status Usulan', 'judul' => 'JUDUL', 'nilai_hibah_selain_rupiah' => 'Nilai Hibah selain Rupiah',
        'nama_mata_uang' => 'Nama Mata Uang', 'nilai_tukar_to_rupiah' => 'Nilai Tukar to Rupiah',
        'hibah_internal_binus' => 'Hibah Internal Binus', 'dana_disetujui' => 'Dana Disetujui', 'hibah_per_prodi' => 'Hibah per Prodi',
        'jenis_penelitian' => 'Jenis Penelitian (Dasar, Terapan, Pengembangan)', 'tkt' => 'TKT (Tingkat Kesiapterapan Teknologi)',
        'sdgs' => 'SDGs', 'keyword_sdgs' => 'Keyword SDGs', 'bidang_ilmu_by_qs_subject' => 'Bidang Ilmu by QS Subject',
        'subtopik_research_roadmap' => 'Subtopik Research Roadmap', 'produk_yang_dihasilkan' => 'Produk yang Dihasilkan',
        'bidang_penelitian' => 'Bidang Penelitian', 'sub_bidang_penelitian' => 'Sub Bidang Penelitian',
        'tujuan_sosial_ekonomi' => 'Tujuan Sosial Ekonomi', 'sub_tujuan_sosial_ekonomi' => 'SubTujuan Sosial Ekonomi',
        'email_peneliti_luar' => 'Email Peneliti luar (Ketua atau anggota)', 'keterangan' => 'Keterangan',
        'mitra' => 'Mitra', 'nama_mitra' => 'Nama Mitra', 'email_mitra' => 'Email Mitra', 'multi_disiplin' => 'Multi Disiplin',
        'evidence' => 'Evidence',
    ];

    public function read(string $path): array
    {
        $reader = new Reader;
        $rows = [];
        $summary = ['sheet' => 'Detail', 'campus' => self::CAMPUS, 'read' => 0, 'excluded_campus' => 0,
            'duplicates' => 0, 'missing' => [], 'invalid_values' => [], 'years' => [], 'roles' => []];
        try {
            $reader->open($path);
            $sheets = [];
            foreach ($reader->getSheets() as $index => $sheet) {
                $sheets[$this->normalize($sheet->getName())] = ['index' => $index, 'name' => $sheet->getName()];
            }
            $sheet = $sheets['malang'] ?? $sheets['detail'] ?? null;
            if ($sheet === null) {
                $this->invalid('Sheet MALANG atau Detail tidak ditemukan. Tidak ada data yang diubah.');
            }
            $summary['sheet'] = $sheet['name'];
            $reader->changeSheet($sheet['index']);
            $map = null;
            foreach ($reader as $number => $cells) {
                $cells = array_map(fn ($value) => $value instanceof DateTimeInterface ? $value->format('Y-m-d') : trim((string) $value), $cells);
                if (! array_filter($cells, fn ($cell) => $cell !== '')) {
                    continue;
                }
                if ($map === null) {
                    $headers = array_map($this->normalize(...), $cells);
                    $map = $this->headers($headers);

                    continue;
                }
                $summary['read']++;
                if ($this->normalize($cells[$map['lokasi_kampus']] ?? '') !== $this->normalize(self::CAMPUS)) {
                    $summary['excluded_campus']++;

                    continue;
                }
                $record = [];
                foreach ($map as $field => $index) {
                    $value = $index === null ? '' : ($cells[$index] ?? '');
                    $record[$field] = in_array($value, ['', '-'], true) ? null : $value;
                }
                foreach (['tahun_anggaran', 'kd_prop', 'kode_dosen_nim', 'nama', 'peran', 'judul'] as $required) {
                    if ($record[$required] === null) {
                        $this->invalid("Baris {$number}: ".self::HEADERS[$required].' wajib diisi.');
                    }
                }
                if (! preg_match('/^(20\d{2}|2100)$/', $record['tahun_anggaran'])) {
                    $this->invalid("Baris {$number}: Tahun Anggaran harus berupa tahun 2000–2100.");
                }
                $record['lokasi_kampus'] = self::CAMPUS;
                $record['kd_prop'] = strtoupper($record['kd_prop']);
                $record['kode_dosen_nim'] = strtoupper($record['kode_dosen_nim']);
                $record['budget_year'] = (int) $record['tahun_anggaran'];
                $record['project_key'] = hash('sha256', $record['tahun_anggaran'].'|'.$record['kd_prop']);
                $record['row_key'] = hash('sha256', $record['project_key'].'|'.$record['kode_dosen_nim'].'|'.$this->normalize($record['peran']));
                $issues = [];
                foreach (['dana_disetujui' => 'approved_amount', 'hibah_internal_binus' => 'internal_amount', 'hibah_per_prodi' => 'program_amount'] as $original => $target) {
                    $record[$target] = $this->amount($record[$original]);
                    if ($record[$original] !== null && $record[$target] === null) {
                        $issues[] = $original;
                    }
                }
                foreach (['tanggal_mulai' => 'starts_on', 'tanggal_selesai' => 'ends_on'] as $original => $target) {
                    $record[$target] = $this->date($record[$original]);
                    if ($record[$original] !== null && $record[$target] === null) {
                        $issues[] = $original;
                    }
                }
                if ($record['starts_on'] && $record['ends_on'] && $record['starts_on'] > $record['ends_on']) {
                    $issues[] = 'rentang_tanggal';
                }
                $sdgs = self::sdgs($record['sdgs']);
                if ($record['sdgs'] !== null && ! $sdgs) {
                    $issues[] = 'sdgs';
                }
                $record['sdg_numbers'] = json_encode($sdgs, JSON_THROW_ON_ERROR);
                $record['data_issues'] = json_encode($issues, JSON_THROW_ON_ERROR);
                $key = $record['row_key'];
                if (isset($rows[$key])) {
                    $previous = array_intersect_key($rows[$key], $record);
                    unset($previous['no']);
                    $comparison = $record;
                    unset($comparison['no']);
                    if ($previous !== $comparison) {
                        $this->invalid("Baris {$number}: identitas proyek/peneliti/peran duplikat dengan isi berbeda.");
                    }
                    $summary['duplicates']++;

                    continue;
                }
                foreach (['tanggal_mulai', 'tanggal_selesai', 'dana_disetujui', 'sdgs', 'program_hibah', 'skema',
                    'bidang_ilmu_by_qs_subject', 'keyword_sdgs', 'produk_yang_dihasilkan'] as $field) {
                    if ($record[$field] === null) {
                        $summary['missing'][$field] = ($summary['missing'][$field] ?? 0) + 1;
                    }
                }
                foreach ($issues as $issue) {
                    $summary['invalid_values'][$issue] = ($summary['invalid_values'][$issue] ?? 0) + 1;
                }
                $record['source_row'] = (int) $number;
                $record['source_payload'] = json_encode(array_combine($headers, array_pad(array_slice($cells, 0, count($headers)), count($headers), null)), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                $rows[$key] = $record;
                $summary['years'][$record['budget_year']] = ($summary['years'][$record['budget_year']] ?? 0) + 1;
                $summary['roles'][$record['peran']] = ($summary['roles'][$record['peran']] ?? 0) + 1;
            }
        } finally {
            $reader->close();
        }
        if (! $rows) {
            $this->invalid('Tidak ada baris pada sheet terpilih dengan Lokasi Kampus Binus @Malang. Tidak ada data yang diubah.');
        }
        $summary['selected'] = count($rows);
        $summary['projects'] = count(array_unique(array_column($rows, 'project_key')));
        $summary['researchers'] = count(array_unique(array_column($rows, 'kode_dosen_nim')));
        ksort($summary['years']);

        return ['records' => array_values($rows), 'summary' => $summary];
    }

    public function amount(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = preg_replace('/\s|^Rp\.?/iu', '', $value);
        if (preg_match('/^\d{1,3}(\.\d{3})+(,\d{1,2})?$/', $value)) {
            $value = str_replace(['.', ','], ['', '.'], $value);
        } elseif (preg_match('/^\d{1,3}(,\d{3})+(\.\d+)?$/', $value)) {
            $value = str_replace(',', '', $value);
        } elseif (preg_match('/^\d+,\d+$/', $value)) {
            $value = str_replace(',', '.', $value);
        }
        if (! preg_match('/^\d+(\.\d+)?$/', $value) || (float) $value >= 1.0e15) {
            return null;
        }

        return number_format((float) $value, 2, '.', '');
    }

    public static function sdgs(?string $value): array
    {
        preg_match_all('/(?:^|[,;\n])\s*(?:SDG\s*)?(1[0-7]|[1-9])(?=\s*[-.:]|\s*(?:[,;]|$))/i', $value ?? '', $matches);
        $numbers = array_values(array_unique(array_map('intval', $matches[1])));
        sort($numbers);

        return $numbers;
    }

    private function date(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (is_numeric($value) && (float) $value > 0 && (float) $value < 100000) {
            return (new DateTimeImmutable('1899-12-30'))->modify('+'.(int) $value.' days')->format('Y-m-d');
        }
        foreach (['!Y-m-d', '!j/n/Y', '!j-n-y', '!j-n-Y', '!j-M-y', '!j-M-Y'] as $format) {
            $date = DateTimeImmutable::createFromFormat($format, $value);
            $errors = DateTimeImmutable::getLastErrors();
            if ($date && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    private function headers(array $headers): array
    {
        $map = [];
        foreach (self::HEADERS as $field => $label) {
            $matches = array_keys($headers, $this->normalize($label), true);
            if (count($matches) > 1) {
                $this->invalid("Kolom {$label} duplikat pada sheet hibah terpilih.");
            }
            $map[$field] = $matches[0] ?? null;
        }
        foreach (['tahun_anggaran', 'kd_prop', 'kode_dosen_nim', 'nama', 'peran', 'judul', 'lokasi_kampus'] as $field) {
            if ($map[$field] === null) {
                $this->invalid('Kolom '.self::HEADERS[$field].' tidak ditemukan pada sheet hibah terpilih.');
            }
        }

        return $map;
    }

    private function normalize(string $value): string
    {
        return strtolower(trim(preg_replace('/\s+/u', ' ', str_replace("\xC2\xA0", ' ', $value))));
    }

    private function invalid(string $message): never
    {
        throw ValidationException::withMessages(['research_file' => $message]);
    }
}
