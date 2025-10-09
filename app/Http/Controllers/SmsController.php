<?php

namespace App\Http\Controllers;

use Twilio\Rest\Client;
use Illuminate\Http\Request;
use App\Notifications\SendTestSms;
use Illuminate\Support\Facades\Notification;

class SmsController extends Controller
{
    public function sendSms()
    {
        // $sid = getenv("TWILIO_SID");
        // $token = getenv("TWILIO_TOKEN");
        // $twilio = new Client($sid, $token);

        // $verification_check = $twilio->verify->v2->services("VA21f604d12d036ef939b2d6fbe76a2357")
        //                             ->verificationChecks
        //                             ->create([
        //                                         "to" => "+6288279137205",
        //                                         "code" => "[Code]"
        //                                     ]
        //                             );

        $phoneNumber = '6288279137205'; // Ganti dengan nomor tujuanmu

        try {
            Notification::route('vonage', $phoneNumber)->notify(new SendTestSms());

            return "SMS berhasil dikirim ke " . $phoneNumber;
        } catch (\Exception $e) {
            // Tangani jika ada error
            return "Gagal mengirim SMS: " . $e->getMessage();
        }
    }
}

// +6288279137205
