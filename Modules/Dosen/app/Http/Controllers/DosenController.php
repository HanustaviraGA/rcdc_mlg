<?php

namespace Modules\Dosen\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AttributeDosen;
use App\Models\DataDosen;
use App\Models\Dosen;
use App\Models\IdentitasDosen;
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
        $query = DataDosen::query();
        // $query->leftJoin('identitas_dosen', 'identitas_dosen.kode_dosen', '=', 'database_dosen.kode_dosen');
        // $query->select('database_dosen.*', 'identitas_dosen.email_dosen', 'identitas_dosen.telp_dosen');
        if ($request->filled('prodi') && $request->input('prodi') !== 'All') {
            $query->where('nama_gugus_binaan', $request->input('prodi'));
        }
        $query->orderBy('nama_dosen', 'asc');

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

    public function read(Request $request, \App\Services\Lecturers\LecturerWorkbook $workbook)
    {
        $request->validate(['dosen' => ['required', 'file', 'mimes:xlsx', 'max:20480']]);
        $summary = $workbook->import($request->file('dosen')->getRealPath());

        return response()->json(['success' => true, 'summary' => $summary,
            'message' => "Import {$summary['sheet']} selesai: {$summary['selected']} dosen, {$summary['mapped_columns']} kolom; {$summary['created']} baru, {$summary['updated']} diperbarui."]);
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

    public function headers(Request $request, \App\Services\Lecturers\LecturerWorkbook $workbook)
    {
        $request->validate(['dosen' => ['required', 'file', 'mimes:xlsx', 'max:20480']]);
        $parsed = $workbook->read($request->file('dosen')->getRealPath());

        return response()->json($parsed['summary']);
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
        $validated = $request->validate(['kode_dosen' => ['required', 'string', 'max:100']]);
        $dosen = DataDosen::findOrFail($validated['kode_dosen']);
        $dosen->delete();

        return response()->json(['success' => true, 'message' => 'Dosen berhasil dihapus dari daftar. Data tetap tersimpan sebagai arsip.']);
    }

    public function visibility(Request $request)
    {
        $validated = $request->validate([
            'kode_dosen' => ['required', 'string', 'max:100'],
            'is_hidden' => ['required', 'boolean'],
        ]);
        $dosen = DataDosen::findOrFail($validated['kode_dosen']);
        $dosen->is_hidden = $request->boolean('is_hidden');
        $dosen->save();

        return response()->json([
            'success' => true,
            'is_hidden' => $dosen->is_hidden,
            'message' => $dosen->is_hidden ? 'Profil dosen disembunyikan dari halaman depan.' : 'Profil dosen ditampilkan kembali di halaman depan.',
        ]);
    }
}
