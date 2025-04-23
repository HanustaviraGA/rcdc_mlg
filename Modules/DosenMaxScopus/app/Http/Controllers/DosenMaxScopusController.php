<?php

namespace Modules\DosenMaxScopus\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Dosen;

class DosenMaxScopusController extends Controller
{
    /**
     * Display a listing of the resource with loadPage helper.
     * @return Renderable
     */
    public function index()
    {
        return loadPage('dosenmaxscopus::index');
    }

    /**
     * Initialize a Datatable.
     * @return Renderable
     */
    public function init_table(Request $request)
    {
        $data = $request->all();

        $stringq = 'SELECT
            dd.kode_dosen,
            dd.nama_dosen,
            dd.ft_dosen,
            dd.jja_dosen,
            dd.pendidikan_dosen,
            dd.jurusan_dosen,
            dd.maxscopuskonf_dosen AS max_mandiri_scopus,
            COALESCE(SUM(CASE 
                WHEN rd.jenis = "Seminar" 
                AND rd.sumber_paper = "Penelitian Mandiri" 
                AND rd.tipe_publikasi = "Scopus" 
                THEN rd.bobot ELSE 0 END), 0) AS mandiri_seminar_scopus
        FROM database_dosen dd
        LEFT JOIN rectorate_dosen rd 
            ON rd.kode_dosen = dd.kode_dosen
            AND rd.year = "'.$data['year'].'"
            AND rd.period = "'.$data['period'].'"
            AND rd.month = "'.$data['month'].'"';

        // Start the WHERE clause (if needed)
        if (isset($data['prodi']) && $data['prodi'] !== 'Semua Prodi') {
            $prodi = addslashes($data['prodi']); // simple security against quote injection
            $stringq .= " WHERE dd.jurusan_dosen = '$prodi'";
        }

        // Always add GROUP BY
        $stringq .= " GROUP BY 
            dd.kode_dosen, dd.nama_dosen, dd.ft_dosen, dd.jja_dosen, 
            dd.pendidikan_dosen, dd.jurusan_dosen, dd.maxscopuskonf_dosen";

        $query = collect(\DB::select($stringq));
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
    public function read(Request $request)
    {
        $data = $request->all();
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
