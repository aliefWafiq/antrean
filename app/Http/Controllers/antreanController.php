<?php

namespace App\Http\Controllers;

use App\Models\antreans;
use App\Models\otps;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Notifications\SendTestSms;
use Illuminate\Support\Facades\Notification;

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

    public function ubahJamSidang()
    {
        $antreanId = Auth::id();
        $dataAntrean = antreans::where('id', $antreanId)->latest()->first();

        return view('ubahjam', ['dataAntrean' => $dataAntrean]);
    }

    public function login(Request $request)
    {
        $data = array(
            'nomorHp' => $request->input('noHp'),
            'password' => $request->input('password')
        );

        if (Auth::attempt($data)) {
            try {
                $phoneNumber = "6281363055921";
                $user = Auth::user();

                $kode_otp = random_int(100000, 999999);
                otps::create([
                    'id_user' => $user->id,
                    'kodeOtp' => $kode_otp,
                    'expired_at' => now()->addMinutes(5),
                    'status' => 'aktif'
                ]);

                Notification::route('vonage', $phoneNumber)->notify(new SendTestSms($kode_otp));

                Auth::logout();
                $request->session()->put('user_id', $user->id);

                return redirect('/antrean');
            } catch (\Exception $e) {
                return "Gagal mengirim SMS: " . $e->getMessage();
            }
        } else {
            // Session::flash('error', 'Nomor hp atau password salah');
            return redirect('/login')->with('error', 'Nomor Hp atau Password salah');
        }
    }

    public function verifyOtp(Request $request) {
        $request->validate(['otp' => 'required|numeric']);

        $userId = $request->session()->get('user_id');

        $otp = otps::where('user_id', $userId)
                    ->where('kodeOtp', $request->otp)
                    ->where('expired_at', '>', now())
                    ->where('status', 'aktif')
                    ->first();
        
        if($otp) {
            $otp->update(['status' => 'sudah dipakai']);

            Auth::loginUsingId($userId);
            $request->session()->forget('user_id');

            return redirect('/antrean');
        }

        return back()->with('error', 'Kode OTP tidak valid atau sudah kadaluarsa');
    }

    public function antrean()
    {
        $antreanId = Auth::id();
        $dataAntrean = antreans::where('id', $antreanId)->latest()->first();

        return view('antrean', ['dataAntrean' => $dataAntrean]);
    }

    public function ambilAntrean(Request $request, antreans $antrean)
    {
        $antrean->update([
            'statusAmbilAntrean' => 'sudah ambil'
        ]);

        return redirect('/antrean')->with('showSucess', true);
    }

    public function editJamSidang(Request $request, antreans $antrean)
    {
        $request->validate([
            'slotJamTersedia' => 'required'
        ]);

        $jamSidang = Carbon::parse($request->slotJamTersedia);


        $antrean->update([
            'jam_perkiraan' => $jamSidang->format('H:i')
        ]);

        return redirect('/antrean')->with('showModal', true);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        return redirect('/login');
    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'nama_pihak' => 'required',
    //         'nomor_perkara' => 'required',
    //         'jenis_perkara' => 'required',
    //     ]);

    //     try {
    //         $antreanBaru = DB::transaction(function () use ($request) {

    //             $sekarang = now();
    //             $perkiraan_sidang = $sekarang->copy()->addMinutes(15);
    //             $tanggal_sidang = $sekarang->copy()->startOfDay();

    //             if ($perkiraan_sidang->hour >= 16) {
    //                 $tanggal_sidang->addDay()->startOfDay();
    //                 $perkiraan_sidang->setTime(8, 0, 0);
    //             }

    //             $antreanTerakhir = antreans::where('tanggal_sidang', $tanggal_sidang)
    //                 ->orderBy('id', 'desc')
    //                 ->lockForUpdate()
    //                 ->first();

    //             $nomorBerikutnya = $antreanTerakhir ? intval($antreanTerakhir->tiketAntrean) + 1 : 1;
    //             $tiketAntrean = str_pad($nomorBerikutnya, 3, '0', STR_PAD_LEFT);

    //             return antreans::create([
    //                 'namaLengkap'   => $request->input('nama_pihak'),
    //                 'noPerkara'     => $request->input('nomor_perkara'),
    //                 'jenisPerkara'  => $request->input('jenis_perkara'),
    //                 'tiketAntrean'  => $tiketAntrean,
    //                 'jam_perkiraan' => $perkiraan_sidang->format('H:i:s'),
    //                 'tanggal_sidang' => $tanggal_sidang->format('Y-m-d'),
    //             ]);
    //         }, 5); 

    //         return redirect('/antrean/' . $antreanBaru->id)->with('showModal', true);
    //     } catch (\Exception $e) {
    //         return back()->with('error', 'Terjadi kesalahan saat mengambil nomor antrean, silakan coba lagi.');
    //     }
    // }

}
