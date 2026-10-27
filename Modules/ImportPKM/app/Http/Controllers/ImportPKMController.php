<?php

namespace Modules\ImportPKM\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PKMDosen;
use App\Services\CommunityDevelopment\CommunityDevelopmentWorkbook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ImportPKMController extends Controller
{
    /**
     * Display a listing of the resource with loadPage helper.
     *
     * @return Renderable
     */
    public function index()
    {
        $start = (int) now()->year - (now()->month < 9 ? 1 : 0);
        $years = collect(range($start + 1, $start - 5));
        $imports = Schema::hasTable('community_development_imports')
            ? DB::table('community_development_imports')->orderByDesc('year_start')->orderBy('semester')->get() : collect();
        $years = $years->merge($imports->pluck('year_start'))->unique()->sortDesc()->values();

        $partnerImports = Schema::hasTable('community_partner_imports')
            ? DB::table('community_partner_imports')->orderByDesc('data_year')->get() : collect();

        return loadPage('importpkm::index', ['academicYears' => $years, 'defaultYear' => $start, 'imports' => $imports, 'partnerImports' => $partnerImports]);
    }

    public function preview(Request $request, CommunityDevelopmentWorkbook $workbook)
    {
        $request->validate(['pkm' => ['required', 'file', 'mimes:xlsx', 'max:20480']]);

        return response()->json($workbook->preview($request->file('pkm')->getRealPath()));
    }

    public function upload(Request $request, CommunityDevelopmentWorkbook $workbook)
    {
        $validated = $request->validate([
            'pkm' => ['required', 'file', 'mimes:xlsx', 'max:20480'],
            'academic_year' => ['required', 'string', 'max:9'],
            'period' => ['required', 'in:Ganjil,Genap'],
            'column_mapping' => ['required', 'json'],
            'header_rows' => ['required', 'json'],
        ]);
        $mapping = json_decode($validated['column_mapping'], true);
        $headers = json_decode($validated['header_rows'], true);
        if (! is_array($mapping) || ! is_array($headers)) {
            throw ValidationException::withMessages(['column_mapping' => 'Pemetaan dan baris header harus berupa objek JSON.']);
        }
        $file = $request->file('pkm');
        $summary = $workbook->import($file->getRealPath(), $validated['academic_year'], $validated['period'],
            $mapping, $headers, $file->getClientOriginalName());

        return response()->json(['success' => true, 'summary' => $summary,
            'message' => 'Import '.$summary['period_label'].' selesai: '.$summary['programs'].' prodi, '.$summary['selected'].' dosen; '.$summary['matched'].' kode cocok dengan data dosen.',
            'warnings' => $summary['warnings'],
            'history_html' => view('importpkm::history', ['imports' => DB::table('community_development_imports')->orderByDesc('year_start')->orderBy('semester')->get()])->render(),
        ]);
    }

    /**
     * Initialize a Datatable.
     *
     * @return Renderable
     */
    public function init_table() {}

    /**
     * Store a newly created resource in storage.
     *
     * @return Renderable
     */
    public function create_old(Request $request)
    {
        $data = $request->all();
        $year = $data['year'];
        $period = $data['period'];
        $check = PKMDosen::where('year', $year)->where('period', $period)->exists();
        if ($check) {
            PKMDosen::where('year', $year)->where('period', $period)->delete();
        }
        // Import file
        $csv = $request->file('pkm');
        $extension = $csv->getClientOriginalExtension();
        $filename = 'Y'.$year.'P'.$period.'.'.$extension;
        $csv->move(public_path('uploads/pkm'), $filename);
        if (($handle = fopen(public_path('uploads/pkm/'.$filename), 'r')) !== false) {
            $row = 0;
            while (($read = fgetcsv($handle, 1000, ';')) !== false) {
                $row++;
                if ($row == 1) {
                    continue;
                }
                PKMDosen::create([
                    'id_pkm' => md5(rand(0, 100).generateCode().date('Y-m-d H:i:s')),
                    'periode' => $read[1],
                    'kode_dosen' => $read[3],
                    'judul_pkm' => $read[4],
                    'jenis_pkm' => $read[5],
                    'peserta' => $read[6],
                    'skema_pendanaan' => $read[7],
                    'nama_mahasiswa' => $read[8],
                    'link_evidence' => $read[9],
                    'year' => $year,
                    'period' => $period,
                    'created_at' => now(),
                ]);
            }
            fclose($handle);
        }

        return response()->json(['success' => true], 200);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'pkm' => ['required', 'file', 'mimes:xlsx', 'max:20480'],
            'year' => ['required', 'integer', 'between:2000,2100'],
            'period' => ['required', 'string', 'max:50'],
        ]);
        $summary = app(\App\Services\Imports\PkmWorkbook::class)->import(
            $request->file('pkm')->getRealPath(), (int) $validated['year'], $validated['period']
        );

        return response()->json(['success' => true, 'summary' => $summary,
            'message' => 'Import PKM selesai: '.$summary['selected'].' baris; '.$summary['skipped_rows'].' baris dilewati.',
            'warnings' => $summary['warnings']]);
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function read(Request $request)
    {
        $data = $request->all();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function update(Request $request)
    {
        $data = $request->all();
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
