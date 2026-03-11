<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KompetensiPegawai extends Model
{
    protected $table = 'kompetensi_pegawai';

    protected $fillable = [
        'nip', 
        'id_kompetensi',         
    ];
}
