<?php

namespace App\Services\Publications;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PublicationDashboard
{
    public function __construct(private PublicationScore $scoring) {}

    public function data(): array
    {
        $master = DB::table('database_dosen')->orderBy('nama_dosen')->get()->keyBy('kode_dosen');
        $details = $this->rows('database_dosen_new')->keyBy('kode_dosen');
        // Resolve legacy abbreviations through matching lecturer codes in both masters.
        $programAliases = $master->groupBy('jurusan_dosen')->map(function ($members) use ($details) {
            $names = $members->map(fn ($member) => $details->get($member->kode_dosen)->nama_gugus_binaan ?? null)->filter()->unique();

            return $names->count() === 1 ? $names->first() : null;
        });
        $candidates = DB::table('rectorate_dosen')->whereIn('submitted', RawPublicationReader::SUBMITTED)
            ->where(fn ($q) => $q->whereRaw('UPPER(TRIM(kampus)) = ?', ['MALANG'])->orWhereNull('kampus')->orWhere('kampus', ''))
            ->get();
        $publications = $candidates->filter(function ($row) use ($master, $details) {
            // Old FM imports filtered MALANG but did not persist the campus column.
            if (trim($row->kampus ?? '') !== '') {
                return true;
            }
            $campus = $details->get($row->kode_dosen)->campus ?? '';

            return $master->has($row->kode_dosen) && str_contains(strtolower($campus), 'malang');
        });
        $snapshots = [];
        $selected = collect();
        $imports = $this->rows('publication_imports');
        $kpiEntries = $this->rows('publication_kpi_entries')->groupBy('publication_import_id');
        $selectedKpi = collect();
        foreach ($publications->groupBy('year')->sortKeys() as $year => $rows) {
            $latest = $rows->sortByDesc(fn ($r) => (int) $r->month * 10 + (int) $r->period)->first();
            $snapshotRows = $rows->filter(fn ($r) => (int) $r->month === (int) $latest->month && (int) $r->period === (int) $latest->period);
            $current = $snapshotRows->sortByDesc('updated_at')->unique(fn ($r) => $r->request_code.'|'.$r->kode_dosen)->values();
            $candidateCount = $candidates->where('year', $year)->where('month', $latest->month)->where('period', $latest->period)->count();
            $batch = $imports->where('year', $year)->where('month', $latest->month)->where('period', $latest->period)->first();
            $batchKpi = $kpiEntries->get($batch->id ?? null, collect());
            $batchSummary = $batch ? json_decode($batch->summary, true) : [];
            $selectedKpi = $selectedKpi->concat($batchKpi->map(function ($entry) use ($year) {
                $entry->year = $year;

                return $entry;
            }));
            $snapshots[(string) $year] = ['year' => (int) $year, 'month' => (int) $latest->month,
                'period' => (int) $latest->period, 'rows' => $current->count(),
                'has_kpi' => $batchKpi->isNotEmpty(), 'kpi_lecturers' => $batchKpi->count(),
                'source_sheet' => $batchSummary['sheet'] ?? 'Raw', 'filename' => $batch->filename ?? null,
                'warnings' => $batchSummary['warnings'] ?? [],
                'kpi_differences' => $batchSummary['kpi']['differences'] ?? [],
                'duplicates' => $snapshotRows->count() - $current->count(),
                'unverified_campus' => $candidateCount - $snapshotRows->count(),
                'legacy_campus' => $current->filter(fn ($r) => ! trim($r->kampus ?? ''))->count()];
            $selected = $selected->concat($current);
        }
        // Normalize weights from the original fractional value using the existing import policy.
        $selected = $selected->map(function ($row) {
            if (is_numeric($row->bobot_asli)) {
                $row->bobot = RawPublicationReader::adjustedWeight((array) $row);
            }

            return $row;
        });
        $byLecturer = $selected->groupBy('kode_dosen');
        $kpiByLecturer = $selectedKpi->groupBy('kode_dosen');
        $profiles = $this->rows('kpi_fm_profiles')->groupBy('kode_dosen');
        $priorities = $this->rows('kpi_research_priorities')->where('year', 2027)->values();
        $research = $this->rows('researchs');
        $grants = app(\App\Services\Research\RectorateResearchRepository::class)->activeRows()
            ->filter(fn ($row) => strtoupper($row->kategori_fm_eksternal_mahasiswa ?? '') === 'FM');
        $faculty = [];
        $codes = $master->keys()->merge($details->keys())->merge($byLecturer->keys())->merge($kpiByLecturer->keys())->unique();
        foreach ($codes as $code) {
            $old = $master->get($code);
            $detail = $details->get($code);
            $papers = $byLecturer->get($code, collect());
            $lecturerKpi = $kpiByLecturer->get($code, collect());
            $latestKpi = $lecturerKpi->sortByDesc('year')->first();
            $latest = $papers->sortByDesc(fn ($r) => (int) $r->year * 100 + (int) $r->month)->first();
            if (! $old && ! $papers->count() && ! $latestKpi && ! str_contains(strtolower($detail->campus ?? ''), 'malang')) {
                continue;
            }
            $education = $this->scoring->education($detail->pendidikan ?? null)
                ?? $this->scoring->education($old->pendidikan_dosen ?? null) ?? $this->scoring->education($latest->pendidikan ?? null);
            $rank = $this->scoring->rank($detail->jja ?? null)
                ?? $this->scoring->rank($old->jja_dosen ?? null) ?? $this->scoring->rank($latest->jja ?? null);
            $type = $this->scoring->faculty($detail->tipe_faculty ?? null) ?? $this->scoring->faculty($old->ft_dosen ?? null);
            $rule = $this->scoring->rule($type, $rank, $education);
            $annual = [];
            foreach (array_keys($snapshots) as $year) {
                $annualPapers = $papers->where('year', $year);
                $profile = $profiles->get($code, collect())->firstWhere('year', $year);
                $entry = $lecturerKpi->firstWhere('year', $year);
                $workbook = PublicationWorkbookMetrics::summarize($annualPapers, $entry);
                $systemScore = $this->scoring->calculate($code, $rule, $annualPapers);
                $annual[$year] = ['score' => $snapshots[$year]['has_kpi'] ? $workbook['score'] : $systemScore,
                    'system_score' => $systemScore, 'workbook' => $workbook,
                    'rows' => $annualPapers->count(), 'scopus' => $annualPapers->where('submitted', 'Scopus FM')->count(),
                    'non_scopus' => $annualPapers->where('submitted', 'Non Scopus FM')->count(),
                    'weight' => $annualPapers->sum('bobot'), 'original_weight' => $annualPapers->sum('bobot_asli'),
                    'cluster' => $profile->cluster ?? null, 'mentor' => $profile->mentor_label ?? null];
            }
            $faculty[] = ['code' => $code, 'name' => $detail->nama_dosen ?? $old->nama_dosen ?? $latestKpi->name ?? $latest->fm_author ?? $code,
                'program_code' => $old->jurusan_dosen ?? null,
                'program' => $detail->nama_gugus_binaan ?? $programAliases->get($old->jurusan_dosen ?? '') ?? $old->jurusan_dosen ?? $latestKpi->program ?? $latest->dept ?? 'Belum tercatat',
                'faculty' => $type, 'faculty_detail' => $detail->tipe_faculty ?? $type,
                'education' => $education, 'rank' => $rank, 'rule' => $rule,
                'annual' => $annual, 'profile_source' => $detail ? 'database_dosen_new' : ($old ? 'database_dosen + Raw' : ($latestKpi ? 'KPI' : 'Raw'))];
        }
        $years = array_map('strval', array_keys($snapshots));
        $latestYear = $years ? end($years) : null;
        $issues = [
            ['feature' => 'KPI workbook dan asumsi positif', 'detail' => 'Bobot Scopus dan Non Scopus masing-masing mengambil MAX(Rectorate, RTTO) per dosen. Rectorate memakai bobot asli. Score KPI mengikuti Score KPI RTTO dari sheet KPI, termasuk nilai nol. Pilihan Perhitungan sistem tetap tersedia secara terpisah. Data RTTO yang kosong tidak dianggap nol.'],
            ['feature' => 'FIRST AUTHOR, TITLE & BOBOT, PIVOT', 'detail' => 'Dihitung ulang dari MALANG (atau Raw jika MALANG tidak ada). Jumlah judul mengikuti Count of Title, yaitu kontribusi dosen–publikasi, bukan judul unik lintas penulis. Rekap prodi menggunakan Prodi KPI dari sumber. First author mensyaratkan Scopus, Scopus FM, dan Y. PIVOT menjumlahkan bobot asli berdasarkan Tipe Publikasi.'],
            ['feature' => 'Riwayat KPI 2023–2024', 'detail' => 'Tahun hanya muncul jika memiliki snapshot publikasi. Angka contoh dalam HTML tidak digunakan.'],
            ['feature' => 'Skor perhitungan sistem', 'detail' => 'Pilihan Perhitungan sistem memakai aturan operasional saat ini, bobot penyesuaian Scopus, dan profil master terbaru. Bukan skor historis yang sudah disahkan. Dalam pilihan ini, dosen tanpa baris publikasi atau profil matriks lengkap ditampilkan tanpa skor.'],
            ['feature' => 'Status publikasi', 'detail' => 'Semua status yang lolos filter Submitted tetap dihitung sesuai import lama, termasuk Reviewed dan Accepted. Submitted adalah kategori pelaporan, bukan bukti terbit.'],
            ['feature' => 'Cluster dan mentor', 'detail' => 'Diambil hanya dari penetapan per tahun. Huruf (A/B/C) pada faculty type tidak dianggap sebagai cluster riset.'],
            ['feature' => 'Peran hibah', 'detail' => 'Peran ketua/anggota hanya bersumber dari rectorate_research. Katalog researchs menunjukkan keterlibatan riset tanpa penetapan peran.'],
            ['feature' => 'Prioritas 2027 dan SDG', 'detail' => 'Diambil dari rencana eksplisit tahun 2027. Judul/keyword riset historis tidak dijadikan rencana atau SDG otomatis.'],
        ];
        if (collect($snapshots)->sum('legacy_campus')) {
            $issues[] = ['feature' => 'Kampus pada import lama', 'detail' => 'Sebagian baris lama tidak menyimpan Kampus. Baris tersebut dipakai hanya jika kode dosen ada di master Malang; jumlahnya ditampilkan pada catatan snapshot.'];
        }

        return ['faculty' => $faculty, 'years' => $years, 'default_year' => $latestYear, 'snapshots' => $snapshots,
            'publications' => $selected->map(fn ($r) => collect((array) $r)->only(['kode_dosen', 'request_code', 'title', 'submitted', 'status', 'jenis', 'tipe_publikasi', 'first_author', 'dept', 'quartile_jurnal', 'bobot', 'bobot_asli', 'source_title', 'publisher', 'year', 'tanggal_pelaporan', 'notes', 'prodi_kpi'])->all())->values(),
            'research' => $research->map(fn ($r) => ['id' => $r->ID, 'code' => $r->kode_dosen, 'title' => $r->title, 'year' => $r->budget_year,
                'fund' => $r->source_of_fund, 'researchers' => json_decode($r->researcher ?? '[]', true) ?: [],
                'contract' => $r->contract_number])->values(),
            'grants' => $grants->map(fn ($r) => ['code' => $r->kode_dosen_nim, 'year' => $r->tahun_anggaran,
                'proposal' => $r->kd_prop, 'role' => $r->peran, 'title' => $r->judul,
                'research_kpi' => $r->pengakuan_kpi_research_program])->unique(fn ($r) => implode('|', [$r['code'], $r['year'], $r['proposal'], $r['role']]))->values(),
            'priorities' => $priorities->map(fn ($r) => ['code' => $r->kode_dosen, 'year' => $r->year,
                'topic' => $r->topic, 'sdgs' => json_decode($r->sdgs ?? '[]', true) ?: [], 'source' => $r->source])->values(),
            'imports' => $this->rows('publication_imports')->sortByDesc('updated_at')->map(fn ($r) => ['filename' => $r->filename,
                'year' => $r->year, 'month' => $r->month, 'updated_at' => $r->updated_at, 'summary' => json_decode($r->summary, true)])->values(),
            'issues' => $issues, 'planning_ready' => Schema::hasTable('kpi_fm_profiles') && Schema::hasTable('kpi_research_priorities')];
    }

    private function rows(string $table): Collection
    {
        return Schema::hasTable($table) ? DB::table($table)->get() : collect();
    }
}
