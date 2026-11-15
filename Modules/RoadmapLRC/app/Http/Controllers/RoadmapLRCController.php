<?php

namespace Modules\RoadmapLRC\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Lecturers\ProdiFmRoadmap;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class RoadmapLRCController extends Controller
{
    /**
     * The FM count roadmap per study program, laid out like sheet JJP.
     */
    public function index(ProdiFmRoadmap $roadmap)
    {
        return loadPage('roadmaplrc::index', ['roadmap' => $roadmap->table()]);
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
     * @return Renderable
     */
    public function init_table()
    {

    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function create(Request $request)
    {
        $data = $request->all();
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function read(Request $request)
    {
        $data = $request->all();
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function delete(Request $request)
    {
        $data = $request->all();
    }
}
