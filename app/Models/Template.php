<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = ['nama_template', 'file_template', 'uploaded_by', 'position_id'];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
