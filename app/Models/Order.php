<?php

namespace App\Models;

use App\Enums\OrderPaymentMethod;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'public_token', 'customer_name', 'customer_phone',
        'purchase_order_number', 'billing_address', 'shipping_address',
        'delivery_date', 'payment_method', 'check_number',
        'status', 'currency', 'subtotal', 'total', 'submitted_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_method' => OrderPaymentMethod::class,
            'delivery_date' => 'date',
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getRouteKeyName(): string
    {
        return 'public_token';
    }
}
