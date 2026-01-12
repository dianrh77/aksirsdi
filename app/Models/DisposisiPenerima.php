<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisposisiPenerima extends Model
{
    use HasFactory;

    protected $fillable = [
        'disposisi_id',
        'penerima_id',
        'status',
        'waktu_baca',
        'waktu_tindak',
        'waktu_selesai',
    ];

    protected $dates = [
        'waktu_baca',
        'waktu_tindak',
        'waktu_selesai',
    ];

    // 🔗 Relasi ke disposisi utama
    public function disposisi()
    {
        return $this->belongsTo(Disposisi::class, 'disposisi_id');
    }

    // 🔗 Relasi ke user penerima
    public function penerima()
    {
        return $this->belongsTo(User::class, 'penerima_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function feedbacks()
    {
        return $this->hasMany(DisposisiFeedback::class, 'disposisi_penerima_id');
    }
}
