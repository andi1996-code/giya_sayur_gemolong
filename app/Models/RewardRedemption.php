<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RewardRedemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'reward_item_id', // kolom lama (nullable)
        'product_id',     // kolom baru (digunakan)
        'points_used',
        'quantity',
        'transaction_id',
        'status',
        'notes',
        'processed_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'points_used' => 'integer',
    ];

    /**
     * Member yang menukar
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Product (reward item) yang ditukar
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Transaction terkait (jika ada)
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Kasir yang memproses
     */
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
