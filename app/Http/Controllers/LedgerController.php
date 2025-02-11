<?php

namespace App\Http\Controllers;

use App\Models\Ledger;
use App\Models\LedgerProducts;
use Illuminate\Http\Response;
use App;
use App\Models\LedgerPayments;
use App\Models\Products;
use App\Models\ProductsBatches;
use App\Models\Shops;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Validator;

class LedgerController extends Controller
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
    $validation_args = [
      'type' => 'required',
      'customer_name' => 'required'
    ];
    if (request('payments')) {
      $validation_args["payments.*.method"] = "required";
      $validation_args["payments.*.status"] = "required";
      $validation_args["payments.*.amount"] = "required";
      $validation_args["payments.*.bank_name"] = "required_if:payments.method,bankTransfer";
      $validation_args["payments.*.transaction_id"] = "required_if:payments.method,bankTransfer";
      $validation_args["payments.*.cheque_number"] = "required_if:payments.method,cheque";
    }
    if (request('products')) {
      $validation_args["total"] = "required";
      $validation_args["products.*.product_name"] = "required";
      $validation_args["products.*.batch_id"] = "nullable";
      $validation_args["products.*.product_id"] = "nullable";
      $validation_args["products.*.quantity"] = "required";
      $validation_args["products.*.rate"] = "required";
    }
    $validated = Validator::make(request()->all(), $validation_args);
    if ($validated->fails()) {
      return response()->json(['errors' => $validated->messages()], Response::HTTP_BAD_REQUEST);
    }
    $response = [
      'success' => false
    ];
    try {
      DB::beginTransaction();
      $ledger = new Ledger();
      $ledger->shop_id = request('shop_id');
      $ledger->type = request('type');
      $ledger->customer_name = request('customer_name');
      $ledger->customer_id = request('customer_id');
      $ledger->total = request('total');
      $ledger->created_at = date('Y-m-d H:i:s');
      $ledger->note = request('note');
      $ledger->save();
      $ledger_id = $ledger->id;
      if (request('payments')) {
        $payments = $validated->validated()['payments'];
        foreach ($payments as $key => $payment) {
          $ledgerPayments = new LedgerPayments();
          $ledgerPayments->shop_id = request('shop_id');
          $ledgerPayments->ledger_id =$ledger_id;
          $ledgerPayments->method = $payment['method'];
          $ledgerPayments->status = $payment['status'];
          $ledgerPayments->amount = $payment['amount'];
          $ledgerPayments->bank_name = $payment['bank_name'];
          $ledgerPayments->transaction_id = $payment['transaction_id'];
          $ledgerPayments->cheque_number = $payment['cheque_number'];
          $ledgerPayments->save();
        }
      }
      if (request('products')) {
        $products = $validated->validated()['products'];
        foreach ($products as $key => $product) {
          $product['ledger_id'] = $ledger_id;
          LedgerProducts::create($product);
        }
        foreach ($products as $key => $product) {
          if (isset($product['product_id']) && isset($product['batch_id'])) {
            ProductsBatches::where('product_id', $product['product_id'])->where('id', $product['batch_id'])->decrement('quantity', $product['quantity']);
          }
        }
      }
      DB::commit();
      $response['success'] = true;
      $response['id'] = $ledger_id;
    } catch (\Exception $e) {
      Log::error($e);
      $response['message'] = $e->getMessage();
      DB::rollback();
    }
    if (request('pdf')) {
      $shop = Shops::where('id', request('shop_id'))->get()->first();
      if (request('html')) {
        return view('pdf.new_order_receipt', ['shop' => $shop, 'entry' => $ledger, 'products' => $products]);
      }
      $pdf = App::make('dompdf.wrapper');
      $pdf->loadHTML(view('pdf.new_order_receipt', ['shop' => $shop, 'entry' => $ledger, 'products' => $products]));
      return $pdf->stream();
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
    $validation_args = [
      'type' => 'required',
      'customer_name' => 'required',
      'payments.*.method' => 'required',
      'payments.*.status' => 'required',
      'payments.*.amount' => 'required',
      'payments.*.bank_name' => 'required_if:payments.method,bankTransfer',
      'payments.*.transaction_id' => 'required_if:payments.method,bankTransfer',
      'payments.*.cheque_number' => 'required_if:payments.method,cheque',
    ];
    $validated = Validator::make(request()->all(), $validation_args);
    if ($validated->fails()) {
      return response()->json(['errors' => $validated->messages()], Response::HTTP_BAD_REQUEST);
    }
    $response = [
      'success' => false
    ];
    try {
      DB::beginTransaction();
      $ledger = Ledger::find(request('ledger_id'));
      $ledger->shop_id = request('shop_id');
      $ledger->type = request('type');
      $ledger->customer_name = request('customer_name');
      $ledger->customer_id = request('customer_id');
      // $ledger->total = request('total');
      $ledger->note = request('note');
      $ledger->save();
      // $ledger->payments()->delete();
      // $payments = $validated->validated()['payments'];
      // foreach ($payments as $payment) {
      //   $ledgerPayments = new LedgerPayments();
      //   $ledgerPayments->shop_id = request('shop_id');
      //   $ledgerPayments->ledger_id = $ledger->id;
      //   $ledgerPayments->method = $payment['method'];
      //   $ledgerPayments->status = $payment['status'];
      //   $ledgerPayments->amount = $payment['amount'];
      //   $ledgerPayments->bank_name = $payment['bank_name'];
      //   $ledgerPayments->transaction_id = $payment['transaction_id'];
      //   $ledgerPayments->cheque_number = $payment['cheque_number'];
      //   $ledgerPayments->save();
      // }
      DB::commit();
      $response['success'] = true;
      $response['id'] = $ledger->id;
    } catch (\Exception $e) {
      Log::error($e);
      $response['message'] = $e->getMessage();
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

    $ledger = Ledger::where('id', request('ledger_id'))->where('shop_id', request('shop_id'))->with('customer')->with('products')->with('payments')->get()->first();
    if (!$ledger) {
      return response()->json(['error' => 'Unable to find ledger.'], Response::HTTP_BAD_REQUEST);
    }
    return response()->json(['entry' => $ledger, 'products' => $ledger->products]);
  }

  public function delete()
  {
    $user = $this->getUser(request());
    if (!$user) {
      return response()->json(['error' => 'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $shop = $user->shops()->where('shop_id', request('shop_id'))->first();
    if (!$shop) {
      return response()->json(['error' => 'Shop not found'], Response::HTTP_BAD_REQUEST);
    }

    try {
      DB::beginTransaction();
      Ledger::where('id', request('ledger_id'))->where('shop_id', request('shop_id'))->delete();
      LedgerProducts::where('ledger_id', request('ledger_id'))->delete();
      LedgerPayments::where('ledger_id', request('ledger_id'))->delete();  
      DB::commit();
    } catch (\Exception $e) {
      Log::error($e);
      DB::rollback();
      return response()->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
    }
    return response()->json(['success' => true]);
  }

  public function viewPdf()
  {
    $user = $this->getUser(request());
    if(!$user){
      return response()->json(['error'=>'Please Login'], Response::HTTP_BAD_REQUEST);
    }

    $ledger = Ledger::where('id', request('ledger_id'))->where('shop_id', request('shop_id'))->get()->first();
    if (!$ledger) {
      return response()->json(['error' => 'Unable to find ledger.'], Response::HTTP_BAD_REQUEST);
    }
    $shop = Shops::where('id', request('shop_id'))->get()->first();
    if (request('html')) {
      return view('pdf.receipt', ['shop' => $shop, 'entry' => $ledger, 'products' => $ledger->products]);
    }
    $pdf = App::make('dompdf.wrapper');
    $pdf->loadHTML(view('pdf.receipt', ['shop' => $shop, 'entry' => $ledger, 'products' => $ledger->products]));
    return $pdf->stream();
  }

  public function viewInvoice()
  {
    $user = $this->getUser(request());
    if(!$user){
      return response()->json(['error'=>'Please Login'], Response::HTTP_BAD_REQUEST);
    }

    $ledger = Ledger::where('id', request('ledger_id'))->where('shop_id', request('shop_id'))->get()->first();
    if (!$ledger) {
      return response()->json(['error' => 'Unable to find ledger.'], Response::HTTP_BAD_REQUEST);
    }
    $shop = Shops::where('id', request('shop_id'))->get()->first();
    if (request('html')) {
      return view('pdf.invoice', ['shop' => $shop, 'entry' => $ledger, 'products' => $ledger->products]);
    }
    $pdf = App::make('dompdf.wrapper');
    $pdf->loadHTML(view('pdf.invoice', ['shop' => $shop, 'entry' => $ledger, 'products' => $ledger->products]));
    return $pdf->stream();
  }

  public function index()
  {
    $user = $this->getUser(request());
    if (!$user) {
      return response()->json(['error' => 'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $shop = $user->shops()->where('shop_id', request('shop_id'))->first();
    if (!$shop) {
      return response()->json(['error' => 'Shop not found'], Response::HTTP_BAD_REQUEST);
    }
    $ledgers = Ledger::with('customer')->with('products')->with('payments')->where("shop_id", request("shop_id"));
    if (isset($_GET['from']) && !empty($_GET['from'])) {
      $ledgers->whereDate("created_at", '>=', date('Y-m-d', strtotime($_GET['from'])));
    }
    if (isset($_GET['to']) && !empty($_GET['to'])) {
      $ledgers->whereDate("created_at", '<=', date('Y-m-d', strtotime($_GET['to'])));
    }
    if (isset($_GET['customer']) && !empty($_GET['customer']) && !(isset($_GET['customerId']) && !empty($_GET['customerId']))) {
      $ledgers->where("customer_name", 'like', '%' . $_GET['customer'] . '%');
    }
    if (isset($_GET['customer']) && !empty($_GET['customer']) && (isset($_GET['customerId']) && !empty($_GET['customerId']))) {
      $ledgers->where("customer_id", $_GET['customerId']);
    }
    // if (isset($_GET['paymentStatus']) && !empty($_GET['paymentStatus'])) {
    //   $ledgers->where("payment_status", $_GET['paymentStatus']);
    // }
    $ledgers = $ledgers->orderBy('created_at', 'desc')->get();
    if (isset($_GET['print']) && $_GET['print'] == 'false') {
      return response()->json(['entries' => $ledgers]);
    }
    $pdf = App::make('dompdf.wrapper');
    $pdf->loadHTML(view('pdf.ledger', ['entries' => $ledgers]));
    return $pdf->stream();
  }

  public function move_existing_receiving_in_new()
  {
    $ledgers = Ledger::get();
    LedgerPayments::truncate();
    $result = [];
    foreach ($ledgers as $ledger) {
      $LedgerPayments = new LedgerPayments();
      $LedgerPayments->shop_id = $ledger->shop_id;
      $LedgerPayments->ledger_id = $ledger->id;
      $LedgerPayments->method = $ledger->payment_method;
      $LedgerPayments->status = $ledger->payment_status;
      $LedgerPayments->amount = $ledger->amount_received;
      $LedgerPayments->bank_name = $ledger->bank_name;
      $LedgerPayments->transaction_id = $ledger->transaction_id;
      $LedgerPayments->cheque_number = $ledger->cheque_number;
      if ($LedgerPayments->save()) {
        $result[] = "Success In " . $ledger->id;
      } else {
        $result[] = "Error In " . $ledger->id;
      }
    }
    return $result;
  }

  public function delete_receiving() {
    LedgerPayments::where('id', request('receiving_id'))->where('ledger_id', request('ledger_id'))->where('shop_id', request('shop_id'))->delete();
    return response()->json(['success' => true]);
  }

  public function wordpress_webhook_new() {
    $response = [
      'success' => false
    ];
    Log::error(json_encode(request('fee_lines')));
    Log::error(json_encode(request('line_items')));
    Log::error(json_encode(request('shipping_lines')));

    $old_entry = Ledger::where([
      'shop_id' => request('shop_id'),
      'remote_order_id' => request('id')
    ])->get()->first();
  
    if ($old_entry) {
      $response['message'] = 'Order #'.request('id').' Already Exist';
      return response()->json($response);
    }

    try {
      DB::beginTransaction();
      $ledger = new Ledger();
      $ledger->shop_id = request('shop_id');
      $ledger->type = 'credit';
      $ledger->customer_name = request('billing')['first_name'] . ' '. request('billing')['last_name'];
      $ledger->customer_id = 0;
      $ledger->total = request('total');
      $ledger->created_at = date('Y-m-d H:i:s');
      $ledger->note = request('note');
      $ledger->remote_order_id = request('id');
      $ledger->save();
      $ledger_id = $ledger->id;


      // if ($payments = request('payments')) {
      //   foreach ($payments as $key => $payment) {

      //   }
      // }

      $ledgerPayments = new LedgerPayments();
      $ledgerPayments->shop_id = request('shop_id');
      $ledgerPayments->ledger_id = $ledger_id;
      $ledgerPayments->method = request('payment_method')?request('payment_method'):'N/A';
      $ledgerPayments->status = request('date_completed') != null ? 'received' : 'pending';
      $ledgerPayments->amount = request('total');
      $ledgerPayments->bank_name = request('payment_method_title');
      $ledgerPayments->transaction_id = request('transaction_id');
      $ledgerPayments->save();

      if ($products = request('line_items')) {
        foreach ($products as $key => $product) {
          $total_quantity = $product['quantity'];
          $sku = $product['sku'];
          $product_name = $product['name'];
          $total_price = $product['total'];

          $system_product = Products::where([
            'sku' => $sku,
            'shop_id' => request('shop_id')
          ])->get()->first();
          
          if ($system_product) {
            $reduce_quantity_from_batches = [];
            $batches = $system_product->batches();
            if ($batches->count()) {
              $quantity_after_deduct = $total_quantity;
              foreach ($batches->get() as $batch) {
                if ($batch->quantity <= 0) {
                  continue;
                }
                Log::error('quantity_after_deduct > '.$quantity_after_deduct);
                Log::error('batch->quantity > '.$batch->quantity);

                if ($quantity_after_deduct >= $batch->quantity) {
                  $new_quantity_after_deduct = $quantity_after_deduct - $batch->quantity;
                  $reduce_quantity_from_batches[] = [
                    'batch_id' => $batch->id,
                    'quantity' => $batch->quantity
                  ];
                  $quantity_after_deduct = $new_quantity_after_deduct;
                } else {
                  $new_quantity_after_deduct = $batch->quantity - $quantity_after_deduct;
                  $reduce_quantity_from_batches[] = [
                    'batch_id' => $batch->id,
                    'quantity' => $quantity_after_deduct
                  ];
                  $quantity_after_deduct = 0;
                }
                Log::error($quantity_after_deduct);

                // if ($batch->quantity >= $quantity_after_deduct) {
                //   $quantity_after_deduct -= $batch->quantity;
                //   $reduce_quantity_from_batches[] = [
                //     'batch_id' => $batch->id,
                //     'quantity' => $batch->quantity
                //   ];
                // }

                // if ($quantity_after_deduct > 0 && $batch->quantity < $quantity_after_deduct) {
                //   $quantity_after_deduct =  $quantity_after_deduct - $batch->quantity;
                //   $reduce_quantity_from_batches[] = [
                //     'batch_id' => $batch->id,
                //     'quantity' => $batch->quantity
                //   ];
                // }
              }

              if ($quantity_after_deduct > 0) {
                $last_batch = $batches->orderBy('id', 'desc')->first();
                $reduce_quantity_from_batches[] = [
                  'batch_id' => $last_batch->id,
                  'quantity' => $quantity_after_deduct,
                ];
              }
              Log::error(json_encode($reduce_quantity_from_batches));

              foreach ($reduce_quantity_from_batches as $reduce_quantity_from_batch) {
                ProductsBatches::where('id', $reduce_quantity_from_batch['batch_id'])->decrement('quantity', $reduce_quantity_from_batch['quantity']);
              }
            }
            if ($reduce_quantity_from_batches && count($reduce_quantity_from_batches)) {
              foreach ($reduce_quantity_from_batches as $reduce_quantity_from_batch) {
                LedgerProducts::create([
                  'ledger_id' => $ledger_id,
                  'product_id' => $system_product->id,
                  'batch_id' => $reduce_quantity_from_batch['batch_id'],
                  'quantity' => $reduce_quantity_from_batch['quantity'],
                  'rate' => $total_price,
                  'product_name' => ($product_name ? $product_name : 'N/A')
                ]);
              }
            } else {
              LedgerProducts::create([
                'ledger_id' => $ledger_id,
                'product_id' => $system_product->id,
                'quantity' => $total_quantity,
                'rate' => $total_price,
                'product_name' => ($product_name ? $product_name : 'N/A')
              ]);
            }
          } else {
            LedgerProducts::create([
              'ledger_id' => $ledger_id,
              'quantity' => $total_quantity,
              'rate' => $total_price,
              'product_name' => ($product_name ? $product_name : 'N/A')
            ]);
          }
        }
      }

      if ($shipping_lines = request('shipping_lines')) {
        foreach ($shipping_lines as $shipping_line) {
          LedgerProducts::create([
            'ledger_id' => $ledger_id,
            'quantity' => 1,
            'rate' => $shipping_line['total'],
            'product_name' => $shipping_line['name']
          ]);
        }
      }
      $fees = request('fee_lines');
      if ($fees && $fees != null) {
        foreach ($fees as $fee) {
          LedgerProducts::create([
            'ledger_id' => $ledger_id,
            'quantity' => 1,
            'rate' => $fee['total'],
            'product_name' => $fee['name']
          ]);
        }
      }

      DB::commit();
      $response['success'] = true;
      $response['id'] = $ledger->id;
    } catch (\Exception $e) {
      Log::error($e);
      $response['message'] = $e->getMessage();
      DB::rollback();
    }
    return response()->json($response);
  }

  public function wordpress_webhook_update() {
    $response = [
      'success' => false
    ];
    try {
      DB::beginTransaction();
      Log::error(print_r(request()));
      $ledger = new Ledger();
      $ledger->shop_id = request('shop_id');
      $ledger->type = 'credit';
      $ledger->customer_name = request('billing')['first_name'] + ' '+ request('billing')['last_name'];
      $ledger->customer_id = 0;
      $ledger->total = request('total');
      $ledger->created_at = date('Y-m-d H:i:s');
      $ledger->note = request('note');
      $ledger->save();
      $ledger_id = $ledger->id;


      // if ($payments = request('payments')) {
      //   foreach ($payments as $key => $payment) {

      //   }
      // }

      $ledgerPayments = new LedgerPayments();
      $ledgerPayments->shop_id = request('shop_id');
      $ledgerPayments->ledger_id = $ledger_id;
      $ledgerPayments->method = request('payment_method');
      $ledgerPayments->status = request('date_completed') != null ? 'received' : 'pending';
      $ledgerPayments->amount = request('total');
      $ledgerPayments->bank_name = request('payment_method_title');
      $ledgerPayments->transaction_id = request('transaction_id');
      $ledgerPayments->save();

      if ($products = request('line_items')) {
        foreach ($products as $key => $product) {
          $total_quantity = $product['quantity'];
          $sku = $product['sku'];
          $product_name = $product['name'];
          $total_price = $product['total'];

          $system_product = Products::where([
            'sku' => $sku,
            'shop_id' => request('shop_id')
          ])->get()->first();
          
          if ($system_product) {
            $reduce_quantity_from_batches = [];
            $batches = $system_product->batches();
            if ($batches->count()) {
              $quantity_after_deduct = $total_quantity;
              foreach ($batches->get() as $batch) {
                if ($batch->quantity >= $quantity_after_deduct) {
                  $reduce_quantity_from_batches[] = [
                    'batch_id' => $batch->id,
                    'quantity' => $total_quantity
                  ];
                  $quantity_after_deduct -= $quantity_after_deduct;
                }

                if ($quantity_after_deduct > 0 && $batch->quantity < $quantity_after_deduct) {
                  $reduce_quantity_from_batches[] = [
                    'batch_id' => $batch->id,
                    'quantity' => $total_quantity
                  ];
                  $quantity_after_deduct -= $batch->quantity;
                }
              }

              if ($quantity_after_deduct > 0) {
                $last_batch = $batches->orderBy('id', 'desc')->first();
                $reduce_quantity_from_batches[] = [
                  'batch_id' => $last_batch->id,
                  'quantity' => $total_quantity,
                ];
              }

              foreach ($reduce_quantity_from_batches as $reduce_quantity_from_batch) {
                $batches->where('id', $reduce_quantity_from_batch['batch_id'])->decrement('quantity', $reduce_quantity_from_batch['quantity']);
              }
            }
            if ($reduce_quantity_from_batches && count($reduce_quantity_from_batches)) {
              foreach ($reduce_quantity_from_batches as $reduce_quantity_from_batch) {
                LedgerProducts::create([
                  'ledger_id' => $ledger_id,
                  'product_id' => $system_product->id,
                  'batch_id' => $reduce_quantity_from_batch['batch_id'],
                  'quantity' => $reduce_quantity_from_batch['quantity'],
                  'rate' => $total_price,
                  'product_name' => $product_name
                ]);
              }
            } else {
              LedgerProducts::create([
                'ledger_id' => $ledger_id,
                'product_id' => $system_product->id,
                'quantity' => $total_quantity,
                'rate' => $total_price,
                'product_name' => $product_name
              ]);
            }
          } else {
            LedgerProducts::create([
              'ledger_id' => $ledger_id,
              'quantity' => $total_quantity,
              'rate' => $total_price,
              'product_name' => $product_name
            ]);
          }
        }
      }

      if ($shipping_lines = request('shipping_lines')) {
        foreach ($shipping_lines as $shipping_line) {
          LedgerProducts::create([
            'ledger_id' => $ledger_id,
            'quantity' => 1,
            'rate' => $shipping_line['total'],
            'product_name' => $shipping_line['method_title']
          ]);
        }
      }
      DB::commit();
      $response['success'] = true;
      $response['id'] = $ledger_id;
    } catch (\Exception $e) {
      Log::error($e);
      $response['message'] = $e->getMessage();
      DB::rollback();
    }

  }
}
