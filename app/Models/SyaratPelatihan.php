<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyaratPelatihan extends Model
{
    protected $table = 'syarat_pelatihan';

    protected $fillable = [
        'id_jabatan', 
        'id_pelatihan', 
        'id_periode'
    ];
}
