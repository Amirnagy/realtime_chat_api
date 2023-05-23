<?php

namespace App\Models;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatMessage extends Model
{
    use HasFactory;

    protected $table = 'chat_messages';
    protected $guarded = ['id'];

    protected $touches =['chat'];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class,'chat_id');
    }
}
