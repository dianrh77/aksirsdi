<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class SuratMasuk extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_surat',
        'tgl_surat',
        'asal_surat',
        'jenis_surat',
        'perihal',
        'file_pdf',
        'created_by',
        'status',
        'position_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function disposisi()
    {
        return $this->hasOne(\App\Models\Disposisi::class, 'surat_id');
    }

    public function internalDocs()
    {
        return $this->hasMany(SuratInternalDoc::class, 'surat_id');
    }

    public function internalDoc()
    {
        return $this->hasOne(SuratInternalDoc::class, 'surat_id')
            ->where('is_active', true);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
