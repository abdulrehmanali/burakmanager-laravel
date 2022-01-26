<?php

namespace App\Http\Controllers;

use App\Models\ProductionProducts;
use Illuminate\Http\Response;
use App\Models\ProductionProductsProducts;
use Validator;

class ProductionProductsController extends Controller
{
  public function create()
  {
    $user = $this->getUser(request());
    if(!$user){
      return response()->json(['error'=>'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $validated = Validator::make(request()->all(), [
      'name' => 'required|max:255',
      'price' => 'required|max:255',
      'sku' => 'required|max:255',
      'products.*.product_id' => 'required',
      'products.*.one_product_quantity' => 'required',
    ]);
    if ($validated->fails()) {
      return response()->json(['errors'=>$validated->messages()], Response::HTTP_BAD_REQUEST);
    }
    $productionProducts = new ProductionProducts();
    $productionProducts->shop_id = request('shop_id');
    $productionProducts->name = request('name');
    $productionProducts->sku = request('sku');
    $productionProducts->price = request('price');
    if(!$productionProducts->save()){
      return response()->json(['error'=>'Unable to save Production Product. Please try again.'], Response::HTTP_BAD_REQUEST);
    }
    $products = [];
    foreach (request('products') as $key => $product) {
      $products[$key]['production_product_id'] = $productionProducts->id;
      $products[$key]['product_id'] = $product['product_id'];
      $products[$key]['one_product_quantity'] = $product['one_product_quantity'];
    }
    ProductionProductsProducts::insert($products);
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
      'price' => 'required|max:255',
      'sku' => 'required|max:255',
      'products.*.product_id' => 'required',
      'products.*.one_product_quantity' => 'required',
    ]);
    if ($validated->fails()) {    
      return response()->json(['errors'=>$validated->messages()], Response::HTTP_BAD_REQUEST);
    }
    if (!request('id')) {
      return response()->json(['error'=>'Unable to find product.'], Response::HTTP_BAD_REQUEST);
    }
    $update = ProductionProducts::where('id', request('id'))->where('shop_id', request('shop_id'))->update(['name'=>request('name'),'price'=>request('price'),'sku'=>request('sku')]);
    if(!$update){
      return response()->json(['error'=>'Unable to update product please try again.'], Response::HTTP_BAD_REQUEST);
    }
    $products = $validated->validated()['products'];
    ProductionProductsProducts::where('production_product_id', request('id'))->delete();
    foreach ($products as $key => $product) {
      $products[$key]['production_product_id'] = request('id');
      $products[$key]['product_id'] = $product['product_id'];
      $products[$key]['one_product_quantity'] = $product['one_product_quantity'];
    }
    ProductionProductsProducts::insert($products);
    return response()->json(['success'=>true]);
  }

  public function view() {
    $user = $this->getUser(request());
    if(!$user){
      return response()->json(['error'=>'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $product = ProductionProducts::where("id",request("id"))->where('shop_id',request('shop_id'))->get()->first();
    if(!$product){
      return response()->json(['error'=>'Unable to find Product.'], Response::HTTP_NOT_FOUND);
    }
    $product['products'] = $product->prodducts;
    return response()->json(['product'=>$product]);
  }

  public function index() {
    $user = $this->getUser(request());
    if(!$user){
      return response()->json(['error'=>'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $productionProducts = ProductionProducts::where("shop_id",request("shop_id"));
    if(request('search')){
      $productionProducts = $productionProducts->where('name','like',"%".request('search')."%")->orWhere('sku','like',"%".request('search')."%");
    }
    $productionProducts = $productionProducts->get();
    return response()->json(['productionProducts'=>$productionProducts]);
  }
}
