<?php

namespace App\Http\Controllers;

use App\Models\antreans;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class antreanController extends Controller
{

    public function home()
    {
        return view('welcome');
    }

    public function antrean($id = null)
    {
        $dataAntrean = null;
        if ($id) {
            $dataAntrean = antreans::find($id);
        }
        return view('antrean', [
            'dataAntrean' => $dataAntrean
        ]);
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

        try {
            $antreanBaru = DB::transaction(function () use ($request) {

                $sekarang = now();
                $perkiraan_sidang = $sekarang->copy()->addMinutes(15);
                $tanggal_sidang = $sekarang->copy()->startOfDay();

                if ($perkiraan_sidang->hour >= 16) {
                    $tanggal_sidang->addDay()->startOfDay();
                    $perkiraan_sidang->setTime(8, 0, 0);
                }

                $antreanTerakhir = antreans::where('tanggal_sidang', $tanggal_sidang)
                    ->orderBy('id', 'desc')
                    ->lockForUpdate()
                    ->first();

                $nomorBerikutnya = $antreanTerakhir ? intval($antreanTerakhir->tiketAntrean) + 1 : 1;
                $tiketAntrean = str_pad($nomorBerikutnya, 3, '0', STR_PAD_LEFT);

                return antreans::create([
                    'namaLengkap'   => $request->input('nama_pihak'),
                    'noPerkara'     => $request->input('nomor_perkara'),
                    'jenisPerkara'  => $request->input('jenis_perkara'),
                    'tiketAntrean'  => $tiketAntrean,
                    'jam_perkiraan' => $perkiraan_sidang->format('H:i:s'),
                    'tanggal_sidang' => $tanggal_sidang->format('Y-m-d'),
                ]);
            }, 5); 

            return redirect('/antrean/' . $antreanBaru->id)->with('showModal', true);
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat mengambil nomor antrean, silakan coba lagi.');
        }
    }
}
