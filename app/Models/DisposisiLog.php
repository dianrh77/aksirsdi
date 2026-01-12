<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisposisiLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'disposisi_id',
        'user_id',
        'aksi',
        'keterangan',
    ];

    // 🔗 Relasi ke disposisi utama
    public function disposisi()
    {
        return $this->belongsTo(Disposisi::class, 'disposisi_id');
    }

    // 🔗 Relasi ke user yang melakukan aksi
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
