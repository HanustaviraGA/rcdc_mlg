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
    
                // Skip the header row
                if ($row == 1) continue;
    
                // Avoid inserting if kode_dosen already exists
                $kodeDosen = $data[0];
                if (!Dosen::where('kode_dosen', $kodeDosen)->exists()) {
                    $ft = explode(' ', $data[4]);
                    $faculty = $ft[0];
                    Dosen::create([
                        'kode_dosen' => $data[0],
                        'nama_dosen' => $data[1],
                        'jurusan_dosen' => $data[2],
                        'jja_dosen'  => preg_replace('/[^A-Z]/i', '', $data[3]),
                        'ft_dosen'   => $data[4],
                    ]);
                }
            }
            fclose($handle);
        }
    }
    
    public function count_kpi($kode_dosen){
        $dosen = Dosen::where('kode_dosen', $kode_dosen)->first();
        // Cek JJA dan FT
        
    }
}
