<?php

namespace App\Models\Defaults;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

use Laravel\Passport\HasApiTokens;
class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use HasApiTokens, Authenticatable, Authorizable;
    protected $table = 'default_users';
    protected $fillable = [
        'name', 'email', 'password', 'username'
    ];
    
    protected $hidden = [
        'password',
    ];
}
