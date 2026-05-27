<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class CashRegister extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'branch_id', 'name', 'status'
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function sessions()
    {
        return $this->hasMany(CashRegisterSession::class);
    }
}
