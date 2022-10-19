<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsBatches extends Model
{
    use HasFactory;
    protected $fillable = [
      'product_id',
      'purchased_at',
      'purchasing_price',
      'selling_price',
      'quantity',
      'measurement_unit',
      'expire_at',
      'status',
      'delivery_at',
      'purchase_from_id'
    ];
    public function purchase_from() {
      return $this->hasOne(Customers::class,'id', 'purchase_from_id');
    }
}
