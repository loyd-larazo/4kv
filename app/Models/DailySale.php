<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailySale extends Model
{
  use HasFactory;

  protected $fillable = [
    'opening_user_id', 'closing_user_id', 'sales_count', 'sales_amount', 'opening_amount', 'closing_amount', 'difference_amount'
  ];

  public function sales() {
    return $this->hasMany(Sale::class)->where('type', 'sales');
  }

  public function openingUser() {
    return $this->belongsTo(User::class, 'opening_user_id', 'id');
  }

  public function closingUser() {
    return $this->belongsTo(User::class, 'closing_user_id', 'id');
  }
}
