<?php

namespace Modules\DosenNonPublikasi\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Dosen;

class DosenNonPublikasiController extends Controller
{
    /**
     * Display a listing of the resource with loadPage helper.
     * @return Renderable
     */
    public function index()
    {
        return loadPage('dosennonpublikasi::index');
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
            COALESCE(SUM(CASE 
                WHEN rd.tipe_publikasi = "Nscopus"
                and rd.jenis = "Jurnal"
                THEN rd.bobot ELSE 0 END), 0) AS jml_nscopus,
            COALESCE(SUM(CASE 
                WHEN rd.tipe_publikasi = "Scopus"
                and rd.jenis = "Jurnal"
                THEN rd.bobot ELSE 0 END), 0) AS jml_scopus
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
            dd.pendidikan_dosen, dd.jurusan_dosen";
        $query = collect(\DB::select($stringq));
        return select_table($query);
    }

    public function init_chart(Request $request){
        $year = (int) $request->input('year');
        $month = (int) $request->input('month');
        $period = (int) $request->input('period');
        $prodi = $request->input('prodi');

        $bindings = [
            'year' => $year,
            'month' => $month,
            'period' => $period,
        ];

        $query = '
            SELECT
                CASE
                    WHEN jml_scopus > 0 AND jml_nscopus = 0 THEN "Hanya Scopus"
                    WHEN jml_scopus = 0 AND jml_nscopus > 0 THEN "Hanya Non Scopus"
                    WHEN jml_scopus > 0 AND jml_nscopus > 0 THEN "Scopus dan Non Scopus"
                    ELSE "Tidak Keduanya"
                END AS category,
                COUNT(*) AS jumlah_dosen
            FROM (
                SELECT
                    dd.kode_dosen,
                    SUM(CASE 
                        WHEN rd.tipe_publikasi = "Nscopus" AND rd.jenis = "Jurnal" THEN rd.bobot 
                        ELSE 0 
                    END) AS jml_nscopus,
                    SUM(CASE 
                        WHEN rd.tipe_publikasi = "Scopus" AND rd.jenis = "Jurnal" THEN rd.bobot 
                        ELSE 0 
                    END) AS jml_scopus
                FROM database_dosen dd
                LEFT JOIN rectorate_dosen rd 
                    ON rd.kode_dosen = dd.kode_dosen
                    AND rd.year = :year
                    AND rd.period = :period
                    AND rd.month = :month';

        if ($prodi && $prodi !== 'Semua Prodi') {
            $query .= ' WHERE dd.jurusan_dosen = :prodi';
            $bindings['prodi'] = $prodi;
        }

        $query .= ' GROUP BY dd.kode_dosen
            ) AS grouped
            GROUP BY category';

        $select = \DB::select($query, $bindings);

        return response()->json([
            'success' => true,
            'data' => $select
        ], 200);
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
