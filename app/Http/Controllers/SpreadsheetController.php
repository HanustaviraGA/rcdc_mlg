<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dosen;

class SpreadsheetController extends Controller
{
    public function read(){
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
    
    public function count_kpi($kode_dosen){
        $dosen = Dosen::where('kode_dosen', $kode_dosen)->first();
        
    }
}
