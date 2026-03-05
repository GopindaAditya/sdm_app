<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PelatihanPegawai extends Model
{
    protected $table = 'pelatihan_pegawai';

    protected $fillable = [
        'nip', 
        'id_pelatihan', 
        'id_periode', 
        'file_sertifikat', 
        'status'
    ];
}
