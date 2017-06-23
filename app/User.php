<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'login', 'email', 'password', 'name', 'birth_date', 'user_gender', 'address', 'img_url',
        'is_banned', 'ban_time', 'user_role', 'user_online', 'user_busy',
        'user_gold', 'user_silver', 'user_energy',
        'user_current_deck', 'last_user_deck', 'user_base_fraction', 'user_available_deck', 'user_cards_in_deck', 'user_magic', 'user_rating',
        'premium_activated', 'premium_expire_date', 'is_activated', 'activation_code'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
}
