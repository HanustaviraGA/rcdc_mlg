<?php

namespace App\Http\Controllers;

use App\Services\Publications\PublicationDashboard;
use App\Services\Publications\PublicationDashboardRemodeledService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller khusus untuk dashboard KPI publikasi yang dirombak ulang.
 * Menggunakan HTML structure dari performance_dashboard_indikator_alokasi_budget_vrtt.html
 * 
 * Controller asli (PublicationDashboardController) tetap dipertahankan untuk backward compatibility
 * di route `/kpi-publikasi-old` dan dapat diakses melalui parameter query.
 */
class PublicationDashboardRemodelController extends Controller
{
    public function __construct(
        private PublicationDashboard $dashboard,
        private PublicationDashboardRemodeledService $remodeledService
    ) {}

    /**
     * Tampilkan dashboard KPI publikasi dengan tampilan baru (HTML terstruktur)
     */
    public function index(): View
    {
        // Fetch data dari PublicationDashboard (existing backend)
        $dashboardData = $this->dashboard->data();

        // Transform ke structure HTML yang baru
        $remodeledData = $this->remodeledService->transform($dashboardData);

        return view('dashboard.publikasi_kpi', [
            'dashboard' => $dashboardData,
            'remodeled' => $remodeledData,
            'isAuthenticated' => auth()->check(),
        ]);
    }

    /**
     * API endpoint: Get ringkasan untuk tahun/indikator tertentu
     * 
     * Query params:
     * - year: tahun (2023-2026)
     * - indicator: publication, vrtt, research, citation, ip, ntr (default: publication)
     * - quarter: Q1-Q4 atau all (default: all)
     */
    public function getSummary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'indicator' => 'nullable|string|in:publication,vrtt,research,citation,ip,ntr',
            'quarter' => 'nullable|string|in:Q1,Q2,Q3,Q4,all',
        ]);

        $year = $validated['year'];
        $indicator = $validated['indicator'] ?? 'publication';
        $quarter = $validated['quarter'] ?? 'all';

        // Get dari localStorage atau DB nanti
        // For now, return empty structure
        return response()->json([
            'year' => $year,
            'indicator' => $indicator,
            'quarter' => $quarter,
            'summary' => [
                'milestone_target' => null,
                'milestone_actual' => null,
                'fm_target' => null,
                'fm_actual' => null,
                'mhs_target' => null,
                'mhs_actual' => null,
                'percentage' => null,
            ],
            'titles' => [
                'MILESTONE',
                'PUBLIKASI SCOPUS FM',
                'PUBLIKASI SCOPUS MHS',
                '% FM HAVE SCOPUS',
            ],
        ]);
    }

    /**
     * API endpoint: Get detail prodi/departemen
     */
    public function getDepartmentDetail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => 'required|integer',
            'dept' => 'required|string',
        ]);

        $dept = $validated['dept'];
        $dashboardData = $this->dashboard->data();

        // Get faculty data untuk dept ini
        $faculty = collect($dashboardData['faculty'] ?? [])
            ->filter(fn ($f) => $this->getProdiCode($f['program']) === $dept)
            ->values()
            ->all();

        return response()->json([
            'dept' => $dept,
            'faculty' => $faculty,
            'publications' => [], // Dari localStorage untuk sekarang
        ]);
    }

    /**
     * API endpoint: Save ringkasan ke session/DB
     */
    public function saveSummary(Request $request): JsonResponse
    {
        $this->authorize('isAdmin');

        $validated = $request->validate([
            'year' => 'required|integer',
            'indicator' => 'required|string',
            'quarter' => 'nullable|string',
            'summary' => 'required|array',
            'titles' => 'required|array',
        ]);

        // Simpan ke session atau DB
        // TODO: Implementasi penyimpanan

        return response()->json(['success' => true, 'message' => 'Ringkasan disimpan']);
    }

    /**
     * API endpoint: Get faculty publications
     */
    public function getFacultyPublications(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => 'required|integer',
            'dept' => 'required|string',
            'code' => 'required|string',
        ]);

        $dashboardData = $this->dashboard->data();

        // Filter publications
        $publications = collect($dashboardData['publications'] ?? [])
            ->filter(fn ($p) => $p['request_code'] && $validated['code'])
            ->values()
            ->all();

        return response()->json([
            'code' => $validated['code'],
            'publications' => $publications,
        ]);
    }

    /**
     * Helper: Get prodi code dari nama prodi
     */
    private function getProdiCode(string $programName): string
    {
        $codes = [
            'Computer Science' => 'CS',
            'Visual Communication Design' => 'DKV',
            'Interior Design' => 'DI',
            'Business Creation' => 'BC',
        ];

        foreach ($codes as $name => $code) {
            if (str_contains(strtolower($programName), strtolower($name))) {
                return $code;
            }
        }

        return strtoupper(substr($programName, 0, 3));
    }
}
