<?php

namespace App\Actions;

use App\Models\CashRegisterSession;
use App\Models\CashTransaction;
use Exception;
use Illuminate\Support\Facades\DB;

class CreateCashTransactionAction
{
    public function execute(array $data): CashTransaction
    {
        return DB::transaction(function () use ($data) {
            $session = CashRegisterSession::findOrFail($data['cash_register_session_id']);

            if ($session->status !== 'open') {
                throw new Exception("No se pueden registrar movimientos en una sesión de caja cerrada.");
            }

            if ($data['amount'] <= 0) {
                throw new Exception("El monto del movimiento debe ser mayor a cero.");
            }

            // If it is an egress (out), make sure we have enough cash in the box (optional but excellent UX safety check)
            if ($data['type'] === 'out') {
                // Calculate cash sales total
                $cashSalesTotal = $session->orders()
                    ->where('payment_method', 'efectivo')
                    ->where('payment_status', 'paid')
                    ->sum('total');

                $manualInflow = $session->transactions()->where('type', 'in')->sum('amount');
                $manualOutflow = $session->transactions()->where('type', 'out')->sum('amount');

                $currentCash = $session->opening_amount + $cashSalesTotal + $manualInflow - $manualOutflow;

                if ($currentCash < $data['amount']) {
                    throw new Exception("Efectivo insuficiente en caja. Disponible: $" . number_format($currentCash, 2));
                }
            }

            return CashTransaction::create([
                'cash_register_session_id' => $session->id,
                'type' => $data['type'],
                'amount' => $data['amount'],
                'reason' => $data['reason'],
            ]);
        });
    }
}
