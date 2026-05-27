<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_register_session_id', 'type', 'amount', 'reason'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function session()
    {
        return $this->belongsTo(CashRegisterSession::class, 'cash_register_session_id');
    }
}
