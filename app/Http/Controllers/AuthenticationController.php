<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserAuthTokens;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Validator;

class AuthenticationController extends Controller
{
  public function signup()
  {
    $validated = Validator::make(request()->all(), [
      'name' => 'required|max:255',
      'email' => 'required|unique:users|max:255',
      'phone_number' => 'required|string|unique:users|max:25',
      'password' => 'required'
    ]);
    if ($validated->fails()) {    
      return response()->json(['errors'=>$validated->messages()], Response::HTTP_BAD_REQUEST);
    }
    $user = User::create(request(['name', 'email', 'phone_number', 'password']));
    if(!$user) {
      return response()->json(['error'=>'Please Try Again'],Response::HTTP_BAD_REQUEST);
    }
    $token = md5(bcrypt(bcrypt(date("Y-m-d H:i:s").$user['id'])));
    $UserAuthTokens = new UserAuthTokens();
    $UserAuthTokens->user_id = $user['id'];
    $UserAuthTokens->token = $token;
    $UserAuthTokens->active = true;
    if($UserAuthTokens->save()){
      return response()->json(['user'=>$user,'token'=>$token]);
    } else {
      return response()->json(['error'=>'Please Try Again'],Response::HTTP_BAD_REQUEST);
    }
  }

  public function login()
  {
    $validated = Validator::make(request()->all(), [
      'email' => 'required|max:255',
      'password' => 'required|string'
    ]);
    if ($validated->fails()) {    
      return response()->json(['errors'=>$validated->messages()], Response::HTTP_BAD_REQUEST);
    }
    $user = User::where('email', '=', request('email'))->first();
    if (!$user) {
      return response()->json(['error'=>'Please check your email'], Response::HTTP_BAD_REQUEST);
    }
    if (!Hash::check(request('password'), $user->password)) {
      return response()->json(['error'=>'Please check your password'], Response::HTTP_BAD_REQUEST);
    }
    $token = md5(bcrypt(bcrypt(date("Y-m-d H:i:s").$user->first()->id)));
    $UserAuthTokens = new UserAuthTokens();
    $UserAuthTokens->user_id = $user->id;
    $UserAuthTokens->token = $token;
    $UserAuthTokens->active = true;
    if($UserAuthTokens->save()){
      return response()->json(['user'=>$user,'token'=>$token]);
    } else {
      return response()->json(['error'=>'Please Try Again'],Response::HTTP_BAD_REQUEST);
    }
  }
}
