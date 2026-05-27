<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Scopes\TenantScope;

class Provider extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'company',
        'email',
        'phone',
        'address',
        'status',
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
