<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class NonApiController extends Controller
{
    public function resources(Request $request, $name){
        return view("projects.web_$name", compact('request'));
    }
}