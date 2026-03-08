<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatPengembangan extends Model
{
    protected $table = 'riwayat_pengembangan';

    protected $fillable = [
        'nip',
        'id_pengembangan',
        'id_periode',
        'tanggal_kegiatan',
        'sertifikat',
        'status'
    ];

    public function pengembangan()
    {
        return $this->belongsTo(Pengembangan::class, 'id_pengembangan', 'id');
    }
    
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'nip', 'nip');
    }
}
