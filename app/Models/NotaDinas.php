<?php

namespace App\Models;

use App\Models\NotaDinasPenerima;
use Illuminate\Database\Eloquent\Model;

class NotaDinas extends Model
{
    protected $table = 'nota_dinas';

    protected $fillable = [
        'pengirim_id',
        'penerima_id',
        'nomor_nota',
        'judul',
        'isi',
        'lampiran',
        'lampiran_lain_nama',
        'lampiran_lain',
        'status',
    ];

    public function balasan()
    {
        return $this->hasMany(NotaDinasBalasan::class, 'nota_dinas_id');
    }

    public function pengirim()
    {
        return $this->belongsTo(User::class, 'pengirim_id');
    }

    public function penerima()
    {
        return $this->belongsToMany(User::class, 'nota_dinas_penerima', 'nota_dinas_id', 'user_id')
            ->withPivot(['tipe', 'status', 'waktu_dibaca', 'waktu_selesai'])
            ->withTimestamps();
    }


    public function penerimas()
    {
        return $this->hasMany(NotaDinasPenerima::class, 'nota_dinas_id');
    }

    public function semuaPenerima()
    {
        return $this->penerimas()->with('user');
    }
}
