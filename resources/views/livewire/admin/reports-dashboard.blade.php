<div x-data="{ sidebarOpen: true }" class="d-flex w-100 min-vh-100 bg-light text-dark" style="font-family: 'Instrument Sans', sans-serif;">
    
    <!-- SideNavBar -->
    <x-sidebar active="admin.reports" />

    <!-- Main Content -->
    <main class="flex-grow-1 p-4 p-md-5 main-content w-100 transition-all" :style="sidebarOpen ? 'margin-left: 300px; max-width: calc(100% - 300px);' : 'margin-left: 80px; max-width: calc(100% - 80px);'" style="transition: all 0.3s ease;">
        
        <!-- Header -->
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
            <div>
                <h2 class="fw-bold text-naval mb-1">Business Intelligence & Analítica</h2>
                <p class="text-muted mb-0 small fw-medium">Monitorea en tiempo real las finanzas y la rentabilidad de tu negocio.</p>
            </div>
            
            <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3">
                <!-- Generar reporte con IA -->
                <button type="button" class="btn btn-outline-naval d-flex align-items-center gap-2 fw-bold rounded-pill px-4 py-2 shadow-sm border-2">
                    <span class="material-symbols-outlined fs-5 text-coral">smart_toy</span>
                    Generar reporte con IA
                </button>

                <!-- Date Filter Form -->
                <div class="d-flex align-items-center gap-3 bg-white p-3 rounded-pill shadow-sm border">
                    <div class="d-flex align-items-center gap-2">
                        <label class="small text-muted fw-bold text-uppercase mb-0">Desde:</label>
                        <input type="date" wire:model.live="startDate" class="form-control form-control-sm border-0 bg-light rounded-pill px-3">
                    </div>
                    <div class="d-flex align-items-center gap-2 border-start ps-3">
                        <label class="small text-muted fw-bold text-uppercase mb-0">Hasta:</label>
                        <input type="date" wire:model.live="endDate" class="form-control form-control-sm border-0 bg-light rounded-pill px-3">
                    </div>
                </div>
            </div>
        </header>

        <!-- KPI Grid -->
        <section class="row g-4 mb-5">
            <!-- Ingresos Totales -->
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 bg-naval text-white p-4 h-100 transition-all hover-translate-y">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="small fw-bold text-uppercase tracking-wider opacity-75">Ingresos Totales</span>
                        <span class="material-symbols-outlined text-coral">payments</span>
                    </div>
                    <h3 class="fw-black mb-1">${{ number_format($totalRevenue, 2) }}</h3>
                    <small class="opacity-75">Total facturado en caja</small>
                </div>
            </div>
            
            <!-- Margen de Utilidad -->
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100 transition-all hover-translate-y border-start border-coral border-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="small text-muted fw-bold text-uppercase tracking-wider">Ganancia Neta</span>
                        <span class="material-symbols-outlined text-success">monetization_on</span>
                    </div>
                    <h3 class="fw-black text-naval mb-1">${{ number_format($totalProfit, 2) }}</h3>
                    <small class="text-secondary small fw-medium">Menos costo de adquisición: ${{ number_format($totalCost, 2) }}</small>
                </div>
            </div>

            <!-- Total Tickets -->
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100 transition-all hover-translate-y">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="small text-muted fw-bold text-uppercase tracking-wider">Tickets Emitidos</span>
                        <span class="material-symbols-outlined text-info">receipt_long</span>
                    </div>
                    <h3 class="fw-black text-naval mb-1">{{ $ordersCount }}</h3>
                    <small class="text-secondary small">Ventas concretadas</small>
                </div>
            </div>

            <!-- Ticket Promedio -->
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100 transition-all hover-translate-y">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="small text-muted fw-bold text-uppercase tracking-wider">Ticket Promedio</span>
                        <span class="material-symbols-outlined text-warning">show_chart</span>
                    </div>
                    <h3 class="fw-black text-naval mb-1">${{ number_format($avgTicket, 2) }}</h3>
                    <small class="text-secondary small">Consumo medio por cliente</small>
                </div>
            </div>
        </section>

        <!-- Branch Sales & Payments breakdown Grid -->
        <section class="row g-4 mb-5">
            <!-- Ventas por Sucursal -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-bottom p-4">
                        <h5 class="fw-bold text-naval mb-0 d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined text-coral">store</span>
                            Ventas por Sucursal
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="py-2 text-muted border-0">Sucursal</th>
                                        <th class="py-2 text-muted text-end border-0">Total Ventas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($branchSales as $bs)
                                        <tr>
                                            <td class="py-3 fw-bold text-dark">{{ $bs->name }}</td>
                                            <td class="py-3 text-end fw-black text-naval">${{ number_format($bs->total, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="2" class="text-center text-muted py-4">No se registran ventas por sucursal.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Métodos de Pago -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-bottom p-4">
                        <h5 class="fw-bold text-naval mb-0 d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined text-coral">account_balance_wallet</span>
                            Métodos de Pago Utilizados
                        </h5>
                    </div>
                    <div class="card-body p-4 d-flex flex-column justify-content-center">
                        @if(empty($paymentStats))
                            <div class="text-center py-4 text-muted">No se registran transacciones de pago.</div>
                        @else
                            <div class="d-flex flex-column gap-3">
                                @foreach($paymentStats as $method => $amount)
                                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-light">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="material-symbols-outlined text-naval">
                                                @if($method === 'Efectivo') payments
                                                @elseif($method === 'Tarjeta') credit_card
                                                @elseif($method === 'Credito') credit_score
                                                @else stars
                                                @endif
                                            </span>
                                            <span class="fw-bold text-dark">{{ $method }}</span>
                                        </div>
                                        <span class="fw-black text-naval">${{ number_format($amount, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <!-- Top Products Ranking -->
        <section class="card border-0 shadow-sm rounded-4 mb-5">
            <div class="card-header bg-white border-bottom p-4">
                <h5 class="fw-bold text-naval mb-0 d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined text-coral">trending_up</span>
                    Productos Estrella (Top 5 Más Vendidos)
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">Producto</th>
                                <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0 text-center">Unidades Vendidas</th>
                                <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0 text-end">Ingresos Totales</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $p)
                                <tr>
                                    <td class="py-3 px-4 fw-bold text-dark">{{ $p->name }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="badge bg-coral bg-opacity-10 text-coral px-3 py-2 rounded-pill fw-bold">{{ $p->quantity }} un.</span>
                                    </td>
                                    <td class="py-3 px-4 text-end fw-black text-naval">${{ number_format($p->revenue, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">No hay información de productos en el periodo.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Cash Register Cuts history -->
        <section class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-naval mb-0 d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined text-coral">lock</span>
                    Auditoría de Cortes de Caja y Arqueos
                </h5>
                <button wire:click="downloadAuditReport" class="btn btn-sm btn-outline-naval rounded-pill fw-bold px-3 py-1.5 d-flex align-items-center gap-1.5">
                    <span class="material-symbols-outlined fs-6">picture_as_pdf</span>
                    Generar reporte
                </button>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">Responsable / Sucursal</th>
                                <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">Apertura</th>
                                <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">Cierre</th>
                                <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0 text-end">Fondo Inicial</th>
                                <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0 text-end">Contado Real</th>
                                <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0 text-end">Diferencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sessions as $s)
                                <tr>
                                    <td class="py-3 px-4">
                                        <h6 class="mb-0 fw-bold text-dark">{{ $s->user_name }}</h6>
                                        <span class="small text-muted">{{ $s->branch_name }} - {{ $s->register_name }}</span>
                                    </td>
                                    <td class="py-3 px-4 small text-secondary">{{ \Carbon\Carbon::parse($s->opened_at)->format('d/m/Y H:i') }}</td>
                                    <td class="py-3 px-4 small text-secondary">
                                        {{ $s->closed_at ? \Carbon\Carbon::parse($s->closed_at)->format('d/m/Y H:i') : 'En curso (Abierta)' }}
                                    </td>
                                    <td class="py-3 px-4 text-end font-monospace">${{ number_format($s->opening_amount, 2) }}</td>
                                    <td class="py-3 px-4 text-end font-monospace fw-bold">
                                        {{ $s->closing_amount ? '$' . number_format($s->closing_amount, 2) : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-end font-monospace fw-bold">
                                        @if(!$s->closed_at)
                                            <span class="badge bg-secondary bg-opacity-10 text-muted px-2 py-1 text-uppercase fw-bold" style="font-size: 10px;">Activa</span>
                                        @else
                                            @php $diff = $s->closing_amount - $s->expected_amount; @endphp
                                            @if($diff == 0)
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1.5 rounded-pill text-uppercase fw-bold" style="font-size: 10px;">Cuadrada</span>
                                            @elseif($diff > 0)
                                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2.5 py-1.5 rounded-pill text-uppercase fw-bold" style="font-size: 10px;">Sobrante (+${{ number_format($diff, 2) }})</span>
                                            @else
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2.5 py-1.5 rounded-pill text-uppercase fw-bold" style="font-size: 10px;">Faltante (-${{ number_format(abs($diff), 2) }})</span>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">No se registran arqueos de caja en este rango de fechas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </main>

    <style>
        .hover-translate-y:hover {
            transform: translateY(-4px);
            box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.08)!important;
        }
        .fw-black {
            font-weight: 900;
        }
    </style>
</div>
