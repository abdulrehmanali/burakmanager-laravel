<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receivings extends Model
{
  use HasFactory;
  public function ledger_payments() {
    return $this->hasMany(LedgerPayments::class, 'receiving_id', 'id');
  }
  public function customer() {
    return $this->hasOne(Customers::class,'id','customer_id');
  }
}
