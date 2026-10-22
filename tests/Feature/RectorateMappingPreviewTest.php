<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ColumnMapping\ColumnMapperService;
use App\Services\Research\RectorateResearchReader;
use Illuminate\Http\UploadedFile;
use Tests\Support\CreatesWorkbook;
use Tests\TestCase;

class RectorateMappingPreviewTest extends TestCase
{
    use CreatesWorkbook;

    protected function tearDown(): void
    {
        $this->removeWorkbooks();
        parent::tearDown();
    }

    public function test_preview_selects_the_same_sheet_as_each_importer(): void
    {
        $path = $this->makeWorkbook([
            'Raw' => [['Publication header'], ['Publication value']],
            'Detail' => [['Research header'], ['Research value']],
        ]);
        $service = app(ColumnMapperService::class);
        $this->assertSame('Detail', $service->preview($path, 'research_file')['sheet']);
        $this->assertSame(['Research header'], $service->preview($path, 'research_file')['headers']);
        $this->assertSame('Raw', $service->preview($path, 'fm_file')['sheet']);
        $this->assertSame('Raw', $service->preview($path, 'mhs_file')['sheet']);

        $unsupported = $this->makeWorkbook(['Data' => [['Header'], ['Value']]]);
        foreach (['fm_file', 'mhs_file', 'research_file'] as $type) {
            $this->assertFalse($service->preview($unsupported, $type)['success']);
        }
    }

    public function test_status_suggestions_use_selected_year_without_guessing_another_year(): void
    {
        $service = app(ColumnMapperService::class);
        $headers = ['St2025', 'St2026', 'RequestCode'];
        $this->assertSame(0, $service->suggestMapping($headers, 'fm_file', 2025)['status']);
        $this->assertSame(1, $service->suggestMapping($headers, 'fm_file', 2026)['status']);
        $this->assertArrayNotHasKey('status', $service->suggestMapping($headers, 'fm_file', 2024));
        $this->assertSame(0, $service->suggestMapping(['RequestCode', 'Request Cod'], 'fm_file')['request_code']);
    }

    public function test_preview_endpoint_returns_year_specific_status_and_all_optional_research_fields(): void
    {
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $path = $this->makeWorkbook([
            'Raw' => [['St2025', 'St2026'], ['Accepted', 'Published']],
            'Detail' => [['Project'], ['Research title']],
        ]);
        $this->postJson(route('importrectorate.preview-columns'), [
            'file' => new UploadedFile($path, 'mapping.xlsx', null, null, true),
            'type' => 'fm_file', 'year' => 2026,
        ])->assertOk()->assertJsonPath('file_info.sheet', 'Raw')->assertJsonPath('suggested_mapping.status', 1);

        $response = $this->postJson(route('importrectorate.preview-columns'), [
            'file' => new UploadedFile($path, 'mapping.xlsx', null, null, true),
            'type' => 'research_file',
        ])->assertOk()->assertJsonPath('file_info.sheet', 'Detail');
        $config = $response->json('mapping_config');
        $this->assertEqualsCanonicalizing(array_keys(RectorateResearchReader::HEADERS), array_keys($config['required'] + $config['optional']));
        $this->assertArrayHasKey('evidence', $config['optional']);
    }
}
