<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class pengajuanJamSidangs extends Model
{
    protected $fillable = [
        'id_user',
        'jam_sidang',
        'status'
    ];
}
