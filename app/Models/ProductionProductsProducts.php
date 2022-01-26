<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionProductsProducts extends Model
{
  use HasFactory;
  protected $table = 'production_product_products';
  protected $fillable = [
    'production_product_id',
    'product_id',
    'one_product_quantity',
  ];
  public function product() {
    return $this->hasOne(Products::class,'production_product_id','id');
  }
}
