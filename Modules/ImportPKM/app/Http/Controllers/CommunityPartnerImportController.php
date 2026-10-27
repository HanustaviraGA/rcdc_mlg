<?php

namespace Modules\ImportPKM\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\CommunityDevelopment\CommunityPartnerWorkbook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CommunityPartnerImportController extends Controller
{
    public function preview(Request $request, CommunityPartnerWorkbook $workbook)
    {
        $validated = $request->validate([
            'partners' => ['required', 'file', 'extensions:xlsx,xlsb', 'max:20480'],
            'sheet' => ['nullable', 'string', 'max:255'],
            'header_row' => ['nullable', 'integer', 'between:1,1000'],
        ]);

        return response()->json($workbook->preview($request->file('partners')->getRealPath(),
            $validated['sheet'] ?? null, isset($validated['header_row']) ? (int) $validated['header_row'] : null));
    }

    public function upload(Request $request, CommunityPartnerWorkbook $workbook)
    {
        $validated = $request->validate([
            'partners' => ['required', 'file', 'extensions:xlsx,xlsb', 'max:20480'],
            'data_year' => ['required', 'integer', 'between:2000,2100'],
            'sheet' => ['required', 'string', 'max:255'],
            'header_row' => ['required', 'integer', 'between:1,1000'],
            'column_mapping' => ['required', 'json'],
        ]);
        $mapping = json_decode($validated['column_mapping'], true);
        if (! is_array($mapping)) {
            throw ValidationException::withMessages(['column_mapping' => 'Pemetaan kolom harus berupa objek JSON.']);
        }
        $file = $request->file('partners');
        $summary = $workbook->import($file->getRealPath(), (int) $validated['data_year'], $validated['sheet'],
            (int) $validated['header_row'], $mapping, $file->getClientOriginalName());

        return response()->json(['success' => true, 'summary' => $summary, 'warnings' => $summary['warnings'],
            'message' => 'Import mitra tahun '.$summary['data_year'].' selesai: '.$summary['selected'].' mitra; '.$summary['duplicates'].' duplikat identik dilewati.',
            'history_html' => view('importpkm::partner_history', ['partnerImports' => DB::table('community_partner_imports')->orderByDesc('data_year')->get()])->render()]);
    }
}
