<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chat extends Model
{
    use HasFactory;

    protected $table = 'chats';
    protected $guarded = ['id'];



    public function participants()
    {
        return $this->hasMany(ChatParticipant::class,'chat_id');
    }

    public function message()
    {
        return $this->hasMany(ChatMessage::class,'chat_id');
    }

    public function lastMessage()
    {
        return $this->hasOne(ChatMessage::class,'chat_id')->latest('updated_at');
    }

    public function scopeHasParticipant($query, $user_id)
    {
        return $query->whereHas('participants', function ($query) use ($user_id) {
            $query->where('user_id', $user_id);
        });
    }

}
