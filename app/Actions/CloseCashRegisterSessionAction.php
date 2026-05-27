<?php

namespace App\Actions;

use App\Models\CashRegisterSession;
use App\Models\Order;
use Exception;
use Illuminate\Support\Facades\DB;

class CloseCashRegisterSessionAction
{
    public function execute(array $data): CashRegisterSession
    {
        return DB::transaction(function () use ($data) {
            $session = CashRegisterSession::findOrFail($data['session_id']);

            if ($session->status !== 'open') {
                throw new Exception("Esta sesión de caja ya se encuentra cerrada.");
            }

            // 1. Calculate cash sales total
            $cashSalesTotal = Order::where('cash_register_session_id', $session->id)
                ->where('payment_method', 'efectivo')
                ->where('payment_status', 'paid')
                ->sum('total');

            // 2. Calculate manual cash transactions
            $manualInflow = $session->transactions()->where('type', 'in')->sum('amount');
            $manualOutflow = $session->transactions()->where('type', 'out')->sum('amount');

            // 3. Compute expected cash amount
            $expectedCash = $session->opening_amount + $cashSalesTotal + $manualInflow - $manualOutflow;

            // 4. Calculate difference
            $closingAmount = $data['closing_amount'];
            $difference = $closingAmount - $expectedCash;

            // 5. Update session
            $session->update([
                'closed_by' => $data['closed_by'],
                'closing_amount' => $closingAmount,
                'expected_amount' => $expectedCash,
                'difference' => $difference,
                'status' => 'closed',
                'closed_at' => now(),
                'closing_notes' => $data['closing_notes'] ?? null,
            ]);

            return $session;
        });
    }
}
