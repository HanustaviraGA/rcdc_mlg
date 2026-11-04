<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\CreatesWorkbook;
use Tests\TestCase;

class RectorateSupportingSheetsTest extends TestCase
{
    use CreatesWorkbook;

    private const FM_HEADERS = ['RequestCode', 'Kode Dosen', 'Kampus', 'Submitted', 'Bobot', 'Title', 'Jenis', 'Tipe Publikasi', 'St2026'];

    private const FM_ROW = ['R1', 'D001', 'MALANG', 'Scopus FM', '1', 'Mapped publication', 'Jurnal', 'Scopus', 'Accepted'];

    private const KPI_MAPPING = [
        'kode_dosen' => 0, 'name' => 1, 'program' => 2, 'academic_rank' => 3, 'faculty_type' => 4,
        'rtto_non_scopus' => 5, 'rtto_scopus' => 6, 'rtto_score' => 7, 'score' => 8,
    ];

    private const UNIT = 'Computer Science (BINUS @Malang)';

    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Run PHP with -d extension=pdo_sqlite.');
        }
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Schema::create('database_dosen', function (Blueprint $table) {
            $table->string('kode_dosen')->primary();
        });
        DB::table('database_dosen')->insert(['kode_dosen' => 'D001']);
        Schema::create('rectorate_dosen', function (Blueprint $table) {
            $table->string('id_rectorate')->primary();
            foreach ([
                'request_code', 'author', 'fm_author', 'kode_dosen', 'jja', 'pendidikan', 'type', 's_f', 'dept',
                'kampus', 'first_author', 'sumber_paper', 'submitted', 'status', 'jenis', 'tipe_publikasi', 'title',
                'scopus_year', 'source_title', 'quartile_jurnal', 'publisher', 'year', 'month', 'period',
            ] as $column) {
                $table->string($column)->nullable();
            }
            $table->double('bobot_asli')->nullable();
            $table->double('bobot')->nullable();
            $table->timestamps();
        });
        (require database_path('migrations/2026_09_08_000000_add_publication_import_tracking.php'))->up();
        (require database_path('migrations/2026_09_23_000000_create_publication_kpi_entries_table.php'))->up();
        (require database_path('migrations/2026_09_24_000100_create_student_publication_imports.php'))->up();
        $this->actingAs((new User)->forceFill(['id' => 1]));
    }

    protected function tearDown(): void
    {
        $this->removeWorkbooks();
        parent::tearDown();
    }

    public function test_fm_preview_lists_every_supporting_sheet_with_its_own_columns(): void
    {
        $file = $this->fmWorkbook();
        $preview = fn (array $extra = []) => $this->postJson(route('importrectorate.preview-columns'), [
            'file' => $this->upload($file), 'type' => 'fm_file', 'year' => 2026,
        ] + $extra);

        $response = $preview()->assertOk()
            ->assertJsonPath('main_role.label', 'Publikasi FM')->assertJsonPath('main_role.expected', 'MALANG atau Raw')
            ->assertJsonPath('extra_roles.kpi.sheet', 'KPI')
            ->assertJsonPath('extra_roles.realization.sheet', null)
            ->assertJsonPath('extra_roles.scoring.sheet', 'Scoring PI')
            ->assertJsonPath('extra_roles.kpi.mapping_config.required.rtto_score', 'Score KPI RTTO')
            ->assertJsonPath('extra_roles.kpi.suggested_mapping.kode_dosen', 0)
            ->assertJsonPath('extra_roles.scoring.suggested_mapping', ['unit' => 0, 'score_1' => 1]);
        $this->assertSame(['kpi', 'realization', 'scoring'], array_keys($response->json('extra_roles')));

        // Another sheet, or none, for a supporting role; the others keep their defaults.
        $preview(['extra_sheets' => json_encode(['kpi' => 'Rekap KPI', 'realization' => 'Target Unit', 'scoring' => '']), 'sheet' => 'Publikasi'])
            ->assertOk()->assertJsonPath('file_info.sheet', 'Publikasi')
            ->assertJsonPath('extra_roles.kpi.sheet', 'Rekap KPI')->assertJsonPath('extra_roles.kpi.header_row', 1)
            ->assertJsonPath('extra_roles.kpi.headers.0', 'NIP Dosen')->assertJsonPath('extra_roles.kpi.preview_data.0.0', 'd001')
            ->assertJsonPath('extra_roles.realization.sheet', 'Target Unit')
            ->assertJsonPath('extra_roles.realization.suggested_mapping.unit', 0)
            ->assertJsonPath('extra_roles.scoring.sheet', null)->assertJsonPath('extra_roles.scoring.headers', []);

        foreach ([['kpi' => 'Missing'], ['list' => 'KPI'], '[]', '{broken'] as $invalid) {
            $preview(['extra_sheets' => is_string($invalid) ? $invalid : json_encode($invalid)])
                ->assertUnprocessable()->assertJsonValidationErrors('extra_sheets');
        }
    }

    public function test_fm_upload_reads_kpi_and_realization_from_the_confirmed_sheets_and_columns(): void
    {
        $this->uploadFm([
            'kpi' => ['sheet' => 'Rekap KPI', 'column_mapping' => self::KPI_MAPPING],
            'realization' => ['sheet' => 'Target Unit', 'column_mapping' => ['unit' => 0, 'target' => 1, 'realization' => 2]],
            'scoring' => ['sheet' => null],
        ])->assertOk()
            ->assertJsonPath('summaries.fm_file.kpi.sheet', 'Rekap KPI')->assertJsonPath('summaries.fm_file.kpi.lecturers', 1)
            ->assertJsonPath('summaries.fm_file.supporting_sheets', ['kpi' => 'Rekap KPI', 'realization' => 'Target Unit', 'scoring' => null])
            ->assertJsonPath('summaries.fm_file.report.realization.CS', ['target' => 35, 'realization' => 2, 'unit' => self::UNIT])
            ->assertJsonPath('summaries.fm_file.report.ranges', [])
            ->assertJsonPath('summaries.fm_file.kpi.differences.0.column', 'skor excel');

        // Values come from the mapped sheet, not from the sheet named KPI.
        $entry = DB::table('publication_kpi_entries')->sole();
        $this->assertSame(['D001', 'Mapped lecturer', 'CS', 2.5, 4.0], [$entry->kode_dosen, $entry->name, $entry->program, (float) $entry->rtto_scopus, (float) $entry->rtto_score]);
    }

    public function test_fm_upload_can_leave_out_a_kpi_sheet_that_the_file_contains(): void
    {
        $this->uploadFm(['kpi' => ['sheet' => null]])->assertOk()
            ->assertJsonPath('summaries.fm_file.kpi.available', false)
            // Roles left out of the selection keep their name-based defaults.
            ->assertJsonPath('summaries.fm_file.report.ranges.CS.1', [0, 22.99]);
        $this->assertSame(0, DB::table('publication_kpi_entries')->count());
        $this->assertSame(1, DB::table('rectorate_dosen')->count());
    }

    public function test_invalid_supporting_sheet_selections_are_rejected_without_writes(): void
    {
        $this->uploadFm(['kpi' => ['sheet' => 'Rekap KPI', 'column_mapping' => array_diff_key(self::KPI_MAPPING, ['rtto_score' => 1])]])
            ->assertUnprocessable()->assertJsonValidationErrors('rectorate')
            ->assertJsonFragment(['rectorate' => ["Sheet KPI (Rekap KPI): Kolom 'Score KPI RTTO' wajib dipetakan Data sebelumnya tetap tersimpan."]]);
        $this->uploadFm(['kpi' => ['sheet' => 'Missing', 'column_mapping' => self::KPI_MAPPING]])
            ->assertUnprocessable()->assertJsonValidationErrors('rectorate');
        foreach ([json_encode(['list' => ['sheet' => 'KPI']]), json_encode(['kpi' => 'KPI']), '[1]'] as $invalid) {
            $this->uploadFm($invalid)->assertUnprocessable()->assertJsonValidationErrors('extra_sheets.fm_file');
        }
        $this->assertSame(0, DB::table('publication_imports')->count());
        $this->assertSame(0, DB::table('rectorate_dosen')->count());
    }

    public function test_mhs_preview_and_upload_use_the_confirmed_list_and_title_sheets(): void
    {
        $file = $this->mhsWorkbook();
        $this->postJson(route('importrectorate.preview-columns'), [
            'file' => $this->upload($file), 'type' => 'mhs_file', 'year' => 2026,
            'extra_sheets' => json_encode(['list' => 'Daftar Scopus', 'title' => 'Rekap Prodi']),
        ])->assertOk()
            ->assertJsonPath('main_role.label', 'Publikasi MHS')
            ->assertJsonPath('extra_roles.list.sheet', 'Daftar Scopus')
            // The TITLE recap starts at the row with Tipe Publikasi followed by Scopus.
            ->assertJsonPath('extra_roles.title.header_row', 2)
            ->assertJsonPath('extra_roles.title.suggested_mapping', ['label' => 1, 'count' => 2])
            ->assertJsonPath('extra_roles.realization.sheet', 'Realization Template');

        $summary = $this->postJson(route('importrectorate.upload'), [
            'mhs_file' => $this->upload($file), 'year' => 2026, 'month' => 9,
            'extra_sheets' => ['mhs_file' => json_encode([
                'list' => ['sheet' => 'Daftar Scopus', 'column_mapping' => ['program' => 0, 'name' => 1, 'title' => 2]],
                'title' => ['sheet' => 'Rekap Prodi', 'column_mapping' => ['label' => 1, 'count' => 2]],
            ])],
        ])->assertOk()->json('summaries.mhs_file');

        $this->assertSame(['CS' => 1, 'BINUS' => 1], $summary['title_counts']);
        $this->assertSame(1, $summary['list_count']);
        $this->assertSame(['list' => 'Daftar Scopus', 'title' => 'Rekap Prodi'], $summary['supporting_sheets']);
        $this->assertSame([['program' => self::UNIT, 'name' => 'Student', 'title' => 'Article']],
            json_decode(DB::table('student_publication_imports')->value('publication_list'), true));
        $this->assertStringContainsString('Realization Template 2', implode(' ', $summary['warnings']));

        $summary = $this->postJson(route('importrectorate.upload'), [
            'mhs_file' => $this->upload($file), 'year' => 2026, 'month' => 9,
            'extra_sheets' => ['mhs_file' => json_encode(['list' => ['sheet' => null], 'title' => ['sheet' => '']])],
        ])->assertOk()->json('summaries.mhs_file');
        $this->assertSame([], $summary['title_counts']);
        $this->assertContains('Sheet LIST tidak digunakan; daftar mahasiswa dibentuk dari sheet publikasi.', $summary['warnings']);
    }

    public function test_hibah_has_only_its_main_sheet(): void
    {
        $file = $this->makeWorkbook(['MALANG' => [['Tahun Anggaran', 'Kd. Prop', 'Kode Dosen/NIM', 'Nama', 'Peran', 'JUDUL', 'Lokasi Kampus']]]);
        $this->postJson(route('importrectorate.preview-columns'), ['file' => $this->upload($file), 'type' => 'research_file'])
            ->assertOk()->assertJsonPath('main_role.expected', 'MALANG atau Detail')->assertJsonPath('extra_roles', []);
        $this->postJson(route('importrectorate.upload'), [
            'research_file' => $this->upload($file), 'year' => 2026, 'month' => 9,
            'extra_sheets' => ['research_file' => json_encode(['kpi' => ['sheet' => 'MALANG']])],
        ])->assertUnprocessable()->assertJsonValidationErrors('extra_sheets.research_file');
    }

    private function uploadFm(array|string $extraSheets)
    {
        return $this->postJson(route('importrectorate.upload'), [
            'fm_file' => $this->upload($this->fmWorkbook()), 'year' => 2026, 'month' => 9, 'sheet' => ['fm_file' => 'Publikasi'],
            'extra_sheets' => ['fm_file' => is_string($extraSheets) ? $extraSheets : json_encode($extraSheets)],
        ]);
    }

    private function fmWorkbook(): string
    {
        return $this->makeWorkbook([
            'Publikasi' => [self::FM_HEADERS, self::FM_ROW],
            'KPI' => [
                ['Kode Dosen', 'Nama Dosen', 'Jurusan Binaan', 'JJA', 'Faculty Type', 'Non Scopus RTTO', 'Scopus RTTO', 'Score KPI RTTO'],
                ['D001', 'Named sheet', 'Wrong', 'AA', 'FM', 9, 9, 1],
            ],
            'Rekap KPI' => [
                ['NIP Dosen', 'Nama', 'Prodi Binaan', 'Jabatan', 'Tipe', 'NS RTTO', 'S RTTO', 'Skor RTTO', 'Skor Excel'],
                ['d001', 'Mapped lecturer', 'CS', 'L', 'FM', '1', '2,5', '4', '9'],
            ],
            'Target Unit' => [['Unit', 'Target 2026', 'Capaian'], [self::UNIT, 35, 2]],
            'Scoring PI' => [['Unit Name', 'Score 1'], [self::UNIT, '0,00 - 22,99']],
        ]);
    }

    private function mhsWorkbook(): string
    {
        $headers = ['RequestCode', 'FM Author', 'Kode Dosen', 'Kampus', 'Submitted', 'Tipe Publikasi', 'Title', 'Prodi MHS', 'First Author'];

        return $this->makeWorkbook([
            'MALANG' => [$headers, ['R1', 'Student', 'Mahasiswa', 'MALANG', 'Scopus Mahasiswa', 'Scopus', 'Article', self::UNIT, 'Y']],
            // Sheets with the default names but other content, which the confirmed choice replaces.
            'LIST' => [['Wrong header']],
            'TITLE' => [['Tipe Publikasi', 'Scopus'], ['Wrong', 7]],
            'Daftar Scopus' => [['Program Studi Mahasiswa', 'Penulis', 'Judul Artikel'], [self::UNIT, 'Student', 'Article']],
            'Rekap Prodi' => [['Rekap Scopus'], ['', 'Tipe Publikasi', 'Scopus'], ['', 'Row Labels', 'Count of Title'], ['', self::UNIT, 1], ['', 'Grand Total', 1]],
            'Realization Template' => [['Unit Name', 'Target', 'Realization'], [self::UNIT, 35, 2]],
        ]);
    }

    private function upload(string $path): UploadedFile
    {
        return new UploadedFile($path, 'report.xlsx', null, null, true);
    }
}
