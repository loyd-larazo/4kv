<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
  protected $fillable = [
    'transaction_id', 'item_id', 'supplier_id', 'quantity', 'amount', 'total_amount'
  ];

  public function item() {
    return $this->belongsTo(Item::class);
  }

  public function supplier() {
    return $this->belongsTo(Supplier::class);
  }
}
