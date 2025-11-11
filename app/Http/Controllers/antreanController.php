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

    public function formVerify()
    {
        if (!session()->has('otp_user_id')) {
            return redirect('/login');
        }
        return view('verifyOtp');
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
        if ($jamSekarangStr < '08:30' || $jamSekarangStr > '14:30') {
            return back()->with('error', 'Maaf, pendaftaran antrean hanya bisa dilakukan pada jam 08:30 - 14:30.');
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

    public function ambilAntrean(Request $request)
    {
        try {
            $idPerkara = session()->get('perkara_id');
            $checkAntrean = antreans::where('id_perkara', $idPerkara)
                ->where('status', 'menunggu')
                ->first();

            if ($checkAntrean) {
                return redirect('/antrean')->with('antreanTelahDiAmbil', 'Anda sudah mengambil antrean sebelumnya.');
            }

            $antreanBaru = DB::transaction(function () use ($request) {
                $idPerkara = session()->get('perkara_id');
                $dataPerkara = perkara::findOrFail($idPerkara);

                $sekarang = now();
                $isBesok = false;

                if ($sekarang->format('H:i') >= '14:30') {
                    $tanggal_sidang_final = $sekarang->copy()->addDay()->startOfDay();
                    $isBesok = true;
                } else {
                    $tanggal_sidang_final = $sekarang->copy()->startOfDay();
                }

                $antreanTerakhir = antreans::where('tanggal_sidang', $tanggal_sidang_final->format('Y-m-d'))
                    ->orderBy('id', 'desc')
                    ->lockForUpdate()
                    ->first();

                if ($antreanTerakhir) {
                    $waktuTerakhir = \Carbon\Carbon::parse($antreanTerakhir->jam_perkiraan);
                    $waktuBerikutnya = $waktuTerakhir->copy()->addMinutes(30);

                    if ($isBesok) {
                        $perkiraan_sidang_final = $waktuBerikutnya;
                    } else {
                        if ($sekarang->gt($waktuBerikutnya)) {
                            $perkiraan_sidang_final = $sekarang;
                        } else {
                            $perkiraan_sidang_final = $waktuBerikutnya;
                        }
                    }
                } else {
                    if ($isBesok) {
                        $perkiraan_sidang_final = $tanggal_sidang_final->copy()->setTime(8, 0, 0);
                    } else {
                        $perkiraan_sidang_final = $sekarang;
                    }
                }

                $jamPerkiraanStr = $perkiraan_sidang_final->format('H:i:s');

                if ($jamPerkiraanStr > '12:00:00' && $jamPerkiraanStr < '13:30:00') {
                    $perkiraan_sidang_final = $tanggal_sidang_final->copy()->setTime(13, 30, 0);
                }

                $nomorBerikutnya = $antreanTerakhir ? intval($antreanTerakhir->tiketAntrean) + 1 : 1;
                $tiketAntrean = str_pad($nomorBerikutnya, 3, '0', STR_PAD_LEFT);

                $namaLengkap = $dataPerkara->namaPihak;
                $noPerkara = $dataPerkara->noPerkara;
                $jenisPerkara = $dataPerkara->jenisPerkara;
                $status = 'menunggu';
                $statusAmbilAntrean = 'sudah ambil';

                return antreans::create([
                    'id_perkara'    => $idPerkara,
                    'namaLengkap'   => $namaLengkap,
                    'noPerkara'     => $noPerkara,
                    'jenisPerkara'  => $jenisPerkara,
                    'tiketAntrean'  => $tiketAntrean,
                    'jam_perkiraan' => $perkiraan_sidang_final->format('H:i:s'),
                    'tanggal_sidang' => $tanggal_sidang_final->format('Y-m-d'),
                    'statusAmbilAntrean' => $statusAmbilAntrean,
                    'status'        => $status
                ]);
            }, 5);

            return redirect('/antrean')->with('showSucess', true);
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat mengambil nomor antrean: ' . $e->getMessage());
        }
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
