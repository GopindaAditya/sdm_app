<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengambanganPegawai extends Model
{
    protected $table = 'pengembangan_pegawai';

    protected $fillable = [
        'nip', 
        'id_pengembangan', 
        'id_periode', 
        'file_sertifikat', 
        'status'
    ];
}
