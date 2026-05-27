<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'branch_id', 'user_id', 'customer_name_manual', 'customer_phone',
        'subtotal', 'tax', 'total', 'payment_method', 'payment_status', 'delivery_status', 'source',
        'is_shipping_required', 'shipping_address', 'shipping_cost',
        'cash_register_session_id', 'discount_amount', 'discount_reason', 'discount_authorized_by', 'currency'
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function registerSession()
    {
        return $this->belongsTo(CashRegisterSession::class, 'cash_register_session_id');
    }

    public function discountAuthorizedBy()
    {
        return $this->belongsTo(User::class, 'discount_authorized_by');
    }

    public function reprints()
    {
        return $this->hasMany(TicketReprint::class);
    }
}
