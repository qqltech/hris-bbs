<?php
namespace App\Listeners;

use Laravel\Passport\Events\AccessTokenCreated;
use Laravel\Passport\Token;

class RevokeOtherTokens
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\OrderShipped  $event
     * @return void
     */
    public function handle(AccessTokenCreated $event)
    {
        if($event->userId==1){return;}
        if( strtolower(env( "SINGLE_LOGIN", "true" ))=="false" ){
            return;
        }
        Token::where(function($query) use($event){
            $query->where('user_id', $event->userId);
            $query->where('id', '<>', $event->tokenId);
        })->update(['revoked' => true]);
    }
}