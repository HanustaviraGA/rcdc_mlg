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

        // Start building the SQL query
        $stringq = 'SELECT
            dd.kode_dosen,
            dd.nama_dosen,
            dd.ft_dosen,
            dd.jja_dosen,
            dd.pendidikan_dosen,
            dd.jurusan_dosen,
            COALESCE(SUM(CASE 
                WHEN rd.tipe_publikasi = "Nscopus" AND rd.jenis = "Jurnal"
                THEN rd.bobot ELSE 0 END), 0) AS jml_nscopus,
            COALESCE(SUM(CASE 
                WHEN rd.tipe_publikasi = "Scopus" AND rd.jenis = "Jurnal"
                THEN rd.bobot ELSE 0 END), 0) AS jml_scopus
        FROM database_dosen dd
        LEFT JOIN rectorate_dosen rd 
            ON rd.kode_dosen = dd.kode_dosen
            AND rd.year = "' . addslashes($data['year']) . '"
            AND rd.period = "' . addslashes($data['period']) . '"
            AND rd.month = "' . addslashes($data['month']) . '"';

        // Build WHERE and HAVING parts
        $where = [];
        $having = [];

        if (isset($data['prodi']) && $data['prodi'] !== 'Semua Prodi') {
            $where[] = "dd.jurusan_dosen = '" . addslashes($data['prodi']) . "'";
        }

        if (isset($data['kondisi']) && $data['kondisi'] !== 'SK') {
            switch ($data['kondisi']) {
                case 'TK':
                    $having[] = "jml_nscopus = 0 AND jml_scopus = 0";
                    break;
                case 'HNC':
                    $having[] = "jml_nscopus > 0 AND jml_scopus = 0";
                    break;
                case 'HC':
                    $having[] = "jml_scopus > 0 AND jml_nscopus = 0";
                    break;
            }
        }

        // Append WHERE clause
        if (!empty($where)) {
            $stringq .= " WHERE " . implode(' AND ', $where);
        }

        // Append GROUP BY clause
        $stringq .= " GROUP BY 
            dd.kode_dosen, dd.nama_dosen, dd.ft_dosen, dd.jja_dosen, 
            dd.pendidikan_dosen, dd.jurusan_dosen";

        // Append HAVING clause
        if (!empty($having)) {
            $stringq .= " HAVING " . implode(' AND ', $having);
        }

        // Execute the query and return as collection
        $query = collect(\DB::select($stringq));
        return select_table($query);
    }


    public function init_chart(Request $request)
    {
        $year = (int) $request->input('year');
        $month = (int) $request->input('month');
        $period = (int) $request->input('period');
        $prodi = $request->input('prodi');
        $kondisi = $request->input('kondisi');

        $bindings = [
            'year' => $year,
            'month' => $month,
            'period' => $period,
        ];

        $whereClauses = [];
        if ($prodi && $prodi !== 'Semua Prodi') {
            $whereClauses[] = 'dd.jurusan_dosen = :prodi';
            $bindings['prodi'] = $prodi;
        }

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

        // Add WHERE clauses if any
        if (!empty($whereClauses)) {
            $query .= ' WHERE ' . implode(' AND ', $whereClauses);
        }

        $query .= ' GROUP BY dd.kode_dosen
            ) AS grouped';

        // Apply kondisi filter using HAVING on aggregated fields
        $havingClauses = [];
        if ($kondisi && $kondisi !== 'SK') {
            switch ($kondisi) {
                case 'TK':
                    $havingClauses[] = 'jml_nscopus = 0 AND jml_scopus = 0';
                    break;
                case 'HNC':
                    $havingClauses[] = 'jml_nscopus > 0 AND jml_scopus = 0';
                    break;
                case 'HC':
                    $havingClauses[] = 'jml_scopus > 0 AND jml_nscopus = 0';
                    break;
            }
        }

        if (!empty($havingClauses)) {
            $query .= ' WHERE ' . implode(' AND ', $havingClauses);
        }

        $query .= ' GROUP BY category';

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
