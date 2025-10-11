<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\VonageMessage;
use Illuminate\Notifications\Notification;

class SendTestSms extends Notification
{
    use Queueable;

    public $kode_otp;

    public function __construct($kode_otp)
    {
        $this->kode_otp = $kode_otp;
    }

    public function via(mixed $notifiable): array
    {
        return ['vonage'];
    }

    public function toVonage(mixed $notifiable): VonageMessage
    {
        return (new VonageMessage)
                    ->content('Ini adalah kode otp kamu'. $this->kode_otp);
    }
}