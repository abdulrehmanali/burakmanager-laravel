<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LedgerPayments extends Model
{
  use HasFactory;
  protected $fillable = [
    'method',
    'status',
    'amount',
    'bank_name',
    'transaction_id',
    'cheque_number',
    'shop_id',
    'ledger_id',
    'receiving_id'
  ];

  public function ledger() {
    return $this->hasOne(Ledger::class, 'id', 'ledger_id');
  }
}
