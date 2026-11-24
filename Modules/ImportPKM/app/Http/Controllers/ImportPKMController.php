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
            ? DB::table('community_development_imports')->where(fn ($q) => $q->where('type', 'semester')->orWhereNull('type'))->orderByDesc('year_start')->orderBy('semester')->get() : collect();
        $years = $years->merge($imports->pluck('year_start'))->unique()->sortDesc()->values();

        $periodicImports = Schema::hasTable('community_development_imports')
            ? DB::table('community_development_imports')->where('type', 'periodic')->orderByDesc('start_date')->get() : collect();

        $partnerImports = Schema::hasTable('community_partner_imports')
            ? DB::table('community_partner_imports')->orderByDesc('data_year')->get() : collect();

        return loadPage('importpkm::index', ['academicYears' => $years, 'defaultYear' => $start, 'imports' => $imports, 'periodicImports' => $periodicImports, 'partnerImports' => $partnerImports]);
    }

    public function preview(Request $request, CommunityDevelopmentWorkbook $workbook)
    {
        $validated = $request->validate([
            'pkm' => ['required', 'file', 'mimes:xlsx', 'max:20480'],
            'type' => ['sometimes', 'required', 'in:semester,periodic'],
            'sheets' => ['nullable', 'json'],
        ]);

        return response()->json($workbook->preview($request->file('pkm')->getRealPath(), $validated['type'] ?? 'semester', $this->sheetSelection($validated)));
    }

    /**
     * Optional sheet names per role, e.g. {"rasio": "Rasio", "keterlibatan": "Data FM"}.
     */
    private function sheetSelection(array $validated): ?array
    {
        if (! isset($validated['sheets'])) {
            return null;
        }
        $sheets = json_decode($validated['sheets'], true);
        if (! is_array($sheets) || array_diff_key($sheets, CommunityDevelopmentWorkbook::SHEETS_SEMESTER)
            || array_filter($sheets, fn ($sheet) => $sheet !== null && (! is_string($sheet) || mb_strlen($sheet) > 255))) {
            throw ValidationException::withMessages(['sheets' => 'Pilihan sheet tidak valid.']);
        }

        return $sheets;
    }

    public function upload(Request $request, CommunityDevelopmentWorkbook $workbook)
    {
        $validated = $request->validate([
            'pkm' => ['required', 'file', 'mimes:xlsx', 'max:20480'],
            'type' => ['required', 'in:semester,periodic'],
            'academic_year' => ['required_if:type,semester', 'nullable', 'string', 'max:9'],
            'period' => ['required_if:type,semester', 'nullable', 'in:Ganjil,Genap'],
            'start_date' => ['required_if:type,periodic', 'nullable', 'date'],
            'end_date' => ['required_if:type,periodic', 'nullable', 'date'],
            'column_mapping' => ['required', 'json'],
            'header_rows' => ['required', 'json'],
            'sheets' => ['nullable', 'json'],
        ]);
        $mapping = json_decode($validated['column_mapping'], true);
        $headers = json_decode($validated['header_rows'], true);
        if (! is_array($mapping) || ! is_array($headers)) {
            throw ValidationException::withMessages(['column_mapping' => 'Pemetaan dan baris header harus berupa objek JSON.']);
        }
        $file = $request->file('pkm');
        $type = $validated['type'];
        $params = $type === 'semester'
            ? ['academic_year' => $validated['academic_year'], 'semester' => $validated['period']]
            : ['start_date' => $validated['start_date'], 'end_date' => $validated['end_date']];

        $summary = $workbook->import($file->getRealPath(), $type, $params,
            $mapping, $headers, $file->getClientOriginalName(), $this->sheetSelection($validated));

        $imports = Schema::hasTable('community_development_imports')
            ? DB::table('community_development_imports')->where(fn ($q) => $q->where('type', 'semester')->orWhereNull('type'))->orderByDesc('year_start')->orderBy('semester')->get() : collect();
        $periodicImports = Schema::hasTable('community_development_imports')
            ? DB::table('community_development_imports')->where('type', 'periodic')->orderByDesc('start_date')->get() : collect();

        return response()->json(['success' => true, 'summary' => $summary,
            'message' => 'Import '.$summary['period_label'].' selesai: '.$summary['programs'].' prodi, '.$summary['selected'].' dosen; '.$summary['matched'].' kode cocok dengan data dosen.',
            'warnings' => $summary['warnings'],
            'history_html' => view('importpkm::history', ['imports' => $imports])->render(),
            'periodic_history_html' => view('importpkm::periodic_history', ['periodicImports' => $periodicImports])->render(),
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        abort_unless(auth()->check(), 403);
        $import = DB::table('community_development_imports')->where('id', $id)->first();
        abort_unless($import, 404, 'Data import tidak ditemukan.');

        DB::transaction(function () use ($id) {
            DB::table('community_development_programs')->where('import_id', $id)->delete();
            DB::table('community_development_lecturers')->where('import_id', $id)->delete();
            DB::table('community_development_imports')->where('id', $id)->delete();
        });

        $label = ($import->type ?? 'semester') === 'periodic'
            ? 'Periode '.$import->start_date.' s.d. '.$import->end_date
            : $import->year_start.'/'.($import->year_start + 1).' '.$import->semester;

        $imports = DB::table('community_development_imports')->where(fn ($q) => $q->where('type', 'semester')->orWhereNull('type'))->orderByDesc('year_start')->orderBy('semester')->get();
        $periodicImports = DB::table('community_development_imports')->where('type', 'periodic')->orderByDesc('start_date')->get();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data import '.$label.' berhasil dihapus.',
                'history_html' => view('importpkm::history', ['imports' => $imports])->render(),
                'periodic_history_html' => view('importpkm::periodic_history', ['periodicImports' => $periodicImports])->render(),
            ]);
        }

        return back()->with('success', 'Data import berhasil dihapus.');
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
                    'year' => $year,
                    'period' => $period,
                    'kode_dosen' => $read[0],
                    'nama_dosen' => $read[1],
                    'pkm_kegiatan' => $read[2],
                    'skor' => $read[3],
                ]);
            }
            fclose($handle);
        }

        return redirect()->route('importpkm.index');
    }

    public function create(Request $request)
    {
        return $this->create_old($request);
    }

    public function read(Request $request)
    {
        $data = $request->all();
        $year = $data['year'] ?? null;
        $period = $data['period'] ?? null;
        $query = PKMDosen::query();
        if ($year) {
            $query->where('year', $year);
        }
        if ($period) {
            $query->where('period', $period);
        }
        $pkmDosens = $query->get();

        return response()->json($pkmDosens);
    }

    public function update(Request $request)
    {
        $data = $request->all();
        $id = $data['id'];
        $pkmDosen = PKMDosen::find($id);
        $pkmDosen->update($data);

        return response()->json($pkmDosen);
    }

    public function delete(Request $request)
    {
        $data = $request->all();
        $id = $data['id'];
        $pkmDosen = PKMDosen::find($id);
        $pkmDosen->delete();

        return response()->json(['success' => true]);
    }
}
