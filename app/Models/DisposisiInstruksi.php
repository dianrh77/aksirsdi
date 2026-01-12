<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisposisiInstruksi extends Model
{
    use HasFactory;

    protected $fillable = [
        'disposisi_id',
        'direktur_id',
        'jenis_direktur',
        'instruksi',
        'batas_waktu',
    ];

    // 🔗 Relasi ke disposisi induk
    public function disposisi()
    {
        return $this->belongsTo(Disposisi::class);
    }

    // 🔗 Relasi ke user direktur yang memberikan instruksi
    public function direktur()
    {
        return $this->belongsTo(User::class, 'direktur_id');
    }
}
