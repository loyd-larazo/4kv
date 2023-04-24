<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnTransaction extends Model
{
    protected $fillable = [
        'user_id', 'transaction_id', 'quantity', 'amount', 'total_amount', 'status'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function transaction() {
        return $this->belongsTo(Transaction::class);
    }

    public function transactionItems() {
        return $this->hasMany(TransactionItem::class);
    }
}
