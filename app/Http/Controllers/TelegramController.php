<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Api as Tg;

class TelegramController extends Controller
{

    public function index(Request $request, $command){
        try{
            $time_start = microtime(true); 
            $tg = new Tg(config("telegram.bot_token"));
            // $tg->setAsyncRequest(true);
            switch($command){
                case "getMe": 
                    $response = $tg->getMe();
                    break;
                case "getUpdates": 
                    $response = $tg->getUpdates();
                    break;
                case "sendMe": 
                    $params = [
                        'chat_id'   => '402325749',
                        'text'      => 'test',
                    ];
                    $response=$tg->sendMessage($params);
                    break;
                case "sendGroup": 
                    $params = [
                        'chat_id'   => '-205378007',
                        'text'      => 'test from server laradev',
                    ];
                    $response=$tg->sendMessage($params);
                    break;
                default: 
                    return response()->json(["data"=>"Need Parameter",422]);
            }
            $time_end = microtime(true);
        }catch(Exception $e){
            return response()->json(["data"=> "ERROR cannot request", 500]);
        }
        return response()->json(["time"=>($time_end - $time_start)/60, "data"=>$response]);
    }
    public function webhook(Request $request){
        return true;
    }
}