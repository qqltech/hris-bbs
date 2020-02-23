<?php 
use Telegram\Bot\Api as Tg;

    function logTg($to,$message){        
        if (strpos(url(), 'localhost') !== false) {
            $params = array_merge(config("telegram.$to"), ["text"=>$message]);
            $tg = new Tg(config("telegram.bot_token"));
            $tg->setAsyncRequest(true)->sendMessage($params);
        }
    }