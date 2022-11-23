<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
  protected $fillable = [
    'sku', 'name', 'barcode', 'cost', 'price', 'description', 'category_id', 'sold_by_weight', 'stock', 'status'
  ];

  public function category() {
    return $this->hasOne(Category::class, 'id', 'category_id');
  }
}
