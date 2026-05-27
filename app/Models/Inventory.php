<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id', 'product_id', 'stock_quantity', 'alert_min_stock'
    ];

    protected static function booted(): void
    {
        $invalidateCache = function ($inventory) {
            $tenantId = null;
            if ($inventory->product) {
                $tenantId = $inventory->product->tenant_id;
            } elseif ($inventory->branch) {
                $tenantId = $inventory->branch->tenant_id;
            }
            
            if (!$tenantId) {
                $tenantId = auth()->user()->tenant_id ?? null;
            }
            
            if ($tenantId) {
                \Illuminate\Support\Facades\Cache::put("tenant_{$tenantId}_catalog_version", \Illuminate\Support\Str::random(8));
            }
        };

        static::saved($invalidateCache);
        static::deleted($invalidateCache);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
