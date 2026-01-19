<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payment_method_id', 'member_id', 'transaction_number', 'name', 'email', 'phone',
        'address', 'notes', 'total', 'cash_received', 'change', 'promo_discount',
        'points_earned', 'points_redeemed', 'points_discount'
    ];

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function products()
    {
        return $this->transactionItems()->with('product');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class);
    }

}
