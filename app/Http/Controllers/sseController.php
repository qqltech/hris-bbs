<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;

class sseController extends Controller
{
    private function getLastUpdate(){
        $data = DB::connection('sqlite')->table('notifications')->latest('id')->first();
        return json_encode($data);
    }
    public function getUpdate(){
        $response = new StreamedResponse();
        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->setCallback(
            function() {
                    // echo "Id: 1\n\n";
                    // echo "retry: 100\n\n"; // no retry would default to 3 seconds.
                    echo "data:".$this->getLastUpdate()." \n\n";
                    ob_flush();
                    flush();
                    sleep(5);
            });
        $response->send();
    }
}
