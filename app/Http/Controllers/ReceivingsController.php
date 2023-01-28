<?php

namespace App\Http\Controllers;

use App\Models\Ledger;
use App\Models\LedgerPayments;
use App\Models\Receivings;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\LedgerProducts;
use Validator;

class ReceivingsController extends Controller
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
      'method' => 'required',
      'status' => 'required',
      'amount' => 'required',
      'bank_name' => 'required_if:method,bankTransfer',
      'transaction_id' => 'required_if:method,bankTransfer',
      'cheque_number' => 'required_if:method,cheque',
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
      $receiving = new Receivings();
      $receiving->shop_id = request('shop_id');
      $receiving->customer_id = request('customer_id');
      $receiving->created_by = $user->id;
      $receiving->last_edit_by = $user->id;
      $receiving->method = request('method');
      $receiving->status = request('status');
      $receiving->amount = request('amount');
      $receiving->bank_name = request('bank_name');
      $receiving->transaction_id = request('transaction_id');
      $receiving->cheque_number = request('cheque_number');
      $receiving->save();
      $response['success'] = true;
      $response['id'] = $receiving->id;

      DB::commit();
    } catch (\Exception $e) {
      Log::error($e);
      $response['message'] = $e->getMessage();
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
    $validation_args = [
      'method' => 'required',
      'status' => 'required',
      'amount' => 'required',
      'bank_name' => 'required_if:method,bankTransfer',
      'transaction_id' => 'required_if:method,bankTransfer',
      'cheque_number' => 'required_if:method,cheque',
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
      $receiving = Receivings::find(request('receiving_id'));
      $receiving->shop_id = request('shop_id');
      $receiving->customer_id = request('customer_id');
      $receiving->last_edit_by = $user->id;
      $receiving->method = request('method');
      $receiving->status = request('status');
      $receiving->amount = request('amount');
      $receiving->bank_name = request('bank_name');
      $receiving->transaction_id = request('transaction_id');
      $receiving->cheque_number = request('cheque_number');
      $receiving->save();
      $response['success'] = true;
      $response['id'] = $receiving->id;
      DB::commit();
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
    $receiving = Receivings::with('ledger_payments')->with('customer')->where('id', request('receiving_id'))->where('shop_id', request('shop_id'))->get()->first();
    if (!$receiving) {
      return response()->json(['error' => 'Unable to find receiving.'], Response::HTTP_BAD_REQUEST);
    }
    $deductedIn = [];
    $usedIn = [];

    $already_used_amount = 0;
    if ($ledger_payments = $receiving->ledger_payments) {
      foreach ($ledger_payments as $key => $ledger_payment) {
        $credit_ledger_entry = $ledger_payment->ledger()->where('ledgers.type', '=', 'credit')->with('payments')->get();
        if ($credit_ledger_entry) {
          $deductedIn[] = $credit_ledger_entry;
          $already_used_amount += $ledger_payment->amount;
        }
        $used_in_ledger_entry = $ledger_payment->ledger()->where('ledgers.type', '=', 'debit')->with('payments')->get();
        if ($used_in_ledger_entry) {
          $usedIn[] = $used_in_ledger_entry;
        }
      }
    }

    $entries = Ledger::leftJoin('ledger_payments',function ($join) {
      $join->on('ledger_payments.ledger_id', '=' , 'ledgers.id');
      $join->whereNull('ledger_payments.receiving_id');
      $join->whereNull('ledger_payments.status');
    })->select([
      'ledgers.*',
      DB::raw("sum(ledger_payments.amount) as total_received")
    ])
    ->groupBy('ledgers.id')
    ->where('ledgers.type', '=', 'credit')
    ->get();

    $totalAvailable = ((float)$receiving->amount - $already_used_amount);
    $possibleEntries = [];
    foreach ($entries as $key => $entry) {
      if ($totalAvailable == 0) {
        break;
      }
      $pending = $entry->total - $entry->amount_received();
      if ( $pending <=  $totalAvailable) {
        $totalAvailable -= $pending;
        $entries[$key]['totalDeducted'] = $pending;
      } else {
        $pending -= $totalAvailable;
        $entries[$key]['totalDeducted'] = $pending;
        $totalAvailable = 0;
      }
      if ($entries[$key]['totalDeducted'] > 0) {
        $possibleEntries[] = $entries[$key];
      }
    }
    return response()->json(['receiving' => $receiving, 'usedIn' => $usedIn, 'deductedIn' => $deductedIn, 'entries' => $possibleEntries, 'alreadyUsedAmount'=>$already_used_amount]);
  }


  public function batchDeduct()
  {
    $user = $this->getUser(request());
    if (!$user) {
      return response()->json(['error' => 'Please Login'], Response::HTTP_BAD_REQUEST);
    }
    $shop = $user->shops()->where('shop_id', request('shop_id'))->first();
    if (!$shop) {
      return response()->json(['error' => 'Shop not found'], Response::HTTP_BAD_REQUEST);
    }
    $receiving = Receivings::with('ledger_payments')->with('customer')->where('id', request('receiving_id'))->where('shop_id', request('shop_id'))->get()->first();
    if (!$receiving) {
      return response()->json(['error' => 'Unable to find receiving.'], Response::HTTP_BAD_REQUEST);
    }
    $deductedIn = [];
    $usedIn = [];

    $already_used_amount = 0;
    if ($ledger_payments = $receiving->ledger_payments) {
      foreach ($ledger_payments as $key => $ledger_payment) {
        $credit_ledger_entry = $ledger_payment->ledger()->where('ledgers.type', '=', 'credit')->with('payments')->get();
        if ($credit_ledger_entry) {
          $deductedIn[] = $credit_ledger_entry;
          $already_used_amount += $ledger_payment->amount;
        }
        $used_in_ledger_entry = $ledger_payment->ledger()->where('ledgers.type', '=', 'debit')->with('payments')->get();
        if ($used_in_ledger_entry) {
          $usedIn[] = $used_in_ledger_entry;
        }
      }
    }

    $entries = Ledger::leftJoin('ledger_payments',function ($join) {
      $join->on('ledger_payments.ledger_id', '=' , 'ledgers.id');
      $join->whereNull('ledger_payments.receiving_id');
      $join->whereNull('ledger_payments.status');
    })->select([
      'ledgers.*',
      DB::raw("sum(ledger_payments.amount) as total_received")
    ])
    ->groupBy('ledgers.id')
    ->where('ledgers.type', '=', 'credit')
    ->get();

    $totalAvailable = ((float)$receiving->amount - $already_used_amount);
    $possibleEntries = [];
    foreach ($entries as $key => $entry) {
      if ($totalAvailable == 0) {
        break;
      }
      $pending = $entry->total - $entry->amount_received();
      if ( $pending <=  $totalAvailable) {
        $totalAvailable -= $pending;
        $entries[$key]['totalDeducted'] = $pending;
      } else {
        $pending -= $totalAvailable;
        $entries[$key]['totalDeducted'] = $pending;
        $totalAvailable = 0;
      }
      if ($entries[$key]['totalDeducted'] > 0) {
        $possibleEntries[] = $entries[$key];
      }
    }

    foreach ($possibleEntries as $pe) {
      $total_deducted_amount = (($pe->total - $pe->amount_received()) - $pe['totalDeducted']);
      if ($total_deducted_amount == 0) {
        $total_deducted_amount = $pe['totalDeducted'];
      }
      $ledgerPayments = new LedgerPayments();
      $ledgerPayments->shop_id = request('shop_id');
      $ledgerPayments->ledger_id = $pe->id;
      $ledgerPayments->method = $receiving->method;
      $ledgerPayments->status = $receiving->status;
      $ledgerPayments->amount = $total_deducted_amount;
      $ledgerPayments->bank_name = $receiving->bank_name;
      $ledgerPayments->transaction_id = $receiving->transaction_id;
      $ledgerPayments->cheque_number = $receiving->cheque_number;
      $ledgerPayments->receiving_id = $receiving->id;
      $ledgerPayments->save();
    }

    return response()->json(['success' => true]);

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

    $receiving = Receivings::where('id', request('receiving_id'))->where('shop_id', request('shop_id'));
    if (!$receiving->delete()) {
      return response()->json(['error' => 'Unable to delete ledger.'], Response::HTTP_BAD_REQUEST);
    }
    return response()->json(['success' => true]);
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
    $receiving = Receivings::with('customer')->where("shop_id", request("shop_id"));
    if (isset($_GET['customerId']) && !empty($_GET['customerId'])) {
      $receiving->where("customer_id", $_GET['customerId']);
    }
    if (isset($_GET['paymentStatus']) && !empty($_GET['paymentStatus'])) {
      $receiving->where("payment_status", $_GET['paymentStatus']);
    }
    $receiving = $receiving->orderBy('created_at', 'desc')->get();
    return response()->json(['receivings' => $receiving]);
  }
}
