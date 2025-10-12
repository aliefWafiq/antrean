<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\pengajuanJamSidangs;
use Illuminate\Support\Facades\Auth;
use App\Models\antreans;
use Illuminate\Http\Request;

class pengajuan_jam_sidangController extends Controller
{

    public function ubahJamSidang()
    {
        $antreanId = Auth::id();
        $dataAntrean = antreans::where('id', $antreanId)->latest()->first();

        return view('ubahJam', ['dataAntrean' => $dataAntrean]);
    }

    public function editJamSidang(Request $request, antreans $antrean)
    {
        $validated = $request->validate([
            'slotJamTersedia' => 'required|date_format:H:i'
        ]);

        pengajuanJamSidangs::create([
            'id_user' => $antrean->id,
            'jam_sidang' => $validated['slotJamTersedia']
        ]);

        return redirect('/antrean')->with('showModal', true);
    }
}
