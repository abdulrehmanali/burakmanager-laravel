<?php

namespace App\Http\Controllers;

use App\Models\Invitations;
use App\Models\ShopUsers;
use Illuminate\Http\Response;
use Validator;

class InvitationsController extends Controller
{
  public function create() {
    $user = $this->getUser(request());
    if(!$user){
      return response()->json(['error'=>'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $validations = [
      'invitations'=>'array',
      'invitations.*.email' => 'required|email',
      'invitations.*.can_create_entries_in_ledger' => 'required',
      'invitations.*.can_create_customers' => 'required',
      'invitations.*.can_create_products' => 'required',
      'invitations.*.can_edit_entries_in_ledger' => 'required',
      'invitations.*.can_edit_customers' => 'required',
      'invitations.*.can_edit_products' => 'required',
    ];
    $validated = Validator::make(request()->all(),$validations);
    if ($validated->fails()) {    
      return response()->json(['errors'=>$validated->messages()], Response::HTTP_BAD_REQUEST);
    }
    $invitations = [];
    $shopId = request('shop_id');
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
    if(Invitations::insert($invitations)){
      return response()->json(['success'=>true]);
    }
    return response()->json(['success'=>false], Response::HTTP_BAD_REQUEST);
  }
  public function delete(){
    $user = $this->getUser(request());
    if(!$user){
      return response()->json(['error'=>'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $invitation = Invitations::where('id',request('invitation_id'))->delete();
    if($invitation){
      return response()->json(['success'=>true]);
    }
    return response()->json(['success'=>false], Response::HTTP_BAD_REQUEST);
  }
  public function accept() {
    $user = $this->getUser(request());
    if(!$user){
      return response()->json(['error'=>'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $accepted = $user->invitations()->where('id',request('id'));
    $acceptedRecord = $accepted->get()->first();
    if($acceptedRecord) {
      $accepted->update(['accepted_at'=>date('Y-m-d H:i:s',now()->timestamp)]);
      ShopUsers::create([
        'shop_id'=>$acceptedRecord->shop_id,
        'user_id'=>$user->id,
        'can_create_entries_in_ledger'=>$acceptedRecord->can_create_entries_in_ledger,
        'can_create_customers'=>$acceptedRecord->can_create_customers,
        'can_create_products'=>$acceptedRecord->can_create_products,
        'can_edit_entries_in_ledger'=>$acceptedRecord->can_edit_entries_in_ledger,
        'can_edit_customers'=>$acceptedRecord->can_edit_customers,
        'can_edit_products'=>$acceptedRecord->can_edit_products
      ]);
      return response()->json(['success'=>true]);
    }else{
      return response()->json(['error'=>'Please try again'], Response::HTTP_BAD_REQUEST);
    }
  }
}
