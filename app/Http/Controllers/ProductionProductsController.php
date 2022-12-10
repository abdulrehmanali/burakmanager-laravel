<?php

namespace App\Http\Controllers;

use App\Models\ProductionProducts;
use Illuminate\Http\Response;
use App\Models\ProductionProductsProducts;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Validator;
use App;
use App\Models\Products;
use App\Models\ProductsBatches;

class ProductionProductsController extends Controller
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
      'price' => 'required|max:255',
      'sku' => 'required|max:255',
      'products.*.product_id' => 'required',
      'products.*.one_product_quantity' => 'required',
    ]);
    if ($validated->fails()) {
      return response()->json(['errors'=>$validated->messages()], Response::HTTP_BAD_REQUEST);
    }
    $response = ['success'=>false];
    try{
      DB::beginTransaction();
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
      DB::commit();
      $response['success'] = true;
    } catch(\Exception $e){
      Log::error($e);
      DB::rollback();
    }
    return response()->json($response);
  }

  function divideFloat($a, $b, $precision=3) {
    $a*=pow(10, $precision);
    $result=(int)($a / $b);
    if (strlen($result)==$precision) return '0.' . $result;
    else return preg_replace('/(\d{' . $precision . '})$/', '.\1', $result);
}

  public function createProduct()
  {
    $user = $this->getUser(request());
    if (!$user) {
      return response()->json(['error' => 'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $shop = $user->shops()->where('shop_id', request('shop_id'))->first();
    if (!$shop) {
      return response()->json(['error' => 'Shop not found'], Response::HTTP_BAD_REQUEST);
    }

    $productionProduct = ProductionProducts::where('id', request('id'))->get()->first();
    if (!$productionProduct) {
      return response()->json(['error' => 'Unable to find Production Product'], Response::HTTP_BAD_REQUEST);
    }

    $response = ['success' => false];
    $product_price = $productionProduct->price;
    $product_sku = $productionProduct->sku;
    $requireQuantity = request('quantity');
  
    $batches = [
      'purchased_at' => date("Y-m-d H:i:s"),
      'purchasing_price' => $product_price,
      'selling_price' => $product_price,
      'quantity' => $requireQuantity,
      'measurement_unit' => 'piece',
      'status' => 'in-production',
    ];

      try {
        DB::beginTransaction();
        $existing_product = Products::where('sku', $product_sku)->get()->first();
        if ($existing_product) {
          $batches['product_id'] = $existing_product->id;
        } else {
          $product = new Products();
          $product->shop_id = request('shop_id');
          $product->name = $productionProduct->name;
          $product->sku = $product_sku;
          if (!$product->save()) {
            $response['error'] = 'Unable to save product please try again.';
            throw new \Exception("Unable to save product please try again");
            return $response;
          }
          $batches['product_id'] = $product->id;
        }
        ProductsBatches::create($batches);
        foreach ($productionProduct->products as $productionProductProduct) {
          $batches_query = $productionProductProduct->product->batches()->where('status', 'active');
          $productionProductProductsBatches = $batches_query->get();
          // $totalQuantity = 0;
          // foreach ($productionProductProductsBatches as $productionProductProductsBatch) {
          //   $totalQuantity += $productionProductProductsBatch->quantity;
          // }
          
          $maxGenerationQuantity = $productionProductProduct->one_product_quantity * $requireQuantity;
          Log::error('maxGenerationQuantity > '.$maxGenerationQuantity);

          foreach ($productionProductProductsBatches as $productionProductProductsBatch) {
            Log::error('maxGenerationQuantity > '.$maxGenerationQuantity);
            $newQuantity = 0;
            if ($productionProductProductsBatch->quantity >= $maxGenerationQuantity) {
              $newQuantity = $productionProductProductsBatch->quantity - $maxGenerationQuantity;
            }else if ($productionProductProductsBatch->quantity <= $maxGenerationQuantity) {
              $newQuantity = $maxGenerationQuantity -  $productionProductProductsBatch->quantity;
            }
            Log::error('newQuantity > '.$newQuantity);
            $maxGenerationQuantity = ($maxGenerationQuantity > $newQuantity?$maxGenerationQuantity - $newQuantity:$newQuantity - $maxGenerationQuantity);
            $new_update = ['quantity'=>$newQuantity];
            if ($newQuantity == 0) {
              $new_update['status'] = "out-of-stock";
            }
            $batches_query->where('id', $productionProductProductsBatch->id)->update($new_update);

            if ($maxGenerationQuantity == 0){
              break;
            }
          }
        }
        $response['success'] = true;
        DB::commit();
      } catch (\Exception $e) {
        Log::error($e);
        DB::rollback();
      }
    return response()->json($response);
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
    $shop = $user->shops()->where('shop_id',request('shop_id'))->first();
    if(!$shop){
      return response()->json(['error'=>'Shop not found'], Response::HTTP_BAD_REQUEST);
    }
    $product = ProductionProducts::where("id",request("id"))->where('shop_id',request('shop_id'))->get()->first();
    if(!$product){
      return response()->json(['error'=>'Unable to find Product.'], Response::HTTP_NOT_FOUND);
    }
    $product['products'] = $product->products;
    for ($i=0; $i < count($product['products']); $i++) { 
      $product['products'][$i]['product'] = $product['products'][$i]->product;
      $product['products'][$i]['product']['batches'] = $product['products'][$i]['product']->batches;
    }
    return response()->json(['production_product'=>$product]);
  }

  public function viewPdf() {
    $user = $this->getUser(request());
    if(!$user){
      return response()->json(['error'=>'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $shop = $user->shops()->where('shop_id',request('shop_id'))->first();
    if(!$shop){
      return response()->json(['error'=>'Shop not found'], Response::HTTP_BAD_REQUEST);
    }
    $product = ProductionProducts::where("id",request("id"))->where('shop_id',request('shop_id'))->get()->first();
    if(!$product){
      return response()->json(['error'=>'Unable to find Product.'], Response::HTTP_NOT_FOUND);
    }
    $product['products'] = $product->products;
    for ($i=0; $i < count($product['products']); $i++) { 
      $product['products'][$i]['product'] = $product['products'][$i]->product;
      $product['products'][$i]['product']['batches'] = $product['products'][$i]['product']->batches;
    }
    $pdf = App::make('dompdf.wrapper');
    $pdf->loadHTML(view('pdf.production_product', ['production_product'=>$product]));
    return $pdf->stream();
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
    $productionProducts = ProductionProducts::where("shop_id",request("shop_id"));
    if(request('search')){
      $productionProducts = $productionProducts->where('name','like',"%".request('search')."%")->orWhere('sku','like',"%".request('search')."%");
    }
    $productionProducts = $productionProducts->get();
    return response()->json(['productionProducts'=>$productionProducts]);
  }
}
