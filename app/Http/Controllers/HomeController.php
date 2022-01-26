<?php

namespace App\Http\Controllers;

use App\Models\Invitations;
use App\Models\Shops;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HomeController extends Controller
{
  public function index(){
    $user = $this->getUser(request());
    if(!$user){
      return response()->json(['error'=>'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $invitations = $user->invitations()->whereNull('accepted_at')->get();
    foreach ($invitations as $key => $invitation) {
      $invitations[$key]['shop'] = $invitation->shop;
    }
    if(!request('shop_id')){
      return response()->json(['invitations'=>$invitations]);
    }
    $shop = Shops::where('id',request('shop_id'))->get()->first();
    $products = $shop->products;
    $numberOfProducts = count($products);
    $numberOfItems = 0;
    $totalCapital = 0;
    $totalProfit = 0;
    foreach ($products as $product) {
      foreach ($product->batches as $batch) {
        $numberOfItems += $batch->quantity;
        $totalCapital += ($batch->quantity * $batch->purchasing_price);
        $totalProfit += ($batch->quantity * ($batch->selling_price - $batch->purchasing_price));
      }
    }
    return response()->json(['invitations'=>$invitations,'numberOfProducts'=>$numberOfProducts,'numberOfItems'=>$numberOfItems,'totalCapital'=>$totalCapital,'totalProfit'=>$totalProfit]);
  }
}
