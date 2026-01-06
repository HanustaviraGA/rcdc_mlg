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
        $year = (int) $request->input('year');
        $month = (int) $request->input('month');
        // if($month == 1 || $month == 2 || $month == 3){
        //     $period = 1;
        // }else if($month == 4 || $month == 5 || $month == 6){
        //     $period = 2;
        // }else if($month == 7 || $month == 8 || $month == 9){
        //     $period = 3;
        // }else if($month == 10 || $month == 11 || $month == 12){
        //     $period = 4;
        // }
        $period = (int) $request->input('period');

        // $year = 2025;
        // $month = 4;
        // $period = 1;

        $prodi = $request->input('prodi');
        $html = '';

        $bindings = [
            'year' => $year,
            'month' => $month,
            'period' => $period,
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
            AND rd.year = :year
            AND rd.period = :period
            AND rd.month = :month';

        if ($prodi && $prodi !== 'Semua Prodi') {
            $query .= ' WHERE dd.jurusan_dosen = :prodi';
            $bindings['prodi'] = $prodi;
        }

        $query .= ' GROUP BY 
            dd.kode_dosen, dd.nama_dosen, dd.ft_dosen, dd.jja_dosen, 
            dd.pendidikan_dosen, dd.jurusan_dosen, dd.maxscopuskonf_dosen ORDER BY dd.nama_dosen ASC';

        $select = \DB::select($query, $bindings);

        $ctr = 1;
        foreach ($select as $key => $value) {
            $kodeDosen = $value->kode_dosen;
            $ftDosen = $value->ft_dosen;
            // $jjaDosen = $value->jja_dosen;
            $pendidikanDosen = $value->pendidikan_dosen;
            $nscopus = floatval(str_replace(',', '.', $value->jml_nscopus));
            $scopus = floatval(str_replace(',', '.', $value->jml_scopus));
            // $kpis = tableKPI($kodeDosen, $nscopus, $scopus);
            preg_match('/^[A-Za-z]+/', $value->jja_dosen, $match);
            $jjaDosen = $match[0];
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
            $html .= '
                <tr>
                    <td>
                        '.$ctr.'
                    </td>
                    <td>   
                        <input type="hidden" name="kd_dosen['.$value->kode_dosen.']" value="'.$value->kode_dosen.'">
                        <div class="d-flex justify-content-start flex-column">
                            <a href="javascript:void(0)" class="text-dark fw-bolder text-hover-primary fs-6">'.$value->nama_dosen.'</a>
                            <span class="text-muted fw-bold text-muted d-block fs-7">'.$value->kode_dosen.' | '.$value->ft_dosen.' - '.$value->jja_dosen.' '.$value->pendidikan_dosen.'</span>
                        </div>
                    </td>
                    <td>
                        '.$value->jurusan_dosen.'
                    </td>
                    <td>
                        <input name="nscopus['.$value->kode_dosen.']" type="number" value="'.$value->jml_nscopus.'" class="form-control form-control bg-gray-100 w-100">
                    </td>
                    <td>
                        <input name="scopus['.$value->kode_dosen.']" type="number" value="'.$value->jml_scopus.'" class="form-control form-control bg-gray-100 w-100">
                    </td>
                    <td>
                        <input name="score['.$value->kode_dosen.']" type="number" value="'.$kpi['kpi'].'" class="form-control form-control bg-gray-100 w-100">
                    </td>
                </tr>
            ';
            $ctr++;
        }
        return response()->json(['html' => $html], 200);
    }

    public function cek_kpi(){
        $query = Dosen::where('ft_dosen', 'Functional')->orderBy('nama_dosen', 'ASC')->get();
        $array = [];
        foreach($query as $dosen){
            if($dosen['ft_dosen'] == 'Functional'){
                if($dosen['jja_dosen'] == 'TP'){
                    if($dosen['pendidikan_dosen'] == 'S1' || $dosen['pendidikan_dosen'] == 'S2'){
                        $kpi = TP12Func($dosen['kode_dosen']);
                    }else if($dosen['pendidikan_dosen'] == 'S3'){
                        $kpi = AA3TP3LK2Func($dosen['kode_dosen']);
                    }
                }else if($dosen['jja_dosen'] == 'AA'){
                    if($dosen['pendidikan_dosen'] == 'S2'){
                        $kpi = AA2Func($dosen['kode_dosen']);
                    }else if($dosen['pendidikan_dosen'] == 'S3'){
                        $kpi = AA3TP3LK2Func($dosen['kode_dosen']);
                    }
                }else if($dosen['jja_dosen'] == 'L'){
                    if($dosen['pendidikan_dosen'] == 'S2'){
                        $kpi = L2Func($dosen['kode_dosen']);
                    }else if($dosen['pendidikan_dosen'] == 'S3'){
                        $kpi = L3LK3Func($dosen['kode_dosen']);
                    }
                }else if($dosen['jja_dosen'] == 'LK'){
                    if($dosen['pendidikan_dosen'] == 'S2'){
                        $kpi = AA3TP3LK2Func($dosen['kode_dosen']);
                    }else if($dosen['pendidikan_dosen'] == 'S3'){
                        $kpi = L3LK3Func($dosen['kode_dosen']);
                    }
                }else if($dosen['jja_dosen'] == 'GB'){
                    $kpi = GBFunc($dosen['kode_dosen']);
                }
            }else{
                if($dosen['jja_dosen'] == 'TP'){
                    if($dosen['pendidikan_dosen'] == 'S1' || $dosen['pendidikan_dosen'] == 'S2'){
                        $kpi = TP12Prof($dosen['kode_dosen']);
                    }else if($dosen['pendidikan_dosen'] == 'S3'){
                        $kpi = AA3TP3LK2Prof($dosen['kode_dosen']);
                    }
                }else if($dosen['jja_dosen'] == 'AA'){
                    if($dosen['pendidikan_dosen'] == 'S2'){
                        $kpi = AA2Prof($dosen['kode_dosen']);
                    }else if($dosen['pendidikan_dosen'] == 'S3'){
                        $kpi = AA3TP3LK2Prof($dosen['kode_dosen']);
                    }
                }else if($dosen['jja_dosen'] == 'L'){
                    if($dosen['pendidikan_dosen'] == 'S2'){
                        $kpi = L2Prof($dosen['kode_dosen']);
                    }else if($dosen['pendidikan_dosen'] == 'S3'){
                        $kpi = L3Prof($dosen['kode_dosen']);
                    }
                }else if($dosen['jja_dosen'] == 'LK'){
                    $kpi = AA3TP3LK2Prof($dosen['kode_dosen']);
                }
            }
            $input = [
                'nama_dosen' => $dosen['nama_dosen'],
                'kpi' => $kpi
            ];
            array_push($array, $input);
        }
        echo var_dump($array);
        exit;
        return response()->json(['success' => true, 'data' => $array], 200);
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
