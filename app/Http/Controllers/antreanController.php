<?php

namespace App\Http\Controllers;

use App\Models\antreans;
use Illuminate\Http\Request;

class antreanController extends Controller
{

    public function home()
    {
        return view('welcome');
    }

    public function ambilAntrean()
    {
        return view('ambil-antrean');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pihak' => 'required',
            'nomor_perkara' => 'required',
            'jenis_perkara' => 'required',
        ]);

        $currentDate = now()->format('Ymd');
        $lastEntry = antreans::latest()->first();

        $tanggal_sidang = date('Y-m-d');

        $currentTime = date('H:i');
        $perkiraan_sidang = date('H:i', strtotime($currentTime . ' +20 minutes'));

        if ($lastEntry && $lastEntry->created_at->format('Ymd') == $currentDate) {
            $lastTiketNumber = (int)$lastEntry->tiketAntrean;
            $tiketAntrean = str_pad($lastTiketNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $tiketAntrean = '001';
        }

        if ($perkiraan_sidang > '16:00') {
            $perkiraan_sidang = '08:00';
            $tanggal_sidang = date('Y-m-d', strtotime($tanggal_sidang . ' +1 day'));
        }

        $antreanBaru = antreans::create([
            'namaLengkap' => $request->input('nama_pihak'),
            'noPerkara' => $request->input('nomor_perkara'),
            'tiketAntrean' => $tiketAntrean,
            'jenisPerkara' => $request->input('jenis_perkara'),
            'jam_perkiraan' => $perkiraan_sidang,
            'tanggal_sidang' => $tanggal_sidang,
        ]);

        // CEK SELECT INPUT
        // MASALAH NO PERKARA KEK ANGKA KEBESARAN
        // BUG POP UP BERGESER

        return redirect('/')->with('antrean', $antreanBaru)->with('showModal', true);
    }
}
