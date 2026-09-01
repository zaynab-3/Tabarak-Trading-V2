<?php

namespace App\Models;

use App\Enums\ImportItemStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'import_batch_id', 'media_id', 'status', 'suggested_name', 'suggested_brand',
        'suggested_category', 'suggested_weight', 'suggested_metadata', 'confidence',
        'warnings', 'provider_metadata', 'approved_product_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => ImportItemStatus::class,
            'suggested_metadata' => 'array',
            'warnings' => 'array',
            'provider_metadata' => 'array',
            'confidence' => 'decimal:4',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function approvedProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'approved_product_id');
    }
}
