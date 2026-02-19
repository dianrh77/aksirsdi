<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disposisi extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_disposisi',
        'surat_id',
        'pengirim_id',
        'validator_manager_id',
        'catatan',
        'status',
        'jenis_disposisi',
        'tanggal_disposisi',
        'finished_at',
    ];

    protected $casts = [
    'tanggal_disposisi' => 'datetime',
    'finished_at' => 'datetime',
];

    // 🔗 Relasi ke penerima
    public function penerima()
    {
        return $this->hasMany(DisposisiPenerima::class, 'disposisi_id');
    }

    // 🔗 Relasi ke log
    public function logs()
    {
        return $this->hasMany(DisposisiLog::class, 'disposisi_id');
    }

    // 🔗 Relasi ke user pengirim (opsional, tanpa foreign)
    public function pengirim()
    {
        return $this->belongsTo(User::class, 'pengirim_id');
    }

    public function validatorManager()
    {
        return $this->belongsTo(User::class, 'validator_manager_id');
    }

    public function suratMasuk()
    {
        return $this->belongsTo(SuratMasuk::class, 'surat_id');
    }

    public function instruksis()
    {
        return $this->hasMany(DisposisiInstruksi::class,);
    }

    public function penerimas()
    {
        return $this->belongsToMany(
            User::class,
            'disposisi_penerimas',
            'disposisi_id', // FK ke disposisi
            'penerima_id'   // FK ke user
        )->withPivot('status', 'waktu_baca', 'waktu_tindak')
            ->withTimestamps();
    }

    public function reject()
    {
        return $this->hasOne(DisposisiReject::class);
    }
}
