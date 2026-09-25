<?php

namespace Modules\ExportReport\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Publications\ReportWorkbookReader;
use App\Services\Reports\MonthlyPublicationReport;
use App\Services\Reports\PublicationDocx;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ExportReportController extends Controller
{
    public function index()
    {
        abort_unless(Auth::check(), 403);
        $savedDraft = null;
        foreach (session('publication_report_drafts', []) as $id => $draft) {
            if ($draft['user_id'] === (string) Auth::id()) {
                $savedDraft = ['id' => $id, 'report' => $draft['report']];
            }
        }

        return loadPage('exportreport::index', compact('savedDraft'));
    }

    public function generate(Request $request, MonthlyPublicationReport $service)
    {
        abort_unless(Auth::check(), 403);
        $data = $request->validate(['year' => ['required', 'integer', 'between:2000,2100'], 'month' => ['required', 'integer', 'between:1,12']]);
        $report = $service->generate((int) $data['year'], (int) $data['month']);
        $id = (string) Str::uuid();
        $drafts = array_slice($request->session()->get('publication_report_drafts', []), -4, null, true);
        $drafts[$id] = ['user_id' => (string) Auth::id(), 'report' => $report];
        $request->session()->put('publication_report_drafts', $drafts);

        return response()->json(['id' => $id, 'report' => $report]);
    }

    public function save(Request $request)
    {
        $report = $this->validatedReport($request);

        return response()->json(['message' => 'Draft tersimpan untuk sesi ini.', 'report' => $report]);
    }

    public function download(Request $request, PublicationDocx $docx)
    {
        $report = $this->validatedReport($request);
        $path = $docx->render($report);

        return response()->download($path, sprintf('Laporan Publikasi Scopus FM & MHS (%04d-%02d).docx', $report['year'], $report['month']),
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])->deleteFileAfterSend(true);
    }

    private function validatedReport(Request $request): array
    {
        abort_unless(Auth::check(), 403);
        $data = $request->validate(['id' => ['required', 'uuid'], 'title' => ['required', 'string', 'max:300'],
            'period_label' => ['required', 'string', 'max:100'], 'tables' => ['required', 'array', 'max:100'],
            'tables.*.id' => ['required', 'string', 'distinct'], 'tables.*.rows' => ['present', 'array', 'max:2000'],
            'tables.*.rows.*' => ['required', 'array', 'max:10'], 'tables.*.rows.*.*' => ['nullable', 'string', 'max:5000']]);
        $drafts = $request->session()->get('publication_report_drafts', []);
        $draft = $drafts[$data['id']] ?? null;
        abort_unless($draft && $draft['user_id'] === (string) Auth::id(), 404);
        $report = $draft['report'];
        $incoming = collect($data['tables'])->keyBy('id');
        if ($incoming->keys()->sort()->values()->all() !== collect($report['tables'])->pluck('id')->sort()->values()->all()) {
            throw ValidationException::withMessages(['tables' => 'Bagian laporan berubah. Generate ulang terlebih dahulu.']);
        }
        $report['title'] = $data['title'];
        $report['period_label'] = $data['period_label'];
        foreach ($report['tables'] as &$table) {
            $rows = $incoming[$table['id']]['rows'];
            foreach ($rows as &$row) {
                if (count($row) !== count($table['headers'])) {
                    throw ValidationException::withMessages(['tables' => 'Jumlah kolom laporan tidak sesuai template.']);
                }
                $row = array_map(fn ($cell) => (string) ($cell ?? ''), array_values($row));
                $numeric = str_ends_with($table['id'], '_summary') ? [1, 2, 3] : (str_starts_with($table['id'], 'fm_') ? [5, 6, 7, 8] : []);
                foreach ($numeric as $column) {
                    if ($row[$column] === '') {
                        continue;
                    }
                    $value = ReportWorkbookReader::number($row[$column]);
                    if ($value === null || ! is_finite($value) || $value < 0 || $value > 1e9) {
                        throw ValidationException::withMessages(['tables' => 'Target, realisasi, jumlah, dan skor harus berupa angka nol atau positif.']);
                    }
                    $isScore = (str_ends_with($table['id'], '_summary') && $column === 3) || (! str_ends_with($table['id'], '_summary') && $column === 8);
                    if ($isScore && ($value > 6 || floor($value) !== $value)) {
                        throw ValidationException::withMessages(['tables' => 'Skor harus berupa bilangan bulat 0 sampai 6.']);
                    }
                    if (! str_ends_with($table['id'], '_summary') && $column === 7 && floor($value) !== $value) {
                        throw ValidationException::withMessages(['tables' => 'Jumlah first author harus berupa bilangan bulat.']);
                    }
                }
            }
            unset($row);
            $table['rows'] = $rows;
        }
        unset($table);
        $drafts[$data['id']]['report'] = $report;
        $request->session()->put('publication_report_drafts', $drafts);

        return $report;
    }
}
