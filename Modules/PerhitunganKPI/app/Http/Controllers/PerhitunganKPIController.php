<?php

namespace Modules\PerhitunganKPI\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Dosen;

class PerhitunganKPIController extends Controller
{
    /**
     * Display a listing of the resource with loadPage helper.
     * @return Renderable
     */
    public function index()
    {
        return loadPage('perhitungankpi::index');
    }

    /**
     * Initialize a Datatable.
     * @return Renderable
     */
    public function init_table_file()
    {
        $html = '';
        if (($handle = fopen("DOSEN.csv", "r")) !== FALSE) {
            $row = 0;
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $row++;
                if ($row == 1){
                    continue;
                }
                $kodeDosen = $data[0];
                $nscopus = floatval(str_replace(',', '.', $data[6]));
                $scopus = floatval(str_replace(',', '.', $data[7]));
                $kpis = tableKPI($kodeDosen, $nscopus, $scopus);
                // if($kpis['score'] > 0){
                     
                // }
                $html .= '
                        <tr>
                            <td>   
                                <input type="hidden" name="kd_dosen['.$kpis['dosen']['kode_dosen'].']" value="'.$kpis['dosen']['kode_dosen'].'">
                                <div class="d-flex justify-content-start flex-column">
                                    <a href="javascript:void(0)" class="text-dark fw-bolder text-hover-primary fs-6">'.$kpis['dosen']['nama_dosen'].'</a>
                                    <span class="text-muted fw-bold text-muted d-block fs-7">'.$kpis['dosen']['kode_dosen'].' | '.$kpis['dosen']['ft_dosen'].' - '.$kpis['dosen']['jja_dosen'].' '.$kpis['dosen']['pendidikan_dosen'].'</span>
                                </div>
                            </td>
                            <td>
                                '.$kpis['dosen']['jurusan_dosen'].'
                            </td>
                            <td>
                                <input name="nscopus['.$kpis['dosen']['kode_dosen'].']" type="number" value="'.$kpis['nscopus'].'" class="form-control form-control bg-gray-100 w-100">
                            </td>
                            <td>
                                <input name="scopus['.$kpis['dosen']['kode_dosen'].']" type="number" value="'.$kpis['scopus'].'" class="form-control form-control bg-gray-100 w-100">
                            </td>
                            <td>
                                <input name="score['.$kpis['dosen']['kode_dosen'].']" type="number" value="'.$kpis['score'].'" class="form-control form-control bg-gray-100 w-100">
                            </td>
                        </tr>'
                    ;
            }
            fclose($handle);
        }
        return response()->json(['html' => $html], 200);
    }

    public function init_table(Request $request){
        $data = $request->all();
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
