<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Defaults\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Validator;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Hash;
use Stevebauman\Location\Location;
use Exception;
class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:default_users',
            'password' => 'required|string|confirmed'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(),422);
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' =>Hash::make($request->password)
        ]);
        // logTg("developer",$user->name." has registered");
        return response()->json([
            'message' => 'Successfully created user!'
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
            // 'remember_me' => 'boolean'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(),401);
        }
        if( !$request->email && !$request->username){
            return response()->json("username or email is required",401);
        }
        if($request->email){
            $user = User::where('email', $request->email)->orWhere('username',$request->email)->first();
        }elseif($request->username){
            $user = User::where('email', $request->username)->orWhere('username',$request->username)->first();
        }
        if ($user) {
            if( isset($user->status) && strtolower($user->status)!='active'){
                return response()->json("username is inactive", 401);
            }
            if (Hash::check($request->password, $user->password)) {
                $tokenResult = $user->createToken($request->email);
                // $token = $tokenResult->token;
                // logTg("developer",$user->name." has logged in");
                
                $agent = new Agent();
                $user->platform = $agent->platform();
                $user->platformversion = $agent->version($agent->platform());
                $user->browser=$agent->browser();
                $user->browserversion=$agent->version($agent->browser());
                $user->location=(new Location)->get($request->ip());
                return response()->json([
                    'access_token' => $tokenResult->token,
                    'token' =>$tokenResult->accessToken,
                    'auth' =>$user->auth,
                    'token_type' => 'Bearer',
                    'data'=>$user
                ]);
            } else {
                $response = "Password missmatch";
                return response($response, 422);
            }
    
        } else {
            $response = 'User does not exist';
            return response($response, 422);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function user(Request $request)
    {
        try{
            $agent = new Agent();
            $request->user()->platform = $agent->platform();
            $request->user()->platformversion = $agent->version($agent->platform());
            $request->user()->browser=$agent->browser();
            $request->user()->browserversion=$agent->version($agent->browser());
            $request->user()->location=(new Location)->get($request->ip());
            return response()->json($request->user());
        }catch(Exception $e){
            $response = 'You Need Logged in';
            return response($response, 401);
        }
    }
    
    public function changePassword(Request $request)
    {
        try{
            $user = User::find($request->user()->id)->update([
                'password' =>Hash::make($request->password)
            ]);
        }catch(Exception $e){
            return $e->getMessage();
        }
        return response()->json([
            'message' => 'Successfully updated password!'
        ], 200);
    }
}
