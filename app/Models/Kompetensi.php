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

    public function pengembangan(){
        return $this->belongsToMany(Pengembangan::class, 'pengembangan_kompetensi','id_kompetensi', 'id_pengembangan');
    }
}
