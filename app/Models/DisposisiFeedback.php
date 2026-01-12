<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisposisiFeedback extends Model
{
    use HasFactory;

    protected $fillable = ['disposisi_penerima_id', 'user_id', 'feedback'];

    public function penerima()
    {
        return $this->belongsTo(DisposisiPenerima::class, 'disposisi_penerima_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lampiran()
    {
        return $this->hasMany(FeedbackAttachment::class, 'feedback_id');
    }
}
