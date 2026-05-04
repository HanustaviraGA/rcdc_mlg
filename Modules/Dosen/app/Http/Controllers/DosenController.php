<?php

namespace Modules\Dosen\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AttributeDosen;
use App\Models\DataDosen;
use App\Models\Dosen;
use App\Models\IdentitasDosen;
use Aspera\Spreadsheet\XLSX\Reader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DosenController extends Controller
{
    /**
     * Display a listing of the resource with loadPage helper.
     *
     * @return Renderable
     */
    public function index()
    {
        $program = DataDosen::select('nama_gugus_binaan')->distinct()->orderBy('nama_gugus_binaan', 'ASC')->get();

        return loadPage('dosen::index', compact('program'));
    }

    /**
     * Initialize a Datatable.
     *
     * @return Renderable
     */
    public function init_table(Request $request)
    {
        $data = $request->all();
        $query = DataDosen::query();
        // $query->leftJoin('identitas_dosen', 'identitas_dosen.kode_dosen', '=', 'database_dosen.kode_dosen');
        // $query->select('database_dosen.*', 'identitas_dosen.email_dosen', 'identitas_dosen.telp_dosen');
        if ($data['prodi'] !== 'All') {
            $query->where('nama_gugus_binaan', $data['prodi']);
        }
        $query->orderBy('nama_dosen', 'asc');
        $query->get();

        return select_table($query);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Renderable
     */
    public function create(Request $request)
    {
        $data = $request->all();
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function read_old()
    {
        if (($handle = fopen('DOSEN.csv', 'r')) !== false) {
            $row = 0;
            while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                $row++;
                if ($row == 1) {
                    continue;
                }
                $kodeDosen = $data[0];
                if (! Dosen::where('kode_dosen', $kodeDosen)->exists()) {
                    $ft = explode(' ', $data[5]);
                    $faculty = $ft[0];
                    $jja = preg_replace('/[^A-Z]/i', '', $data[4]);
                    Dosen::create([
                        'kode_dosen' => $kodeDosen,
                        'nama_dosen' => $data[1],
                        'pendidikan_dosen' => $data[2],
                        'jurusan_dosen' => $data[3],
                        'jja_dosen' => $jja,
                        'ft_dosen' => $faculty,
                    ]);
                } else {
                    $ft = explode(' ', $data[5]);
                    $faculty = $ft[0];
                    $jja = preg_replace('/[^A-Z]/i', '', $data[4]);
                    $update = Dosen::where('kode_dosen', $kodeDosen)->update([
                        'nama_dosen' => $data[1],
                        'pendidikan_dosen' => $data[2],
                        'jurusan_dosen' => $data[3],
                        'jja_dosen' => $jja,
                        'ft_dosen' => $faculty,
                    ]);
                }
            }
            fclose($handle);
        }
    }

    public function read(Request $request)
    {
        // Import file
        $xlsx = $request->file('dosen');
        $extension = $xlsx->getClientOriginalExtension();
        $year = date('Y');
        $month = date('m');
        $filename = 'DSN-Y'.$year.'M'.$month.'.'.$extension;
        $xlsx->move(public_path('uploads/dosen'), $filename);
        $reader = new Reader;
        $reader->open(public_path('uploads/dosen/'.$filename));
        $sheets = $reader->getSheets();
        foreach ($sheets as $index => $sheet_data) {
            $reader->changeSheet($index);
            // Note: Any call to changeSheet() resets the current read position to the beginning of the selected sheet.
            if ($sheet_data->getName() == 'Malang' || $sheet_data->getName() == 'FM Malang') {
                $headerMap = null;
                foreach ($reader as $row_number => $row) {
                    if ($headerMap === null && in_array('Kode Dosen', $row, true)) {
                        $headerMap = [];
                        foreach ($row as $idx => $name) {
                            $name = trim((string) $name);
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
                        if (! array_key_exists($name, $headerMap)) {
                            return null;
                        }

                        return $row[$headerMap[$name]] ?? null;
                    };

                    $kodeDosen = trim((string) $getCell('Kode Dosen'));
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
            } else {
                continue;
            }
        }
        $reader->close();

        return response()->json(['success' => true], 200);
    }

    public function detail(Request $request)
    {
        $kodeDosen = $request->input('kode_dosen');
        if (! $kodeDosen) {
            return response()->json(['message' => 'Kode dosen tidak ditemukan'], 422);
        }

        $dosen = DataDosen::where('kode_dosen', $kodeDosen)->first();
        $identitas = IdentitasDosen::where('kode_dosen', $kodeDosen)->first();
        $attributes = AttributeDosen::where('kode_dosen', $kodeDosen)
            ->get(['attribute_dosen', 'attribute_icon']);

        return response()->json([
            'dosen' => $dosen,
            'identitas' => $identitas,
            'attributes' => $attributes,
        ], 200);
    }

    public function headers(Request $request)
    {
        $xlsx = $request->file('dosen');
        $extension = $xlsx->getClientOriginalExtension();
        $filename = 'DSN-HEADERS.'.$extension;
        $xlsx->move(public_path('uploads/dosen'), $filename);

        $reader = new Reader;
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
            if (! empty($headers)) {
                break;
            }
        }
        $reader->close();

        return response()->json(['headers' => $headers], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'kode_dosen' => ['required', 'string'],
            'foto_dosen' => ['nullable', 'image'],
            'video_dosen' => ['nullable', 'string', 'max:255'],
            'deskripsi_dosen' => ['nullable', 'string'],
            'link_google_scholar' => ['nullable', 'string', 'max:255'],
            'link_scopus' => ['nullable', 'string', 'max:255'],
            'link_sinta' => ['nullable', 'string', 'max:255'],
            'link_garuda' => ['nullable', 'string', 'max:255'],
            'link_orcid' => ['nullable', 'string', 'max:255'],
            'attributes' => ['nullable', 'string'],
        ]);

        $kodeDosen = $validated['kode_dosen'];
        if (! DataDosen::where('kode_dosen', $kodeDosen)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Data dosen tidak ditemukan',
            ], 404);
        }

        $attributes = json_decode($request->input('attributes', '[]'), true);
        if (! is_array($attributes)) {
            return response()->json([
                'success' => false,
                'message' => 'Format attribute dosen tidak valid',
            ], 422);
        }

        DB::transaction(function () use ($request, $validated, $kodeDosen, $attributes) {
            $identitas = IdentitasDosen::firstOrNew(['kode_dosen' => $kodeDosen]);
            if (! $identitas->exists) {
                $identitas->id_identitas = (string) Str::uuid();
                $identitas->kode_dosen = $kodeDosen;
            }

            $identitas->fill([
                'video_dosen' => $validated['video_dosen'] ?? null,
                'deskripsi_dosen' => $validated['deskripsi_dosen'] ?? null,
                'link_google_scholar' => $validated['link_google_scholar'] ?? null,
                'link_scopus' => $validated['link_scopus'] ?? null,
                'link_sinta' => $validated['link_sinta'] ?? null,
                'link_garuda' => $validated['link_garuda'] ?? null,
                'link_orcid' => $validated['link_orcid'] ?? null,
            ]);

            if ($request->hasFile('foto_dosen')) {
                $photo = $request->file('foto_dosen');
                $uploadPath = public_path('uploads/dosen/foto');
                if (! is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $extension = $photo->getClientOriginalExtension() ?: $photo->extension();
                $filename = 'foto-'.Str::slug($kodeDosen).'-'.now()->format('YmdHis').'.'.$extension;
                $photo->move($uploadPath, $filename);
                $identitas->foto_dosen = $filename;
            }

            $identitas->save();

            AttributeDosen::where('kode_dosen', $kodeDosen)->delete();
            foreach ($attributes as $attribute) {
                $attributeName = trim((string) ($attribute['attribute_dosen'] ?? ''));
                if ($attributeName === '') {
                    continue;
                }

                $icon = trim((string) ($attribute['attribute_icon'] ?? ''));
                $icon = preg_replace('/^bi\s+/', '', $icon);
                $icon = preg_match('/^bi-[a-z0-9-]+$/i', $icon) ? 'bi '.$icon : '';

                AttributeDosen::create([
                    'id_attribute' => (string) Str::uuid(),
                    'kode_dosen' => $kodeDosen,
                    'attribute_dosen' => $attributeName,
                    'attribute_icon' => $icon,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Data dosen berhasil disimpan',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function delete(Request $request)
    {
        $data = $request->all();
    }
}
