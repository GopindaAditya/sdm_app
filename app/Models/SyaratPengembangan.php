<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyaratPengembangan extends Model
{
    protected $table = 'syarat_pengembangan';

    protected $fillable = [
        'id_jabatan', 
        'id_pengembangan', 
        'id_periode'
    ];
}
