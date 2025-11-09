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
        $perkaraId = session()->get('perkara_id');
        $dataAntrean = antreans::where('id_perkara', $perkaraId)->latest()->first();

        if (!$dataAntrean) {
            return redirect('/antrean')->with('error', 'Anda harus memiliki antrean terlebih dahulu.');
        }

        $allSlots = [
            '08:30',
            '09:00',
            '09:30',
            '10:00',
            '10:30',
            '11:00',
            '11:30',
            '13:30',
            '14:00',
            '14:30' 
        ];

        $tanggalSidang = $dataAntrean->tanggal_sidang;

        $takenSlots = antreans::where('tanggal_sidang', $tanggalSidang)
            ->whereNotNull('jam_perkiraan') 
            ->pluck('jam_perkiraan')    
            ->map(function ($time) {
                return Carbon::parse($time)->format('H:i');
            })
            ->all();

        $availableSlots = array_diff($allSlots, $takenSlots);

        return view('ubahJam', [ 
            'dataAntrean' => $dataAntrean,
            'availableSlots' => $availableSlots
        ]);
    }

    public function editJamSidang(Request $request, antreans $antrean)
    {
        $validated = $request->validate([
            'slotJamTersedia' => 'required|date_format:H:i'
        ]);

        $jamPilihan = $request->slotJamTersedia . ':00';

        pengajuanJamSidangs::create([
            'id_user' => $antrean->id,
            'jam_sidang' => $jamPilihan
        ]);

        return redirect('/antrean')->with('showModal', true);
    }
}
