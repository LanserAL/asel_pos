<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class PaymentMethodSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'method_type', 'is_enabled', 'instructions'
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
