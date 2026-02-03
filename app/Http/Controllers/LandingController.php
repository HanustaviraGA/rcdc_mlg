<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DataDosen;
use App\Models\AttributeDosen;

class LandingController extends Controller
{
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

        return view('landing.index', compact(
            'dosen',
            'prodiLabels',
            'prodiCounts',
            'jjaLabels',
            'jjaData',
            'pendidikanLabels',
            'pendidikanData'
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
        $client = new \GuzzleHttp\Client();
        $response = $client->post('https://binus.ac.id/malang/computer-science/wp-json/binus-scholar/v1/lecturers/researchs', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('BINUS_API_TOKEN'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'lecturer_id' => $kode_dosen,
            ],
        ]);
        $research = json_decode($response->getBody(), true);
        $researchs = $research['data'];
        // Community Development
        $client_comdev = new \GuzzleHttp\Client();
        $response_comdev = $client_comdev->post('https://binus.ac.id/malang/computer-science/wp-json/binus-scholar/v1/lecturers/community-services', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('BINUS_API_TOKEN'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'lecturer_id' => $kode_dosen,
            ],
        ]);
        $comdev = json_decode($response_comdev->getBody(), true);
        $comdevs = $comdev['data']['v2'];
        dd($comdevs);
        return view('landing.service-details', compact('dosen', 'attribute', 'researchs', 'comdevs'));
    }
}
