<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PointTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'transaction_id',
        'type',
        'points',
        'balance_before',
        'balance_after',
        'transaction_amount',
        'description',
        'expired_at',
    ];

    protected $casts = [
        'transaction_amount' => 'decimal:2',
        'expired_at' => 'date',
    ];

    /**
     * Relationships
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get type display name
     */
    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            'earned' => 'Diperoleh',
            'redeemed' => 'Ditukar',
            'expired' => 'Kadaluarsa',
            'adjusted' => 'Penyesuaian',
            default => $this->type,
        };
    }

    /**
     * Get type badge color
     */
    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'earned' => 'success',
            'redeemed' => 'warning',
            'expired' => 'danger',
            'adjusted' => 'info',
            default => 'secondary',
        };
    }
}
