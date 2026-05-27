<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'name', 'email', 'phone', 'rfc', 'razon_social',
        'regimen_fiscal', 'postal_code', 'credit_limit', 'credit_balance', 'loyalty_points'
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creditTransactions()
    {
        return $this->hasMany(CustomerCreditTransaction::class);
    }

    public function loyaltyTransactions()
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
