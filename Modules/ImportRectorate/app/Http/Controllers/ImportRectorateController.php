<?php

namespace Modules\ImportRectorate\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\RectorateMahasiswa;
use App\Models\RectorateDosen;

class ImportRectorateController extends Controller
{

    /**
     * Display a listing of the resource with loadPage helper.
     * @return Renderable
     */
    public function index()
    {
        return loadPage('importrectorate::index');
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
        $period = $data['period'];
        $month = $data['month'];
        $fmmhs = $data['fmmhs'];
        // if($month == 1 || $month == 2 || $month == 3){
        //     $period = 1;
        // }else if($month == 4 || $month == 5 || $month == 6){
        //     $period = 2;
        // }else if($month == 7 || $month == 8 || $month == 9){
        //     $period = 3;
        // }else if($month == 10 || $month == 11 || $month == 12){
        //     $period = 4;
        // }
        $year = $data['year'];

        if($fmmhs == 'FM'){
            $check = RectorateDosen::where('year', $year)->where('period', $period)->where('month', $month)->exists();
            if($check){
                RectorateDosen::where('year', $year)->where('period', $period)->where('month', $month)->delete();
            }
        }else{
            $check = RectorateMahasiswa::where('year', $year)->where('period', $period)->where('month', $month)->exists();
            if($check){
                RectorateMahasiswa::where('year', $year)->where('period', $period)->where('month', $month)->delete();
            }
        }
        
        // Import file
        $csv = $request->file('rectorate');
        $extension = $csv->getClientOriginalExtension();
        $filename = 'Y'.$year.'M'.$month.'P'.$period.'T'.$fmmhs.'.'.$extension;
        $csv->move(public_path('uploads/rectorate'), $filename);
        if (($handle = fopen(public_path('uploads/rectorate/'.$filename), "r")) !== FALSE) {
            $row = 0;
            while (($read = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $row++;
                if($fmmhs == 'FM'){
                    if ($row == 1 || $read[9] !== 'MALANG'){
                        continue;
                    }
                    // Adjustment untuk scopus
                    $bobot = floatval(str_replace(',', '.', $read[12]));
                    if($read[16] == 'Scopus'){
                        // Kalau tipenya jurnal
                        if($read[15] == 'Jurnal'){
                            if($read[21] == 'Q1'){
                                $bobot = 3;
                            }else if($read[21] == 'Q2'){
                                $bobot = 2;
                            }else if($read[21] == 'Q3' || $read[21] == 'Q4'){
                                $bobot = 1;
                            }else if($read[21] == 'Q2/Q3'){ // Samakan dengan Q3
                                $bobot = 1;
                            }
                        // Kalau tipenya seminar/konferensi
                        }else if($read[15] == 'Seminar'){
                            $bobot = 1;
                        }
                    }
                    RectorateDosen::create([
                        'id_rectorate' => md5(rand(0, 100).generateCode().date('Y-m-d H:i:s')),
                        'request_code' => $read[0],
                        'author' => $read[1],
                        'kode_dosen' => $read[3],
                        'first_author' => $read[10],
                        'sumber_paper' => $read[11],
                        'bobot' => $bobot,
                        'bobot_asli' => floatval(str_replace(',', '.', $read[12])),
                        'submitted' => $read[13],
                        'status' => $read[14],
                        'jenis' => $read[15],
                        'tipe_publikasi' => $read[16],
                        'title' => $read[17],
                        'scopus_year' => $read[18],
                        'source_title' => $read[19],
                        'publisher' => $read[20],
                        'quartile_jurnal' => $read[21],
                        'year' => $year,
                        'period' => $period,
                        'month' => $month,
                    ]);
                }else{
                    if($row == 1 || $read[6] !== 'MALANG'){
                        continue;
                    }
                    $bobot = floatval(str_replace(',', '.', $read[9]));
                    RectorateMahasiswa::create([
                        'id_rectorate' => md5(rand(0, 100).generateCode().date('Y-m-d H:i:s')),
                        'request_code' => $read[0],
                        'author' => $read[1],
                        'fm_author' => $read[2],
                        'sf' => $read[4],
                        'dept' => $read[5],
                        'first_author' => $read[7],
                        'sumber_paper' => $read[8],
                        'bobot' => $bobot,
                        // 'bobot_asli' => $read[9],
                        'submitted' => $read[10],
                        'status' => $read[11],
                        'jenis' => $read[12],
                        'tipe_publikasi' => $read[13],
                        'title' => $read[14],
                        'scopus_year' => $read[15],
                        'source_title' => $read[16],
                        // 'publisher' => $read[20],
                        'quartile_jurnal' => $read[17],
                        'year' => $year,
                        'period' => $period,
                        'month' => $month,
                    ]);
                }
            }
            fclose($handle);
        }
        return response()->json(['success' => true], 200);
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
