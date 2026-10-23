<?php

namespace Modules\ImportRectorate\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ColumnMapping\ColumnMapperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ColumnMappingController extends Controller
{
    public function __construct(private ColumnMapperService $mapperService) {}

    /**
     * Preview file dan dapatkan header columns + suggested mapping.
     */
    public function preview(Request $request)
    {
        abort_unless(Auth::check(), 403);

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx', 'max:20480'],
            'type' => ['required', 'in:fm_file,mhs_file,research_file'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
        ]);

        $file = $validated['file'];
        $type = $validated['type'];

        $preview = $this->mapperService->preview($file->getRealPath(), $type);

        if (! $preview['success']) {
            return response()->json($preview, 422);
        }

        $suggestedMapping = $this->mapperService->suggestMapping($preview['headers'], $type, (int) ($validated['year'] ?? now()->year));
        $config = $this->mapperService->getMappingConfig($type, (int) ($validated['year'] ?? now()->year));
        $hasKpi = $type === 'fm_file' && in_array('KPI', array_map(fn ($sheet) => strtoupper(trim($sheet)), $preview['sheets'] ?? []), true);

        return response()->json([
            'success' => true,
            'file_info' => [
                'name' => $file->getClientOriginalName(),
                'sheet' => $preview['sheet'],
            ],
            'headers' => $preview['headers'],
            'preview_data' => $preview['preview_data'],
            'suggested_mapping' => $suggestedMapping,
            'mapping_config' => $config,
            'has_kpi' => $hasKpi,
        ]);
    }
}
