<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dosen;
use App\Models\PKMDosen;
use Aspera\Spreadsheet\XLSX\Reader;

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

    public function read_xlsx(){
        $reader = new Reader();
        $reader->open(public_path('uploads/pkm/xlsx/PKMS.xlsx'));
        $sheets = $reader->getSheets();
        foreach($sheets as $index => $sheet_data){
            $reader->changeSheet($index);
            // Note: Any call to changeSheet() resets the current read position to the beginning of the selected sheet.
            if($sheet_data->getName() == 'List' || $sheet_data->getName() == 'CE'){
                if($sheet_data->getName() == 'List'){
                    $count = 0;
                    foreach ($reader as $row_number => $row){
                        $count++;
                        if ($count == 1){
                            continue;
                        }
                        PKMDosen::create([
                            'id_pkm' => md5(rand(0, 100).generateCode().date('Y-m-d H:i:s')),
                            'periode' => $row[1],
                            'kode_dosen' => $row[3],
                            'judul_pkm'=> $row[4],
                            'jenis_pkm'=> $row[5],
                            'peserta'=> $row[6],
                            'skema_pendanaan'=> $row[7],
                            'nama_mahasiswa'=> $row[8],
                            'link_evidence'=> $row[9],
                            'year' => 2025,
                            'period' => 'Ganjil',
                            'created_at' => now()
                        ]);
                    }
                }else{

                }
            }else{
                continue;
            }
        }
        $reader->close();
    }

    public function count_kpi($kode_dosen){
        $dosen = Dosen::where('kode_dosen', $kode_dosen)->first();

    }
}
