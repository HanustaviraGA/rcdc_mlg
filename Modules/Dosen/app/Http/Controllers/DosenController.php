<?php

namespace Modules\Dosen\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Dosen;
use App\Models\IdentitasDosen;

class DosenController extends Controller
{
    /**
     * Display a listing of the resource with loadPage helper.
     * @return Renderable
     */
    public function index()
    {
        return loadPage('dosen::index');
    }

    /**
     * Initialize a Datatable.
     * @return Renderable
     */
    public function init_table(Request $request)
    {
        $data = $request->all();
        $query = Dosen::query();
        $query->leftJoin('identitas_dosen', 'identitas_dosen.kode_dosen', '=', 'database_dosen.kode_dosen');
        $query->select('database_dosen.*', 'identitas_dosen.email_dosen', 'identitas_dosen.telp_dosen');
        if($data['prodi'] !== 'All'){
            $query->where('database_dosen.jurusan_dosen', $data['prodi']);
        }
        $query->orderBy('database_dosen.nama_dosen', 'asc');
        $query->get();
        return select_table($query);
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
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request)
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
