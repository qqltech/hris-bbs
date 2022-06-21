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
    protected $guarded = ['id'];
    
    protected $hidden = [
        'password',
    ];
    /**
     * Get User from bearer token in Authorization key Header when outside auth guard
     */
    public function getFromHeaderToken( $bearerToken = null ){
        $access_token = $bearerToken ?? app()->request->header('Authorization');
        if(!$access_token) return null;
        try{
            $auth_header = explode(' ', $access_token);
            $token = $auth_header[1];
            $token_parts = explode('.', $token);
            $token_header = $token_parts[1];
            $token_header_json = base64_decode($token_header);
            $token_header_array = json_decode($token_header_json, true);
            $accessUser = \Laravel\Passport\Token::find($token_header_array['jti']);
            if( !$accessUser || ($accessUser && $accessUser->revoked) ){
                return null;
            }
            return $this->find($accessUser->user_id);
        }catch(\Exception $e){
            return null;
        }
    }
}
