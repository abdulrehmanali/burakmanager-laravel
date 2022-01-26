<?php

namespace App\Http\Controllers;

use App\Models\Invitations;
use App\Models\Shops;
use App\Models\ShopUsers;
use Illuminate\Http\Response;
use Validator;

class ShopsController extends Controller
{
  public function index()
  {
    $user = $this->getUser(request());
    if(!$user){
      return response()->json(['error'=>'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $shops = $user->shops()->orderBy('created_at','desc')->get();
    foreach ($shops as $key => $shop) {
      $shops[$key]['shop'] = $shop->shop;
    }
    return response()->json(['shops'=>$shops]);
  }

  public function view()
  {
    $user = $this->getUser(request());
    if(!$user){
      return response()->json(['error'=>'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $shop = $user->shops()->where('shop_id',request('id'))->first();
    if(!$shop){
      return response()->json(['error'=>'Shop not found'], Response::HTTP_BAD_REQUEST);
    }
    $shop = $shop->shop;
    $shop['invitations'] = $shop->invitations()->whereNull('accepted_at')->get();
    $shop['users'] = $shop->users;
    foreach ($shop['invitations'] as $key => $value) {
      $shop['invitations'][$key]['user'] = $value->user;
    }
    foreach ($shop['users'] as $key => $value) {
      $shop['users'][$key]['user'] = $value->user;
    }
    return response()->json(['shop'=>$shop]);
  }

  public function create()
  {
    $user = $this->getUser(request());
    if(!$user){
      return response()->json(['error'=>'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $validations = [
      'name' => 'required|max:255',
      'address' => 'required|max:255',
      'currency' => 'required|max:5',
    ];
    if(request('invitations')){
      $validations = [
        'name' => 'required|max:255',
        'address' => 'required|max:255',
        'currency' => 'required|max:5',
        'invitations'=>'array',
        'invitations.*.email' => 'required|email',
        'invitations.*.can_create_entries_in_ledger' => 'required',
        'invitations.*.can_create_customers' => 'required',
        'invitations.*.can_create_products' => 'required',
        'invitations.*.can_edit_entries_in_ledger' => 'required',
        'invitations.*.can_edit_customers' => 'required',
        'invitations.*.can_edit_products' => 'required',
      ];
    }
    $validated = Validator::make(request()->all(),$validations);
    if ($validated->fails()) {    
      return response()->json(['errors'=>$validated->messages()], Response::HTTP_BAD_REQUEST);
    }
    $shop = new Shops();
    $shop->name = request('name');
    $shop->address = request('address');
    $shop->currency = request('currency');
    $shop->admin_id = $user->id;
    if($shop->save()) {
      $shopId = $shop->id;
      ShopUsers::create([
        'shop_id'=>$shopId,
        'user_id'=>$user->id,
        'can_create_entries_in_ledger'=>true,
        'can_create_customers'=>true,
        'can_create_products'=>true,
        'can_edit_entries_in_ledger'=>true,
        'can_edit_customers'=>true,
        'can_edit_products'=>true
      ]);

      if(request('invitations')){
        $invitations = [];
        foreach (request('invitations') as $invitation) {
          $invitations[] = [
            'shop_id' => $shopId,
            'email' => $invitation['email'],
            'token' => bcrypt($shopId.date('YmdHis').$invitation['email']),
            'expire_at' => date('Y-m-d', strtotime("-7 days")),
            'can_create_entries_in_ledger' => $invitation['can_create_entries_in_ledger'],
            'can_create_customers' => $invitation['can_create_customers'],
            'can_create_products' => $invitation['can_create_products'],
            'can_edit_entries_in_ledger' => $invitation['can_edit_entries_in_ledger'],
            'can_edit_customers' => $invitation['can_edit_customers'],
            'can_edit_products' => $invitation['can_edit_products']
          ];
        }
        Invitations::insert($invitations);
      }
      return response()->json(['success'=>true]);
    }
    return response()->json(['success'=>false], Response::HTTP_BAD_REQUEST);
  }

  public function delete_user(){
    $user = $this->getUser(request());
    if(!$user){
      return response()->json(['error'=>'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    ShopUsers::where('shop_id',request('shop_id'))->where('user_id',request('user_id'))->delete();
    return response()->json(['success'=>true]);
  }
}
