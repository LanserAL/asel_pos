<?php

namespace App\Actions;

use App\Models\Order;
use App\Models\TicketReprint;
use Exception;
use Illuminate\Support\Facades\DB;

class ReprintTicketAction
{
    public function execute(array $data): TicketReprint
    {
        return DB::transaction(function () use ($data) {
            $order = Order::findOrFail($data['order_id']);

            // Limit reprints count (e.g., max 5 reprints)
            $reprintsCount = TicketReprint::where('order_id', $order->id)->count();
            if ($reprintsCount >= 5) {
                throw new Exception("Se ha alcanzado el límite máximo de reimpresiones (5) para este ticket.");
            }

            if (empty($data['reason'])) {
                throw new Exception("El motivo de la reimpresión es obligatorio.");
            }

            return TicketReprint::create([
                'order_id' => $order->id,
                'user_id' => $data['user_id'],
                'reason' => $data['reason'],
                'type' => $data['type'] ?? 'full',
            ]);
        });
    }
}
