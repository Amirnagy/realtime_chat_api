<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatParticipant extends Model
{
    use HasFactory;

    protected $table = 'chat_participants';
    protected $guarded = ['id'];


    public function user()
    {
        return $this->belongsTo(User::class ,'user_id');
    }
}
