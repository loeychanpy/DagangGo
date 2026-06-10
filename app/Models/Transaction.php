<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne; 

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'invoice_number',
        'user_id',
        'total_price',
        'pay_amount',
        'change_amount',
        'subtotal',
        'discount',
        'payment_method',
        'status',
        'remaining_bill',
        'due_date',
    ];

    protected $casts = [
        'due_date'       => 'date',
        'total_price'    => 'decimal:2',
        'subtotal'       => 'decimal:2',
        'discount'       => 'decimal:2',
        'pay_amount'     => 'decimal:2',
        'change_amount'  => 'decimal:2',
        'remaining_bill' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(TransactionPayment::class);
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }
}
