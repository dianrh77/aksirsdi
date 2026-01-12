<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaDinasBalasan extends Model
{
    protected $table = 'nota_dinas_balasan';

    protected $fillable = [
        'nota_dinas_id',
        'user_id',
        'balasan',
        'lampiran',
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
