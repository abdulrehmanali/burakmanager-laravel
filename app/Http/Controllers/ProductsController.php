<?php

namespace App\Http\Controllers;

use App\Models\ProductionProducts;
use App\Models\Products;
use Illuminate\Http\Response;
use App\Models\ProductsBatches;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Validator;

class ProductsController extends Controller
{
  public function create()
  {
    $user = $this->getUser(request());
    if (!$user) {
      return response()->json(['error' => 'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $shop = $user->shops()->where('shop_id', request('shop_id'))->first();
    if (!$shop) {
      return response()->json(['error' => 'Shop not found'], Response::HTTP_BAD_REQUEST);
    }
    $validated = Validator::make(request()->all(), [
      'name' => 'required|max:255',
      'sku' => 'max:255',
      'batches.*.purchased_at' => 'required',
      'batches.*.purchasing_price' => 'required',
      'batches.*.selling_price' => 'required',
      'batches.*.quantity' => 'required',
      'batches.*.measurement_unit' => 'required',
      'batches.*.purchase_from' => 'nullable',
      'batches.*.expire_at' => 'nullable',
      'batches.*.status' => 'required',
    ]);
    if ($validated->fails()) {
      return response()->json(['errors' => $validated->messages()], Response::HTTP_BAD_REQUEST);
    }
    $response = ['success' => false];
    try {
      DB::beginTransaction();
      $product = new Products();
      $product->shop_id = request('shop_id');
      $product->name = request('name');
      $product->sku = request('sku');
      if (!$product->save()) {
        return response()->json(['error' => 'Unable to save product please try again.'], Response::HTTP_BAD_REQUEST);
      }
      $batches = request('batches');
      foreach ($batches as $key => $value) {
        $batches[$key]['created_at'] = date("Y-m-d H:i:s");
        $batches[$key]['updated_at'] = date("Y-m-d H:i:s");
        $batches[$key]['purchase_from_id'] = null;
        $batches[$key]['product_id'] = $product->id;
        if (!isset($batches[$key]['status']) && empty($batches[$key]['status'])) {
          $batches[$key]['status'] = 'on-hold';
        }
        if (isset($value['purchase_from'])){
          if (isset($value['purchase_from']['id']) && !empty($value['purchase_from']['id'])) {
            $batches[$key]['purchase_from_id'] = $value['purchase_from']['id'];
          }
        unset($batches[$key]['purchase_from']);
        }
        if (isset($value['purchased_at'])) {
          $batches[$key]['purchased_at'] = date("Y-m-d H:i:s", strtotime($batches[$key]['purchased_at']));
        }
        if (isset($value['expire_at'])) {
          $batches[$key]['expire_at'] = date("Y-m-d H:i:s", strtotime($batches[$key]['expire_at']));
        }
        if (isset($value['delivery_at'])) {
          $batches[$key]['delivery_at'] = date("Y-m-d H:i:s", strtotime($batches[$key]['delivery_at']));
        }
      }
      ProductsBatches::insert($batches);
      DB::commit();
      $response['success'] = true;
    } catch (\Exception $e) {
      Log::error($e);
      DB::rollback();
    }
    return response()->json($response);
  }

  public function update()
  {
    $user = $this->getUser(request());
    if (!$user) {
      return response()->json(['error' => 'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $shop = $user->shops()->where('shop_id', request('shop_id'))->first();
    if (!$shop) {
      return response()->json(['error' => 'Shop not found'], Response::HTTP_BAD_REQUEST);
    }
    $user = $this->getUser(request());
    if (!$user) {
      return response()->json(['error' => 'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $validated = Validator::make(request()->all(), [
      'name' => 'required|max:255',
      'sku' => 'max:255',
      'batches.*.purchased_at' => 'required',
      'batches.*.purchasing_price' => 'required',
      'batches.*.selling_price' => 'required',
      'batches.*.quantity' => 'required',
      'batches.*.measurement_unit' => 'required',
      'batches.*.purchase_from' => 'nullable',
      'batches.*.expire_at' => 'nullable',
      'batches.*.status' => 'required',
    ]);
    if ($validated->fails()) {
      return response()->json(['errors' => $validated->messages()], Response::HTTP_BAD_REQUEST);
    }
    if (!request('id')) {
      return response()->json(['error' => 'Unable to find product.'], Response::HTTP_BAD_REQUEST);
    }
    $response = ['success' => false];
    try {
      DB::beginTransaction();
      $update = Products::where('id', request('id'))->where('shop_id', request('shop_id'))->update(['name' => request('name'), 'sku' => request('sku')]);
      if (!$update) {
        return response()->json(['error' => 'Unable to update product please try again.'], Response::HTTP_BAD_REQUEST);
      }
      $batches = $validated->valid()['batches'];
      ProductsBatches::where('product_id', request('id'))->delete();
      foreach ($batches as $key => $value) {
        if (empty($batches[$key]['created_at'])) {
          $batches[$key]['created_at'] = date("Y-m-d H:i:s");
        }
        $batches[$key]['updated_at'] = date("Y-m-d H:i:s");
        $batches[$key]['purchase_from_id'] = null;
        $batches[$key]['product_id'] = (int) request('id');
        if (!isset($batches[$key]['status']) && empty($batches[$key]['status'])) {
          $batches[$key]['status'] = 'on-hold';
        }
        if (isset($value['purchase_from'])) {
          if (isset($value['purchase_from']['id']) && !empty($value['purchase_from']['id'])) {
            $batches[$key]['purchase_from_id'] = $value['purchase_from']['id'];
          }
          unset($batches[$key]['purchase_from']);
        }
        if (isset($value['purchased_at'])) {
          $batches[$key]['purchased_at'] = date("Y-m-d H:i:s", strtotime($batches[$key]['purchased_at']));
        }
        if (isset($value['expire_at'])) {
          $batches[$key]['expire_at'] = date("Y-m-d H:i:s", strtotime($batches[$key]['expire_at']));
        }
        if (isset($value['delivery_at'])) {
          $batches[$key]['delivery_at'] = date("Y-m-d H:i:s", strtotime($batches[$key]['delivery_at']));
        }
        ProductsBatches::create($batches[$key]);

      }
      DB::commit();
      $response['success'] = true;
    } catch (\Exception $e) {
      Log::error($e);
      $response['message'] = $e;
      DB::rollback();
    }
    return response()->json($response);
  }

  public function view()
  {
    $user = $this->getUser(request());
    if (!$user) {
      return response()->json(['error' => 'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $shop = $user->shops()->where('shop_id', request('shop_id'))->first();
    if (!$shop) {
      return response()->json(['error' => 'Shop not found'], Response::HTTP_BAD_REQUEST);
    }
    $product = Products::where("id", request("id"))->where('shop_id', request('shop_id'))->with('batches')->get()->first();
    if (!$product) {
      return response()->json(['error' => 'Unable to find product.'], Response::HTTP_BAD_REQUEST);
    }
    $product['batches'] = $product->batches;
    for ($i=0; $i < count($product->batches); $i++) { 
      $product['batches'][$i]['purchase_from'] = $product->batches[$i]->purchase_from;
    }
    $productionProducts = [];
    $productionProductsQuery = ProductionProducts::where('shop_id', request('shop_id'))->where('sku', $product->sku)->get()->first();
    if ($productionProductsQuery) {
      foreach ($productionProductsQuery->products as $productionProducts_product) {
        $quantity = 0;
        $measurement_unit = '';
        foreach ($productionProducts_product->product->batches as $value) {
          $quantity += (int)$value->quantity;
          $measurement_unit = $value->measurement_unit;
        }
        $productionProducts[] = [
          'id' => $productionProducts_product->product->id,
          'name' => $productionProducts_product->product->name,
          'sku' => $productionProducts_product->product->sku,
          'one_product_quantity' => $productionProducts_product->one_product_quantity,
          'measurement_unit'=> $measurement_unit,
          'quantity' => $quantity
        ];
      }
    }
    return response()->json(['product' => $product, 'productionProducts' => $productionProducts]);
  }

  public function index()
  {
    $user = $this->getUser(request());
    if (!$user) {
      return response()->json(['error' => 'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $products = Products::where("shop_id", request("shop_id"));
    $search = request('search');
    if (request('search')) {
      $searchField = request('searchField');
      if ($searchField == "quantity") {
        $products = $products->leftJoin('products_batches', 'products.id', '=', 'products_batches.product_id');
        $products = $products->groupBy('products_batches');
        $products = $products->whereRaw('SUM(products_batches.quantity) = ?', [$search]);

        $productsWithBatchCount = DB::table('products')
        ->leftJoin('product_batches', 'products.id', '=', 'product_batches.product_id')
        ->select('products.id', 'products.name', DB::raw('COUNT(product_batches.id) as batch_count'))
        ->groupBy('products.id', 'products.name')
        ->get();
      }

      // $products = $products->where(function ($query) {
      //   $query->where('name', 'like', "%" . request('search') . "%")->orWhere('sku', 'like', "%" . request('search') . "%");
      // });
    }
    $products = $products->get();
    foreach ($products as $key => $value) {
      $product[$key]['batches'] = $value->batches;
    }
    return response()->json(['products' => $products]);
  }
}
