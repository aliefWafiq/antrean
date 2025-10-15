<?php

namespace App\Http\Controllers;

use App\Mail\SendOtpEmail;
use App\Models\antreans;
use App\Models\otps;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use App\Notifications\SendTestSms;
use Illuminate\Support\Facades\Mail;
// use Illuminate\Support\Facades\Notification;
use Twilio\Rest\Client;


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
        // $request->validate([
        //     'noHp' => 'required',
        //     'password' => 'required'
        // ]);

        $request->validate([
            'email' => 'required',
        ]);

        
        $data = antreans::where('email', $request->email)->first();
        // dd($data);

        if (!$data) {
            return back()->with('error', 'Email yang Anda masukkan tidak terdaftar.');
        }

        // $user = antreans::where('nomorHp', $request->noHp)->first();
        $expiredTime = now()->addMinutes(5);

        try {
            $kode_otp = random_int(100000, 999999);
            otps::create([
                'id_user' => $data->id,
                'kodeOtp' => $kode_otp,
                'expired_at' => $expiredTime,
                'status' => 'aktif'
            ]);

            $email = new \SendGrid\Mail\Mail();

            $email->setFrom(config('mail.from.address'), config('mail.from.name'));
            $email->setSubject("Kode Verifikasi Anda");
            $email->addTo($data->email, $data->namaLengkap);
            $email->addContent("text/plain", "Kode verifikasi Anda adalah: " . $kode_otp);
            $email->addContent(
                "text/html",
                "<h1>Kode Verifikasi Anda</h1><p>Gunakan kode di bawah ini untuk login:</p><h2><strong>" . $kode_otp . "</strong></h2>"
            );

            $sendgrid = new \SendGrid(env('SENDGRID_API_KEY'));
            $response = $sendgrid->send($email);

            $request->session()->put('otp_user_id', $data->id);
            $request->session()->put('otp_phone_number', $data->nomorHp);
            $request->session()->put('email', $data->email);

            return redirect('/verify-otp')->with('success', 'Kode OTP telah di kirim ke SMS anda.');
        } catch (\Exception $e) {
            dd($e->getMessage()); 
            return back()->with('error', 'Gagal mengirim OTP: ' . $e->getMessage());
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|numeric']);

        $userId = $request->session()->get('otp_user_id');

        if (!$userId) {
            return redirect('/login')->with('error', 'Silahkan login lagi');
        }

        $otp = otps::where('id_user', $userId)
            ->where('kodeOtp', $request->otp)
            // ->where('expired_at', '>', now())
            ->where('status', 'aktif')
            ->first();

        if ($otp) {
            $otp->update(['status' => 'sudah ditukar']);

            Auth::loginUsingId($userId);
            $request->session()->forget('otp_user_id');
            $request->session()->forget('otp_phone_number');
            $request->session()->forget('email');

            $request->session()->regenerate();

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

    public function ambilAntrean(antreans $antrean)
    {
        $antrean->update([
            'statusAmbilAntrean' => 'sudah ambil'
        ]);

        $receiverNumber = '+18777804236';
        // $receiverNumber = $user->nomorHp;
        $message = 'Sukses mengambil antrean, silahkan menunggu di ruang tunggu';

        $sid = env('TWILIO_SID');
        $token = env('TWILIO_TOKEN');
        $fromNumber = env('TWILIO_FROM');

        $client = new Client($sid, $token);
        $client->messages->create($receiverNumber, [
            'from' => $fromNumber,
            'body' => $message
        ]);

        return redirect('/antrean')->with('showSucess', true);
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


    //     public function login(Request $request)
    // {
    // $request->validate([
    //     'noHp' => 'required',
    //     'password' => 'required'
    // ]);

    // $antrean = antreans::where('nomorHp', $request->noHp)->first();

    // if($antrean && $request->password === $antrean->password){
    //     Auth::login($antrean);
    //     $request->session()->regenerate();
    //     return redirect('/antrean');
    // }

    // return back()->with('error', 'Nomor Hp atau password salah');

    // KODE KALAU PAKE SMS
    // $request->validate(['noHp' => 'required']);

    // $user = antreans::where('nomorHp', $request->noHp)->first();
    // $expiredTime = now()->addMinutes(5);

    // try {
    // $kode_otp = random_int(100000, 999999);
    // otps::create([
    //     'id_user' => $user->id,
    //     'kodeOtp' => $kode_otp,
    //     'expired_at' => $expiredTime,
    //     'status' => 'aktif'
    // ]);

    // $receiverNumber = '+18777804236';
    // $receiverNumber = $user->nomorHp;
    // $message = 'Kode OTP Anda ' . $kode_otp;

    // $sid = env('TWILIO_SID');
    // $token = env('TWILIO_TOKEN');
    // $fromNumber = env('TWILIO_FROM');

    // $client = new Client($sid, $token);
    // $client->messages->create($receiverNumber, [
    //     'from' => $fromNumber,
    //     'body' => $message
    // ]);

    // $request->session()->put('otp_user_id', $user->id);
    // $request->session()->put('otp_phone_number', $user->nomorHp);

    //     return redirect('/antrean')->with('success', 'Kode OTP telah di kirim ke SMS anda.');
    // } catch (\Exception $e) {
    //     return back()->with('error', 'Gagal mengirim SMS: ' . $e->getMessage());
    // }
    // }
}
