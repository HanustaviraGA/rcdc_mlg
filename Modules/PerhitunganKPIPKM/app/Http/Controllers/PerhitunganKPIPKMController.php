<?php

namespace Modules\PerhitunganKPIPKM\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\PKMDosen;
use App\Models\Dosen;

class PerhitunganKPIPKMController extends Controller
{
    /**
     * Display a listing of the resource with loadPage helper.
     * @return Renderable
     */
    public function index()
    {
        return loadPage('perhitungankpipkm::index');
    }

    /**
     * Initialize a Datatable.
     * @return Renderable
     */
    public function init_table(Request $request)
    {
        $data = $request->all();
        $kode_dosen = $data['kode_dosen'];
        $html = '
            <tr id="package_empty">
                <td colspan="6" id="empty-message-package" class="text-center">Tidak ada Data</td>
            </tr>
        ';
        $dosen = Dosen::where('kode_dosen', $kode_dosen)->select('nama_dosen')->first();
        if($dosen){
            $score = PKMScore($kode_dosen, $data['year'], $data['period']);
            $list_pkm = PKMDosen::where('kode_dosen', $kode_dosen)->where('year', $data['year'])->where('period', $data['period'])->get();
            if($list_pkm->count() > 0){
                $html = '';
                foreach($list_pkm as $value){
                    $html .= '
                        <tr>
                            <td>
                                '.$value->periode.'
                            </td>
                            <td>
                                '.$value->judul_pkm.'
                            </td>
                            <td>
                                '.$value->jenis_pkm.'
                            </td>
                            <td>
                                '.$value->peserta.'
                            </td>
                            <td>
                                '.$value->skema_pendanaan.'
                            </td>
                            <td>
                                '.$value->nama_mahasiswa.'
                            </td>
                        </tr>
                    ';
                }
            }
            return response()->json(['success' => true, 'dosen' => $dosen['nama_dosen'], 'skor' => $score, 'html' => $html], 200);
        }else{
            return response()->json(['success' => false, 'html' => $html, 'message' => 'Data dosen tidak ditemukan !'], 404);
        }
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
