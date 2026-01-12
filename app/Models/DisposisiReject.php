<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisposisiReject extends Model
{
    protected $fillable = ['disposisi_id', 'direktur_id', 'alasan'];

    public function disposisi()
    {
        return $this->belongsTo(Disposisi::class);
    }

    public function direktur()
    {
        return $this->belongsTo(User::class, 'direktur_id');
    }
}
