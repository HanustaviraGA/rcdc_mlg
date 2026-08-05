<?php

namespace Modules\OutletPublikasi\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\OutletPublikasi\Models\OutletPublikasi;
use Yajra\DataTables\Facades\DataTables;

class OutletPublikasiController extends Controller
{
    /**
     * Display a listing of the resource with loadPage helper.
     *
     * @return Renderable
     */
    public function index()
    {
        return loadPage('outletpublikasi::index');
    }

    public function publicIndex(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $sort = $request->query('sort') === 'asc' ? 'asc' : 'desc';

        $outletPublikasi = OutletPublikasi::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('nama_conference', 'like', '%'.$search.'%')
                        ->orWhere('scope', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('created_at', $sort)
            ->orderBy('id', $sort)
            ->paginate(10)
            ->withQueryString();

        return view('outletpublikasi::public', compact('outletPublikasi', 'search', 'sort'));
    }

    /**
     * Initialize a Datatable.
     *
     * @return Renderable
     */
    public function init_table(Request $request)
    {
        return DataTables::eloquent(
            OutletPublikasi::query()->select([
                'id',
                'nama_conference',
                'tipe_kerjasama',
                'deadline_submission',
                'scope',
                'contact_pic',
                'created_at',
            ])
        )
            ->addIndexColumn()
            ->toJson();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Renderable
     */
    public function create(Request $request)
    {
        $outletPublikasi = OutletPublikasi::create($this->validatedData($request));

        return response()->json([
            'success' => true,
            'message' => 'Outlet publikasi berhasil ditambahkan.',
            'data' => $outletPublikasi,
        ], 201);
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function read(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'integer', 'exists:outlet_publikasi,id'],
        ]);

        return response()->json([
            'success' => true,
            'data' => OutletPublikasi::findOrFail($validated['id']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function update(Request $request)
    {
        $validatedId = $request->validate([
            'id' => ['required', 'integer', 'exists:outlet_publikasi,id'],
        ]);

        $outletPublikasi = OutletPublikasi::findOrFail($validatedId['id']);
        $outletPublikasi->update($this->validatedData($request));

        return response()->json([
            'success' => true,
            'message' => 'Outlet publikasi berhasil diperbarui.',
            'data' => $outletPublikasi->fresh(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function delete(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'integer', 'exists:outlet_publikasi,id'],
        ]);

        OutletPublikasi::findOrFail($validated['id'])->delete();

        return response()->json([
            'success' => true,
            'message' => 'Outlet publikasi berhasil dihapus.',
        ]);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'nama_conference' => ['required', 'string', 'max:255'],
            'tipe_kerjasama' => ['required', Rule::in(['BJIC', 'Co-Host', '-'])],
            'deadline_submission' => ['required', 'date'],
            'scope' => ['required', 'string', 'max:20000'],
            'contact_pic' => ['required', 'string', 'max:255'],
        ]);
    }
}
