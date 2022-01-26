<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LedgerProducts extends Model
{
  use HasFactory;
  protected $fillable = [
    'ledger_id',
    'product_name',
    'product_id',
    'batch_id',
    'quantity',
    'rate'
  ];
  public function product() {
    return $this->hasMany(Products::class,'id','product_id');
  }
}
