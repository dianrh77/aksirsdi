<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'password',
        'role_name',
        'status',
        'avatar',
        'join_date',
        'last_login',
        'phone_number',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Auto generate user_id AKSI-0001
     */
    protected static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $latestUser = self::orderBy('user_id', 'desc')->first();
            $nextID = $latestUser ? intval(substr($latestUser->user_id, 5)) + 1 : 1;
            $model->user_id = 'AKSI-' . sprintf("%04d", $nextID);

            while (self::where('user_id', $model->user_id)->exists()) {
                $nextID++;
                $model->user_id = 'AKSI-' . sprintf("%04d", $nextID);
            }
        });
    }

    /**
     * RELATIONSHIPS
     */

    // multi posisi
    public function positions()
    {
        return $this->belongsToMany(Position::class, 'position_user')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    // posisi utama (primary)
    public function primaryPosition()
    {
        return $this->positions()->wherePivot('is_primary', true)->first();
    }

    // helper untuk tampilan tabel
    public function getPositionsLabelAttribute()
    {
        return $this->positions->pluck('name')->join(', ');
    }

    /**
     * ROLE helper
     */
    public function hasRole($roles)
    {
        if (is_array($roles)) {
            return in_array($this->role_name, $roles);
        }
        return $this->role_name === $roles;
    }

    public function getLevel()
    {
        $pos = $this->primaryPosition();

        // Jika user belum punya posisi utama → kembalikan seperti staf
        if (!$pos) {
            return 999; // level non-struktural
        }

        $level = 1;

        while ($pos->parent) {
            $level++;
            $pos = $pos->parent;
        }

        return $level;
    }
}
