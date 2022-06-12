<?php

namespace App\Http\Controllers;

use App\Models\Ledger;
use App\Models\LedgerProducts;
use Illuminate\Http\Response;
use App;
use App\Models\Products;
use App\Models\ProductsBatches;
use App\Models\Shops;
use Validator;

class LedgerController extends Controller
{
  public function create()
  {
    $user = $this->getUser(request());
    if(!$user){
      return response()->json(['error'=>'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $validation_args = [
      'type' => 'required',
      'payment_method' => 'required',
      'customer_name' => 'required',
      'payment_status' => 'required',
      'amount_received' => 'required',
      'bank_name' => 'required_if:payment_method,bankTransfer',
      'transaction_id' => 'required_if:payment_method,bankTransfer',
      'cheque_number' => 'required_if:payment_method,cheque',
    ];
    if(request('products')){
      $validation_args["total"] = "required";
      $validation_args["products.*.product_name"] = "required";
      $validation_args["products.*.batch_id"] = "nullable";
      $validation_args["products.*.product_id"] = "nullable";
      $validation_args["products.*.quantity"] = "required";
      $validation_args["products.*.rate"] = "required";
    }
    $validated = Validator::make(request()->all(), $validation_args);
    if ($validated->fails()) {
      return response()->json(['errors'=>$validated->messages()], Response::HTTP_BAD_REQUEST);
    }
    $ledger = new Ledger();
    $ledger->shop_id = request('shop_id');
    $ledger->type = request('type');
    $ledger->payment_method = request('payment_method');
    $ledger->payment_status = request('payment_status');
    $ledger->amount_received = request('amount_received');
    $ledger->customer_name = request('customer_name');
    $ledger->customer_id = request('customer_id');
    $ledger->total = request('total');
    $ledger->bank_name = request('bank_name');
    $ledger->transaction_id = request('transaction_id');
    $ledger->cheque_number = request('cheque_number');
    $ledger->created_at = request('created_at');
    if(!$ledger->save()){
      return response()->json(['error'=>'Unable to save ledger please try again.'], Response::HTTP_BAD_REQUEST);
    }
    if(request('products')){
      $products = $validated->validated()['products'];
      foreach ($products as $key => $product) {
        $products[$key]['ledger_id'] = $ledger->id;
      }
      if(!LedgerProducts::insert($products)){
        return response()->json(['success'=>false,'error'=>'Please Ty Again']);
      }
      foreach ($products as $key => $product) {
        if(isset($product['product_id']) && isset($product['batch_id'])){
          ProductsBatches::where('product_id', $product['product_id'])->where('id', $product['batch_id'])->decrement('quantity', $product['quantity']);
        }
      }
    }
    if(request('pdf')){
      $shop = Shops::where('id',request('shop_id'))->get()->first();
      if(request('html')){
        return view('pdf.new_order_recipt', ['shop' => $shop,'entry'=>$ledger,'products'=>$products]);
      }
      $pdf = App::make('dompdf.wrapper');
      $pdf->loadHTML(view('pdf.new_order_recipt', ['shop' => $shop,'entry'=>$ledger,'products'=>$products]));
      return $pdf->stream();    
    }
    return response()->json(['success'=>true,'id'=>$ledger->id]);
  }

  public function update()
  {
    $user = $this->getUser(request());
    if(!$user){
      return response()->json(['error'=>'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $validation_args = [
      'type' => 'required',
      'payment_method' => 'required',
      'payment_status' => 'required',
      'amount_received' => 'required',
      'bank_name' => 'required_if:payment_method,bankTransfer',
      'transaction_id' => 'required_if:payment_method,bankTransfer',
      'cheque_number' => 'required_if:payment_method,cheque',
    ];
    $validated = Validator::make(request()->all(), $validation_args);
    if ($validated->fails()) {
      return response()->json(['errors'=>$validated->messages()], Response::HTTP_BAD_REQUEST);
    }
    $ledger = new Ledger(request('id'));
    $ledger->shop_id = request('shop_id');
    $ledger->type = request('type');
    $ledger->payment_method = request('payment_method');
    $ledger->payment_status = request('payment_status');
    $ledger->amount_received = request('amount_received');
    $ledger->customer_name = request('customer_name');
    $ledger->customer_id = request('customer_id');
    $ledger->total = request('total');
    $ledger->bank_name = request('bank_name');
    $ledger->transaction_id = request('transaction_id');
    $ledger->cheque_number = request('cheque_number');
    if(!$ledger->save()){
      return response()->json(['error'=>'Unable to save ledger please try again.'], Response::HTTP_BAD_REQUEST);
    }
    return response()->json(['success'=>true, 'id'=>$ledger->id]);
  }

  public function view() {
    $user = $this->getUser(request());
    if(!$user){
      return response()->json(['error'=>'Please Login'], Response::HTTP_BAD_REQUEST);
    }

    $ledger = Ledger::where('id',request('ledger_id'))->where('shop_id',request('shop_id'))->get()->first();
    if(!$ledger){
      return response()->json(['error'=>'Unable to find ledger.'], Response::HTTP_BAD_REQUEST);
    }
    return response()->json(['entry'=>$ledger,'products'=>$ledger->products]);
  }

  public function delete() {
    $user = $this->getUser(request());
    if(!$user){
      return response()->json(['error'=>'Please Login'], Response::HTTP_BAD_REQUEST);
    }

    $ledger = Ledger::where('id',request('ledger_id'))->where('shop_id',request('shop_id'));
    if(!$ledger->delete()){
      LedgerProducts::where('ledger_id',request('ledger_id'))->delete();
      return response()->json(['error'=>'Unable to delete ledger.'], Response::HTTP_BAD_REQUEST);
    }
    return response()->json(['success'=>true]);
  }

  public function viewPdf() {
    // $user = $this->getUser(request());
    // if(!$user){
    //   return response()->json(['error'=>'Please Login'], Response::HTTP_BAD_REQUEST);
    // }

    $ledger = Ledger::where('id',request('ledger_id'))->where('shop_id',request('shop_id'))->get()->first();
    if(!$ledger){
      return response()->json(['error'=>'Unable to find ledger.'], Response::HTTP_BAD_REQUEST);
    }
    $shop = Shops::where('id',request('shop_id'))->get()->first();
    if(request('html')){
      return view('pdf.recipt', ['shop' => $shop,'entry'=>$ledger,'products'=>$ledger->products]);
    }
    $pdf = App::make('dompdf.wrapper');
    $pdf->loadHTML(view('pdf.recipt', ['shop' => $shop,'entry'=>$ledger,'products'=>$ledger->products]));
    return $pdf->stream();
  }

  public function index() {
    $user = $this->getUser(request());
    if(!$user){
      return response()->json(['error'=>'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $ledgers = Ledger::where("shop_id",request("shop_id"));
    $ledgers = $ledgers->orderBy('created_at','desc')->get();
    foreach ($ledgers as $key => $ledger) {
      $ledgers[$key]['customer'] = $ledger->customer;
    }
    return response()->json(['entries'=>$ledgers]);
  }
}
