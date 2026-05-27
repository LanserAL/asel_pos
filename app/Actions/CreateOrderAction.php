<?php

namespace App\Actions;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class CreateOrderAction
{
    public function execute(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $order = Order::create([
                'tenant_id' => $data['tenant_id'],
                'branch_id' => $data['branch_id'],
                'user_id' => $data['user_id'] ?? null,
                'customer_name_manual' => $data['customer_name_manual'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'subtotal' => $data['subtotal'],
                'tax' => $data['tax'],
                'total' => $data['total'],
                'payment_method' => $data['payment_method'],
                'payment_status' => $data['payment_status'] ?? 'pending',
                'delivery_status' => $data['delivery_status'] ?? 'pendiente',
                'source' => $data['source'] ?? 'pos',
            ]);

            return $order;
        });
    }
}
