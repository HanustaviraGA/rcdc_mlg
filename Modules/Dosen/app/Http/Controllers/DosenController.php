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
        $reader = new Reader();
        $reader->open(public_path('uploads/dosen/'.$filename));
        $sheets = $reader->getSheets();
        foreach($sheets as $index => $sheet_data){
            $reader->changeSheet($index);
            // Note: Any call to changeSheet() resets the current read position to the beginning of the selected sheet.
            if($sheet_data->getName() == 'Malang' || $sheet_data->getName() == 'FM Malang'){
                $headerMap = null;
                foreach ($reader as $row_number => $row){
                    if ($headerMap === null && in_array('Kode Dosen', $row, true)) {
                        $headerMap = [];
                        foreach ($row as $idx => $name) {
                            $name = trim((string)$name);
                            if ($name !== '') {
                                $headerMap[$name] = $idx;
                            }
                        }
                        continue;
                    }

                    if ($headerMap === null) {
                        continue;
                    }

                    $getCell = function (string $name) use ($row, $headerMap) {
                        if (!array_key_exists($name, $headerMap)) {
                            return null;
                        }
                        return $row[$headerMap[$name]] ?? null;
                    };

                    $kodeDosen = trim((string)$getCell('Kode Dosen'));
                    if ($kodeDosen === '') {
                        continue;
                    }

                    DataDosen::updateOrCreate(
                        ['kode_dosen' => $kodeDosen],
                        [
                            'fakultas_internal' => $getCell('Fakultas Internal'),
                            'nama_gugus_binaan' => $getCell('Nama Gugus Binaan'),
                            'nama_program' => $getCell('Nama Program'),
                            'lokasi' => $getCell('Lokasi'),
                            'campus' => $getCell('Campus'),
                            'nama_gugus_binaan_eksternal' => $getCell('Nama Gugus Binaan Eksternal'),
                            'acad_career' => $getCell('Acad Career'),
                            'nama_dosen' => $getCell('Nama Dosen'),
                            'tipe' => $getCell('Tipe'),
                            'nama_tipe_dosen_detail' => $getCell('Nama Tipe Dosen Detail'),
                            'status' => $getCell('Status'),
                            'effdate_dosen_cuti' => $getCell('Effdate Dosen Cuti'),
                            'remun' => $getCell('Remun'),
                            'homebase_remun' => $getCell('Homebase Remun'),
                            'jenis_registrasi' => $getCell('Jenis Registrasi'),
                            'nomor_nidn_nupn' => $getCell('Nomor Nidn/Nupn'),
                            'university_registered_nidn' => $getCell('University Registered NIDN'),
                            'pendidikan' => $getCell('Pendidikan'),
                            'alumni' => $getCell('Alumni'),
                            'jurusan' => $getCell('Jurusan'),
                            'jja' => $getCell('JJA'),
                            'tmt_jja' => $getCell('Tmt JJA'),
                            'university_registered_jja' => $getCell('University Registered JJA'),
                            'nomor_sk_jja' => $getCell('Nomor SK JJA'),
                            'jka' => $getCell('JKA'),
                            'tmt_jka' => $getCell('Tmt JKA'),
                            'toefl' => $getCell('TOEFL'),
                            'status_serdos' => $getCell('Status Serdos'),
                            'jenis_kelamin' => $getCell('Jenis Kelamin'),
                            'tanggal_lahir' => $getCell('Tanggal Lahir'),
                            'usia' => $getCell('Usia'),
                            'agama' => $getCell('Agama'),
                            'alamat' => $getCell('Alamat'),
                            'no_telp' => $getCell('No Telp'),
                            'no_hp' => $getCell('No HP'),
                            'no_hp_2' => $getCell('No HP2'),
                            'email_1' => $getCell('Email 1'),
                            'email_2' => $getCell('Email 2'),
                            'tgl_mulai_mengajar' => $getCell('Tgl Mulai Mengajar'),
                            'kewarganegaraan' => $getCell('Kewarganegaraan'),
                            'bn_id' => $getCell('Binusian ID'),
                            'nama_kelompok_rumpun_ilmu' => $getCell('Nama Kelompok Rumpun Ilmu'),
                            'nama_rumpun_ilmu' => $getCell('Nama Rumpun Ilmu'),
                            'tipe_faculty' => $getCell('Tipe Faculty'),
                            'tax_status' => $getCell('Tax Status'),
                            'status_pernikahan' => $getCell('Status Pernikahan'),
                            'note' => $getCell('NOTE'),
                        ]
                    );
                }
            }else{
                continue;
            }
        }
        $reader->close();
        return response()->json(['success' => true], 200);
    }

    public function headers(Request $request)
    {
        $xlsx = $request->file('dosen');
        $extension = $xlsx->getClientOriginalExtension();
        $filename = 'DSN-HEADERS.'. $extension;
        $xlsx->move(public_path('uploads/dosen'), $filename);

        $reader = new Reader();
        $reader->open(public_path('uploads/dosen/'.$filename));
        $headers = [];
        foreach ($reader->getSheets() as $index => $sheet_data) {
            $reader->changeSheet($index);
            foreach ($reader as $row) {
                if (in_array('Kode Dosen', $row, true)) {
                    $headers = array_values(array_filter(array_map('trim', array_map('strval', $row))));
                    break;
                }
            }
            if (!empty($headers)) {
                break;
            }
        }
        $reader->close();

        return response()->json(['headers' => $headers], 200);
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
