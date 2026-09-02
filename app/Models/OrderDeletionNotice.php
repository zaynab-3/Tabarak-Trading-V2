<?php

namespace App\Models;

use App\Enums\OrderDeletionMode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDeletionNotice extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'public_token', 'customer_name', 'customer_phone', 'order_status',
        'currency', 'subtotal', 'total', 'deletion_mode', 'restored_quantity', 'items',
        'submitted_at', 'completed_at', 'deleted_by', 'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'deletion_mode' => OrderDeletionMode::class,
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
