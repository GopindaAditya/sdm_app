<?php

namespace App\Models;

use App\Models\RiwayatPengembangan;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Pegawai extends Authenticatable
{
    protected $table = 'pegawai';

    protected $primaryKey = 'nip';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nip',
        'password',
        'name',
        'id_jabatan'
    ]; 

    protected $hidden = [
        'password'
    ];

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'id_jabatan');
    }

    public function riwayatPengembangan(){
        return $this->hasMany(RiwayatPengembangan::class, 'nip');
    }
    
}
