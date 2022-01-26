<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitations extends Model
{
  use HasFactory;
  protected $fillable = [
    'shop_id',
    'email',
    'token',
    'expire_at',
    'accepted_at',
    'can_create_entries_in_ledger',
    'can_create_customers',
    'can_create_products',
    'can_edit_entries_in_ledger',
    'can_edit_customers',
    'can_edit_products',
  ];

  public function shop() {
    return $this->hasOne(Shops::class,'id', 'shop_id');
  }
}
