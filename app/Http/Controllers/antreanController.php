<?php

namespace App\Http\Controllers;

// use App\Mail\SendOtpEmail;
use App\Models\antreans;
use App\Models\otps;
use App\Models\perkara;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
// use App\Notifications\SendTestSms;
// use Illuminate\Support\Facades\Mail;
// use Illuminate\Support\Facades\Notification;
// use Twilio\Rest\Client;
// use Illuminate\Support\Facades\Log;
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

        $sekarangCek = now();

        if ($sekarangCek->format('H:i') >= '16:00') {
            $tanggalSidangCek = $sekarangCek->copy()->addDay()->startOfDay();
        } else {
            $tanggalSidangCek = $sekarangCek->copy()->startOfDay();
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

            return redirect('/antrean')->with('info', 'Anda sudah mengambil antrean untuk hari ini.');
        }

        try {
            $antreanBaru = DB::transaction(function () use ($dataPerkara) {
                $sekarang = now();
                $isBesok = false;

                if ($sekarang->format('H:i') >= '16:00') {
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
                    $waktuTerakhir = Carbon::parse($antreanTerakhir->jam_perkiraan);
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

                return antreans::create([
                    'id_perkara'        => $dataPerkara->id,
                    'namaLengkap'       => $dataPerkara->namaPihak,
                    'noPerkara'         => $dataPerkara->noPerkara,
                    'jenisPerkara'      => $dataPerkara->jenisPerkara,
                    'tiketAntrean'      => $tiketAntrean,
                    'jam_perkiraan'     => $perkiraan_sidang_final->format('H:i:s'),
                    'tanggal_sidang'    => $tanggal_sidang_final->format('Y-m-d'),
                    'statusAmbilAntrean' => 'sudah ambil',
                    'status'            => 'menunggu'
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
            return back()->with('error', 'Gagal mengambil antrean. Coba lagi. Pesan:' . $e->getMessage());
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
            ->where('expired_at', '>', now())
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

        return back()->with('error', 'Kode OTP tidak valid atau sudah kadaluarsa, silahkan cek lagi kode yang dikirim');
    }

    public function kirimUlangOtp(Request $request)
    {
        $userId = $request->session()->get('otp_user_id');

        $data = antreans::where('id', $userId)->first();

        $expiredTime = now()->addMinutes(5);
        $kode_otp = random_int(100000, 999999);
        otps::create([
            'id_user' => $userId,
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

        return redirect('/verify-otp')->with('success', 'Kode OTP telah di kirim ke Email anda.');
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




    // public function login(Request $request)
    //     {
    // $request->validate([
    //     'noHp' => 'required',
    //     'password' => 'required'
    // ]);

    //         $request->validate([
    //             'email' => 'required',
    //         ]);


    //         $data = antreans::where('email', $request->email)->first();

    //         if (!$data) {
    //             return back()->with('error', 'Email yang Anda masukkan tidak terdaftar.');
    //         }

    // $user = antreans::where('nomorHp', $request->noHp)->first();
    //         $expiredTime = now()->addMinutes(5);

    //         try {
    //             $kode_otp = random_int(100000, 999999);
    //             otps::create([
    //                 'id_user' => $data->id,
    //                 'kodeOtp' => $kode_otp,
    //                 'expired_at' => $expiredTime,
    //                 'status' => 'aktif'
    //             ]);

    //             $email = new \SendGrid\Mail\Mail();

    //             $email->setFrom(config('mail.from.address'), config('mail.from.name'));
    //             $email->setSubject("Kode Verifikasi Anda");
    //             $email->addTo($data->email, $data->namaLengkap);
    //             $email->addContent("text/plain", "Kode verifikasi Anda adalah: " . $kode_otp);
    //             $email->addContent(
    //                 "text/html",
    //                 "<h1>Kode Verifikasi Anda</h1><p>Gunakan kode di bawah ini untuk login:</p><h2><strong>" . $kode_otp . "</strong></h2>"
    //             );

    //             $sendgrid = new \SendGrid(env('SENDGRID_API_KEY'));
    //             $response = $sendgrid->send($email);

    //             $request->session()->put('otp_user_id', $data->id);
    //             $request->session()->put('otp_phone_number', $data->nomorHp);
    //             $request->session()->put('email', $data->email);

    //             return redirect('/verify-otp')->with('success', 'Kode OTP telah di kirim ke Email anda.');
    //         } catch (\Exception $e) {
    //             dd($e->getMessage());
    //             return back()->with('error', 'Gagal mengirim OTP: ' . $e->getMessage());
    //         }
    //     }






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

    //     public function ambilAntrean(antreans $antrean)
    // {
    //     $antrean->update([
    //         'statusAmbilAntrean' => 'sudah ambil'
    //     ]);

    //     $receiverNumber = '+18777804236';
    //     $receiverNumber = $user->nomorHp;
    //     $message = 'Sukses mengambil antrean, silahkan menunggu di ruang tunggu';

    //     $sid = env('TWILIO_SID');
    //     $token = env('TWILIO_TOKEN');
    //     $fromNumber = env('TWILIO_FROM');

    //     $client = new Client($sid, $token);
    //     $client->messages->create($receiverNumber, [
    //         'from' => $fromNumber,
    //         'body' => $message
    //     ]);

    //     $email = new \SendGrid\Mail\Mail();

    //     $email->setFrom(config('mail.from.address'), config('mail.from.name'));
    //     $email->setSubject("Sukses mengambil antrean");
    //     $email->addTo($antrean->email, $antrean->namaLengkap);
    //     $email->addContent("text/plain", "Sukses mengambil antrean, silahkan menunggu di ruang tunggu");
    //     $email->addContent(
    //         "text/html",
    //         "<h1>Sukses mengambil antrean</h1><h2><strong>silahkan menunggu di ruang tunggu</strong></h2>"
    //     );

    //     $sendgrid = new \SendGrid(env('SENDGRID_API_KEY'));
    //     $response = $sendgrid->send($email);



    //     return redirect('/antrean')->with('showSucess', true);
    // }
}
