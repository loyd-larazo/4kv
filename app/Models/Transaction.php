<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
  protected $fillable = [
    'transaction_code', 'user_id', 'stock_man', 'total_quantity', 'total_amount', 'remarks'
  ];

  public function items() {
    return $this->hasMany(TransactionItem::class);
  }
}
