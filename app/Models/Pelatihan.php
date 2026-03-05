<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelatihan extends Model
{
    protected $table = 'pelatihan';

    protected $fillable = [
        'nama_pelatihan'
    ];
}
