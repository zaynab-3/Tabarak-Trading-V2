<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;

    protected $table = 'media';

    protected $fillable = [
        'disk', 'path', 'original_name', 'mime_type', 'extension', 'size',
        'width', 'height', 'alt_text', 'checksum', 'sort_order',
    ];

    protected $appends = ['url'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    public function productImages(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function importItems(): HasMany
    {
        return $this->hasMany(ImportItem::class);
    }

    public function categoryImages(): HasMany
    {
        return $this->hasMany(Category::class, 'image_id');
    }

    public function brandLogos(): HasMany
    {
        return $this->hasMany(Brand::class, 'logo_image_id');
    }

    public function getUrlAttribute(): string
    {
        if ($this->disk === 'public') {
            return route('media.show', $this);
        }

        return Storage::disk($this->disk)->url($this->path);
    }
}
