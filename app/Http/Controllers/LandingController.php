<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DataDosen;

class LandingController extends Controller
{
    public function home(){
        Auth::logout();
        $dosen = DataDosen::limit(6)->inRandomOrder()->get();
        return view('landing.index', compact('dosen'));
    }

    public function lecturers(Request $request){
        $data = $request->all();
        $dosen = DataDosen::query();
        if(isset($data['search'])){
            $dosen->where('nama_dosen', 'LIKE', '%'.$data['search'].'%')
                ->orWhere('nama_gugus_binaan', 'LIKE', '%'.$data['search'].'%');
        }
        if (!empty($data['gugus'])) {
            $dosen->where('nama_gugus_binaan', $data['gugus']);
        }
        $dosen->orderBy('nama_dosen', 'ASC');
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
}
