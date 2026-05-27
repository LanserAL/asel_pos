<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'name', 'address', 'phone', 'status', 'currency'
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
