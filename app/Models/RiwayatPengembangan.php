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
}
