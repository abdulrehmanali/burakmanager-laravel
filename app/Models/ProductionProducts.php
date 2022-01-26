<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionProducts extends Model
{
  use HasFactory;
  protected $fillable = [
    'name',
    'shop_id',
    'price',
    'sku',
  ];
  public function products() {
    return $this->hasMany(ProductionProductsProducts::class,'id','production_product_id');
  }
}
