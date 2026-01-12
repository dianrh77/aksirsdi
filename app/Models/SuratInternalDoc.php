<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratInternalDoc extends Model
{
    protected $fillable = [
        'surat_id',
        'template_id',
        'data_isian',
        'file_docx',
        'file_pdf',
        'lampiran_pdf',
        'version',
        'is_active',
    ];

    protected $casts = [
        'data_isian' => 'array',
    ];

    public function surat()
    {
        return $this->belongsTo(SuratMasuk::class, 'surat_id');
    }

    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id');
    }
}
