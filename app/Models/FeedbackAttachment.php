<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedbackAttachment extends Model
{
    protected $fillable = ['feedback_id', 'file_path', 'file_name'];

    public function feedback()
    {
        return $this->belongsTo(DisposisiFeedback::class, 'feedback_id');
    }
}
