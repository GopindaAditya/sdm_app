<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengembangan extends Model
{
    protected $table = 'pengembangan';

    protected $fillable = [
        'nama_pengembangan'
    ];

    public function kompetensi() {
        return $this->belongsToMany(Kompetensi::class, 'pengembangan_kompetensi', 'id_pengembangan', 'id_kompetensi')
                    ->withPivot('mulai_berlaku', 'akhir_berlaku');
    }
    
    public function riwayatPengembangan() {
        return $this->hasMany(RiwayatPengembangan::class, 'id_pengembangan', 'id');
    }
}
