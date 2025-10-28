<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_online',
        'last_status_update',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'email_verified_at'    => 'datetime',
        'password'             => 'hashed',
        'is_online'            => 'boolean',
        'last_status_update'   => 'datetime',
    ];

    /**
     * User belongs to many chats.
     */
    public function chats()
    {
        return $this->belongsToMany(Chat::class);
    }

    /**
     * User has many messages.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
