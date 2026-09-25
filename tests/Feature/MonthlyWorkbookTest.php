<?php

namespace Tests\Feature;

use App\Models\DataDosen;
use App\Services\Lecturers\LecturerWorkbook;
use App\Services\Publications\StudentPublicationImporter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\Support\CreatesWorkbook;
use Tests\TestCase;

class MonthlyWorkbookTest extends TestCase
{
    use CreatesWorkbook;

    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Run PHP with -d extension=pdo_sqlite.');
        }
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        (require database_path('migrations/2026_09_24_000100_create_student_publication_imports.php'))->up();
        Schema::create('database_dosen_new', function (Blueprint $table) {
            $table->string('kode_dosen')->primary();
            foreach (array_diff(array_keys(LecturerWorkbook::HEADERS), ['kode_dosen']) as $field) {
                $table->text($field)->nullable();
            }
            $table->timestamps();
        });
        (require database_path('migrations/2026_09_25_000000_add_visibility_and_soft_deletes_to_database_dosen_new.php'))->up();
    }

    protected function tearDown(): void
    {
        $this->removeWorkbooks();
        parent::tearDown();
    }

    public function test_lecturer_mapping_preserves_missing_columns_and_phone_leading_zero(): void
    {
        DataDosen::create(['kode_dosen' => 'D1', 'nama_dosen' => 'Old', 'agama' => 'Preserved']);
        $path = $this->makeWorkbook(['Data All' => [['Kode Dosen', 'Nama Dosen'], ['OTHER', 'Not Malang']], ' MALANG ' => [
            [' no hp2 ', 'Nama Dosen', 'Kode Dosen', 'Campus'], ['081234567890', 'New', 'd1', 'Binus Malang'],
        ]]);
        $summary = app(LecturerWorkbook::class)->import($path);
        $this->assertSame(4, $summary['mapped_columns']);
        $this->assertSame(1, $summary['selected']);
        $this->assertSame('081234567890', DataDosen::find('D1')->no_hp_2);
        $this->assertSame('Preserved', DataDosen::find('D1')->agama);
        $this->assertSame(1, DataDosen::count());
    }

    public function test_invalid_later_lecturer_row_prevents_partial_update(): void
    {
        DataDosen::create(['kode_dosen' => 'D1', 'nama_dosen' => 'Original']);
        try {
            app(LecturerWorkbook::class)->import($this->makeWorkbook(['Malang' => [
                ['Kode Dosen', 'Nama Dosen'], ['D1', 'Changed'], ['D1', 'Conflicting'],
            ]]));
            $this->fail('Conflicting lecturer must fail.');
        } catch (ValidationException) {
            $this->assertSame('Original', DataDosen::find('D1')->nama_dosen);
        }
    }

    public function test_reimport_keeps_hidden_and_deleted_profiles_without_duplicate_codes(): void
    {
        DataDosen::create(['kode_dosen' => 'D1', 'nama_dosen' => 'Hidden'])->forceFill(['is_hidden' => true])->save();
        DataDosen::create(['kode_dosen' => 'D2', 'nama_dosen' => 'Archived'])->delete();
        $summary = app(LecturerWorkbook::class)->import($this->makeWorkbook(['Malang' => [
            ['Kode Dosen', 'Nama Dosen'], ['D1', 'Updated hidden'], ['D2', 'Updated archive'], ['D3', 'New lecturer'],
        ]]));

        $this->assertSame(1, $summary['created']);
        $this->assertSame(2, $summary['updated']);
        $this->assertTrue(DataDosen::find('D1')->is_hidden);
        $this->assertTrue(DataDosen::withTrashed()->find('D2')->trashed());
        $this->assertSame('Updated archive', DataDosen::withTrashed()->find('D2')->nama_dosen);
        $this->assertSame(3, DataDosen::withTrashed()->count());
        $this->assertSame(['D3'], DataDosen::visibleOnWebsite()->pluck('kode_dosen')->all());
    }

    public function test_student_import_uses_three_sheets_and_keeps_months_independent(): void
    {
        $path = $this->studentWorkbook();
        $service = app(StudentPublicationImporter::class);
        $summary = $service->import($path, 'mhs.xlsx', 2026, 9);
        $this->assertSame(2, $summary['selected']);
        $this->assertSame(1, $summary['scopus']);
        $this->assertSame(1, $summary['title_counts']['BINUS']);
        $this->assertSame(1, $summary['list_count']);
        $this->assertStringContainsString('Realization Template 2', implode(' ', $summary['warnings']));
        $service->import($path, 'august.xlsx', 2026, 8);
        $service->import($path, 'september-reupload.xlsx', 2026, 9);
        $this->assertSame(2, DB::table('student_publication_imports')->count());
        $this->assertSame('september-reupload.xlsx', DB::table('student_publication_imports')->where('month', 9)->value('filename'));
    }

    public function test_invalid_student_upload_leaves_existing_month_unchanged(): void
    {
        $service = app(StudentPublicationImporter::class);
        $service->import($this->studentWorkbook(), 'valid.xlsx', 2026, 9);
        try {
            $service->import($this->makeWorkbook(['MALANG' => [['Wrong header']]]), 'bad.xlsx', 2026, 9);
            $this->fail('Invalid workbook must fail.');
        } catch (ValidationException) {
            $this->assertSame('valid.xlsx', DB::table('student_publication_imports')->value('filename'));
        }
    }

    private function studentWorkbook(): string
    {
        $headers = ['RequestCode', 'FM Author', 'Kode Dosen', 'Kampus', 'Submitted', 'Tipe Publikasi', 'Title', 'Prodi MHS', 'First Author', 'St2024'];

        return $this->makeWorkbook([
            'MALANG' => [$headers, ['R1', 'Student', 'Mahasiswa', 'MALANG', 'Scopus Mahasiswa', 'Scopus', 'Article', 'Computer Science (BINUS @Malang)', 'Y', 'Accepted'],
                ['R2', 'Student', 'Mahasiswa', 'MALANG', 'Non Scopus Mahasiswa', 'Nscopus', 'Non Scopus', 'Computer Science (BINUS @Malang)', 'Y', 'Accepted']],
            'TITLE' => [['Tipe Publikasi', 'Scopus'], ['Row Labels', 'Count of Title'], ['Computer Science (BINUS @Malang)', 1], ['Grand Total', 1]],
            'LIST' => [['Prodi', 'MHS Author', 'Title'], ['Computer Science (BINUS @Malang)', 'Student', 'Article']],
            'Realization Template' => [['Unit Name', 'Target', 'Realization'], ['Computer Science (BINUS @Malang)', 35, 2]],
            'Scoring PI' => [['Unit Name', 'Score 1'], ['Computer Science (BINUS @Malang)', '0,00 - 22,99']],
        ]);
    }
}
