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
                AND rd.year = :year
                AND rd.period = :period
                AND rd.month = :month';

        if (!empty($whereClauses)) {
            $query .= ' WHERE ' . implode(' AND ', $whereClauses);
        }

        $query .= '
            GROUP BY 
                dd.kode_dosen, dd.nama_dosen, dd.ft_dosen, dd.jja_dosen, 
                dd.pendidikan_dosen, dd.jurusan_dosen, dd.maxscopuskonf_dosen
        ';

        // Now filter the result using HAVING clause (aggregated data comparison)
        $havingClause = '';
        if ($kondisi && $kondisi !== 'SK') {
            switch ($kondisi) {
                case 'ME':
                    $havingClause = 'HAVING mandiri_seminar_scopus > max_mandiri_scopus';
                    break;
                case 'SE':
                    $havingClause = 'HAVING mandiri_seminar_scopus = max_mandiri_scopus';
                    break;
                case 'KR':
                    $havingClause = 'HAVING mandiri_seminar_scopus < max_mandiri_scopus';
                    break;
                case 'TK':
                    $havingClause = 'HAVING mandiri_seminar_scopus = 0';
                    break;
            }
        }

        if (!empty($havingClause)) {
            $query .= ' ' . $havingClause;
        }

        $results = collect(\DB::select($query, $bindings));

        return select_table($results);
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

        $havingClause = '';
        if ($kondisi && $kondisi !== 'SK') {
            switch ($kondisi) {
                case 'ME':
                    $havingClause = 'HAVING mandiri_seminar_scopus > max_mandiri_scopus';
                    break;
                case 'SE':
                    $havingClause = 'HAVING mandiri_seminar_scopus = max_mandiri_scopus';
                    break;
                case 'KR':
                    $havingClause = 'HAVING mandiri_seminar_scopus < max_mandiri_scopus';
                    break;
                case 'TK':
                    $havingClause = 'HAVING mandiri_seminar_scopus = 0';
                    break;
            }
        }

        $query = '
            SELECT
                CASE
                    WHEN mandiri_seminar_scopus = 0 THEN "Tidak Memiliki Konferensi"
                    WHEN mandiri_seminar_scopus < max_mandiri_scopus THEN "Kurang Dari Batas"
                    WHEN mandiri_seminar_scopus > max_mandiri_scopus THEN "Melebihi Batas"
                    WHEN mandiri_seminar_scopus = max_mandiri_scopus THEN "Sesuai Batas"
                END AS category,
                COUNT(*) AS jumlah_dosen
            FROM (
                SELECT
                    dd.kode_dosen,
                    dd.maxscopuskonf_dosen AS max_mandiri_scopus,
                    COALESCE(SUM(CASE 
                        WHEN rd.jenis = "Seminar" 
                            AND rd.sumber_paper = "Penelitian Mandiri" 
                            AND rd.tipe_publikasi = "Scopus" 
                        THEN rd.bobot ELSE 0 END), 0) AS mandiri_seminar_scopus
                FROM database_dosen dd
                LEFT JOIN rectorate_dosen rd 
                    ON rd.kode_dosen = dd.kode_dosen
                    AND rd.year = :year
                    AND rd.period = :period
                    AND rd.month = :month';

        // Add WHERE clauses
        if (!empty($whereClauses)) {
            $query .= ' WHERE ' . implode(' AND ', $whereClauses);
        }

        $query .= '
                GROUP BY dd.kode_dosen, dd.maxscopuskonf_dosen';

        // Add HAVING clause if needed
        if (!empty($havingClause)) {
            $query .= ' ' . $havingClause;
        }

        $query .= '
            ) AS grouped
            GROUP BY category';

        $result = \DB::select($query, $bindings);

        return response()->json([
            'success' => true,
            'data' => $result
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
