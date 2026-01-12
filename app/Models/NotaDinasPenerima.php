<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class NotaDinasPenerima extends Model
{
    protected $table = 'nota_dinas_penerima';

    protected $fillable = [
        'nota_dinas_id',
        'user_id',
        'tipe',        // langsung / validasi / delegasi
        'status',      // baru / dibaca / diproses / dibalas / selesai
        'waktu_dibaca',
        'waktu_selesai',
    ];

    public function nota()
    {
        return $this->belongsTo(NotaDinas::class, 'nota_dinas_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
