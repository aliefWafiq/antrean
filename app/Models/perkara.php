<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class perkara extends Model
{
    use HasFactory;

    protected $table = 'perkara';

    protected $fillable = [
        'tanggal_sidang',
        'noPerkara',
        'sidang_Keliling',
        'ruangan_sidang',
        'agenda',
    ];

    protected $casts = [
        'tanggal_sidang' => 'datetime'
    ];
}
