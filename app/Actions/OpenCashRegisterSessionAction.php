<?php

namespace App\Actions;

use App\Models\CashRegister;
use App\Models\CashRegisterSession;
use Exception;
use Illuminate\Support\Facades\DB;

class OpenCashRegisterSessionAction
{
    public function execute(array $data): CashRegisterSession
    {
        return DB::transaction(function () use ($data) {
            $cashRegister = CashRegister::findOrFail($data['cash_register_id']);

            // Check if there is already an active session for this cash register
            $activeSessionExists = CashRegisterSession::where('cash_register_id', $cashRegister->id)
                ->where('status', 'open')
                ->exists();

            if ($activeSessionExists) {
                throw new Exception("Esta caja registradora ya tiene una sesión abierta activa.");
            }

            // Also check if this user already has another open box session in the same branch
            $userActiveSession = CashRegisterSession::where('opened_by', $data['opened_by'])
                ->where('status', 'open')
                ->exists();

            if ($userActiveSession) {
                throw new Exception("El usuario ya cuenta con otra sesión de caja abierta. Debe cerrarla primero.");
            }

            return CashRegisterSession::create([
                'cash_register_id' => $cashRegister->id,
                'opened_by' => $data['opened_by'],
                'opening_amount' => $data['opening_amount'],
                'notes' => $data['notes'] ?? null,
                'status' => 'open',
                'opened_at' => now(),
            ]);
        });
    }
}
