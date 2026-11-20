<?php

namespace Modules\RoadmapLRC\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Lecturers\ProdiFmRoadmap;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Modules\RoadmapLRC\Services\RoadmapWorkbook;

class RoadmapLRCController extends Controller
{
    /**
     * The FM count roadmap per study program, laid out like sheet JJP.
     */
    public function index(ProdiFmRoadmap $roadmap)
    {
        return loadPage('roadmaplrc::index', ['roadmap' => $roadmap->table()]);
    }

    public function previewUpload(Request $request, RoadmapWorkbook $workbook): JsonResponse
    {
        abort_unless(Auth::check(), 403, 'Silakan login untuk mengunggah roadmap.');
        $request->validate(['roadmap' => ['required', 'file', 'mimes:xlsx', 'extensions:xlsx', 'max:20480']]);
        $path = $request->file('roadmap')->getRealPath();
        $parsed = $workbook->read($path);

        return response()->json([
            'summary' => $parsed['summary'], 'preview_hash' => hash_file('sha256', $path),
            'preview_html' => view('roadmaplrc::upload_preview', $parsed)->render(),
        ]);
    }

    public function importUpload(Request $request, RoadmapWorkbook $workbook, ProdiFmRoadmap $roadmap): JsonResponse
    {
        abort_unless(Auth::check(), 403, 'Silakan login untuk mengunggah roadmap.');
        $validated = $request->validate([
            'roadmap' => ['required', 'file', 'mimes:xlsx', 'extensions:xlsx', 'max:20480'],
            'confirmed' => ['accepted'],
            'preview_hash' => ['required', 'string', 'size:64', 'regex:/^[a-f0-9]{64}$/'],
        ]);
        $path = $request->file('roadmap')->getRealPath();
        if (! hash_equals($validated['preview_hash'], hash_file('sha256', $path))) {
            throw ValidationException::withMessages(['roadmap' => 'File berubah setelah pratinjau. Baca ulang pratinjau sebelum mengimpor.']);
        }
        $summary = $workbook->import($path);
        $table = $roadmap->table();
        $years = $table['years'];

        return response()->json([
            'summary' => $summary,
            'message' => "Upload roadmap selesai: {$summary['inserted']} prodi/tahun ditambahkan, {$summary['updated']} diperbarui, {$summary['unchanged']} tetap sama. {$summary['mismatches']} isian memiliki S2 + S3 berbeda dari Jumlah FM.",
            'table_html' => view('roadmaplrc::table', ['roadmap' => $table])->render(),
            'period_label' => $years ? ', '.head($years).'–'.last($years) : '',
        ]);
    }

    /**
     * Sets one number of the roadmap (FM count, S2 or S3 of a program year) and its note.
     */
    public function update(Request $request, ProdiFmRoadmap $roadmap): JsonResponse
    {
        abort_unless(Auth::check(), 403, 'Silakan login untuk mengubah roadmap.');
        $row = $roadmap->update($request->only(['id', 'field', 'nilai', 'catatan']));
        $field = ProdiFmRoadmap::FIELDS[$request->input('field')];
        $value = $row->{$field['column']};

        return response()->json([
            'message' => "{$field['label']} {$row->prodi} {$row->year} disimpan: {$value}.",
            'table_html' => view('roadmaplrc::table', ['roadmap' => $roadmap->table()])->render(),
        ]);
    }

    /**
     * Initialize a Datatable.
     *
     * @return Renderable
     */
    public function init_table() {}

    /**
     * Store a newly created resource in storage.
     *
     * @return Renderable
     */
    public function create(Request $request)
    {
        $data = $request->all();
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function read(Request $request)
    {
        $data = $request->all();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function delete(Request $request)
    {
        $data = $request->all();
    }
}
