<?php

namespace Modules\RCDCEvent\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\PresensiEvent;
use App\Models\Dosen;

class RCDCEventController extends Controller
{
    /**
     * Display a listing of the resource with loadPage helper.
     * @return Renderable
     */
    public function index()
    {
        return loadPage('rcdcevent::index');
    }

    /**
     * Initialize a Datatable.
     * @return Renderable
     */
    public function init_table(Request $request)
    {
        $data = $request->all();
        $number = (int)$data['number'];
        $query = PresensiEvent::all();
        foreach($query as $presensi){
            $dosen = Dosen::where('kode_dosen', $presensi['kode_dosen'])->first();
            createSertif([
                'name' => $dosen['nama_dosen'],
                'code' => $presensi['kode_dosen'],
                'eventname' => $data['eventname'],
                'date' => date('Y-m-d'),
                'number' => $number,
            ]);
            $number++;
        }
        return response()->json(['success' => true, 'number' => $number], 200);
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
