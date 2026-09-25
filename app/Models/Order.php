<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        '_id',
        'orderNumber',
        'user_id',
        'customerName',
        'email',
        'totalPrice',
        'amountDiscount',
        'currency',
        'status',
        'orderDate',
        'stripeCheckoutSessionId',
        'stripeCustomerId',
        'stripePaymentIntentId',
        'invoice',
        'address',
    ];

    protected $casts = [
        'invoice' => 'array',
        'address' => 'array',
        'orderDate' => 'datetime',
        'totalPrice' => 'float',
        'amountDiscount' => 'float',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
