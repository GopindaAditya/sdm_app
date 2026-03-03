<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyaratKompetensi extends Model
{
    protected $table = 'syarat_kompetensi';

    protected $fillable = [
        'id_jabatan', 
        'id_kompetensi', 
        'id_periode'
    ];

    public function kompetensi()
    {
        return $this->belongsTo(Kompetensi::class, 'id_kompetensi');
    }
}
