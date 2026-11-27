<?php

namespace App\Models;

use App\Enums\OrderDeletionMode;
use App\Enums\OrderPaymentMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDeletionNotice extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'public_token', 'customer_name', 'customer_phone',
        'purchase_order_number', 'billing_address', 'shipping_address',
        'delivery_date', 'payment_method', 'check_number', 'order_status',
        'currency', 'subtotal', 'total', 'deletion_mode', 'restored_quantity', 'items',
        'submitted_at', 'completed_at', 'deleted_by', 'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'deletion_mode' => OrderDeletionMode::class,
            'payment_method' => OrderPaymentMethod::class,
            'delivery_date' => 'date',
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'restored_quantity' => 'integer',
            'items' => 'array',
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
            'recorded_at' => 'datetime',
        ];
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
