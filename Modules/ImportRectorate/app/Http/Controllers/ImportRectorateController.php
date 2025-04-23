<?php

namespace Modules\ImportRectorate\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
        $year = $data['year'];
        $check = RectorateDosen::where('year', $year)->where('period', $period)->where('month', $month)->exists();
        if($check){
            RectorateDosen::where('year', $year)->where('period', $period)->where('month', $month)->delete();
        }
        // Import file
        $csv = $request->file('rectorate');
        $extension = $csv->getClientOriginalExtension();
        $filename = 'Y'.$year.'M'.$month.'P'.$period.'.'.$extension;
        $csv->move(public_path('uploads/rectorate'), $filename);
        if (($handle = fopen(public_path('uploads/rectorate/'.$filename), "r")) !== FALSE) {
            $row = 0;
            while (($read = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $row++;
                if ($row == 1 || $read[9] !== 'MALANG'){
                    continue;
                }
                RectorateDosen::create([
                    'id_rectorate' => md5(rand(0, 100).generateCode().date('Y-m-d H:i:s')),
                    'request_code' => $read[0],
                    'author' => $read[1],
                    'kode_dosen' => $read[3],
                    'first_author' => $read[10],
                    'sumber_paper' => $read[11],
                    'bobot' => floatval(str_replace(',', '.', $read[12])),
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
