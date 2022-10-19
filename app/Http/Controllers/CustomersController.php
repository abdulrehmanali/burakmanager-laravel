<?php

namespace App\Http\Controllers;

use App\Models\Customers;
use Illuminate\Http\Response;
use Validator;

class CustomersController extends Controller
{
  public function create()
  {
    $user = $this->getUser(request());
    if(!$user){
      return response()->json(['error'=>'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $shop = $user->shops()->where('shop_id',request('shop_id'))->first();
    if(!$shop){
      return response()->json(['error'=>'Shop not found'], Response::HTTP_BAD_REQUEST);
    }
    $validated = Validator::make(request()->all(), [
      'name' => 'required|max:255',
      'email' => 'nullable|email',
      'phone_number' => 'required',
      'ntn' => 'nullable',
      'company_name' => 'nullable',
      'address' => 'nullable',
    ]);
    if ($validated->fails()) {
      return response()->json(['errors'=>$validated->messages()], Response::HTTP_BAD_REQUEST);
    }
    $customer = new Customers();
    $customer->shop_id = request('shop_id');
    $customer->name = request('name');
    $customer->email = request('email');
    $customer->phone_number = request('phone_number');
    $customer->ntn = request('ntn');
    $customer->company_name = request('company_name');
    $customer->address = request('address');
    if(!$customer->save()){
      return response()->json(['error'=>'Unable to save customer please try again.'], Response::HTTP_BAD_REQUEST);
    }
    return response()->json(['success'=>true]);
  }

  public function update()
  {
    $user = $this->getUser(request());
    if(!$user){
      return response()->json(['error'=>'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $shop = $user->shops()->where('shop_id',request('shop_id'))->first();
    if(!$shop){
      return response()->json(['error'=>'Shop not found'], Response::HTTP_BAD_REQUEST);
    }
    $validated = Validator::make(request()->all(), [
      'name' => 'name|max:255',
      'email' => 'nullable|email',
      'phone_number' => 'nullable',
      'ntn' => 'nullable',
      'company_name' => 'nullable',
      'address' => 'nullable',
    ]);
    if ($validated->fails()) {
      return response()->json(['errors'=>$validated->messages()], Response::HTTP_BAD_REQUEST);
    }
    $customer = Customers::where('id',request('customer_id'))->where('shop_id',request('shop_id'))->update([
      'name' => request('name'),
      'email' => request('email'),
      'phone_number' => request('phone_number'),
      'company_name' => request('company_name'),
      'ntn' => request('ntn'),
      'address' => request('address'),
    ]);
    if(!$customer){
      return response()->json(['error'=>'Unable to save customer please try again.'], Response::HTTP_BAD_REQUEST);
    }
    return response()->json(['success'=>true]);

  }

  public function view() {
    $user = $this->getUser(request());
    if(!$user){
      return response()->json(['error'=>'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $shop = $user->shops()->where('shop_id',request('shop_id'))->first();
    if(!$shop){
      return response()->json(['error'=>'Shop not found'], Response::HTTP_BAD_REQUEST);
    }

    $customer = Customers::where('id',request('customer_id'))->where('shop_id',request('shop_id'))->get()->first();
    if(!$customer){
      return response()->json(['error'=>'Unable to find customer.'], Response::HTTP_BAD_REQUEST);
    }
    return response()->json(['customer'=>$customer]);
  }

  public function index() {
    $user = $this->getUser(request());
    if(!$user){
      return response()->json(['error'=>'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $shop = $user->shops()->where('shop_id',request('shop_id'))->first();
    if(!$shop){
      return response()->json(['error'=>'Shop not found'], Response::HTTP_BAD_REQUEST);
    }
    $customer = Customers::where("shop_id",request("shop_id"));
    if(request('search') && !empty(request('search'))){
      $customer = $customer->where(function ($query) {
        $query->where('name','like',"%".request('search')."%")
              ->orWhere('email','like',"%".request('search')."%");
    });
    }
    $customer = $customer->get();
    return response()->json(['customers'=>$customer]);
  }
}
