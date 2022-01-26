<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customers extends Model
{
  use HasFactory;
  protected $fillable = [
    'name',
    'email',
    'phone_number',
    'shop_id',
    'company_name',
    'ntn',
    'address',
  ];
}
