<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DamageItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id', 'item_id', 'quantity', 'amount', 'total_amount'
    ];
    
    public function item() {
        return $this->belongsTo(Item::class);
    }
}
