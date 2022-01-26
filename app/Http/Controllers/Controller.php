<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserAuthTokens;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public function getUser(Request $request)
    {
      $authorization = $request->header('Authorization');
      if(!$authorization){
        return false;
      }
      $userAuthTokens = UserAuthTokens::where('token',$authorization)->where('active',true)->get();
      if(!$userAuthTokens || !$userAuthTokens->first()){
        return false;
      }
      $userAuthTokens = $userAuthTokens->first();
      $user = User::where('id',$userAuthTokens->user_id)->get()->first();
      return $user;
    }
}
