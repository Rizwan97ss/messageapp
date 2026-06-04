<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'type',
        'name',
        'avatar',
        'created_by',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['role', 'joined_at', 'left_at', 'last_read_at'])
            ->withTimestamps();
    }

    public function activeUsers()
    {
        return $this->users()->wherePivotNull('left_at');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}