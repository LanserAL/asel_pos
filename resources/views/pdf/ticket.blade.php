<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Compra - ASEL POS</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #333333;
            margin: 0;
            padding: 30px;
            line-height: 1.5;
        }
        .header {
            width: 100%;
            margin-bottom: 30px;
        }
        .header td {
            vertical-align: top;
        }
        .brand-name {
            font-size: 28px;
            font-weight: bold;
            color: #0e2649;
            margin: 0;
            letter-spacing: 1px;
        }
        .brand-subtitle {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        .invoice-title {
            font-size: 24px;
            color: #ff6b6b;
            font-weight: bold;
            text-transform: uppercase;
            text-align: right;
            margin: 0;
        }
        .info-table {
            width: 100%;
            margin-bottom: 30px;
        }
        .info-table td {
            vertical-align: top;
            width: 50%;
        }
        .info-box {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-right: 15px;
        }
        .info-box.right {
            margin-right: 0;
            margin-left: 15px;
        }
        .info-label {
            font-size: 10px;
            color: #888;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 3px;
            display: block;
        }
        .info-value {
            font-size: 14px;
            color: #0e2649;
            font-weight: bold;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #0e2649;
            color: #ffffff;
            text-align: left;
            padding: 12px;
            font-size: 12px;
            text-transform: uppercase;
        }
        .items-table th.right {
            text-align: right;
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #eeeeee;
        }
        .items-table td.right {
            text-align: right;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 8px 12px;
            text-align: right;
        }
        .totals-table .label {
            width: 70%;
            color: #666;
            font-weight: bold;
        }
        .totals-table .value {
            width: 30%;
            color: #0e2649;
            font-weight: bold;
            font-size: 14px;
        }
        .totals-table .grand-total .label,
        .totals-table .grand-total .value {
            font-size: 18px;
            color: #ff6b6b;
            border-top: 2px solid #ff6b6b;
            padding-top: 15px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 11px;
            color: #888;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            color: #fff;
        }
        .badge-paid {
            background-color: #198754;
        }
        .badge-pending {
            background-color: #ffc107;
            color: #000;
        }
    </style>
</head>
<body>
@php
    $formatPrice = function($amount) use ($order) {
        $currency = $order->currency ?? 'MXN';
        switch ($currency) {
            case 'USD':
                return 'US$' . number_format($amount, 2);
            case 'EUR':
                return number_format($amount, 2) . ' €';
            case 'COP':
                return 'COL$' . number_format($amount, 2);
            case 'MXN':
            default:
                return '$' . number_format($amount, 2);
        }
    };
@endphp

    <table class="header">
        <tr>
            <td>
                <h1 class="brand-name">{{ $tenantName }}</h1>
                <div class="brand-subtitle">{{ $branchName }}</div>
                @if($branchAddress)
                    <div style="font-size: 11px; color: #888; margin-top: 3px;">{{ $branchAddress }}</div>
                @endif
                @if($branchPhone)
                    <div style="font-size: 11px; color: #888;">Tel: {{ $branchPhone }}</div>
                @endif
            </td>
            <td>
                <h2 class="invoice-title">Comprobante</h2>
                <div style="text-align: right; margin-top: 10px;">
                    <span class="info-label" style="display: inline;">N° DE VENTA:</span>
                    <span class="info-value" style="color: #ff6b6b;">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div style="text-align: right; margin-top: 5px;">
                    <span class="info-label" style="display: inline;">FECHA:</span>
                    <span class="info-value">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td>
                <div class="info-box">
                    <span class="info-label">Facturado A:</span>
                    <div class="info-value">{{ $order->customer_name_manual ?? 'Público en General' }}</div>
                    @if($order->customer_phone)
                        <div style="font-size: 12px; color: #666; margin-top: 4px;">Tel: {{ $order->customer_phone }}</div>
                    @endif
                </div>
            </td>
            <td>
                <div class="info-box right">
                    <span class="info-label">Detalles del Pago:</span>
                    <table style="width: 100%; font-size: 12px;">
                        <tr>
                            <td style="color: #666; padding-bottom: 5px;">Método:</td>
                            <td style="text-align: right; font-weight: bold; color: #0e2649;">{{ strtoupper($order->payment_method) }}</td>
                        </tr>
                        <tr>
                            <td style="color: #666; padding-bottom: 5px;">Estado:</td>
                            <td style="text-align: right;">
                                @if($order->payment_status === 'paid')
                                    <span class="badge badge-paid">PAGADO</span>
                                @else
                                    <span class="badge badge-pending">PENDIENTE</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #666;">Canal:</td>
                            <td style="text-align: right; font-weight: bold;">{{ $order->source === 'pos' ? 'Caja Físico' : 'Catálogo Online' }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Descripción del Artículo</th>
                <th style="width: 10%; text-align: center;">Cant.</th>
                <th class="right" style="width: 20%;">Precio Unit.</th>
                <th class="right" style="width: 20%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($order->items as $item)
                <tr>
                    <td>
                        <div style="font-weight: bold; color: #0e2649;">{{ $item->product_name_backup }}</div>
                        @if($item->product && $item->product->sku)
                            <div style="font-size: 10px; color: #888; margin-top: 2px;">SKU: {{ $item->product->sku }}</div>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ $item->quantity }}</td>
                    <td class="right">{{ $formatPrice($item->price) }}</td>
                    <td class="right" style="font-weight: bold;">{{ $formatPrice($item->total) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #888;">
                        Consumo General de Artículos POS<br>
                        <span style="font-size: 10px;">Transacción de venta física sin desglose</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table style="width: 100%;">
        <tr>
            <td style="width: 50%; vertical-align: bottom;">
                <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #ff6b6b;">
                    <strong style="color: #0e2649; display: block; margin-bottom: 5px;">¡Gracias por su compra!</strong>
                    <span style="font-size: 11px; color: #666;">Si tiene alguna duda sobre este comprobante, por favor contacte a la sucursal emisora.</span>
                </div>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <table class="totals-table">
                    <tr>
                        <td class="label">Subtotal:</td>
                        <td class="value">{{ $formatPrice($order->subtotal) }}</td>
                    </tr>
                    <tr>
                        <td class="label">IVA (16.00%):</td>
                        <td class="value">{{ $formatPrice($order->tax) }}</td>
                    </tr>
                    @if($order->shipping_cost > 0)
                    <tr>
                        <td class="label">Costo de Envío:</td>
                        <td class="value">{{ $formatPrice($order->shipping_cost) }}</td>
                    </tr>
                    @endif
                    <tr class="grand-total">
                        <td class="label">Total a Pagar:</td>
                        <td class="value">{{ $formatPrice($order->total) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="footer">
        Este documento es una representación impresa de un comprobante de venta digital.<br>
        Generado por <strong>ASEL POS</strong> - Software Punto de Venta Omnicanal
    </div>

</body>
</html>
