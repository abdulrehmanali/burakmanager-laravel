<?php

namespace App\Http\Controllers;

use App\Models\ProductionProducts;
use App\Models\Products;
use Illuminate\Http\Response;
use App\Models\ProductsBatches;
use Validator;

class ProductsController extends Controller
{
  public function create()
  {
    $user = $this->getUser(request());
    if(!$user){
      return response()->json(['error'=>'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $validated = Validator::make(request()->all(), [
      'name' => 'required|max:255',
      'sku' => 'max:255',
      'batches.*.purchased_at' => 'required',
      'batches.*.purchasing_price' => 'required',
      'batches.*.selling_price' => 'required',
      'batches.*.quantity' => 'required',
      'batches.*.measurement_unit' => 'required'
    ]);
    if ($validated->fails()) {
      return response()->json(['errors'=>$validated->messages()], Response::HTTP_BAD_REQUEST);
    }
    $product = new Products();
    $product->shop_id = request('shop_id');
    $product->name = request('name');
    $product->sku = request('sku');
    if(!$product->save()){
      return response()->json(['error'=>'Unable to save product please try again.'], Response::HTTP_BAD_REQUEST);
    }
    $batches = request('batches');
    foreach ($batches as $key => $value) {
      $batches[$key]['product_id'] = $product->id;
      $batches[$key]['status'] = 'active';
    }
    ProductsBatches::insert($batches);
    return response()->json(['success'=>true]);
  }

  public function update()
  {
    $user = $this->getUser(request());
    if(!$user){
      return response()->json(['error'=>'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $validated = Validator::make(request()->all(), [
      'name' => 'required|max:255',
      'sku' => 'max:255',
      'batches' => 'required',
      'batches.*.purchased_at' => 'required',
      'batches.*.purchasing_price' => 'required',
      'batches.*.selling_price' => 'required',
      'batches.*.quantity' => 'required',
      'batches.*.measurement_unit' => 'required'
    ]);
    if ($validated->fails()) {    
      return response()->json(['errors'=>$validated->messages()], Response::HTTP_BAD_REQUEST);
    }
    if (!request('id')) {    
      return response()->json(['error'=>'Unable to find product.'], Response::HTTP_BAD_REQUEST);
    }
    $update = Products::where('id', request('id'))->where('shop_id', request('shop_id'))->update(['name'=>request('name'),'sku'=>request('sku')]);
    if(!$update){
      return response()->json(['error'=>'Unable to update product please try again.'], Response::HTTP_BAD_REQUEST);
    }
    $batches = $validated->validated()['batches'];
    ProductsBatches::where('product_id', request('id'))->delete();
    foreach ($batches as $key => $value) {
      $batches[$key]['product_id'] = request('id');
      $batches[$key]['status'] = 'active';
    }
    ProductsBatches::insert($batches);
    return response()->json(['success'=>true]);
  }

  public function view() {
    $user = $this->getUser(request());
    if(!$user){
      return response()->json(['error'=>'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $product = Products::where("id",request("id"))->where('shop_id',request('shop_id'))->get()->first();
    if(!$product){
      return response()->json(['error'=>'Unable to find product.'], Response::HTTP_BAD_REQUEST);
    }
    $product['batches'] = $product->batches;
    $productionProducts = [];
    $productionProducts = ProductionProducts::where('shop_id',request('shop_id'))->where('sku',$product->sku)->get()->first();
    if($productionProducts){
      foreach ($productionProducts->products as $productionProducts_product) {
        $quantity = 0;
        foreach ($productionProducts_product->product->batches as $value) {
          $quantity += $value->quantity;
        }
        $productionProducts[] = [
          'id'=>$productionProducts_product->product->id,
          'name'=>$productionProducts_product->product->name,
          'sku'=>$productionProducts_product->product->sku,
          'measurement_unit'=>$productionProducts_product->product->measurement_unit,
          'one_product_quantity'=>$productionProducts_product->product->one_product_quantity,
          'quantity' =>$quantity
        ];
      }
    }
    return response()->json(['product'=>$product, 'productionProducts'=> $productionProducts]);
  }

  public function index() {
    $user = $this->getUser(request());
    if(!$user){
      return response()->json(['error'=>'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $products = Products::where("shop_id",request("shop_id"));
    if(request('search')){
      $products = $products->where(function ($query) {
        $query->where('name','like',"%".request('search')."%")
              ->orWhere('sku','like',"%".request('search')."%");
    });
    }
    $products = $products->get();
    foreach ($products as $key => $value) {
      $product[$key]['batches'] = $value->batches;
    }
    return response()->json(['products'=>$products]);
  }
}
