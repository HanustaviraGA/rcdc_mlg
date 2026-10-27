<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Publications\StudentPublicationImporter;
use App\Services\Research\RectorateResearchImporter;
use App\Services\Research\RectorateResearchReader;
use App\Services\Research\ResearchGallery;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\Support\CreatesWorkbook;
use Tests\TestCase;

class RectorateOptionalMappingTest extends TestCase
{
    use CreatesWorkbook;

    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Run PHP with -d extension=pdo_sqlite.');
        }
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        (require database_path('migrations/2026_09_24_000100_create_student_publication_imports.php'))->up();
        (require database_path('migrations/2026_09_08_000200_add_rectorate_research_imports.php'))->up();
        (require database_path('migrations/2026_09_24_000000_add_monthly_research_snapshots.php'))->up();
    }

    protected function tearDown(): void
    {
        $this->removeWorkbooks();
        parent::tearDown();
    }

    public function test_separate_student_and_research_uploads_preserve_other_sources_for_the_same_month(): void
    {
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $studentValues = $this->studentValues();
        $researchValues = $this->researchValues();
        $studentPath = $this->arbitraryWorkbook('MALANG', $studentValues);
        $researchPath = $this->arbitraryWorkbook('Detail', $researchValues);
        $upload = fn (string $type, string $path, string $filename, array $values) => $this->postJson(route('importrectorate.upload'), [
            'year' => 2026, 'month' => 9,
            $type => new UploadedFile($path, $filename, null, null, true),
            'column_mapping' => [$type => json_encode(array_flip(array_keys($values)), JSON_THROW_ON_ERROR)],
        ]);

        $response = $upload('mhs_file', $studentPath, 'students.xlsx', $studentValues)
            ->assertOk()->assertJsonPath('summaries.mhs_file.selected', 1);
        $this->assertSame(['mhs_file'], array_keys($response->json('summaries')));
        $this->assertSame(0, DB::table('research_imports')->count());
        $students = DB::table('student_publication_imports')->sole();

        $response = $upload('research_file', $researchPath, 'research.xlsx', $researchValues)
            ->assertOk()->assertJsonPath('summaries.research_file.selected', 1);
        $this->assertSame(['research_file'], array_keys($response->json('summaries')));
        $this->assertEquals($students, DB::table('student_publication_imports')->sole());
        $research = DB::table('research_imports')->sole();
        $researchRows = DB::table('rectorate_research')->get();

        $invalidPath = $this->makeWorkbook(['MALANG' => [['Wrong header']]]);
        $upload('mhs_file', $invalidPath, 'invalid.xlsx', $studentValues)->assertUnprocessable();
        $this->assertEquals($students, DB::table('student_publication_imports')->sole());
        $this->assertEquals($research, DB::table('research_imports')->sole());

        $response = $upload('mhs_file', $studentPath, 'students-reupload.xlsx', $studentValues)->assertOk();
        $this->assertSame('students-reupload.xlsx', DB::table('student_publication_imports')->sole()->filename);
        $this->assertSame(1, DB::table('student_publication_imports')->count());
        $this->assertEquals($research, DB::table('research_imports')->sole());
        $this->assertEquals($researchRows, DB::table('rectorate_research')->get());
        $this->assertStringContainsString('students-reupload.xlsx', $response->json('history_html'));
        $this->assertStringContainsString('research.xlsx', $response->json('history_html'));
    }

    public function test_student_required_only_arbitrary_headers_are_mapped_and_persisted(): void
    {
        $values = $this->studentValues();
        $path = $this->arbitraryWorkbook('MALANG', $values);
        $summary = app(StudentPublicationImporter::class)->import($path, 'students.xlsx', 2026, 9,
            columnMapping: array_flip(array_keys($values)));

        $this->assertSame(1, $summary['selected']);
        $this->assertSame(1, $summary['scopus']);
        $this->assertSame(1, $summary['list_count']);
        $record = $this->studentRecord();
        foreach ($values as $field => $value) {
            $this->assertSame($value, $record[$field]);
        }
        $this->assertSame('', $record['first_author']);
        $list = json_decode(DB::table('student_publication_imports')->value('publication_list'), true);
        $this->assertSame('Student Article', $list[0]['title']);
    }

    public function test_student_optional_first_author_is_persisted_when_mapped(): void
    {
        $values = $this->studentValues() + ['first_author' => 'Y'];
        app(StudentPublicationImporter::class)->import($this->arbitraryWorkbook('Raw', $values), 'students.xlsx', 2026, 9,
            columnMapping: array_flip(array_keys($values)));

        $this->assertSame('Y', $this->studentRecord()['first_author']);
    }

    public function test_student_optional_omitted_null_and_empty_mapping_do_not_fall_back_to_headers(): void
    {
        $values = $this->studentValues() + ['first_author' => 'Must not be imported'];
        $path = $this->studentWorkbook($values);
        $requiredMapping = array_flip(array_keys($this->studentValues()));
        foreach ([[], ['first_author' => null], ['first_author' => '']] as $index => $optional) {
            app(StudentPublicationImporter::class)->import($path, 'students.xlsx', 2026, $index + 1,
                columnMapping: $requiredMapping + $optional);
            $this->assertSame('', $this->studentRecord($index + 1)['first_author']);
        }
    }

    public function test_student_auto_mapping_still_accepts_original_headers_without_first_author(): void
    {
        $summary = app(StudentPublicationImporter::class)->import($this->studentWorkbook($this->studentValues()), 'auto.xlsx', 2026, 9);

        $this->assertSame(1, $summary['selected']);
        $this->assertSame('', $this->studentRecord()['first_author']);
    }

    public function test_student_required_mappings_cannot_be_omitted_even_with_recognized_headers(): void
    {
        $values = $this->studentValues();
        $path = $this->studentWorkbook($values);
        $importer = app(StudentPublicationImporter::class);
        $importer->import($path, 'original.xlsx', 2026, 9);
        $before = DB::table('student_publication_imports')->first();
        foreach (array_keys(app(\App\Services\ColumnMapping\ColumnMapperService::class)->getMappingConfig('mhs_file')['required']) as $required) {
            foreach (['omitted', null, ''] as $missing) {
                $mapping = array_flip(array_keys($values));
                unset($mapping[$required]);
                if ($missing !== 'omitted') {
                    $mapping[$required] = $missing;
                }
                $this->assertInvalid(fn () => $importer->import($path, 'invalid.xlsx', 2026, 9, columnMapping: $mapping));
            }
        }
        $this->assertEquals($before, DB::table('student_publication_imports')->first());
    }

    public function test_student_mapped_required_row_values_are_still_validated(): void
    {
        foreach (array_intersect(['request_code', 'name', 'title', 'program', 'type'], array_keys(app(\App\Services\ColumnMapping\ColumnMapperService::class)->getMappingConfig('mhs_file')['required'])) as $required) {
            $values = array_replace($this->studentValues(), [$required => '']);
            $path = $this->arbitraryWorkbook('MALANG', $values);
            $this->assertInvalid(fn () => app(StudentPublicationImporter::class)->import($path, 'invalid.xlsx', 2026, 9,
                columnMapping: array_flip(array_keys($values))));
        }
        $this->assertSame(0, DB::table('student_publication_imports')->count());
    }

    public function test_research_required_only_arbitrary_headers_are_mapped_and_persisted(): void
    {
        $values = $this->researchValues();
        $summary = app(RectorateResearchImporter::class)->import($this->arbitraryWorkbook('Detail', $values), 'research.xlsx',
            year: 2026, month: 9, columnMapping: array_flip(array_keys($values)));

        $this->assertSame(1, $summary['selected']);
        $this->assertSame(1, $summary['projects']);
        $record = (array) DB::table('rectorate_research')->sole();
        foreach ($values as $field => $value) {
            $this->assertSame($value, $record[$field]);
        }
        foreach (array_diff(array_keys(RectorateResearchReader::HEADERS), array_keys($values)) as $optional) {
            $this->assertNull($record[$optional], $optional);
        }
        $this->assertNull($record['approved_amount']);
        $this->assertNull($record['starts_on']);
        $this->assertSame([], json_decode($record['sdg_numbers'], true));
    }

    public function test_research_upload_uses_the_location_column_confirmed_after_preview(): void
    {
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $values = $this->researchValues() + ['chosen_location' => 'BINUS @Bandung'];
        $headers = array_map(fn ($field) => RectorateResearchReader::HEADERS[$field] ?? 'Lokasi pilihan', array_keys($values));
        $path = $this->makeWorkbook(['Detail' => [$headers, array_values($values), array_values(array_replace($values, [
            'kode_dosen_nim' => 'D002', 'peran' => 'Anggota 1', 'chosen_location' => 'BINUS @Kemanggisan',
        ]))]]);
        $this->postJson(route('importrectorate.preview-columns'), [
            'file' => new UploadedFile($path, 'locations.xlsx', null, null, true), 'type' => 'research_file',
        ])->assertOk()->assertJsonPath('suggested_mapping.lokasi_kampus', 1);

        $mapping = array_flip(array_keys($this->researchValues()));
        $mapping['lokasi_kampus'] = count($values) - 1;
        $upload = fn (array $confirmedMapping) => $this->postJson(route('importrectorate.upload'), [
            'year' => 2026, 'month' => 9,
            'research_file' => new UploadedFile($path, 'locations.xlsx', null, null, true),
            'column_mapping' => ['research_file' => json_encode($confirmedMapping, JSON_THROW_ON_ERROR)],
        ]);
        $upload($mapping)->assertOk()->assertJsonPath('summaries.research_file.selected', 2)
            ->assertJsonPath('summaries.research_file.excluded_campus', 0);
        $this->assertSame(['BINUS @Bandung', 'BINUS @Kemanggisan'], DB::table('rectorate_research')->orderBy('source_row')->pluck('lokasi_kampus')->all());
        $project = app(ResearchGallery::class)->rectorateProjects()->sole();
        $this->assertCount(2, $project['people']);
        $this->assertSame('BINUS @Bandung, BINUS @Kemanggisan', $project['campus']);

        $mapping['lokasi_kampus'] = 1;
        $upload($mapping)->assertOk()->assertJsonPath('summaries.research_file.already_imported', false);
        $this->assertSame(['BINUS @Malang', 'BINUS @Malang'], DB::table('rectorate_research')->orderBy('source_row')->pluck('lokasi_kampus')->all());
        $this->assertSame(1, DB::table('research_imports')->count());
    }

    public function test_research_same_file_with_changed_mapping_replaces_persisted_rows(): void
    {
        $values = $this->researchValues() + ['alternate_title' => 'Corrected research title'];
        $path = $this->arbitraryWorkbook('Detail', $values);
        $mapping = array_flip(array_keys($this->researchValues()));
        $importer = app(RectorateResearchImporter::class);
        $first = $importer->import($path, 'research.xlsx', year: 2026, month: 9, columnMapping: $mapping);
        $this->assertSame('Research project', DB::table('rectorate_research')->sole()->judul);

        $mapping['judul'] = count($values) - 1;
        $second = $importer->import($path, 'remapped.xlsx', year: 2026, month: 9, columnMapping: $mapping);

        $this->assertFalse($second['already_imported']);
        $this->assertSame($first['import_id'], $second['import_id']);
        $batch = DB::table('research_imports')->sole();
        $this->assertSame(hash_file('sha256', $path), $batch->sha256);
        $this->assertSame('remapped.xlsx', $batch->filename);
        $this->assertEquals(
            array_diff_key($second, array_flip(['import_id', 'already_imported'])),
            json_decode($batch->summary, true, flags: JSON_THROW_ON_ERROR)
        );
        $record = DB::table('rectorate_research')->sole();
        $this->assertSame($batch->id, $record->research_import_id);
        $this->assertSame('Corrected research title', $record->judul);
    }

    public function test_all_research_optional_fields_can_be_explicitly_mapped_and_persisted(): void
    {
        $values = $this->allResearchValues();
        app(RectorateResearchImporter::class)->import($this->arbitraryWorkbook('MALANG', $values), 'research.xlsx',
            year: 2026, month: 9, columnMapping: array_flip(array_keys($values)));

        $record = (array) DB::table('rectorate_research')->sole();
        foreach (array_diff(array_keys(RectorateResearchReader::HEADERS), array_keys($this->researchValues())) as $optional) {
            $this->assertSame($values[$optional], $record[$optional], $optional);
        }
        $this->assertSame('2026-01-01', $record['starts_on']);
        $this->assertEquals(1000000, $record['approved_amount']);
        $this->assertSame([4, 9], json_decode($record['sdg_numbers'], true));
    }

    public function test_research_optional_omitted_null_and_empty_mapping_do_not_fall_back_to_headers(): void
    {
        $values = $this->allResearchValues();
        $headers = array_map(fn ($field) => RectorateResearchReader::HEADERS[$field], array_keys($values));
        $path = $this->makeWorkbook(['Detail' => [array_values($headers), array_values($values)]]);
        $optionalFields = array_diff(array_keys(RectorateResearchReader::HEADERS), array_keys($this->researchValues()));
        $requiredMapping = array_flip(array_keys($this->researchValues()));
        foreach ([[], array_fill_keys($optionalFields, null), array_fill_keys($optionalFields, '')] as $index => $optional) {
            app(RectorateResearchImporter::class)->import($path, 'research.xlsx', year: 2026, month: $index + 1,
                columnMapping: $requiredMapping + $optional);
            $batch = DB::table('research_imports')->where('month', $index + 1)->sole();
            $record = (array) DB::table('rectorate_research')->where('research_import_id', $batch->id)->sole();
            foreach ($optionalFields as $field) {
                $this->assertNull($record[$field], $field);
            }
        }
    }

    public function test_research_auto_mapping_still_accepts_original_required_headers(): void
    {
        $values = $this->researchValues();
        $headers = array_map(fn ($field) => RectorateResearchReader::HEADERS[$field], array_keys($values));
        $path = $this->makeWorkbook(['Detail' => [array_values($headers), array_values($values)]]);
        $parsed = app(RectorateResearchReader::class)->read($path);

        $this->assertCount(1, $parsed['records']);
        $this->assertSame('Research project', $parsed['records'][0]['judul']);
        $this->assertNull($parsed['records'][0]['evidence']);
    }

    public function test_research_required_mappings_cannot_be_omitted_even_with_recognized_headers(): void
    {
        $values = $this->researchValues();
        $headers = array_map(fn ($field) => RectorateResearchReader::HEADERS[$field], array_keys($values));
        $path = $this->makeWorkbook(['Detail' => [array_values($headers), array_values($values)]]);
        foreach (array_keys($values) as $required) {
            foreach (['omitted', null, ''] as $missing) {
                $mapping = array_flip(array_keys($values));
                unset($mapping[$required]);
                if ($missing !== 'omitted') {
                    $mapping[$required] = $missing;
                }
                $this->assertInvalid(fn () => app(RectorateResearchReader::class)->read($path, $mapping));
            }
        }
        $this->assertSame(0, DB::table('research_imports')->count());
    }

    public function test_research_mapped_required_row_values_are_still_validated(): void
    {
        foreach (['tahun_anggaran', 'kd_prop', 'kode_dosen_nim', 'nama', 'peran', 'judul', 'lokasi_kampus'] as $required) {
            $values = array_replace($this->researchValues(), [$required => '']);
            $path = $this->arbitraryWorkbook('Detail', $values);
            $this->assertInvalid(fn () => app(RectorateResearchReader::class)->read($path, array_flip(array_keys($values))));
        }
    }

    private function studentValues(): array
    {
        return ['title' => 'Student Article', 'name' => 'Student', 'program' => 'Computer Science (BINUS @Malang)',
            'request_code' => 'R1', 'campus' => 'MALANG', 'code' => 'Mahasiswa',
            'type' => 'Scopus', 'submitted' => 'Scopus Mahasiswa'];
    }

    private function researchValues(): array
    {
        return ['judul' => 'Research project', 'lokasi_kampus' => 'BINUS @Malang', 'nama' => 'Researcher',
            'kd_prop' => 'BNS2026-1', 'peran' => 'Ketua', 'tahun_anggaran' => '2026', 'kode_dosen_nim' => 'D001'];
    }

    private function allResearchValues(): array
    {
        return $this->researchValues() + array_replace(array_fill_keys(array_keys(RectorateResearchReader::HEADERS), 'Optional value'), [
            'tanggal_mulai' => '2026-01-01', 'tanggal_selesai' => '2026-12-31', 'dana_disetujui' => '1.000.000',
            'hibah_internal_binus' => '500000', 'hibah_per_prodi' => '250000', 'sdgs' => '4, 9',
        ]);
    }

    private function arbitraryWorkbook(string $sheet, array $values): string
    {
        $headers = array_map(fn ($index) => 'Unrelated column '.$index, range(0, count($values) - 1));

        return $this->makeWorkbook([$sheet => [$headers, array_values($values)]]);
    }

    private function studentWorkbook(array $values): string
    {
        $labels = ['request_code' => 'RequestCode', 'name' => 'FM Author', 'code' => 'Kode Dosen',
            'campus' => 'Kampus', 'submitted' => 'Submitted', 'type' => 'Tipe Publikasi',
            'title' => 'Title', 'program' => 'Prodi MHS', 'first_author' => 'First Author'];
        $headers = array_map(fn ($field) => $labels[$field], array_keys($values));

        return $this->makeWorkbook(['MALANG' => [array_values($headers), array_values($values)]]);
    }

    private function studentRecord(int $month = 9): array
    {
        return json_decode(DB::table('student_publication_imports')->where('month', $month)->value('records'), true, flags: JSON_THROW_ON_ERROR)[0];
    }

    private function assertInvalid(callable $import): void
    {
        try {
            $import();
            $this->fail('A missing required mapping or value must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertNotEmpty($exception->errors());
        }
    }
}
