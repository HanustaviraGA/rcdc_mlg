<?php

namespace Tests\Feature;

use App\Services\ColumnMapping\ColumnMapperService;
use Tests\TestCase;

class ColumnMappingTest extends TestCase
{
    protected ColumnMapperService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ColumnMapperService;
    }

    /**
     * Test getMappingConfig untuk FM file
     */
    public function test_fm_mapping_config_has_required_fields(): void
    {
        $config = $this->service->getMappingConfig('fm_file');

        $this->assertNotEmpty($config['required']);
        $this->assertNotEmpty($config['optional']);
        $this->assertNotEmpty($config['aliases']);

        // Check required fields
        $this->assertArrayNotHasKey('request_code', $config['required']);
        $this->assertArrayHasKey('request_code', $config['optional']);
        $this->assertArrayHasKey('kode_dosen', $config['required']);
        $this->assertArrayHasKey('kampus', $config['required']);
    }

    /**
     * Test getMappingConfig untuk Research file
     */
    public function test_research_mapping_config_has_required_fields(): void
    {
        $config = $this->service->getMappingConfig('research_file');

        $this->assertNotEmpty($config['required']);
        $this->assertArrayHasKey('tahun_anggaran', $config['required']);
        $this->assertArrayHasKey('kode_dosen_nim', $config['required']);
        $this->assertArrayHasKey('judul', $config['required']);
    }

    /**
     * Test getMappingConfig untuk Student Publication file
     */
    public function test_mhs_mapping_config_has_required_fields(): void
    {
        $config = $this->service->getMappingConfig('mhs_file');

        $this->assertSame([
            'name', 'campus', 'submitted', 'type', 'title', 'program',
        ], array_keys($config['required']));
        $this->assertArrayNotHasKey('nim', $config['required']);
        $this->assertArrayHasKey('first_author', $config['optional']);
        $this->assertArrayHasKey('request_code', $config['optional']);
        $this->assertArrayHasKey('code', $config['optional']);
    }

    /**
     * Test suggest mapping dengan exact match
     */
    public function test_suggest_mapping_exact_match(): void
    {
        $headers = [
            'Request Code',
            'Kode Dosen',
            'Kampus',
            'Submitted',
            'Bobot',
            'Title',
            'Jenis',
            'Tipe Publikasi',
        ];

        $mapping = $this->service->suggestMapping($headers, 'fm_file');

        $this->assertNotEmpty($mapping);
        $this->assertArrayHasKey('request_code', $mapping);
        $this->assertArrayHasKey('kode_dosen', $mapping);
        $this->assertArrayHasKey('title', $mapping);
    }

    /**
     * Test suggest mapping dengan partial match
     */
    public function test_suggest_mapping_partial_match(): void
    {
        // Headers dengan format berbeda
        $headers = [
            'requestcode',       // request_code
            'kode_dosen',        // kode_dosen
            'kampus',            // kampus
            'tipe_laporan',      // submitted
            'bobot_asli',        // bobot_asli
            'judul publikasi',   // title
            'publikasi',         // jenis
            'tipe',              // tipe_publikasi
        ];

        $mapping = $this->service->suggestMapping($headers, 'fm_file');

        // Should have some mappings even with different format
        $this->assertNotEmpty($mapping);
        $this->assertTrue(count($mapping) > 0);
    }

    /**
     * Test validateMapping dengan valid data
     */
    public function test_validate_mapping_all_required_mapped(): void
    {
        $mapping = [
            'request_code' => 0,
            'kode_dosen' => 1,
            'kampus' => 2,
            'submitted' => 3,
            'bobot_asli' => 4,
            'title' => 5,
            'jenis' => 6,
            'tipe_publikasi' => 7,
            'status' => 8,
        ];

        $validation = $this->service->validateMapping($mapping, 'fm_file');

        $this->assertTrue($validation['valid']);
        $this->assertEmpty($validation['errors']);
    }

    /**
     * Test validateMapping dengan missing required field
     */
    public function test_validate_mapping_missing_required_field(): void
    {
        $mapping = [
            'request_code' => 0,
            'kode_dosen' => 1,
            // Missing kampus, submitted, bobot_asli, title, jenis, tipe_publikasi
        ];

        $validation = $this->service->validateMapping($mapping, 'fm_file');

        $this->assertFalse($validation['valid']);
        $this->assertNotEmpty($validation['errors']);
        $this->assertArrayHasKey('kampus', $validation['errors']);
    }

    /**
     * Test validateMapping dengan optional field missing
     */
    public function test_validate_mapping_optional_field_missing(): void
    {
        $mapping = [
            'request_code' => 0,
            'kode_dosen' => 1,
            'kampus' => 2,
            'submitted' => 3,
            'bobot_asli' => 4,
            'title' => 5,
            'jenis' => 6,
            'tipe_publikasi' => 7,
            'status' => 8,
            // Missing optional fields like fm_author, first_author, dll
        ];

        $validation = $this->service->validateMapping($mapping, 'fm_file');

        $this->assertTrue($validation['valid']);
        $this->assertNotEmpty($validation['warnings']);
        // Optional fields warning
        $this->assertTrue(count($validation['warnings']) > 0);
    }

    /**
     * Test validateMapping untuk research file
     */
    public function test_validate_mapping_research_file(): void
    {
        $mapping = [
            'tahun_anggaran' => 0,
            'kd_prop' => 1,
            'kode_dosen_nim' => 2,
            'nama' => 3,
            'peran' => 4,
            'judul' => 5,
            'lokasi_kampus' => 6,
        ];

        $validation = $this->service->validateMapping($mapping, 'research_file');

        $this->assertTrue($validation['valid']);
        $this->assertEmpty($validation['errors']);
    }

    /**
     * Test invalid import type
     */
    public function test_mapping_config_invalid_type(): void
    {
        $config = $this->service->getMappingConfig('invalid_type');

        $this->assertEmpty($config);
    }

    public function test_row_validation_uses_required_fields_from_each_mapping_config(): void
    {
        foreach (['fm_file', 'mhs_file', 'research_file'] as $type) {
            $config = $this->service->getMappingConfig($type);
            $record = array_fill_keys(array_keys($config['required']), 'value');
            $this->assertTrue($this->service->validateRow($record, $type, 2)['valid']);
            foreach ($config['required'] as $field => $label) {
                $validation = $this->service->validateRow(array_replace($record, [$field => null]), $type, 2);
                $this->assertFalse($validation['valid']);
                $this->assertArrayHasKey($field, $validation['errors']);
                $this->assertStringContainsString($label, $validation['errors'][$field]);
            }
            foreach (array_keys($record) as $field) {
                $this->assertTrue($this->service->validateRow(array_replace($record, [$field => '0']), $type, 2)['valid']);
            }
        }
    }

    public function test_row_errors_show_the_mapped_excel_column_and_source_header(): void
    {
        $config = $this->service->getMappingConfig('fm_file');
        $record = array_fill_keys(array_keys($config['required']), 'value');
        $record['title'] = '';
        $validation = $this->service->validateRow($record, 'fm_file', 7, ['title' => 26], [26 => 'Judul Terbitan']);
        $this->assertSame('Baris 7: Title (title) kosong pada kolom AA (Judul Terbitan).', $validation['errors']['title']);
        $this->assertArrayNotHasKey('request_code', $validation['errors']);
        $this->assertFalse($this->service->validateRow([], 'invalid_type', 2)['valid']);
    }
}
