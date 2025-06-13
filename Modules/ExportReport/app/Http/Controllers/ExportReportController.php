<?php

namespace Modules\ExportReport\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Dosen;
use App\Models\RectorateMahasiswa;

class ExportReportController extends Controller
{
    /**
     * Display a listing of the resource with loadPage helper.
     * @return Renderable
     */
    public function index()
    {
        return loadPage('exportreport::index');
    }

    /**
     * Initialize a Datatable.
     * @return Renderable
     */
    public function init_table()
    {

    }

    public function generate_pdf(Request $request)
    {
        $data = $request->all();

        $reportHtml = '';
        $reportHtmlMHS = '';
        $rowspan = 1;
        $no = 1;
        $rowspanMHS = 1;
        $noMHS = 1;

        $year = date('Y');
        $month = date('m') - 1;

        dd($month);
        exit;

        // $period = 2;
        if($month == 1 || $month == 2 || $month == 3){
            $period = 1;
        }else if($month == 4 || $month == 5 || $month == 6){
            $period = 2;
        }else if($month == 7 || $month == 8 || $month == 9){
            $period = 3;
        }else if($month == 10 || $month == 11 || $month == 12){
            $period = 4;
        }

        // $query = Dosen::orderBy('jurusan_dosen', 'asc')->get();
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

        if (isset($prodi) && $prodi !== 'Semua Prodi') {
            $query .= ' WHERE dd.jurusan_dosen = :prodi';
            $bindings['prodi'] = $prodi;
        }

        $query .= ' GROUP BY 
            dd.kode_dosen, dd.nama_dosen, dd.ft_dosen, dd.jja_dosen, 
            dd.pendidikan_dosen, dd.jurusan_dosen, dd.maxscopuskonf_dosen ORDER BY dd.jurusan_dosen ASC';

        $select = \DB::select($query, $bindings);

        $previousJurusan = null;
        $previousJurusanMHS = null;

        foreach ($select as $index => $row) {
            $kodeDosen = $row->kode_dosen;
            $ftDosen = $row->ft_dosen;
            $jjaDosen = $row->jja_dosen;
            $pendidikanDosen = $row->pendidikan_dosen;
            $nscopus = floatval(str_replace(',', '.', $row->jml_nscopus));
            $scopus = floatval(str_replace(',', '.', $row->jml_scopus));
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

            $bgColor = ($no % 2 == 0) ? 'white' : 'white';
        
            if ($previousJurusan !== $row->jurusan_dosen) {
                $reportHtml .= '<tr>';
                $reportHtml .= '<td colspan="8" style="background-color: #f0f0f0; font-weight: bold; text-align:left; margin-top: 5px;">PRODI: ' . htmlspecialchars($row->jurusan_dosen) . '</td>';
                $reportHtml .= '</tr>';
        
                $reportHtml .= '
                    <tr>
                        <th class="t-center head" style="width:10%">KODE DOSEN</th>
                        <th class="t-center head" style="width:30%">NAMA DOSEN</th>
                        <th class="t-center head" style="width:10%">JURUSAN BINAAN</th>
                        <th class="t-center head" style="width:10%">JJA</th>
                        <th class="t-center head" style="width:15%">FACULTY TYPE</th>
                        <th class="t-center head" style="width:10%">NON SCOPUS</th>
                        <th class="t-center head" style="width:10%">SCOPUS</th>
                        <th class="t-center head" style="width:10%">SKOR KPI</th>
                    </tr>';
                $previousJurusan = $row->jurusan_dosen;
            }
        
            $reportHtml .= '<tr>';
            $reportHtml .= '<td class="t-center" style="background-color:' . $bgColor . ';" rowspan="' . $rowspan . '">' . $row->kode_dosen . '</td>';
            $reportHtml .= '<td class="t-left" style="background-color:' . $bgColor . ';" rowspan="' . $rowspan . '">' . $row->nama_dosen . '</td>';
            $reportHtml .= '<td class="t-left" style="background-color:' . $bgColor . ';" rowspan="' . $rowspan . '">' . $row->jurusan_dosen . '</td>';
            $reportHtml .= '<td class="t-left" style="background-color:' . $bgColor . ';" rowspan="' . $rowspan . '">' . $row->jja_dosen . '</td>';
            $reportHtml .= '<td class="t-left" style="background-color:' . $bgColor . ';" rowspan="' . $rowspan . '">' . $row->ft_dosen . '</td>';
            $reportHtml .= '<td class="t-left" style="background-color:' . $bgColor . ';" rowspan="' . $rowspan . '">' . $row->jml_nscopus . '</td>';
            $reportHtml .= '<td class="t-left" style="background-color:' . $bgColor . ';" rowspan="' . $rowspan . '">' . $row->jml_scopus . '</td>';
            $reportHtml .= '<td class="t-left" style="background-color:' . $bgColor . ';" rowspan="' . $rowspan . '">' . number_format($kpi['kpi'], 0, '', '.') . '</td>';
            $reportHtml .= '</tr>';
        
            $no++;
        }

        $queryMHS = RectorateMahasiswa::where('year', $year)->where('period', $period)->where('month', $month)->get();
        foreach($queryMHS as $mhs){
            $bgColorMHS = ($no % 2 == 0) ? 'white' : 'white';
            if ($previousJurusanMHS !== $mhs->dept) {
                $reportHtmlMHS .= '<tr>';
                $reportHtmlMHS .= '<td colspan="2" style="background-color: #f0f0f0; font-weight: bold; text-align:left; margin-top: 5px;">PRODI: ' . htmlspecialchars($mhs->dept) . '</td>';
                $reportHtmlMHS .= '</tr>';
        
                $reportHtmlMHS .= '
                    <tr>
                        <th class="t-center head">Mhs Author</th>
                        <th class="t-center head">Title</th>
                    </tr>';
                $previousJurusanMHS = $mhs->dept;
            }
            $reportHtmlMHS .= '<tr>';
            $reportHtmlMHS .= '<td class="t-left" style="background-color:' . $bgColorMHS . ';" rowspan="' . $rowspanMHS . '">' . $mhs->fm_author . '</td>';
            $reportHtmlMHS .= '<td class="t-left" style="background-color:' . $bgColorMHS . ';" rowspan="' . $rowspanMHS . '">' . $mhs->title . '</td>';
            $reportHtmlMHS .= '</tr>';
            $noMHS++;
        }

        $html = '
            <style>
                *, table, p, li {
                    line-height: 1.5;
                    font-size: 11px;
                }
                .kop { text-align: left; display: block; margin: 0 auto; }
                .kop h1 { font-size: 11px; }
                .left, .right { padding: 2px; }
                .right { text-align: right; }
                .t-center { vertical-align: middle !important; text-align: center; }
                .head { background-color: #5a8ed1; }
                .laporan td, .laporan th {
                    border: 1px solid black;
                    border-collapse: collapse;
                    padding: 5px 6px;
                    line-height: 12px;
                }
                table { border-collapse: collapse; width: 100%; }
            </style>

            <div style="text-align:center;">
                <h4 style="margin: 0;">LAPORAN PENCAPAIAN PUBLIKASI SCOPUS FM DAN MAHASISWA</h4>
                <h4 style="margin: 0;">2025 (April)</h4>
            </div>

            <br><br>

            <!-- Tables first -->
            <table width="100%" style="margin-bottom: 10px;">
                <tr>
                    <td width="50%" valign="top" align="center">
                        <strong>Scopus FM</strong><br>
                        <table class="laporan" cellspacing="0" cellpadding="4">
                            <thead>
                                <tr>
                                    <th style="background-color: #5a8ed1;">PRODI</th>
                                    <th style="background-color: #5a8ed1;">TARGET</th>
                                    <th style="background-color: #5a8ed1;">REALIZATION</th>
                                    <th style="background-color: #5a8ed1;">SCORE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Binus @ Malang</td>
                                    <td>74</td>
                                    <td>11</td>
                                    <td>1</td>
                                </tr>
                                <tr>
                                    <td>BC</td>
                                    <td>26</td>
                                    <td>4.5</td>
                                    <td>1</td>
                                </tr>
                                <tr>
                                    <td>CS</td>
                                    <td>16</td>
                                    <td>4.5</td>
                                    <td>1</td>
                                </tr>
                                <tr>
                                    <td>DKV</td>
                                    <td>11</td>
                                    <td>0</td>
                                    <td>1</td>
                                </tr>
                                <tr>
                                    <td>DI</td>
                                    <td>7</td>
                                    <td>0</td>
                                    <td>1</td>
                                </tr>
                                <tr>
                                    <td>ILKOM</td>
                                    <td>7</td>
                                    <td>0</td>
                                    <td>0</td>
                                </tr>
                                <tr>
                                    <td>PR</td>
                                    <td>7</td>
                                    <td>0</td>
                                    <td>0</td>
                                </tr>

                                <tr>
                                    <td>CBDC</td>
                                    <td>-</td>
                                    <td>0</td>
                                    <td>0</td>
                                </tr>
                                <tr>
                                    <td>LC</td>
                                    <td>-</td>
                                    <td>0</td>
                                    <td>0</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td width="50%" valign="top" align="center">
                        <strong>Scopus Mahasiswa</strong><br>
                        <table class="laporan" cellspacing="0" cellpadding="4">
                            <thead>
                                <tr>
                                    <th style="background-color: #5a8ed1;">PRODI</th>
                                    <th style="background-color: #5a8ed1;">TARGET</th>
                                    <th style="background-color: #5a8ed1;">REALIZATION</th>
                                    <th style="background-color: #5a8ed1;">SCORE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Binus @ Malang</td>
                                    <td>74</td>
                                    <td>11</td>
                                    <td>1</td>
                                </tr>
                                <tr>
                                    <td>BC</td>
                                    <td>26</td>
                                    <td>4.5</td>
                                    <td>1</td>
                                </tr>
                                <tr>
                                    <td>CS</td>
                                    <td>16</td>
                                    <td>4.5</td>
                                    <td>1</td>
                                </tr>
                                <tr>
                                    <td>DKV</td>
                                    <td>11</td>
                                    <td>0</td>
                                    <td>1</td>
                                </tr>
                                <tr>
                                    <td>DI</td>
                                    <td>7</td>
                                    <td>0</td>
                                    <td>1</td>
                                </tr>
                                <tr>
                                    <td>ILKOM</td>
                                    <td>7</td>
                                    <td>0</td>
                                    <td>0</td>
                                </tr>
                                <tr>
                                    <td>PR</td>
                                    <td>7</td>
                                    <td>0</td>
                                    <td>0</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>

            <br>

            <!-- Charts go below -->
            <table width="100%" style="margin-top: 10px;">
                <tr>
                    <td width="50%" align="center">
                        <strong>Scopus FM</strong><br>
                        <img src="https://www.skillsyouneed.com/images/graph1.png" width="300">
                    </td>
                    <td width="50%" align="center">
                        <strong>Scopus Mahasiswa</strong><br>
                        <img src="https://www.skillsyouneed.com/images/graph1.png" width="300">
                    </td>
                </tr>
            </table>

            <br><br>
            <br><br>
            <br><br>
            <br><br>

            <table class="laporan" cellspacing="0" style="width:100%;">
                ' . $reportHtml . '
                <tr><td colspan="7" style="border:none; padding:10px"></td></tr>
            </table>
            
            <br><br>

            <div style="text-align:center;">
                <h4 style="margin: 0;">SCOPUS MAHASISWA</h4>
            </div>

            <br><br>
            
            <table class="laporan" cellspacing="0" style="width:100%;">
                ' . $reportHtmlMHS . '
                <tr><td colspan="7" style="border:none; padding:10px"></td></tr>
            </table>
            
            <br><br>';

        $dataprint = ['content' => $html];

        $filename = 'kpi-' . date('his') . '.pdf';
        $pdf = viewPDF($dataprint, [
            'title' => 'Laporan Publikasi',
            'margin_top' => 10,
            'margin_left' => 5,
            'margin_right' => 5,
            'margin_bottom' => 10,
            'orientation' => 'L',
            'filepath' => public_path('uploads/kpi/' . $filename)
        ]);

        return response()->json(['success' => true, 'record' => url('/') . '/public/uploads/kpi/' . $filename], 200);
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
