<?php

namespace App\Http\Controllers;

use App\Models\ShopWebhooks;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Validator;

class ShopWebhooksController extends Controller
{
  public function index() {
    $user = $this->getUser(request());
    if (!$user) {
      return response()->json(['error' => 'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $shop = $user->shops()->where('shop_id', request('shop_id'))->first();
    if (!$shop) {
      return response()->json(['error' => 'Shop not found'], Response::HTTP_BAD_REQUEST);
    }
    $webhooks = ShopWebhooks::where('shop_id', request('shop_id'))->get();
    return response()->json(['webhooks' => $webhooks]);
  }

  public function create() {
    $user = $this->getUser(request());
    if (!$user) {
      return response()->json(['error' => 'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $shop = $user->shops()->where('shop_id', request('shop_id'))->first();
    if (!$shop) {
      return response()->json(['error' => 'Shop not found'], Response::HTTP_BAD_REQUEST);
    }
    $validated = Validator::make(request()->all(), [
      'platform_name' => 'required|max:255',
      'receiving_store_url' => 'required|max:255',
      'auth_key' => 'required|max:255'
    ]);
    if ($validated->fails()) {
      return response()->json(['errors' => $validated->messages()], Response::HTTP_BAD_REQUEST);
    }
    $response = ['success' => false];
    $shopWebhooks = new ShopWebhooks();
    $shopWebhooks->shop_id = request('shop_id');
    $shopWebhooks->platform_name = request('platform_name');
    $shopWebhooks->receiving_store_url = request('receiving_store_url');
    $shopWebhooks->auth_key = request('auth_key');
    if ($shopWebhooks->save()) {
      $response['success'] = true;
      return response()->json($response);
    }
    return response()->json($response);
  }

  public function edit() {
    $user = $this->getUser(request());
    if (!$user) {
      return response()->json(['error' => 'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $shop = $user->shops()->where('shop_id', request('shop_id'))->first();
    if (!$shop) {
      return response()->json(['error' => 'Shop not found'], Response::HTTP_BAD_REQUEST);
    }
    $validated = Validator::make(request()->all(), [
      'platform_name' => 'required|max:255',
      'receiving_store_url' => 'required|max:255',
      'auth_key' => 'required|max:255'
    ]);
    if ($validated->fails()) {
      return response()->json(['errors' => $validated->messages()], Response::HTTP_BAD_REQUEST);
    }
    $response = ['success' => false];
    $shopWebhook = ShopWebhooks::where('shop_id', request('shop_id'))->where('id', request('id'))->get()->first();
    $shopWebhook->platform_name = request('platform_name');
    $shopWebhook->receiving_store_url = request('receiving_store_url');
    $shopWebhook->auth_key = request('auth_key');
    if ($shopWebhook->save()) {
      $response['success'] = true;
      return response()->json($response);
    }
    return response()->json($response);
  }

  public function delete() {
    $user = $this->getUser(request());
    if (!$user) {
      return response()->json(['error' => 'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $shop = $user->shops()->where('shop_id', request('shop_id'))->first();
    if (!$shop) {
      return response()->json(['error' => 'Shop not found'], Response::HTTP_BAD_REQUEST);
    }
    $response = ['success' => false];
    $shopWebhook = ShopWebhooks::where('shop_id', request('shop_id'))->where('id', request('id'));
    if ($shopWebhook->delete()) {
      $response['success'] = true;
      return response()->json($response);
    }
    return response()->json($response);
  }
}
