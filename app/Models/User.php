<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'username',
        'password',
        'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // friend relationships

    // requests the user SENT
    public function sentRequests(): HasMany
    {
        return $this->hasMany(FriendRequest::class, 'sender_id');
    }

    // requests the user RECEIVED
    public function receivedRequests(): HasMany
    {
        return $this->hasMany(FriendRequest::class, 'receiver_id');
    }
}
