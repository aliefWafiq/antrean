<?php

namespace App\Http\Controllers;

use App\Models\antreans;
use App\Models\perkara;
use App\Models\pengajuanJamSidangs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class antreanController extends Controller
{

    public function home()
    {
        return view('welcome');
    }

    public function loginView()
    {
        return view('login');
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'NomorPerkara' => 'required',
        ]);

        $dataPerkara = perkara::where('noPerkara', $request->NomorPerkara)->first();

        if (!$dataPerkara) {
            return back()->with('error', 'Data yang Anda masukkan tidak terdaftar.');
        }

        $sekarang = now();
        $tanggalSidangCek = $sekarang->copy()->startOfDay();

        $jamSekarangStr = $sekarang->format('H:i');
        if ($jamSekarangStr < '07:00' || $jamSekarangStr > '14:30') {
            return back()->with('error', 'Maaf, pendaftaran antrean hanya bisa dilakukan pada jam 07:00 - 14:30.');
        }

        $antreanSudahAda = antreans::where('id_perkara', $dataPerkara->id)
            ->where('tanggal_sidang', $tanggalSidangCek->format('Y-m-d'))
            ->first();

        if ($antreanSudahAda) {
            session([
                'perkara_id' => $dataPerkara->id,
                'perkara_nomor' => $dataPerkara->noPerkara,
                'perkara_pihak' => $dataPerkara->namaPihak
            ]);
            Auth::loginUsingId($dataPerkara->id);
            return redirect('/antrean');
        }

        try {
            $antreanBaru = DB::transaction(function () use ($dataPerkara, $sekarang, $tanggalSidangCek) {

                $dataTiket = [
                    "08:30" => "001",
                    "08:45" => "002",
                    "09:00" => "003",
                    "09:15" => "004",
                    "09:30" => "005",
                    "09:45" => "006",
                    "10:00" => "007",
                    "10:15" => "008",
                    "10:30" => "009",
                    "10:45" => "010",
                    "11:00" => "011",
                    "11:15" => "012",
                    "11:30" => "013",
                    "11:45" => "014",
                    "13:00" => "015",
                    "13:15" => "016",
                    "13:30" => "017",
                    "13:45" => "018",
                    "14:00" => "019",
                    "14:15" => "020",
                    "14:30" => "021",
                    "14:45" => "022",
                    "15:00" => "023",
                    "15:15" => "024",
                    "15:30" => "025",
                    "15:45" => "026",
                ];

                $tanggal_sidang_final = $tanggalSidangCek->copy();
                $tanggal_sidang_final_str = $tanggal_sidang_final->format('Y-m-d');
                $jamSlots = array_keys($dataTiket);
                $waktuSekarangStr = $sekarang->format('H:i');

                $takenSlotsReguler = antreans::where('tanggal_sidang', $tanggal_sidang_final_str)
                    ->lockForUpdate()
                    ->pluck('jam_perkiraan')
                    ->map(function ($time) {
                        return Carbon::parse($time)->format('H:i');
                    })->toArray();

                $takenSlotsRequest = pengajuanJamSidangs::where('tanggal_sidang', $tanggal_sidang_final_str)
                    ->where('status', 'diterima')
                    ->pluck('jam_sidang')
                    ->map(function ($time) {
                        return Carbon::parse($time)->format('H:i');
                    })->toArray();

                $takenSlots = array_unique(array_merge($takenSlotsReguler, $takenSlotsRequest));

                $jamPerkiraanStr = "";
                $tiketAntrean = "";
                $foundSlot = false;

                foreach ($jamSlots as $slot) {

                    if ($slot > $waktuSekarangStr && !in_array($slot, $takenSlots)) {
                        $jamPerkiraanStr = $slot;
                        $tiketAntrean = $dataTiket[$slot];
                        $foundSlot = true;
                        break;
                    }
                }

                if (!$foundSlot) {
                    throw new \Exception('Maaf, antrean untuk hari ini sudah penuh');
                }

                list($jam, $menit) = explode(':', $jamPerkiraanStr);
                $perkiraan_sidang_final = $tanggal_sidang_final->copy()->setTime($jam, $menit, 0);

                return antreans::create([
                    'id_perkara'         => $dataPerkara->id,
                    'namaLengkap'        => $dataPerkara->namaPihak,
                    'noPerkara'          => $dataPerkara->noPerkara,
                    'jenisPerkara'       => $dataPerkara->jenisPerkara,
                    'tiketAntrean'       => $tiketAntrean,
                    'jam_perkiraan'      => $perkiraan_sidang_final->format('H:i:s'),
                    'tanggal_sidang'     => $tanggal_sidang_final->format('Y-m-d'),
                    'statusAmbilAntrean' => 'sudah ambil',
                    'status'             => 'menunggu'
                ]);
            }, 5);

            session([
                'perkara_id' => $dataPerkara->id,
                'perkara_nomor' => $dataPerkara->noPerkara,
                'perkara_pihak' => $dataPerkara->namaPihak
            ]);
            Auth::loginUsingId($dataPerkara->id);

            return redirect('/antrean');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengambil antrean: ' . $e->getMessage());
        }
    }

    public function antrean()
    {
        $perkaraId = session()->get('perkara_id');

        $dataPerkara = perkara::where('id', $perkaraId)->first();
        $dataAntrean = antreans::where('id_perkara', $perkaraId)->latest()->first();
        $countAntreanHariIni = antreans::where('tanggal_sidang', now()->format('Y-m-d'))
            ->where('status', 'menunggu')
            ->count();

        return view('antrean', [
            'dataAntrean' => $dataAntrean,
            'dataPerkara' => $dataPerkara,
            'countAntreanHariIni' => $countAntreanHariIni
        ]);
    }

    public function search($query)
    {
        return perkara::select('noPerkara')
            ->where('noPerkara', 'LIKE', '%' . $query . '%')
            ->limit(10)
            ->get();
    }

    public function logout(Request $request)
    {
        $request->session()->forget('perkara_id');
        return redirect('/login');
    }
}
