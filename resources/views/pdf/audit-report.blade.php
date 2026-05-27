<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Auditoría - Cortes de Caja y Arqueos</title>
    <style>
        body {
            font-family: 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif;
            color: #2D3A56;
            margin: 0;
            padding: 0;
            font-size: 10px;
            line-height: 1.4;
        }
        .header {
            border-bottom: 2px solid #ff6b6b;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .logo-section {
            float: left;
            width: 60%;
        }
        .logo-title {
            font-size: 20px;
            font-weight: bold;
            color: #1a2b4c;
            margin: 0;
        }
        .logo-subtitle {
            font-size: 11px;
            color: #ff6b6b;
            font-weight: bold;
            margin: 2px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-section {
            float: right;
            width: 40%;
            text-align: right;
            color: #6C757D;
        }
        .info-section h3 {
            margin: 0 0 4px 0;
            color: #1a2b4c;
            font-size: 14px;
            font-weight: bold;
        }
        .clear {
            clear: both;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #1a2b4c;
            margin: 20px 0 10px 0;
            border-bottom: 1px solid #E9ECEF;
            padding-bottom: 4px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th {
            background-color: #1a2b4c;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            font-size: 9px;
            text-transform: uppercase;
        }
        .data-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #E9ECEF;
            vertical-align: top;
        }
        .data-table tr:nth-child(even) {
            background-color: #F8F9FA;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 50px;
            font-size: 8px;
            font-weight: bold;
            display: inline-block;
            text-transform: uppercase;
        }
        .badge-success {
            background-color: #D4EDDA;
            color: #155724;
        }
        .badge-danger {
            background-color: #F8D7DA;
            color: #721C24;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-danger {
            color: #dc3545;
            font-weight: bold;
        }
        .text-success {
            color: #28a745;
            font-weight: bold;
        }
        .notes-text {
            font-style: italic;
            color: #6c757d;
            font-size: 8.5px;
            max-width: 150px;
            word-wrap: break-word;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            color: #6C757D;
            font-size: 8px;
            border-top: 1px solid #E9ECEF;
            padding-top: 8px;
        }
        .brand-footer {
            font-weight: bold;
            color: #1a2b4c;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="logo-section">
            @if(!empty($logo) && file_exists(public_path('storage/' . $logo)))
                <img src="{{ public_path('storage/' . $logo) }}" alt="Logo" style="max-height: 45px; margin-bottom: 5px; display: block;">
            @endif
            <h1 class="logo-title">{{ $tenantName }}</h1>
            <p class="logo-subtitle">Auditoría Interna de Cajas</p>
        </div>
        <div class="info-section">
            <h3>Reporte de Cortes & Arqueos</h3>
            <p style="margin:0;">Generado: {{ now()->format('d/m/Y H:i') }}</p>
            <p style="margin:0;">Periodo: {{ date('d/m/Y', strtotime($startDate)) }} al {{ date('d/m/Y', strtotime($endDate)) }}</p>
        </div>
        <div class="clear"></div>
    </div>

    <!-- Summary Box -->
    <div class="section-title">Resumen del Periodo de Auditoría</div>
    <table class="data-table" style="margin-bottom: 25px;">
        <thead>
            <tr>
                <th class="text-center">Total Sesiones</th>
                <th class="text-center">Sesiones Abiertas</th>
                <th class="text-center">Sesiones Cerradas</th>
                <th class="text-right">Fondo Inicial Total</th>
                <th class="text-right">Esperado en Caja</th>
                <th class="text-right">Arqueado en Caja</th>
                <th class="text-right">Diferencia Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalSessions = $sessions->count();
                $openSessions = $sessions->where('status', 'open')->count();
                $closedSessions = $sessions->where('status', 'closed')->count();
                $totalOpening = $sessions->sum('opening_amount');
                $totalExpected = $sessions->sum('expected_amount');
                $totalClosed = $sessions->sum('closing_amount');
                $totalDiff = $sessions->sum('difference');
            @endphp
            <tr style="background-color: #ffffff !important; font-weight: bold; font-size: 11px;">
                <td class="text-center">{{ $totalSessions }}</td>
                <td class="text-center" style="color: #ff6b6b;">{{ $openSessions }}</td>
                <td class="text-center" style="color: #28a745;">{{ $closedSessions }}</td>
                <td class="text-right">${{ number_format($totalOpening, 2) }}</td>
                <td class="text-right">${{ number_format($totalExpected, 2) }}</td>
                <td class="text-right">${{ number_format($totalClosed, 2) }}</td>
                <td class="text-right {{ $totalDiff < 0 ? 'text-danger' : ($totalDiff > 0 ? 'text-success' : '') }}">
                    ${{ number_format($totalDiff, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Ledger Table -->
    <div class="section-title">Detalle Transaccional de Arqueos</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Sucursal / Caja</th>
                <th>Cajero</th>
                <th>Apertura</th>
                <th>Cierre</th>
                <th class="text-right">Inicial</th>
                <th class="text-right">Esperado</th>
                <th class="text-right">Arqueo</th>
                <th class="text-right">Diferencia</th>
                <th>Estatus</th>
                <th>Notas de Cierre</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sessions as $session)
                <tr>
                    <td style="font-weight: bold; font-size: 9px;">#{{ $session->id }}</td>
                    <td>
                        <span style="font-weight: bold; display: block;">{{ $session->branch_name }}</span>
                        <span style="color: #6c757d; font-size: 8.5px;">Caja: {{ $session->register_name }}</span>
                    </td>
                    <td>{{ $session->user_name }}</td>
                    <td>{{ date('d/m/Y H:i', strtotime($session->opened_at)) }}</td>
                    <td>{{ $session->closed_at ? date('d/m/Y H:i', strtotime($session->closed_at)) : 'N/A' }}</td>
                    <td class="text-right">${{ number_format($session->opening_amount, 2) }}</td>
                    <td class="text-right">${{ $session->expected_amount !== null ? '$' . number_format($session->expected_amount, 2) : 'N/A' }}</td>
                    <td class="text-right">${{ $session->closing_amount !== null ? '$' . number_format($session->closing_amount, 2) : 'N/A' }}</td>
                    <td class="text-right {{ $session->difference < 0 ? 'text-danger' : ($session->difference > 0 ? 'text-success' : '') }}">
                        {{ $session->difference !== null ? '$' . number_format($session->difference, 2) : 'N/A' }}
                    </td>
                    <td>
                        <span class="badge {{ $session->status === 'closed' ? 'badge-success' : 'badge-danger' }}">
                            {{ $session->status === 'closed' ? 'Cerrada' : 'Abierta' }}
                        </span>
                    </td>
                    <td class="notes-text">{{ $session->closing_notes ?: 'Sin comentarios de cierre' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="color: #6C757D; padding: 20px;">
                        No se registraron arqueos de caja en este periodo de tiempo.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Este documento es un reporte formal de control de auditoría interna de cajas emitido por la administración de la tienda.<br>
        Generado de forma automatizada por el sistema <span class="brand-footer">ASEL POS</span>. Todos los derechos reservados.
    </div>

</body>
</html>
