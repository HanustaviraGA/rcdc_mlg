<?php

namespace Modules\ImportRectorate\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ColumnMapping\ColumnMapperService;
use App\Services\ColumnMapping\WorkbookSheetRoles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ColumnMappingController extends Controller
{
    public function __construct(private ColumnMapperService $mapperService, private WorkbookSheetRoles $roles) {}

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
            'sheet' => ['nullable', 'string', 'max:255'],
            // JSON: supporting role => sheet name ('' when the role is not used).
            'extra_sheets' => ['nullable', 'string'],
        ]);

        $file = $validated['file'];
        $type = $validated['type'];
        $year = (int) ($validated['year'] ?? now()->year);
        $chosen = $this->roles->previewChoice($type, $validated['extra_sheets'] ?? null, 'extra_sheets');

        $preview = $this->mapperService->preview($file->getRealPath(), $type, $validated['sheet'] ?? null);

        if (! $preview['success']) {
            return response()->json($preview, 422);
        }

        $suggestedMapping = $this->mapperService->suggestMapping($preview['headers'], $type, $year);
        $config = $this->mapperService->getMappingConfig($type, $year);
        $hasKpi = $type === 'fm_file' && in_array('KPI', array_map(fn ($sheet) => strtoupper(trim($sheet)), $preview['sheets'] ?? []), true);

        return response()->json([
            'success' => true,
            'file_info' => [
                'name' => $file->getClientOriginalName(),
                'sheet' => $preview['sheet'],
            ],
            'sheets' => $preview['sheets'],
            'headers' => $preview['headers'],
            'preview_data' => $preview['preview_data'],
            'suggested_mapping' => $suggestedMapping,
            'mapping_config' => $config,
            'has_kpi' => $hasKpi,
            // What the main sheet stands for, then every supporting sheet with its own preview.
            'main_role' => $this->roles->main($type),
            'extra_roles' => (object) $this->roles->preview($file->getRealPath(), $type, $chosen, $year),
        ]);
    }
}
