<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DataDosen;
use App\Models\AttributeDosen;
use App\Models\Researchs;
use App\Models\Comdevs;

class LandingController extends Controller
{
    public function __construct(){
        // ignore_user_abort(true);
        set_time_limit(0);
    }

    public function home(){
        Auth::logout();
        $dosen = DataDosen::limit(6)->inRandomOrder()->get();
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

        // Research
        // $kode_dosen = 'D6394';
        $set_dosen = DataDosen::inRandomOrder()->first();
        $kode_dosen = $set_dosen['kode_dosen'];
        $client = new \GuzzleHttp\Client();
        // $response = $client->post('https://binus.ac.id/malang/computer-science/wp-json/binus-scholar/v1/lecturers/researchs', [
        //     'headers' => [
        //         'Authorization' => 'Bearer ' . env('BINUS_API_TOKEN'),
        //         'Content-Type' => 'application/json',
        //     ],
        //     'json' => [
        //         'lecturer_id' => $kode_dosen,
        //     ],
        // ]);
        // $research = json_decode($response->getBody(), true);
        // $researchs = $research['data'];
        $researchs = Researchs::where('kode_dosen', $kode_dosen)
            // ->whereIn('budget_year', [2023, 2024, 2025, 2026])
        ->orderBy('budget_year', 'desc')
        ->limit(4)
        ->get();
        return view('landing.index', compact(
            'researchs',
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

    public function lecturers(Request $request){
        $data = $request->all();
        $dosen = DataDosen::query();
        $dosen->leftJoin('identitas_dosen', 'database_dosen_new.kode_dosen', '=', 'identitas_dosen.kode_dosen');
        if(isset($data['search'])){
            $dosen->where('nama_dosen', 'LIKE', '%'.$data['search'].'%')
                ->orWhere('nama_gugus_binaan', 'LIKE', '%'.$data['search'].'%');
        }
        if (!empty($data['gugus'])) {
            $dosen->where('nama_gugus_binaan', $data['gugus']);
        }
        $dosen->orderBy('nama_dosen', 'ASC');
        $dosen->select('database_dosen_new.*', 'identitas_dosen.foto_dosen');
        $dosen = $dosen->paginate(12);
        $gugusBinaan = DataDosen::query()
            ->select('nama_gugus_binaan')
            ->whereNotNull('nama_gugus_binaan')
            ->where('nama_gugus_binaan', '!=', '')
            ->distinct()
            ->orderBy('nama_gugus_binaan', 'ASC')
            ->pluck('nama_gugus_binaan');

        return view('landing.team', compact('dosen', 'gugusBinaan'));
    }

    public function lecture_detail($kode_dosen){
        $dosen = DataDosen::leftJoin('identitas_dosen', 'database_dosen_new.kode_dosen', '=', 'identitas_dosen.kode_dosen')->where('database_dosen_new.kode_dosen', $kode_dosen)->first();
        $attribute = AttributeDosen::where('kode_dosen', $kode_dosen)->get();
        // Research
        // $client = new \GuzzleHttp\Client();
        // $response = $client->post('https://binus.ac.id/malang/computer-science/wp-json/binus-scholar/v1/lecturers/researchs', [
        //     'headers' => [
        //         'Authorization' => 'Bearer ' . env('BINUS_API_TOKEN'),
        //         'Content-Type' => 'application/json',
        //     ],
        //     'json' => [
        //         'lecturer_id' => $kode_dosen,
        //     ],
        // ]);
        // $research = json_decode($response->getBody(), true);
        // $researchs = $research['data'];
        $researchs = Researchs::where('kode_dosen', $kode_dosen)
            // ->whereIn('budget_year', [2023, 2024, 2025, 2026])
        ->orderByDesc('budget_year')
        ->limit(4)
        ->get();
        // Community Development
        // $client_comdev = new \GuzzleHttp\Client();
        // $response_comdev = $client_comdev->post('https://binus.ac.id/malang/computer-science/wp-json/binus-scholar/v1/lecturers/community-services', [
        //     'headers' => [
        //         'Authorization' => 'Bearer ' . env('BINUS_API_TOKEN'),
        //         'Content-Type' => 'application/json',
        //     ],
        //     'json' => [
        //         'lecturer_id' => $kode_dosen,
        //     ],
        // ]);
        // $comdev = json_decode($response_comdev->getBody(), true);
        // $comdevs = $comdev['data']['v2'];
        $comdevs = Comdevs::where('kode_dosen', $kode_dosen)
            // ->whereIn('event_date', ['2023', '2024', '2025', '2026'])
        ->orderByDesc('event_date')
        ->limit(6)
        ->get();
        // dd($comdevs);
        return view('landing.service-details', compact('dosen', 'attribute', 'researchs', 'comdevs', 'kode_dosen'));
    }

    public function sync_research_ac_id(){
        $query = DataDosen::all();
        // Research
        foreach($query as $dosen){
            $client = new \GuzzleHttp\Client();
            $response = $client->post('https://binus.ac.id/malang/computer-science/wp-json/binus-scholar/v1/lecturers/researchs', [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('BINUS_API_TOKEN'),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'lecturer_id' => $dosen['kode_dosen'],
                ],
            ]);
            $research = json_decode($response->getBody(), true);
            $researchs = $research['data'];
            foreach($researchs as $list_riset){
                $find = Researchs::where('ID', $list_riset['ID'])->first();
                try{
                    \DB::beginTransaction();
                    if($find){
                        Researchs::where('ID', $list_riset['ID'])->update([
                            'kode_dosen' => $dosen['kode_dosen'] ?? null,
                            'title' => $list_riset['title'] ?? null,
                            'propose_year' => $list_riset['propose_year'] ?? null,
                            'budget_year' => $list_riset['budget_year'] ?? null,
                            'institution' => $list_riset['institution'] ?? null,
                            'contract_number' => $list_riset['contract_number'] ?? null,
                            'abstract' => $list_riset['abstract'] ?? null,
                            'keywords' => $list_riset['keywords'] ?? null,
                            'source_of_fund' => $list_riset['source_of_fund'] ?? null,
                            'funding' => $list_riset['funding'] ?? null,
                            'researcher' => json_encode($list_riset['researcher'] ?? null),
                            'permalink' => $list_riset['permalink'] ?? null,
                            'updated_at' => now()
                        ]);
                    }else{
                        Researchs::create([
                            'ID' => $list_riset['ID'],
                            'kode_dosen' => $dosen['kode_dosen'] ?? null,
                            'title' => $list_riset['title'] ?? null,
                            'propose_year' => $list_riset['propose_year'] ?? null,
                            'budget_year' => $list_riset['budget_year'] ?? null,
                            'institution' => $list_riset['institution'] ?? null,
                            'contract_number' => $list_riset['contract_number'] ?? null,
                            'abstract' => $list_riset['abstract'] ?? null,
                            'keywords' => $list_riset['keywords'] ?? null,
                            'source_of_fund' => $list_riset['source_of_fund'] ?? null,
                            'funding' => $list_riset['funding'] ?? null,
                            'researcher' => json_encode($list_riset['researcher'] ?? null),
                            'permalink' => $list_riset['permalink'] ?? null,
                            'created_at' => now()
                        ]);
                    }
                    \DB::commit();
                }catch(\Exception $e){
                    \DB::rollBack();
                }
            }
        }
    }

    public function sync_comdev_ac_id(){
        $query = DataDosen::all();
        // Community Development
        foreach($query as $dosen){
            $client_comdev = new \GuzzleHttp\Client();
            $response_comdev = $client_comdev->post('https://binus.ac.id/malang/computer-science/wp-json/binus-scholar/v1/lecturers/community-services', [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('BINUS_API_TOKEN'),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'lecturer_id' => $dosen['kode_dosen'],
                ],
            ]);
            $comdev = json_decode($response_comdev->getBody(), true);
            $comdevs = $comdev['data']['v2'];
            foreach($comdevs as $list_comdev){
                $find = Comdevs::where('ID', $list_comdev['ID'])->first();
                try{
                    \DB::beginTransaction();
                    if($find){
                        Comdevs::where('ID', $list_comdev['ID'])->update([
                            'kode_dosen' => $dosen['kode_dosen'] ?? null,
                            'community_name' => $list_comdev['community_name'] ?? null,
                            'topic_name' => $list_comdev['topic_name'] ?? null,
                            'subtopic_name' => $list_comdev['subtopic_name'] ?? null,
                            'location' => $list_comdev['location'] ?? null,
                            'event_date' => $list_comdev['event_date'] ?? null,
                            'updated_at' => now()
                        ]);
                    }else{
                        Comdevs::create([
                            'ID' => $list_comdev['ID'],
                            'kode_dosen' => $dosen['kode_dosen'] ?? null,
                            'community_name' => $list_comdev['community_name'] ?? null,
                            'topic_name' => $list_comdev['topic_name'] ?? null,
                            'subtopic_name' => $list_comdev['subtopic_name'] ?? null,
                            'location' => $list_comdev['location'] ?? null,
                            'event_date' => $list_comdev['event_date'] ?? null,
                            'created_at' => now()
                        ]);
                    }
                    \DB::commit();
                }catch(\Exception $e){
                    \DB::rollBack();
                }
            } 
        }
    }
}
