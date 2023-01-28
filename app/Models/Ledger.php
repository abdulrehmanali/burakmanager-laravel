<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ledger extends Model
{
  use HasFactory;
  protected $fillable = [
    'shop_id',
    'type',
    'payment_method',
    'payment_status',
    'amount_received',
    'total',
    'customer_name',
    'customer_id',
    'note',
    'bank_name',
    'transaction_id',
    'cheque_number'
  ];
  public function products() {
    return $this->hasMany(LedgerProducts::class);
  }
  public function customer() {
    return $this->hasOne(Customers::class, 'id', 'customer_id');
  }
  public function payments() {
    return $this->hasMany(LedgerPayments::class, 'ledger_id', 'id');
  }
  public function amount_received() {
    $amount_received = 0.00;
    foreach ($this->payments as $receiving) {
      $amount_received += (float)$receiving->amount;
    }
    return $amount_received;
  }
  public function toArray()
  {
    $array = parent::toArray();
    $array['amount_received'] = $this->amount_received();
    return $array;
  }
}
