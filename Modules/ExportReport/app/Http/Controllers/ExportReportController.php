<?php

namespace Modules\ExportReport\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Dosen;

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
        $rowspan = 1;
        $no = 1;

        $query = Dosen::orderBy('jurusan_dosen', 'asc')->get();

        $previousJurusan = null;

        foreach ($query as $index => $row) {
            $bgColor = ($no % 2 == 0) ? 'white' : 'white';
        
            // Check if jurusan_dosen changed (or first iteration)
            if ($previousJurusan !== $row->jurusan_dosen) {
                // Optional: Add a group label row (can be removed if not needed)
                $reportHtml .= '<tr>';
                $reportHtml .= '<td colspan="8" style="background-color: #f0f0f0; font-weight: bold; text-align:left;">JURUSAN: ' . htmlspecialchars($row->jurusan_dosen) . '</td>';
                $reportHtml .= '</tr>';
        
                // Re-add the table header row
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
            $reportHtml .= '<td class="t-left" style="background-color:' . $bgColor . ';" rowspan="' . $rowspan . '">' . number_format(0, 0, '', '.') . '</td>';
            $reportHtml .= '<td class="t-left" style="background-color:' . $bgColor . ';" rowspan="' . $rowspan . '">' . number_format(0, 0, '', '.') . '</td>';
            $reportHtml .= '<td class="t-left" style="background-color:' . $bgColor . ';" rowspan="' . $rowspan . '">' . number_format(0, 0, '', '.') . '</td>';
            $reportHtml .= '</tr>';
        
            $no++;
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
            </table>';

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
