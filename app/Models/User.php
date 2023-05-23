<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Laravel\Sanctum\HasApiTokens;
use App\Notifications\MessageSent;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $guarded =['id'];

    const USER_TOKEN = 'UserToken';
    protected $hidden = [
        'password',
    ];


    public function routeNotificationForOneSignal()
    {
        return ['tags' => ['key' => 'user_id'    , 'relation'=>  '=' , 'value' => (string)($this->id)]];
    //  return ['tags' => ['key' => 'device_uuid', 'relation' => '=' , 'value' => '1234567890-abcdefgh-1234567']];

    }

    public function sendNewMessageNotification($data)
    {
        $this->notify(new MessageSent($data));
    }
}
