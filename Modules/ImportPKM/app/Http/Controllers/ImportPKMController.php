<?php

namespace Modules\ImportPKM\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\PKMDosen;

class ImportPKMController extends Controller
{
    /**
     * Display a listing of the resource with loadPage helper.
     * @return Renderable
     */
    public function index()
    {
        return loadPage('importpkm::index');
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
        $year = $data['year'];
        $period = $data['period'];
        $check = PKMDosen::where('year', $year)->where('period', $period)->exists();
        if($check){
            PKMDosen::where('year', $year)->where('period', $period)->delete();
        }
        // Import file
        $csv = $request->file('pkm');
        $extension = $csv->getClientOriginalExtension();
        $filename = 'Y'.$year.'P'.$period.'.'.$extension;
        $csv->move(public_path('uploads/pkm'), $filename);
        if (($handle = fopen(public_path('uploads/pkm/'.$filename), "r")) !== FALSE) {
            $row = 0;
            while (($read = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $row++;
                if ($row == 1){
                    continue;
                }
                PKMDosen::create([
                    'id_pkm' => md5(rand(0, 100).generateCode().date('Y-m-d H:i:s')),
                    'periode' => $read[1], 
                    'kode_dosen' => $read[3],
                    'judul_pkm'=> $read[4],
                    'jenis_pkm'=> $read[5],
                    'peserta'=> $read[6],
                    'skema_pendanaan'=> $read[7],
                    'nama_mahasiswa'=> $read[8],
                    'link_evidence'=> $read[9],
                    'year' => $year,
                    'period' => $period,
                    'created_at' => now()
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
