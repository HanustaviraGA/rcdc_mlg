<?php

namespace Modules\Dosen\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Dosen;
use App\Models\DataDosen;
use App\Models\IdentitasDosen;
use Aspera\Spreadsheet\XLSX\Reader;

class DosenController extends Controller
{
    /**
     * Display a listing of the resource with loadPage helper.
     * @return Renderable
     */
    public function index()
    {
        $program = DataDosen::select('nama_gugus_binaan')->distinct()->orderBy('nama_gugus_binaan', 'ASC')->get();
        return loadPage('dosen::index', compact('program'));
    }

    /**
     * Initialize a Datatable.
     * @return Renderable
     */
    public function init_table(Request $request)
    {
        $data = $request->all();
        $query = DataDosen::query();
        // $query->leftJoin('identitas_dosen', 'identitas_dosen.kode_dosen', '=', 'database_dosen.kode_dosen');
        // $query->select('database_dosen.*', 'identitas_dosen.email_dosen', 'identitas_dosen.telp_dosen');
        if($data['prodi'] !== 'All'){
            $query->where('nama_gugus_binaan', $data['prodi']);
        }
        $query->orderBy('nama_dosen', 'asc');
        $query->get();
        return select_table($query);
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
    public function read_old(){
        if (($handle = fopen("DOSEN.csv", "r")) !== FALSE) {
            $row = 0;
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $row++;
                if ($row == 1){
                    continue;
                }
                $kodeDosen = $data[0];
                if (!Dosen::where('kode_dosen', $kodeDosen)->exists()) {
                    $ft = explode(' ', $data[5]);
                    $faculty = $ft[0];
                    $jja = preg_replace('/[^A-Z]/i', '', $data[4]);
                    Dosen::create([
                        'kode_dosen' => $kodeDosen,
                        'nama_dosen' => $data[1],
                        'pendidikan_dosen' => $data[2],
                        'jurusan_dosen' => $data[3],
                        'jja_dosen'  => $jja,
                        'ft_dosen'   => $faculty,
                    ]);
                }else{
                    $ft = explode(' ', $data[5]);
                    $faculty = $ft[0];
                    $jja = preg_replace('/[^A-Z]/i', '', $data[4]);
                    $update = Dosen::where('kode_dosen', $kodeDosen)->update([
                        'nama_dosen' => $data[1],
                        'pendidikan_dosen' => $data[2],
                        'jurusan_dosen' => $data[3],
                        'jja_dosen'  => $jja,
                        'ft_dosen'   => $faculty,
                    ]);
                }
            }
            fclose($handle);
        }
    }

    public function read(Request $request){
        // Import file
        $xlsx = $request->file('dosen');
        $extension = $xlsx->getClientOriginalExtension();
        $year = date('Y');
        $month = date('m');
        $filename = 'DSN-Y'.$year.'M'.$month.'.'.$extension;
        $xlsx->move(public_path('uploads/dosen'), $filename);
        if (($handle = fopen(public_path('uploads/dosen/'.$filename), "r")) !== FALSE) {
            $reader = new Reader();
            $reader->open(public_path('uploads/dosen/'.$filename));
            $sheets = $reader->getSheets();
            foreach($sheets as $index => $sheet_data){
                $reader->changeSheet($index);
                // Note: Any call to changeSheet() resets the current read position to the beginning of the selected sheet.
                if($sheet_data->getName() == 'Malang'){
                    $count = 0;
                        // foreach ($reader as $row_number => $row){
                        //     $count++;
                        //     if ($count == 1 || $row[0] == 'Kode Dosen'){
                        //         continue;
                        //     }
                        //     $kodeDosen = $row[0];
                        //     $ft = explode(' ', $row[44]);
                        //     $faculty = $ft[0];
                        //     $jja = preg_replace('/[^A-Z]/i', '', $row[22]);
                        //     $jurusan = '';
                            
                        //     // Adjustment jurusan
                        //     if($row[2] == 'Public Relations'){
                        //         $jurusan = 'PR';
                        //     }else if($row[2] == 'Entrepreneurship'){
                        //         $jurusan = 'BC';
                        //     }else if($row[2] == 'Computer Science'){
                        //         $jurusan = 'CS';
                        //     }else if($row[2] == 'Communications'){
                        //         $jurusan = 'Ilkom';
                        //     }else if($row[2] == 'Interior Design'){
                        //         $jurusan = 'DI';
                        //     }else if($row[2] == 'Visual Communication Design'){
                        //         $jurusan = 'DKV';
                        //     }else if($row[2] == 'English Literature'){
                        //         $jurusan = 'LC';
                        //     }else if($row[2] == 'Character Building'){
                        //         $jurusan = 'CBDC';
                        //     }
                            
                        //     if (!Dosen::where('kode_dosen', $kodeDosen)->exists()) {
                        //         Dosen::create([
                        //             'kode_dosen' => $kodeDosen,
                        //             'nama_dosen' => $row[8],
                        //             'pendidikan_dosen' => $row[19],
                        //             'jurusan_dosen' => $jurusan,
                        //             'jja_dosen'  => $jja,
                        //             'ft_dosen'   => $faculty,
                        //         ]);
                        //     }else{
                        //         $update = Dosen::where('kode_dosen', $kodeDosen)->update([
                        //             'nama_dosen' => $row[8],
                        //             'pendidikan_dosen' => $row[19],
                        //             'jurusan_dosen' => $jurusan,
                        //             'jja_dosen'  => $jja,
                        //             'ft_dosen'   => $faculty,
                        //         ]);
                        //     }
                        // }
                        foreach ($reader as $row_number => $row){
                            $count++;
                            if ($count == 1 || $row[0] == 'Kode Dosen'){
                                continue;
                            }
                            $kodeDosen = $row[0];
                            $ft = explode(' ', $row[44]);
                            $faculty = $ft[0];
                            $jja = preg_replace('/[^A-Z]/i', '', $row[22]);
                            $jurusan = '';
                            
                            // Adjustment jurusan
                            if($row[2] == 'Public Relations'){
                                $jurusan = 'PR';
                            }else if($row[2] == 'Entrepreneurship'){
                                $jurusan = 'BC';
                            }else if($row[2] == 'Computer Science'){
                                $jurusan = 'CS';
                            }else if($row[2] == 'Communications'){
                                $jurusan = 'Ilkom';
                            }else if($row[2] == 'Interior Design'){
                                $jurusan = 'DI';
                            }else if($row[2] == 'Visual Communication Design'){
                                $jurusan = 'DKV';
                            }else if($row[2] == 'English Literature'){
                                $jurusan = 'LC';
                            }else if($row[2] == 'Character Building'){
                                $jurusan = 'CBDC';
                            }
                            
                            if (!Dosen::where('kode_dosen', $kodeDosen)->exists()) {
                                Dosen::create([
                                    'kode_dosen' => $kodeDosen,
                                    'nama_dosen' => $row[8],
                                    'pendidikan_dosen' => $row[19],
                                    'jurusan_dosen' => $jurusan,
                                    'jja_dosen'  => $jja,
                                    'ft_dosen'   => $faculty,
                                ]);
                            }else{
                                $update = Dosen::where('kode_dosen', $kodeDosen)->update([
                                    'nama_dosen' => $row[8],
                                    'pendidikan_dosen' => $row[19],
                                    'jurusan_dosen' => $jurusan,
                                    'jja_dosen'  => $jja,
                                    'ft_dosen'   => $faculty,
                                ]);
                            }
                        }
                }else{
                    continue;
                }
            }
            $reader->close();
        }
        return response()->json(['success' => true], 200);
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
