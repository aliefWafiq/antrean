<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\VonageMessage;
use Illuminate\Notifications\Notification;

class SendTestSms extends Notification
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function via(mixed $notifiable): array
    {
        return ['vonage'];
    }

    public function toVonage(mixed $notifiable): VonageMessage
    {
        return (new VonageMessage)
                    ->content('Ini adalah SMS tes dari aplikasi Laravel kamu. Berhasil! ✅');
    }
}