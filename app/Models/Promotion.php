<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'name', 'type', 'code', 'discount_type', 
        'discount_value', 'min_quantity', 'start_date', 'end_date', 'status'
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_quantity' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'promotion_products')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function promotionProducts()
    {
        return $this->hasMany(PromotionProduct::class);
    }

    /**
     * Scope to filter currently active and valid promotions.
     */
    public function scopeActive($query)
    {
        $now = now();
        return $query->where('status', 'active')
            ->where(function ($q) use ($now) {
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $now);
            });
    }
}
