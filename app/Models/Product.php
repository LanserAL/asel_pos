<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'title', 'slug', 'sku', 'barcode', 'description', 'raw_title', 'price', 'cost', 'image_path', 'status'
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        $invalidateCache = function ($product) {
            $tenantId = $product->tenant_id ?? (auth()->user()->tenant_id ?? null);
            if ($tenantId) {
                \Illuminate\Support\Facades\Cache::put("tenant_{$tenantId}_catalog_version", \Illuminate\Support\Str::random(8));
            }
        };

        static::saved($invalidateCache);
        static::deleted($invalidateCache);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function getImageUrlAttribute()
    {
        if (!$this->image_path) {
            return null;
        }

        if (filter_var($this->image_path, FILTER_VALIDATE_URL)) {
            return $this->image_path;
        }

        return \Illuminate\Support\Facades\Storage::url($this->image_path);
    }
}
