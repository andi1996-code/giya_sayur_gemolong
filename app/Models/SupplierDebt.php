<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierDebt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'supplier_name',
        'supplier_phone',
        'supplier_address',
        'transaction_type',
        'amount',
        'description',
        'transaction_date',
        'due_date',
        'status',
        'paid_amount',
        'remaining_amount',
        'notes',
        'user_id',
        'product_id',
        'quantity',
        'unit',
        'stock_type'
    ];

    protected $casts = [
        'amount' => 'integer',
        'paid_amount' => 'integer',
        'remaining_amount' => 'integer',
        'transaction_date' => 'date',
        'due_date' => 'date',
        'quantity' => 'decimal:2',
    ];

    protected $attributes = [
        'paid_amount' => 0,
        'remaining_amount' => 0,
        'notes' => '',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Accessors & Mutators
    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function getFormattedPaidAmountAttribute()
    {
        return 'Rp ' . number_format($this->paid_amount, 0, ',', '.');
    }

    public function getFormattedRemainingAmountAttribute()
    {
        return 'Rp ' . number_format($this->remaining_amount, 0, ',', '.');
    }

    // Helper Methods
    public function updateStatus()
    {
        if ($this->paid_amount >= $this->amount) {
            $this->status = 'lunas';
            $this->remaining_amount = 0;
        } elseif ($this->paid_amount > 0) {
            $this->status = 'sebagian_lunas';
            $this->remaining_amount = $this->amount - $this->paid_amount;
        } else {
            $this->status = 'belum_lunas';
            $this->remaining_amount = $this->amount;
        }

        $this->save();
    }

    public function isOverdue()
    {
        if (!$this->due_date || $this->status === 'lunas') {
            return false;
        }

        return $this->due_date < now()->toDateString() && $this->status !== 'lunas';
    }

    // Scopes
    public function scopeHutang($query)
    {
        return $query->where('transaction_type', 'hutang');
    }

    public function scopePiutang($query)
    {
        return $query->where('transaction_type', 'piutang');
    }

    public function scopeBelumLunas($query)
    {
        return $query->whereIn('status', ['belum_lunas', 'sebagian_lunas']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now()->toDateString())
                    ->whereIn('status', ['belum_lunas', 'sebagian_lunas']);
    }

    // Mutators to ensure NULL values are handled
    public function setPaidAmountAttribute($value)
    {
        $this->attributes['paid_amount'] = $value ?? 0;
    }

    public function setNotesAttribute($value)
    {
        $this->attributes['notes'] = $value ?? '';
    }
}
