<?php

namespace Modules\Home\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\DataDosen;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource with loadPage helper.
     * @return Renderable
     */
    public function index()
    {
        $jumlah_fm = \DB::table('v_statistik_prodi')->select(\DB::raw('SUM(jml_fm) as total_fm'))->get();
        $total_fm = $jumlah_fm[0]->total_fm;
        $fm = \DB::table('v_statistik_prodi')
        ->get();
        // dd($fm);
        // exit;
        $array_nama = [];
        $array_fm = [];
        foreach ($fm as $k) {
            array_push($array_nama, $k->nama_gugus_binaan);
            array_push($array_fm, $k->jml_fm);
        }

        return loadPage('home::index', compact('total_fm', 'array_nama', 'array_fm'));
    }

    /**
     * Initialize a Datatable.
     * @return Renderable
     */
    public function init_table()
    {
        $query = DataDosen::query();
        $query->limit(5);
        $query->inRandomOrder();
        $query = $query->get();
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
