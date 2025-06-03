<?php

namespace Modules\PerhitunganKPIDosen\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\RectorateDosen;

class PerhitunganKPIDosenController extends Controller
{
    /**
     * Display a listing of the resource with loadPage helper.
     * @return Renderable
     */
    public function index()
    {
        return loadPage('perhitungankpidosen::index');
    }

    /**
     * Initialize a Datatable.
     * @return Renderable
     */
    public function init_table(Request $request){
        $data = $request->all();
        $kode_dosen = $data['kode_dosen'];
        $year = $data['year'];

        $html = '
            <tr id="package_empty">
                <td colspan="5" id="empty-message-package" class="text-center">No Records Found</td>
            </tr>
        ';

        $bindings = [
            'year' => $year,
            // 'month' => $month,
            // 'period' => $period,
        ];

        $query = 'SELECT
            dd.kode_dosen,
            dd.nama_dosen,
            dd.ft_dosen,
            dd.jja_dosen,
            dd.pendidikan_dosen,
            dd.jurusan_dosen,
            COALESCE(SUM(CASE 
                WHEN rd.tipe_publikasi = "Nscopus" THEN rd.bobot
                ELSE 0
            END), 0) AS jml_nscopus,
            COALESCE(SUM(CASE 
                WHEN rd.tipe_publikasi = "Scopus" THEN rd.bobot
                ELSE 0
            END), 0) AS jml_scopus
            FROM database_dosen dd
            LEFT JOIN rectorate_dosen rd 
            ON rd.kode_dosen = dd.kode_dosen
            AND rd.year = :year';

        if ($kode_dosen && $kode_dosen !== '') {
            $query .= ' WHERE dd.kode_dosen = :kode_dosen';
            $bindings['kode_dosen'] = $kode_dosen;
        }

        $query .= ' GROUP BY 
            dd.kode_dosen, dd.nama_dosen, dd.ft_dosen, dd.jja_dosen, 
            dd.pendidikan_dosen, dd.jurusan_dosen, dd.maxscopuskonf_dosen ORDER BY dd.nama_dosen ASC';

        $select = \DB::select($query, $bindings);
        
        $kodeDosen = $kode_dosen;
        $ftDosen = $select[0]->ft_dosen;
        $jjaDosen = $select[0]->jja_dosen;
        $pendidikanDosen = $select[0]->pendidikan_dosen;
        $nscopus = floatval(str_replace(',', '.', $select[0]->jml_nscopus));
        $scopus = floatval(str_replace(',', '.', $select[0]->jml_scopus));
        // $kpis = tableKPI($kodeDosen, $nscopus, $scopus);
        if($ftDosen == 'Functional'){
            if($jjaDosen == 'TP'){
                if($pendidikanDosen == 'S1' || $pendidikanDosen == 'S2'){
                    $kpi = TP12Func($kodeDosen);
                }else if($pendidikanDosen == 'S3'){
                    $kpi = AA3TP3LK2Func($kodeDosen);
                }
            }else if($jjaDosen == 'AA'){
                if($pendidikanDosen == 'S2'){
                    $kpi = AA2Func($kodeDosen);
                }else if($pendidikanDosen == 'S3'){
                    $kpi = AA3TP3LK2Func($kodeDosen);
                }
            }else if($jjaDosen == 'L'){
                if($pendidikanDosen == 'S2'){
                    $kpi = L2Func($kodeDosen);
                }else if($pendidikanDosen == 'S3'){
                    $kpi = L3LK3Func($kodeDosen);
                }
            }else if($jjaDosen == 'LK'){
                if($pendidikanDosen == 'S2'){
                    $kpi = AA3TP3LK2Func($kodeDosen);
                }else if($pendidikanDosen == 'S3'){
                    $kpi = L3LK3Func($kodeDosen);
                }
            }else if($jjaDosen == 'GB'){
                $kpi = GBFunc($kodeDosen);
            }
        }else{
            if($jjaDosen == 'TP'){
                if($pendidikanDosen == 'S1' || $pendidikanDosen == 'S2'){
                    $kpi = TP12Prof($kodeDosen);
                }else if($pendidikanDosen == 'S3'){
                    $kpi = AA3TP3LK2Prof($kodeDosen);
                }
            }else if($jjaDosen == 'AA'){
                if($pendidikanDosen == 'S2'){
                    $kpi = AA2Prof($kodeDosen);
                }else if($pendidikanDosen == 'S3'){
                    $kpi = AA3TP3LK2Prof($kodeDosen);
                }
            }else if($jjaDosen == 'L'){
                if($pendidikanDosen == 'S2'){
                    $kpi = L2Prof($kodeDosen);
                }else if($pendidikanDosen == 'S3'){
                    $kpi = L3Prof($kodeDosen);
                }
            }else if($jjaDosen == 'LK'){
                $kpi = AA3TP3LK2Prof($kodeDosen);
            }
        }

        $rectorate = RectorateDosen::where('kode_dosen', $kode_dosen)->where('year', $year)->get();
        if($rectorate){
            $html = '';
            foreach ($rectorate as $key => $value) {
                $html .= '
                    <tr>
                        <td>
                            '.$value->title.'
                        </td>
                        <td>
                            '.$value->jenis.'
                        </td>
                        <td>
                            '.$value->tipe_publikasi.'
                        </td>
                        <td>
                            '.$value->quartile_jurnal.'
                        </td>
                        <td>
                            '.$value->bobot.'
                        </td>
                    </tr>
                ';
            }
        }

        $arr_data = [
            'main_data' => $select[0],
            'kpi' => $kpi,
            'html' => $html
        ];

        return response()->json($arr_data, 200);
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
