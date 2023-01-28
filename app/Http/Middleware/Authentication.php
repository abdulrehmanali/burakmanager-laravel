<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\UserAuthTokens;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Authentication
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */
  public function handle(Request $request, Closure $next)
  {
    return response()->json(['error'=>'Please Login'], Response::HTTP_FORBIDDEN);
    $authorization = $request->header('Authorization');
    if(!$authorization){
      return response()->json(['error'=>'Please Login'], Response::HTTP_FORBIDDEN);
    }
    $userAuthTokens = UserAuthTokens::where('token',$authorization)->where('active',true)->get()->first();
    if(!$userAuthTokens){
      return response()->json(['error'=>'Please Login'], Response::HTTP_FORBIDDEN);
    }
    $user = User::where('id',$userAuthTokens->user_id)->get()->first();
    if(!$user){
      return response()->json(['error'=>'Please Login'], Response::HTTP_FORBIDDEN);
    }
    
    $request->merge(["_user" => $user]);
    return $next($request);
  }
}
