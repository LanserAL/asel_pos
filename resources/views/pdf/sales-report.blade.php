<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas Consolidado</title>
    <style>
        body {
            font-family: 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif;
            color: #2D3A56;
            margin: 0;
            padding: 0;
            font-size: 11px;
            line-height: 1.5;
        }
        .header {
            border-bottom: 2px solid #E63946;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .logo-section {
            float: left;
            width: 50%;
        }
        .logo-title {
            font-size: 24px;
            font-weight: bold;
            color: #1a2b4c;
            margin: 0;
        }
        .logo-subtitle {
            font-size: 11px;
            color: #6C757D;
            margin: 0;
            text-uppercase;
            letter-spacing: 1px;
        }
        .info-section {
            float: right;
            width: 50%;
            text-align: right;
            color: #6C757D;
        }
        .info-section h3 {
            margin: 0 0 5px 0;
            color: #1a2b4c;
            font-size: 16px;
        }
        .clear {
            clear: both;
        }
        .kpi-container {
            margin-bottom: 25px;
        }
        .kpi-card {
            float: left;
            width: 30%;
            background-color: #F8F9FA;
            border-radius: 12px;
            padding: 12px;
            margin-right: 3%;
            border: 1px solid #E9ECEF;
            text-align: center;
        }
        .kpi-card.last {
            margin-right: 0;
        }
        .kpi-title {
            font-size: 10px;
            text-transform: uppercase;
            color: #6C757D;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .kpi-value {
            font-size: 18px;
            font-weight: bold;
            color: #1a2b4c;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1a2b4c;
            margin: 25px 0 10px 0;
            border-bottom: 1px solid #E9ECEF;
            padding-bottom: 5px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .data-table th {
            background-color: #1a2b4c;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 8px;
            font-size: 10px;
            text-transform: uppercase;
        }
        .data-table td {
            padding: 8px;
            border-bottom: 1px solid #E9ECEF;
        }
        .data-table tr:nth-child(even) {
            background-color: #F8F9FA;
        }
        .badge {
            padding: 3px 8px;
            border-radius: 50px;
            font-size: 9px;
            font-weight: bold;
            display: inline-block;
        }
        .badge-success {
            background-color: #D4EDDA;
            color: #155724;
        }
        .badge-pending {
            background-color: #FFF3CD;
            color: #856404;
        }
        .chart-table {
            width: 100%;
            margin-bottom: 25px;
        }
        .chart-col {
            float: left;
            width: 48%;
            margin-right: 4%;
        }
        .chart-col.last {
            margin-right: 0;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            color: #6C757D;
            font-size: 9px;
            border-top: 1px solid #E9ECEF;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="logo-section">
            <h1 class="logo-title">ASEL POS</h1>
            <p class="logo-subtitle">{{ $tenantName }}</p>
        </div>
        <div class="info-section">
            <h3>Reporte de Ventas</h3>
            <p style="margin:0;">Generado: {{ now()->format('d/m/Y H:i') }}</p>
            <p style="margin:0;">Periodo: {{ $startDate }} al {{ $endDate }}</p>
        </div>
        <div class="clear"></div>
    </div>

    <!-- KPIs -->
    <div class="kpi-container">
        <div class="kpi-card">
            <div class="kpi-title">Ingresos Totales</div>
            <div class="kpi-value">${{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-title">Total de Ventas</div>
            <div class="kpi-value">{{ $ordersCount }}</div>
        </div>
        <div class="kpi-card last">
            <div class="kpi-title">Ticket Promedio</div>
            <div class="kpi-value">${{ number_format($avgTicket, 2) }}</div>
        </div>
        <div class="clear"></div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="chart-table">
        <div class="chart-col">
            <div class="section-title">Ventas por Sucursal</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Sucursal</th>
                        <th class="text-right">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($branchStats as $branch => $amount)
                        <tr>
                            <td>{{ $branch }}</td>
                            <td class="text-right">${{ number_format($amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="chart-col last">
            <div class="section-title">Métodos de Pago</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Método</th>
                        <th class="text-right">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paymentStats as $method => $amount)
                        <tr>
                            <td>{{ $method }}</td>
                            <td class="text-right">${{ number_format($amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="clear"></div>
    </div>

    <!-- Listado de transacciones -->
    <div class="section-title">Detalle de Transacciones</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Sucursal</th>
                <th>Método</th>
                <th>Canal</th>
                <th>Pago</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr>
                    <td>#{{ $order->id }}</td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $order->customer_name_manual ?? 'Cliente General' }}</td>
                    <td>{{ $order->branch->name ?? 'N/A' }}</td>
                    <td>{{ ucfirst($order->payment_method) }}</td>
                    <td>{{ $order->source === 'pos' ? 'POS Caja' : 'Catálogo' }}</td>
                    <td>
                        <span class="badge {{ $order->payment_status === 'paid' ? 'badge-success' : 'badge-pending' }}">
                            {{ $order->payment_status === 'paid' ? 'Pagado' : 'Pendiente' }}
                        </span>
                    </td>
                    <td class="text-right" style="font-weight: bold;">${{ number_format($order->total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #6C757D;">No se encontraron ventas en este periodo.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Reporte Oficial de Ventas - ASEL POS Cloud System - Todos los derechos reservados &copy; {{ now()->year }}
    </div>

</body>
</html>
