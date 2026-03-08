<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kompetensi extends Model
{
    protected $table = 'kompetensi';

    protected $fillable = [
        'nama_kompetensi', 
        'kategori'
    ];

    public function jabatan() {
        return $this->belongsToMany(Jabatan::class, 'jabatan_kompetensi', 'id_kompetensi', 'id_jabatan');
    }

    public function pengembangan() {
        return $this->belongsToMany(Pengembangan::class, 'pengembangan_kompetensi', 'id_kompetensi', 'id_pengembangan');
    }
}
