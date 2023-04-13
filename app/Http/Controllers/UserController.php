<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Defaults\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Exception;
class UserController extends Controller
{
    public function register(Request $request,$local=false)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:default_users',
            'password' => 'required|string|confirmed'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(),422);
        }
        
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' =>Hash::make($request->password),
            'remember_token'=>Str::random(60)
        ]);
        
        return $local?true:response()->json([
            'message' => 'Successfully created user!'
        ], 201);
    }

    public function login(Request $request,$email_verified=false)
    {
        $email_verified = env('EMAIL_VERIFIED', false);
        $validator = Validator::make($request->all(), [
            'password' => 'required|string'
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
            if($email_verified){
                if($user->email_verified_at==null){
                    return response()->json("Please Open your Email or Whatsapp to Verify!",401);
                }
            }
            if( isset($user->status) && strtolower($user->status)!='active'){
                return response()->json("username is inactive", 401);
            }
            if (Hash::check($request->password, $user->password)) {
                $platform = isMobile() ? 'mobile' : 'desktop' ;
                $tokenResult = $user->createToken( $user->name." ($platform)" );
                
                $agent = new Agent();
                $user->platform = $agent->platform();
                $user->platformversion = $agent->version($agent->platform());
                $user->browser=$agent->browser();
                $user->browserversion=$agent->version($agent->browser());
                $userData = [
                    'access_token' => $tokenResult->token,
                    'token' => $tokenResult->accessToken,
                    'auth' => $user->auth,
                    'token_type' => 'Bearer',
                    'data' => $user
                ];
                if( env("RESPONSE_FINALIZER") ){
                    $funcArr = explode(".", env("RESPONSE_FINALIZER"));
                    $class = getCore($funcArr[0]) ?? getCustom($funcArr[0]);
                    $func = $funcArr[1];
                    $userData = $class->$func( (array)$userData, 'login' );
                }
                return response()->json($userData);
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
            $userData = $request->user();
            $userData->platform = $agent->platform();
            $userData->platformversion = $agent->version($agent->platform());
            $userData->browser=$agent->browser();
            $userData->browserversion=$agent->version($agent->browser());
            if( env("RESPONSE_FINALIZER") ){
                $funcArr = explode(".", env("RESPONSE_FINALIZER"));
                $class = getCore($funcArr[0]) ?? getCustom($funcArr[0]);
                $func = $funcArr[1];
                $userData = $class->$func( $userData->toArray(), 'user' );
            }
            return response()->json($userData);
        }catch(Exception $e){
            $response = 'You Need Logged in';
            return response($response, 401);
        }
    }
    
    public function changePassword(Request $request)
    {
        if($request->old_password){
            return $this->changePasswordAuth($request);
        }
        try{
            User::find($request->user()->id)->update([
                'password' =>Hash::make($request->password)
            ]);
        }catch(Exception $e){
            return $e->getMessage();
        }
        return response()->json([
            'message' => 'Successfully updated password!'
        ], 200);
    }
    
    public function changePasswordAuth(Request $request)
    {
        $user = User::find(Auth::user()->id);
        try{
            if (Hash::check($request->old_password, $user->password)) {
                $user->update([
                    'password' =>Hash::make($request->new_password)
                ]);                
            }else{
                return response()->json('Mismatch Old Password!', 401);
            }
        }catch(Exception $e){
            return $e->getMessage();
        }
        return response()->json([
            'message' => 'Successfully updated password!'
        ], 200);
    }

    public function verify($token)
    {
        $user = User::where('remember_token', $token)->first();
        if($user){
            $user->update([
                "email_verified_at"=>Carbon::now()
            ]);
            $template= "Your account($user->email) has been verified successfully!";
            return view("defaults.email",compact('template'));
        }else{
            $template= "Sorry your token is invalid!";
            return view("defaults.email",compact('template'));
        }
    }
    
    public function unlockScreen(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'password' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(),422);
        }
        
        $user = User::find(Auth::user()->id);
        $password = $request->password;

        if (Hash::check( base64_decode($request->password) , $user->password)) {
            return [ 'message'=>'unlocked successfully' ];
        }else{
            return response()->json(['message'=>'password salah'], 401);
        }
    }
}
