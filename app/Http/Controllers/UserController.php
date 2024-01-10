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

    public function ResetPasswordLink( Request $request ){
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'callback' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(),422);
        }
        $user = User::where( @$request->column ?? 'email', $request->email )->first();
        if( !$user ){
            return response()->json(['message'=>'user tidak ditemukan'], 401);
        }
        $token = random_str_cache(25, 600, $request->email, [1,2,3,4,5,6,7,8,9,0,'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z']);

        $res = SendEmail($request->email, env('APP_NAME').': Link Reset Password', 
            "Hai $user->name, Click link $request->callback?token=$token untuk reset password anda. <br/>"
            ."<i>Link ini berlaku hanya 10 menit sejak email ini dikirimkan<br/>"
            ."Abaikan jika anda tidak merasa ingin melakukan reset password</i>"
        );

        return response()->json([
            'message'=>"Link reset password telah dikirim ke $request->email berlaku(expired) 10 menit"
        ]);
    }
    
    public function ResetPasswordTokenVerify( Request $request, $token ){
        $account = get_random_str_cache( $token, $isPull=false );
        if( !$account ){
            return response()->json([
                'message'=>"Maaf token tidak dikenali atau mungkin telah expired"
            ], 401);
        }
        return response()->json([
            'verified' => true,
            'email' => $account,
            'url_change_password_post' => url("reset-password")
        ]);
    }
    
    public function ResetPassword( Request $request ){
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'password' => 'required|string|confirmed'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(),422);
        }
        
        $account = get_random_str_cache( $request->token, $isPull=false );
        if( !$account ){
            return response()->json([
                'message'=>"Maaf token tidak dikenali atau mungkin telah expired"
            ], 401);
        }

        $user = User::where( @$request->column ?? 'email', $account )->first();
        if( !$user ){
            return response()->json(['message'=>'user tidak ditemukan'], 401);
        }
        
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'message'=>"Password telah berhasil diupdate, silahkan login"
        ]);
    }
}
