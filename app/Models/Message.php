<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'sender_id',
        'chat_id',
        'type',
        'content',
        'file_path',
        'is_read',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function chat(){
        return $this->belongsTo(Chat::class, 'chat_id');
    }
}
