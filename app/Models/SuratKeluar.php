<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SuratKeluar extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_surat',
        'tgl_surat',
        'tujuan_surat',
        'perihal',
        'file_pdf',
        'created_by',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
