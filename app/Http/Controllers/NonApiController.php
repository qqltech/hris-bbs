<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class NonApiController extends Controller
{
    public function resources(Request $request, $name){
        try{
            return view("projects.web_$name", compact('request'));
        }catch(\Exception $e){
            abort(404);
        }
    }
}