<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class otps extends Model
{
    protected $fillable = [
        'id_user',
        'kodeOtp',
        'expired_at',
        'status'
    ];
}
