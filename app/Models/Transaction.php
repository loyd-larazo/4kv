<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
  protected $fillable = [
    'transaction_code', 'total_quantity', 'total_amount', 'laborer_id', 'remarks'
  ];

  public function items() {
    return $this->hasMany(TransactionItem::class);
  }

  public function laborer() {
    return $this->hasOne(Laborer::class, 'id', 'laborer_id');
  }
}
