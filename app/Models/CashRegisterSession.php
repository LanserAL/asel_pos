<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashRegisterSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_register_id', 'opened_by', 'closed_by', 'opening_amount', 
        'closing_amount', 'expected_amount', 'difference', 'status', 
        'opened_at', 'closed_at', 'notes', 'closing_notes'
    ];

    protected $casts = [
        'opening_amount' => 'decimal:2',
        'closing_amount' => 'decimal:2',
        'expected_amount' => 'decimal:2',
        'difference' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function register()
    {
        return $this->belongsTo(CashRegister::class, 'cash_register_id');
    }

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function transactions()
    {
        return $this->hasMany(CashTransaction::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
