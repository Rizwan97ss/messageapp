<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'body',
        'ciphertext',
        'nonce',
        'encryption_meta',
        'type',
        'reply_to_id',
        'edited_at',
        'deleted_at',
    ];

    protected $casts = [
        'encryption_meta' => 'array',
        'edited_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function attachments()
    {
        return $this->hasMany(MessageAttachment::class);
    }

    public function reads()
    {
        return $this->hasMany(MessageRead::class);
    }

    public function deletions()
    {
        return $this->hasMany(MessageDeletion::class);
    }

    public function deletedForUser(int $userId): bool
    {
        return $this->deletions()->where('user_id', $userId)->exists();
    }
}