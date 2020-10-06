<?php

namespace App\Models\Defaults;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Contracts\Auth\MustVerifyEmail;

use Laravel\Passport\HasApiTokens;
class User extends Model implements AuthenticatableContract, AuthorizableContract,MustVerifyEmail
{
    use HasApiTokens, Authenticatable, Authorizable;
    protected $table = 'default_users';
    protected $guarded = ['id'];
    
    protected $hidden = [
        'password',
    ];
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
