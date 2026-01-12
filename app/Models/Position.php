<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = ['name', 'parent_id'];

    public function parent()
    {
        return $this->belongsTo(Position::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Position::class, 'parent_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'position_user')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function getIsManagerAttribute()
    {
        // 1. Berdasarkan nama jabatan
        if (
            stripos($this->name, 'manager') !== false ||
            stripos($this->name, 'manajer') !== false
        ) {
            return true;
        }

        // 2. Berdasarkan hirarki (punya bawahan)
        return $this->children()->count() > 0;
    }

    public function getLevel()
    {
        $level = 1;
        $pos = $this;

        while ($pos->parent) {
            $level++;
            $pos = $pos->parent;
        }

        return $level;
    }
}
