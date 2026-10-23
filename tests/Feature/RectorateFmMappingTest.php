<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ColumnMapping\ColumnMapperService;
use App\Services\Publications\PublicationImporter;
use App\Services\Publications\RawPublicationReader;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\CreatesWorkbook;
use Tests\TestCase;

class RectorateFmMappingTest extends TestCase
{
    use CreatesWorkbook;

    private const REQUIRED_HEADERS = [
        'RequestCode', 'Kode Dosen', 'Kampus', 'Submitted', 'Bobot', 'Title', 'Jenis', 'Tipe Publikasi', 'St2026',
    ];

    private const REQUIRED_MAPPING = [
        'request_code' => 0, 'kode_dosen' => 1, 'kampus' => 2, 'submitted' => 3, 'bobot_asli' => 4,
        'title' => 5, 'jenis' => 6, 'tipe_publikasi' => 7, 'status' => 8,
    ];

    private const REQUIRED_ROW = ['R1', 'D001', 'MALANG', 'Scopus FM', '0,5', 'Mapped publication', 'Jurnal', 'Scopus', 'Accepted'];

    private const OPTIONAL_VALUES = [
        'author' => 'Author name', 's_f' => 'SOCS', 'dept' => 'Computer Science', 'sumber_paper' => '2A',
        'scopus_year' => '2026', 'source_title' => 'Journal name', 'notes' => 'Mapped note',
        'fm_author' => 'Test Lecturer', 'jja' => 'L200', 'pendidikan' => 'S2', 'type' => 'Faculty Member',
        'first_author' => 'Y', 'quartile_jurnal' => 'Q1', 'publisher' => 'Publisher name',
        'tanggal_pelaporan' => '2026-01-01', 'prodi_kpi' => 'Computer Science',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Run PHP with -d extension=pdo_sqlite.');
        }
        $this->assertSame('sqlite', config('database.default'));
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
    }

    protected function tearDown(): void
    {
        $this->removeWorkbooks();
        parent::tearDown();
    }

    public function test_required_only_arbitrary_headers_are_read_and_imported_with_explicit_mapping(): void
    {
        $file = $this->fmWorkbook($this->arbitraryHeaders(9));
        $parsed = app(RawPublicationReader::class)->read($file, 2026, self::REQUIRED_MAPPING);
        $record = $parsed['records'][0];

        $this->assertSame(1, $parsed['summary']['selected']);
        foreach (['request_code' => 'R1', 'kode_dosen' => 'D001', 'title' => 'Mapped publication', 'status' => 'Accepted'] as $field => $value) {
            $this->assertSame($value, $record[$field]);
        }
        $this->assertSame(0.5, $record['bobot_asli']);
        $this->assertSame(0.5, $record['bobot']);
        foreach (array_keys(self::OPTIONAL_VALUES) as $field) {
            $this->assertNull($record[$field], $field);
        }

        $summary = $this->import($file, self::REQUIRED_MAPPING);
        $this->assertSame(1, $summary['selected']);
        $this->assertSame('R1', DB::table('rectorate_dosen')->value('request_code'));
        $this->assertSame('mapped.xlsx', DB::table('publication_imports')->value('filename'));
    }

    public static function emptyOptionalMappings(): array
    {
        return ['omitted' => ['omitted'], 'null' => [null], 'empty string' => ['']];
    }

    #[DataProvider('emptyOptionalMappings')]
    public function test_optional_mapping_can_be_omitted_null_or_empty_even_with_populated_matching_headers(mixed $empty): void
    {
        $optional = array_diff_key(app(ColumnMapperService::class)->getMappingConfig('fm_file')['optional'], ['request_code' => true]);
        $this->assertEqualsCanonicalizing(array_keys(self::OPTIONAL_VALUES), array_keys($optional));
        $file = $this->fmWorkbook(
            array_merge(self::REQUIRED_HEADERS, array_values($optional)),
            array_merge(self::REQUIRED_ROW, array_values(self::OPTIONAL_VALUES)),
            true
        );
        $mapping = self::REQUIRED_MAPPING;
        if ($empty !== 'omitted') {
            $mapping += array_fill_keys(array_keys($optional), $empty);
        }

        $record = app(RawPublicationReader::class)->read($file, 2026, $mapping)['records'][0];
        foreach (array_keys($optional) as $field) {
            $this->assertNull($record[$field], $field);
        }
        $this->assertSame(0.5, $record['bobot'], 'Unmapped Q1 must not be silently auto-detected.');

        $summary = $this->import($file, $mapping);
        $this->assertSame(1, $summary['selected']);
        $this->assertTrue($summary['kpi']['available']);
        $this->assertSame(1, DB::table('publication_kpi_entries')->count());
        $stored = DB::table('rectorate_dosen')->first();
        foreach (array_keys($optional) as $field) {
            $this->assertNull($stored->{$field}, $field);
        }
    }

    public function test_supplied_optional_mappings_are_honored_by_reader_and_importer(): void
    {
        $mapping = array_combine(
            array_merge(array_keys(self::REQUIRED_MAPPING), array_keys(self::OPTIONAL_VALUES)),
            range(0, count(self::REQUIRED_MAPPING) + count(self::OPTIONAL_VALUES) - 1)
        );
        $file = $this->fmWorkbook(
            $this->arbitraryHeaders(count($mapping)),
            array_merge(self::REQUIRED_ROW, array_values(self::OPTIONAL_VALUES)),
            true
        );

        $record = app(RawPublicationReader::class)->read($file, 2026, $mapping)['records'][0];
        foreach (self::OPTIONAL_VALUES as $field => $value) {
            $this->assertSame($value, $record[$field], $field);
        }
        $this->assertSame(3.0, $record['bobot']);
        $this->import($file, $mapping);
        $stored = DB::table('rectorate_dosen')->first();
        foreach (self::OPTIONAL_VALUES as $field => $value) {
            $this->assertSame($value, $stored->{$field}, $field);
        }
    }

    public function test_first_author_and_program_remain_optional_without_explicit_mapping_when_kpi_exists(): void
    {
        $file = $this->fmWorkbook(self::REQUIRED_HEADERS, self::REQUIRED_ROW, true);
        $record = app(RawPublicationReader::class)->read($file, 2026)['records'][0];
        $this->assertNull($record['first_author']);
        $this->assertNull($record['prodi_kpi']);

        $summary = $this->import($file);
        $this->assertSame(1, $summary['selected']);
        $this->assertSame(1, $summary['kpi']['lecturers']);
        $this->assertNull(DB::table('rectorate_dosen')->value('first_author'));
        $this->assertNull(DB::table('rectorate_dosen')->value('prodi_kpi'));
    }

    public static function invalidMappings(): array
    {
        return [
            'missing required' => [array_diff_key(self::REQUIRED_MAPPING, ['status' => true])],
            'null required' => [array_replace(self::REQUIRED_MAPPING, ['title' => null])],
            'empty required' => [array_replace(self::REQUIRED_MAPPING, ['title' => ''])],
            'negative index' => [array_replace(self::REQUIRED_MAPPING, ['title' => -1])],
            'out of range index' => [array_replace(self::REQUIRED_MAPPING, ['title' => 9])],
            'non-numeric index' => [array_replace(self::REQUIRED_MAPPING, ['title' => 'A'])],
            'fractional index' => [array_replace(self::REQUIRED_MAPPING, ['title' => 1.5])],
            'boolean index' => [array_replace(self::REQUIRED_MAPPING, ['title' => false])],
            'duplicate index' => [array_replace(self::REQUIRED_MAPPING, ['title' => 0])],
            'unknown field' => [self::REQUIRED_MAPPING + ['unknown_field' => null]],
        ];
    }

    #[DataProvider('invalidMappings')]
    public function test_invalid_mapping_is_rejected_without_replacing_existing_snapshot(array $mapping): void
    {
        $file = $this->fmWorkbook($this->arbitraryHeaders(9));
        $this->import($file, self::REQUIRED_MAPPING);
        $before = DB::table('publication_imports')->first();
        $validation = app(ColumnMapperService::class)->validateMapping($mapping, 'fm_file', 9);
        $this->assertFalse($validation['valid']);
        $this->assertNotEmpty($validation['errors']);

        try {
            app(RawPublicationReader::class)->read($file, 2026, $mapping);
            $this->fail('Invalid mapping must be rejected by the reader.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('rectorate', $exception->errors());
        }
        try {
            app(PublicationImporter::class)->import($file, 'invalid.xlsx', 2026, 9, 3, columnMapping: $mapping);
            $this->fail('Invalid mapping must be rejected by the importer.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('rectorate', $exception->errors());
        }
        $this->assertEquals($before, DB::table('publication_imports')->first());
        $this->assertSame(1, DB::table('rectorate_dosen')->count());
    }

    public function test_preview_exposes_required_only_config_and_mapped_upload_uses_confirmed_mapping(): void
    {
        $headers = $this->arbitraryHeaders(9);
        $file = $this->fmWorkbook($headers, self::REQUIRED_ROW, true);
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $response = $this->postJson(route('importrectorate.preview-columns'), [
            'type' => 'fm_file', 'file' => $this->upload($file),
        ])->assertOk()->assertJsonPath('success', true)->assertJsonPath('has_kpi', true);

        $this->assertSame($headers, $response->json('headers'));
        $this->assertSame(array_keys(array_diff_key(self::REQUIRED_MAPPING, ['request_code' => true])), array_keys($response->json('mapping_config.required')));
        $this->assertArrayHasKey('request_code', $response->json('mapping_config.optional'));
        $this->assertArrayHasKey('first_author', $response->json('mapping_config.optional'));
        $this->assertArrayHasKey('prodi_kpi', $response->json('mapping_config.optional'));
        $this->assertSame(0, DB::table('publication_imports')->count());

        $this->postJson(route('importrectorate.upload'), [
            'year' => 2026, 'month' => 9, 'fm_file' => $this->upload($file),
            'column_mapping' => ['fm_file' => json_encode(self::REQUIRED_MAPPING, JSON_THROW_ON_ERROR)],
        ])->assertOk()->assertJsonPath('summaries.fm_file.selected', 1)->assertJsonPath('summaries.fm_file.kpi.lecturers', 1);

        $this->assertSame('R1', DB::table('rectorate_dosen')->value('request_code'));
        $this->assertNull(DB::table('rectorate_dosen')->value('first_author'));
        $this->assertNull(DB::table('rectorate_dosen')->value('prodi_kpi'));
    }

    public function test_upload_rejects_malformed_json_and_non_object_mappings_without_writes(): void
    {
        $file = $this->fmWorkbook(self::REQUIRED_HEADERS);
        $this->actingAs((new User)->forceFill(['id' => 1]));
        foreach (['{not-json', 'null', '[]', '[0,1]', '"text"', 'true'] as $raw) {
            $this->postJson(route('importrectorate.upload'), [
                'year' => 2026, 'month' => 9, 'fm_file' => $this->upload($file),
                'column_mapping' => ['fm_file' => $raw],
            ])->assertUnprocessable()->assertJsonValidationErrors('column_mapping.fm_file');
        }
        $this->assertSame(0, DB::table('publication_imports')->count());
        $this->assertSame(0, DB::table('rectorate_dosen')->count());
        $this->assertSame(0, DB::table('publication_kpi_entries')->count());
    }

    public function test_optional_request_code_can_be_unmapped_without_merging_different_titles(): void
    {
        $first = array_replace(self::REQUIRED_ROW, [5 => 'First publication']);
        $second = array_replace(self::REQUIRED_ROW, [5 => 'Second publication']);
        $file = $this->makeWorkbook(['MALANG' => [self::REQUIRED_HEADERS, $first, $second, $first]]);
        $this->actingAs((new User)->forceFill(['id' => 1]));
        foreach ([[], ['request_code' => null], ['request_code' => '']] as $optional) {
            $mapping = array_diff_key(self::REQUIRED_MAPPING, ['request_code' => true]) + $optional;
            $parsed = app(RawPublicationReader::class)->read($file, 2026, $mapping);
            $this->assertSame(2, $parsed['summary']['selected']);
            $this->assertSame(2, $parsed['summary']['publications']);
            $this->assertSame(1, $parsed['summary']['duplicates']);
            $this->assertSame([null, null], array_column($parsed['records'], 'request_code'));
            $this->postJson(route('importrectorate.upload'), [
                'year' => 2026, 'month' => 9, 'fm_file' => $this->upload($file),
                'column_mapping' => ['fm_file' => json_encode($mapping, JSON_THROW_ON_ERROR)],
            ])->assertOk()->assertJsonPath('summaries.fm_file.selected', 2);
            $this->assertSame(2, DB::table('rectorate_dosen')->whereNull('request_code')->count());
        }
    }

    public function test_mapped_blank_request_codes_and_automatic_mapping_without_request_header_are_accepted(): void
    {
        $row = array_replace(self::REQUIRED_ROW, [0 => '', 4 => '0', 5 => '0']);
        $parsed = app(RawPublicationReader::class)->read($this->fmWorkbook(self::REQUIRED_HEADERS, $row), 2026, self::REQUIRED_MAPPING);
        $this->assertNull($parsed['records'][0]['request_code']);
        $this->assertSame(0.0, $parsed['records'][0]['bobot_asli']);
        $this->assertSame('0', $parsed['records'][0]['title']);
        $headers = array_slice(self::REQUIRED_HEADERS, 1);
        $file = $this->makeWorkbook(['MALANG' => [$headers, array_slice(self::REQUIRED_ROW, 1)]]);
        $summary = $this->import($file);
        $this->assertSame(1, $summary['selected']);
        $this->assertNull(DB::table('rectorate_dosen')->sole()->request_code);
    }

    public function test_missing_required_value_reports_the_confirmed_source_column_and_preserves_existing_data(): void
    {
        $file = $this->fmWorkbook(self::REQUIRED_HEADERS);
        $this->import($file, self::REQUIRED_MAPPING);
        $before = DB::table('rectorate_dosen')->get();
        $batch = DB::table('publication_imports')->sole();
        $invalidFile = $this->fmWorkbook($this->arbitraryHeaders(9), array_replace(self::REQUIRED_ROW, [5 => '']));
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $response = $this->postJson(route('importrectorate.upload'), [
            'year' => 2026, 'month' => 9, 'fm_file' => $this->upload($invalidFile),
            'column_mapping' => ['fm_file' => json_encode(array_diff_key(self::REQUIRED_MAPPING, ['request_code' => true]), JSON_THROW_ON_ERROR)],
        ])->assertUnprocessable()->assertJsonValidationErrors('rectorate');
        $this->assertStringContainsString('Baris 2: Title (title) kosong pada kolom F (Custom column 5).', $response->json('errors.rectorate.0'));
        $this->assertEquals($before, DB::table('rectorate_dosen')->get());
        $this->assertEquals($batch, DB::table('publication_imports')->sole());
    }

    public function test_changing_required_fields_in_mapping_service_is_enough_to_change_reader_validation(): void
    {
        $this->app->instance(ColumnMapperService::class, new class extends ColumnMapperService
        {
            public function getMappingConfig(string $importType, ?int $year = null): array
            {
                $config = parent::getMappingConfig($importType, $year);
                foreach (['status', 'bobot_asli'] as $field) {
                    $config['optional'][$field] = $config['required'][$field];
                    unset($config['required'][$field]);
                }

                return $config;
            }
        });
        $mapping = array_diff_key(self::REQUIRED_MAPPING, array_flip(['status', 'bobot_asli', 'request_code']));
        $file = $this->fmWorkbook(self::REQUIRED_HEADERS);
        $record = app(RawPublicationReader::class)->read($file, 2026, $mapping)['records'][0];
        $this->assertNull($record['status']);
        $this->assertNull($record['bobot_asli']);
        $this->assertNull($record['request_code']);
        $this->assertSame(1, $this->import($file, $mapping)['selected']);
    }

    public function test_configured_status_alias_is_used_for_both_suggestions_and_automatic_import(): void
    {
        $mapper = new class extends ColumnMapperService
        {
            public function getMappingConfig(string $importType, ?int $year = null): array
            {
                $config = parent::getMappingConfig($importType, $year);
                $config['aliases']['status'] = ['Publication State'];

                return $config;
            }
        };
        $this->app->instance(ColumnMapperService::class, $mapper);
        $headers = array_replace(self::REQUIRED_HEADERS, [8 => 'Publication State']);
        $this->assertSame(8, $mapper->suggestMapping($headers, 'fm_file', 2026)['status']);
        $record = app(RawPublicationReader::class)->read($this->fmWorkbook($headers), 2026)['records'][0];
        $this->assertSame('Accepted', $record['status']);
    }

    private function import(string $file, ?array $mapping = null): array
    {
        return app(PublicationImporter::class)->import($file, 'mapped.xlsx', 2026, 9, 3, columnMapping: $mapping);
    }

    private function upload(string $file): UploadedFile
    {
        return new UploadedFile($file, 'mapped.xlsx', null, null, true);
    }

    private function arbitraryHeaders(int $count): array
    {
        return array_map(fn ($index) => 'Custom column '.$index, range(0, $count - 1));
    }

    private function fmWorkbook(array $headers, array $row = self::REQUIRED_ROW, bool $withKpi = false): string
    {
        $sheets = ['MALANG' => [$headers, $row]];
        if ($withKpi) {
            $sheets['KPI'] = [
                ['Kode Dosen', 'Nama Dosen', 'Jurusan Binaan', 'JJA', 'Faculty Type', 'Non Scopus RTTO', 'Scopus RTTO', 'Score KPI RTTO'],
                ['D001', 'Test Lecturer', 'Computer Science', 'L200', 'Functional', '0', '1', '3'],
            ];
        }

        return $this->makeWorkbook($sheets);
    }
}
