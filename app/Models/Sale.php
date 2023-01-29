<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id', 'daily_sale_id', 'reference', 'total_quantity', 'total_amount', 'paid_amount', 'change_amount', 'type'
  ];

  public function items() {
    return $this->hasMany(SaleItem::class);
  }

  public function user() {
    return $this->belongsTo(User::class);
  }
}
