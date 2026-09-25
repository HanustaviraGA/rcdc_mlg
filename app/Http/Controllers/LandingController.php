<?php

namespace App\Http\Controllers;

use App\Models\AttributeDosen;
use App\Models\Comdevs;
use App\Models\DataDosen;
use App\Services\Research\ResearchGallery;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class LandingController extends Controller
{
    private const BINUS_SCHOLAR_BASE_URL = 'https://binus.ac.id/malang/computer-science/wp-json/binus-scholar/v1/lecturers/';

    public function home()
    {
        Auth::logout();
        $dosen = DataDosen::visibleOnWebsite()->limit(6)->inRandomOrder()->get();
        $prodi = \DB::table('v_statistik_prodi')->get();
        $prodiLabels = [];
        $prodiCounts = [];
        foreach ($prodi as $item) {
            $prodiLabels[] = $item->nama_gugus_binaan;
            $prodiCounts[] = $item->jml_fm;
        }

        $umkmYearRows = \DB::table('umkm_partnership')
            ->select('tahun_bergabung', \DB::raw('COUNT(*) as total'))
            ->whereNotNull('tahun_bergabung')
            ->where('tahun_bergabung', '!=', '')
            ->groupBy('tahun_bergabung')
            ->orderBy('tahun_bergabung', 'ASC')
            ->get();
        $umkmYearLabels = $umkmYearRows->pluck('tahun_bergabung')->values();
        $umkmYearData = $umkmYearRows->pluck('total')->values();

        $umkmClusterRows = \DB::table('umkm_partnership')
            ->select('cluster', \DB::raw('COUNT(*) as total'))
            ->whereNotNull('cluster')
            ->where('cluster', '!=', '')
            ->groupBy('cluster')
            ->orderBy('cluster', 'ASC')
            ->get();
        $umkmClusterLabels = $umkmClusterRows->pluck('cluster')->values();
        $umkmClusterData = $umkmClusterRows->pluck('total')->values();

        $jjaCounts = [];
        $jjaRows = DataDosen::query()
            ->select('jja')
            ->whereNotNull('jja')
            ->where('jja', '!=', '')
            ->get();
        foreach ($jjaRows as $row) {
            $prefix = 'Lainnya';
            if (preg_match('/^[A-Za-z]+/', $row->jja, $matches)) {
                $prefix = strtoupper($matches[0]);
            }
            $jjaCounts[$prefix] = ($jjaCounts[$prefix] ?? 0) + 1;
        }
        ksort($jjaCounts);
        $jjaLabels = array_keys($jjaCounts);
        $jjaData = array_values($jjaCounts);

        $pendidikanCounts = [];
        $pendidikanRows = DataDosen::query()
            ->select('pendidikan')
            ->whereNotNull('pendidikan')
            ->where('pendidikan', '!=', '')
            ->get();
        foreach ($pendidikanRows as $row) {
            $label = $row->pendidikan;
            $pendidikanCounts[$label] = ($pendidikanCounts[$label] ?? 0) + 1;
        }
        ksort($pendidikanCounts);
        $pendidikanLabels = array_keys($pendidikanCounts);
        $pendidikanData = array_values($pendidikanCounts);

        $galleryProjects = app(\App\Services\Research\ResearchGallery::class)->projects();
        $researchGalleryProjects = $galleryProjects->where('source', 'rectorate');
        if ($researchGalleryProjects->isEmpty()) {
            $researchGalleryProjects = $galleryProjects->where('source', 'system');
        }
        $researchGalleryCount = $researchGalleryProjects->count();
        $researchGalleryProjects = $researchGalleryProjects->sortByDesc('year')->take(4)->values();

        return view('landing.index', compact(
            'researchGalleryProjects',
            'researchGalleryCount',
            'dosen',
            'prodiLabels',
            'prodiCounts',
            'jjaLabels',
            'jjaData',
            'pendidikanLabels',
            'pendidikanData',
            'umkmYearLabels',
            'umkmYearData',
            'umkmClusterLabels',
            'umkmClusterData'
        ));
    }

    public function lecturers(Request $request)
    {
        $data = $request->all();
        $dosen = DataDosen::visibleOnWebsite();
        $dosen->leftJoin('identitas_dosen', 'database_dosen_new.kode_dosen', '=', 'identitas_dosen.kode_dosen');
        if (isset($data['search'])) {
            $dosen->where(function ($query) use ($data) {
                $query->where('nama_dosen', 'LIKE', '%'.$data['search'].'%')
                    ->orWhere('nama_gugus_binaan', 'LIKE', '%'.$data['search'].'%');
            });
        }
        if (! empty($data['gugus'])) {
            $dosen->where('nama_gugus_binaan', $data['gugus']);
        }
        $dosen->orderBy('nama_dosen', 'ASC');
        $dosen->select('database_dosen_new.*', 'identitas_dosen.foto_dosen');
        $dosen = $dosen->paginate(12)->withQueryString();
        $gugusBinaan = DataDosen::visibleOnWebsite()
            ->select('nama_gugus_binaan')
            ->whereNotNull('nama_gugus_binaan')
            ->where('nama_gugus_binaan', '!=', '')
            ->distinct()
            ->orderBy('nama_gugus_binaan', 'ASC')
            ->pluck('nama_gugus_binaan');

        return view('landing.team', compact('dosen', 'gugusBinaan'));
    }

    public function lecture_detail($kode_dosen, ResearchGallery $gallery)
    {
        $dosen = DataDosen::visibleOnWebsite()
            ->leftJoin('identitas_dosen', 'database_dosen_new.kode_dosen', '=', 'identitas_dosen.kode_dosen')
            ->where('database_dosen_new.kode_dosen', $kode_dosen)
            ->select('identitas_dosen.*', 'database_dosen_new.*')
            ->firstOrFail();
        $attribute = AttributeDosen::where('kode_dosen', $kode_dosen)->get();
        $projects = $gallery->forLecturer($kode_dosen);
        $researchCount = $projects->count();
        $researchs = $projects->take(3)->values();

        // Community Development
        $comdevs = $this->getComdevs($kode_dosen);

        return view('landing.service-details', compact('dosen', 'attribute', 'researchs', 'researchCount', 'comdevs', 'kode_dosen'));
    }

    private function getComdevs(string $kode_dosen): array
    {
        $comdev = $this->requestBinusScholar('POST', 'community-services', $kode_dosen);
        $comdevs = data_get($comdev, 'data.v2');

        if (is_array($comdevs) && ! empty($comdevs)) {
            return $comdevs;
        }

        return Comdevs::where('kode_dosen', $kode_dosen)->get()->toArray();
    }

    private function requestBinusScholar(string $method, string $endpoint, string $kode_dosen): array
    {
        try {
            $response = Http::baseUrl(self::BINUS_SCHOLAR_BASE_URL)
                ->withToken((string) env('BINUS_API_TOKEN'))
                ->connectTimeout(5)->timeout(10)
                ->send($method, $endpoint, ['json' => ['lecturer_id' => $kode_dosen]]);

            if (! $response->successful()) {
                return [];
            }

            $payload = $response->json();

            return is_array($payload) ? $payload : [];
        } catch (ConnectionException $e) {
            return [];
        }
    }
}
