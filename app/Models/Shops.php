<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shops extends Model
{
    use HasFactory;
    public function products() {
      return $this->hasMany(Products::class, 'shop_id');
    }
    public function invitations() {
      return $this->hasMany(Invitations::class, 'shop_id');
    }
    public function users() {
      return $this->hasMany(ShopUsers::class, 'shop_id');
    }
}
