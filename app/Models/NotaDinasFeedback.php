<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaDinasFeedback extends Model 
{
    protected $table = 'nota_dinas_feedback';
    protected $fillable = ['nota_dinas_id', 'user_id', 'pesan', 'lampiran'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
