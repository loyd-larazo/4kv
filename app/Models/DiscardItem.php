<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscardItem extends Model
{
    protected $fillable = [
        'user_id', 'item_id', 'supplier_id', 'quantity', 'amount', 'total_amount'
    ];

    public function item() {
        return $this->belongsTo(Item::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function supplier() {
        return $this->belongsTo(Supplier::class);
    }
}
