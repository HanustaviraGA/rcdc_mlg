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

        // Without a default sheet the preview shows the first sheet; any sheet can then be chosen.
        $custom = $this->makeWorkbook(['Cover' => [['Report title']], 'Data' => [['Header'], ['Value']]]);
        foreach (['fm_file', 'mhs_file', 'research_file'] as $type) {
            $preview = $service->preview($custom, $type);
            $this->assertTrue($preview['success'], $type);
            $this->assertSame(['Cover', 'Data'], $preview['sheets']);
            $this->assertSame('Cover', $preview['sheet']);
            $chosen = $service->preview($custom, $type, 'Data');
            $this->assertSame(['Data', ['Header'], [['Value']]], [$chosen['sheet'], $chosen['headers'], $chosen['preview_data']]);
            $this->assertSame('Detail', $service->preview($path, $type, 'Detail')['sheet'], 'a chosen sheet overrides the default');
            $missing = $service->preview($custom, $type, 'Missing');
            $this->assertFalse($missing['success']);
            $this->assertSame(['Cover', 'Data'], $missing['sheets']);
        }
    }

    public function test_preview_endpoint_reads_the_chosen_sheet_and_lists_all_sheets(): void
    {
        $this->actingAs((new User)->forceFill(['id' => 1]));
        $path = $this->makeWorkbook(['Raw' => [['Default'], ['Value']], 'Data 2026' => [['Code', 'Title'], ['D001', 'Paper']]]);
        $request = fn (array $data) => $this->postJson(route('importrectorate.preview-columns'),
            ['file' => new UploadedFile($path, 'mapping.xlsx', null, null, true), 'type' => 'fm_file'] + $data);
        $request([])->assertOk()->assertJsonPath('file_info.sheet', 'Raw')->assertJsonPath('sheets', ['Raw', 'Data 2026']);
        $request(['sheet' => 'Data 2026'])->assertOk()->assertJsonPath('file_info.sheet', 'Data 2026')
            ->assertJsonPath('headers', ['Code', 'Title'])->assertJsonPath('preview_data.0.1', 'Paper');
        $request(['sheet' => 'Missing'])->assertUnprocessable()->assertJsonPath('sheets', ['Raw', 'Data 2026']);
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
