<?php

namespace App\Services;

use App\Models\Order;

class WhatsAppService
{
    public function generateOrderLink(Order $order, string $storeName, array $items): string
    {
        $itemsText = "";
        foreach ($items as $item) {
            $itemsText .= "• {$item['quantity']}x {$item['name']} (\$" . number_format($item['price'], 2) . ")\n";
        }

        $trackingUrl = url("/orders/tracking/{$order->id}");
        $totalFormatted = number_format($order->total, 2);

        $message = <<<EOT
*NUEVO PEDIDO RECIBIDO - {$storeName}*
*Pedido ID:* #{$order->id}
*Sucursal:* {$order->branch->name}
----------------------------------
{$itemsText}----------------------------------
*Total:* \${$totalFormatted}
*Método de Pago:* {$order->payment_method}
*Cliente:* {$order->customer_name_manual}
*Seguimiento del pedido:* {$trackingUrl}
EOT;

        return 'https://wa.me/?text=' . urlencode($message);
    }
}
