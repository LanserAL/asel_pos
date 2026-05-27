<div class="d-flex" x-data="{ sidebarOpen: true, showTicketModal: false, activeTicket: null }">
    <!-- Sidebar Component -->
    <x-sidebar active="vendedor.orders" />

    <!-- Main Content Area -->
    <div class="flex-grow-1 min-vh-100 bg-light transition-all" :style="sidebarOpen ? 'margin-left: 300px;' : 'margin-left: 80px;'" style="transition: margin-left 0.3s ease;">
        <!-- Header -->
        <header class="bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center sticky-top z-2 shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <h4 class="mb-0 fw-bold text-naval">Ventas y Facturación</h4>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-coral text-white px-3 py-2 rounded-pill fw-bold">ASEL POS CLOUD</span>
                <span class="text-secondary small">{{ now()->format('d M Y') }}</span>
            </div>
        </header>

        <!-- Main Body -->
        <div class="p-4">
            @if(session()->has('message'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined text-success">check_circle</span>
                        <strong>¡Éxito!</strong> {{ session('message') }}
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- KPIs Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-3 p-3 bg-white text-naval position-relative overflow-hidden h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase text-muted fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Ventas Totales</h6>
                                <h3 class="mb-0 fw-bold">{{ $totalOrdersFiltered }}</h3>
                            </div>
                            <div class="bg-naval text-white p-3 rounded-circle d-flex align-items-center justify-content-center shadow-sm">
                                <span class="material-symbols-outlined fs-3">receipt_long</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-3 p-3 bg-white text-naval position-relative overflow-hidden h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase text-muted fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Cobrado (Efectivo/Pagos)</h6>
                                <h3 class="mb-0 fw-bold text-success">${{ number_format($totalRevenueFiltered, 2) }}</h3>
                            </div>
                            <div class="bg-success text-white p-3 rounded-circle d-flex align-items-center justify-content-center shadow-sm">
                                <span class="material-symbols-outlined fs-3">payments</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-3 p-3 bg-white text-naval position-relative overflow-hidden h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase text-muted fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Por Cobrar (Pendientes)</h6>
                                <h3 class="mb-0 fw-bold text-coral">${{ number_format($pendingRevenueFiltered, 2) }}</h3>
                            </div>
                            <div class="bg-coral text-white p-3 rounded-circle d-flex align-items-center justify-content-center shadow-sm">
                                <span class="material-symbols-outlined fs-3">pending_actions</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Panel -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 d-flex align-items-center gap-2 text-naval">
                        <span class="material-symbols-outlined">filter_list</span>
                        Filtros de Búsqueda
                    </h5>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-medium">Buscar Cliente o Ticket ID</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><span class="material-symbols-outlined fs-5">search</span></span>
                                <input type="text" wire:model.live.debounce.300ms="search" class="form-control bg-light border-start-0 ps-0" placeholder="Ej. Juan Pérez o #102">
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label text-muted small fw-medium">Sucursal</label>
                            <select wire:model.live="branchIdFilter" class="form-select bg-light">
                                <option value="">Todas</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label text-muted small fw-medium">Método de Pago</label>
                            <select wire:model.live="paymentMethodFilter" class="form-select bg-light">
                                <option value="">Todos</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="tarjeta">Tarjeta</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label text-muted small fw-medium">Pago</label>
                            <select wire:model.live="paymentStatusFilter" class="form-select bg-light">
                                <option value="">Todos</option>
                                <option value="paid">Pagado</option>
                                <option value="pending">Pendiente</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label text-muted small fw-medium">Canal</label>
                            <select wire:model.live="sourceFilter" class="form-select bg-light">
                                <option value="">Todos</option>
                                <option value="pos">POS Caja</option>
                                <option value="catalog_online">Catálogo Web</option>
                            </select>
                        </div>

                        <!-- Fila 2 de Filtros -->
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-medium">Rango Preestablecido</label>
                            <select wire:model.live="dateRange" class="form-select bg-light">
                                <option value="today">Hoy</option>
                                <option value="yesterday">Ayer</option>
                                <option value="week">Última Semana</option>
                                <option value="month">Este Mes</option>
                                <option value="custom">Personalizado</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-medium">Fecha Inicio</label>
                            <input type="date" wire:model.live="startDate" class="form-control bg-light" {{ $dateRange !== 'custom' ? 'disabled' : '' }}>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-medium">Fecha Fin</label>
                            <input type="date" wire:model.live="endDate" class="form-control bg-light" {{ $dateRange !== 'custom' ? 'disabled' : '' }}>
                        </div>

                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="button" wire:click="clearFilters" class="btn btn-outline-secondary w-100 rounded-pill d-flex align-items-center justify-content-center gap-2">
                                <span class="material-symbols-outlined fs-5">filter_alt_off</span>
                                Limpiar Filtros
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table of Orders -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-naval">Listado de Ventas</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('vendedor.orders.report.pdf', [
                            'search' => $search,
                            'branch_id' => $branchIdFilter,
                            'payment_method' => $paymentMethodFilter,
                            'payment_status' => $paymentStatusFilter,
                            'delivery_status' => $deliveryStatusFilter,
                            'source' => $sourceFilter,
                            'start_date' => $startDate,
                            'end_date' => $endDate
                        ]) }}" class="btn btn-coral text-white fw-bold rounded-pill px-4 py-2 d-flex align-items-center gap-2 shadow-sm">
                            <span class="material-symbols-outlined fs-5">picture_as_pdf</span>
                            Descargar Reporte PDF
                        </a>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase text-secondary fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">
                            <tr>
                                <th class="ps-4 py-3">Venta ID</th>
                                <th class="py-3">Fecha y Hora</th>
                                <th class="py-3">Cliente</th>
                                <th class="py-3">Sucursal</th>
                                <th class="py-3">Método</th>
                                <th class="py-3">Canal</th>
                                <th class="py-3">Pago</th>
                                <th class="py-3">Entrega</th>
                                <th class="py-3 text-end pe-4">Monto Total</th>
                                <th class="py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $o)
                                <tr>
                                    <td class="ps-4 fw-bold text-naval">#{{ $o->id }}</td>
                                    <td class="text-secondary small">{{ $o->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="fw-medium">{{ $o->customer_name_manual ?? 'Público en General' }}</td>
                                    <td class="text-secondary">{{ $o->branch->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-secondary text-white px-2 py-1 text-uppercase" style="font-size: 10px;">
                                            {{ $o->payment_method }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $o->source === 'pos' ? 'bg-info text-dark' : 'bg-primary text-white bg-naval' }} px-2 py-1 text-uppercase" style="font-size: 10px;">
                                            {{ $o->source === 'pos' ? 'Caja POS' : 'Catálogo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <button wire:click="togglePaymentStatus({{ $o->id }})" class="btn p-0 border-0">
                                            <span class="badge {{ $o->payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }} px-2 py-1" style="font-size: 11px;">
                                                {{ $o->payment_status === 'paid' ? 'Cobrado' : 'Pendiente' }}
                                            </span>
                                        </button>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border dropdown-toggle px-2 py-1 small" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 11px;">
                                                {{ ucfirst($o->delivery_status) }}
                                            </button>
                                            <ul class="dropdown-menu shadow-sm">
                                                <li><a class="dropdown-menu-item dropdown-item small" href="#" wire:click.prevent="updateDeliveryStatus({{ $o->id }}, 'pendiente')">Pendiente</a></li>
                                                <li><a class="dropdown-menu-item dropdown-item small" href="#" wire:click.prevent="updateDeliveryStatus({{ $o->id }}, 'preparando')">Preparando</a></li>
                                                <li><a class="dropdown-menu-item dropdown-item small" href="#" wire:click.prevent="updateDeliveryStatus({{ $o->id }}, 'enviado')">Enviado</a></li>
                                                <li><a class="dropdown-menu-item dropdown-item small" href="#" wire:click.prevent="updateDeliveryStatus({{ $o->id }}, 'entregado')">Entregado</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4 fw-bold text-naval">${{ number_format($o->total, 2) }}</td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-light border btn-sm p-1 rounded-circle d-inline-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <span class="material-symbols-outlined fs-5">more_vert</span>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center gap-2 small" href="#" wire:click.prevent="selectOrder({{ $o->id }})">
                                                        <span class="material-symbols-outlined fs-5">visibility</span> Ver Detalle
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center gap-2 small text-success" href="#" @click='
                                                        activeTicket = {!! json_encode([
                                                            "id" => $o->id,
                                                            "date" => $o->created_at->format("d/m/Y H:i"),
                                                            "client" => $o->customer_name_manual ?? "Público en General",
                                                            "branch" => $o->branch->name ?? "",
                                                            "subtotal" => "$" . number_format($o->subtotal, 2),
                                                            "tax" => "$" . number_format($o->tax, 2),
                                                            "total" => "$" . number_format($o->total, 2),
                                                            "method" => strtoupper($o->payment_method),
                                                            "status" => $o->payment_status === "paid" ? "PAGADO" : "PENDIENTE",
                                                            "items" => $o->items->map(fn($item) => ["name" => $item->product_name_backup, "qty" => $item->quantity, "price" => number_format($item->price, 2), "total" => number_format($item->total, 2)])->toArray()
                                                        ], JSON_HEX_APOS) !!};
                                                        showTicketModal = true;
                                                    '>
                                                        <span class="material-symbols-outlined fs-5">receipt</span> Ticket Térmico (Simular)
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center gap-2 small text-primary" target="_blank" href="{{ route('orders.tracking', ['orderId' => $o->id]) }}">
                                                        <span class="material-symbols-outlined fs-5">location_on</span> Ver Rastreo
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center gap-2 small text-info" href="#" @click.prevent="
                                                        navigator.clipboard.writeText('{{ route('orders.tracking', ['orderId' => $o->id]) }}'); 
                                                        let btn = $event.currentTarget;
                                                        let originalText = btn.innerHTML;
                                                        btn.innerHTML = '<span class=\'material-symbols-outlined fs-5\'>check</span> ¡Copiado!';
                                                        setTimeout(() => btn.innerHTML = originalText, 2000);
                                                    ">
                                                        <span class="material-symbols-outlined fs-5">content_copy</span> Copiar Link Rastreo
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center gap-2 small text-danger" href="{{ route('vendedor.orders.ticket.pdf', $o->id) }}">
                                                        <span class="material-symbols-outlined fs-5">picture_as_pdf</span> Descargar Ticket PDF
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">
                                        <span class="material-symbols-outlined fs-1 mb-2 d-block text-secondary">receipt_long</span>
                                        No se encontraron ventas con los filtros aplicados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($orders->hasPages())
                    <div class="card-footer bg-white border-top py-3 px-4">
                        {{ $orders->links('vendor.livewire.bootstrap') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Detalle de Venta -->
    <div wire:ignore.self class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                @if($selectedOrder)
                    <div class="modal-header border-bottom py-3 px-4 d-flex align-items-center bg-light" style="border-top-left-radius: 20px; border-top-right-radius: 20px;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-coral text-white p-2 rounded-circle d-flex align-items-center justify-content-center">
                                <span class="material-symbols-outlined">receipt_long</span>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-naval" id="orderDetailsModalLabel">Detalle de Venta #{{ $selectedOrder->id }}</h5>
                                <small class="text-muted">{{ $selectedOrder->created_at->format('d M Y, H:i:s') }}</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="closeDetails"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <h6 class="fw-bold text-naval mb-2 uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Información del Cliente</h6>
                            <div class="bg-light rounded-3 p-3">
                                <p class="mb-1"><strong>Nombre:</strong> {{ $selectedOrder->customer_name_manual ?? 'Público en General' }}</p>
                                <p class="mb-0"><strong>Teléfono:</strong> {{ $selectedOrder->customer_phone ?? 'Sin teléfono registrado' }}</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold text-naval mb-2 uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Información Operativa</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="bg-light rounded-3 p-2 text-center">
                                        <small class="text-muted d-block">Sucursal</small>
                                        <strong class="text-naval small">{{ $selectedOrder->branch->name ?? 'N/A' }}</strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-light rounded-3 p-2 text-center">
                                        <small class="text-muted d-block">Atendido por</small>
                                        <strong class="text-naval small">{{ optional($selectedOrder->user)->name ?? 'N/A' }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold text-naval mb-2 uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Artículos y Desglose</h6>
                            <div class="border rounded-3 p-3">
                                <div class="table-responsive mb-2">
                                    <table class="table table-sm table-borderless align-middle mb-0" style="font-size: 13px;">
                                        <thead>
                                            <tr class="border-bottom text-muted">
                                                <th class="ps-0 py-1">Cant. / Articulo</th>
                                                <th class="text-end pe-0 py-1">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($selectedOrder->items as $item)
                                                <tr>
                                                    <td class="ps-0 py-2">
                                                        <div class="fw-bold text-naval">{{ $item->quantity }}x <span class="fw-medium text-dark">{{ $item->product_name_backup }}</span></div>
                                                        <small class="text-muted">${{ number_format($item->price, 2) }} c/u</small>
                                                    </td>
                                                    <td class="text-end pe-0 py-2 fw-semibold text-naval">
                                                        ${{ number_format($item->total, 2) }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td class="ps-0 py-2 text-muted">
                                                        Consumo General de Artículos
                                                    </td>
                                                    <td class="text-end pe-0 py-2 fw-semibold text-naval">
                                                        ${{ number_format($selectedOrder->subtotal, 2) }}
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <hr class="my-2 dashed">
                                <div class="d-flex justify-content-between mb-2 text-secondary small">
                                    <span>Subtotal</span>
                                    <span>${{ number_format($selectedOrder->subtotal, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2 text-secondary small">
                                    <span>IVA (16%)</span>
                                    <span>${{ number_format($selectedOrder->tax, 2) }}</span>
                                </div>
                                @if($selectedOrder->is_shipping_required)
                                    <div class="d-flex justify-content-between mb-2 text-secondary small">
                                        <span>Costo de Envío</span>
                                        <span class="text-danger fw-bold">POR COTIZAR</span>
                                    </div>
                                @endif
                                <div class="d-flex justify-content-between pt-2 border-top">
                                    <span class="fw-bold text-naval">Total Cobrado</span>
                                    <strong class="text-coral fs-5">${{ number_format($selectedOrder->total, 2) }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <div class="bg-light rounded-3 p-2 text-center">
                                    <small class="text-muted d-block">Método Pago</small>
                                    <span class="badge bg-secondary text-white text-uppercase mt-1">{{ $selectedOrder->payment_method }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light rounded-3 p-2 text-center">
                                    <small class="text-muted d-block">Estado de Pago</small>
                                    <span class="badge {{ $selectedOrder->payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }} mt-1">
                                        {{ $selectedOrder->payment_status === 'paid' ? 'Cobrado' : 'Pendiente' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0 justify-content-between">
                        <a href="{{ route('vendedor.orders.ticket.pdf', $selectedOrder->id) }}" class="btn btn-outline-danger rounded-pill px-4">
                            <span class="material-symbols-outlined fs-5 align-middle">picture_as_pdf</span> Descargar PDF
                        </a>
                        <button type="button" class="btn btn-naval rounded-pill px-4 text-white" data-bs-dismiss="modal" wire:click="closeDetails">Cerrar</button>
                    </div>
                @else
                    <div class="modal-body p-5 text-center">
                        <div class="spinner-border text-coral" role="status"></div>
                        <p class="mt-3 text-muted fw-bold">Cargando detalles...</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- MODAL SIMULADOR TICKET PREVIO & REIMPRESIÓN (PDF BEAUTIFUL STYLE PREVIEW) -->
    <div class="modal fade show" tabindex="-1" style="display: block; background-color: rgba(0,0,0,0.6);" x-show="showTicketModal" x-cloak wire:ignore.self @close-reprint-modal.window="showTicketModal = false">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 750px;">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden text-dark bg-white">
                <div class="modal-header border-bottom py-3 px-4 d-flex align-items-center bg-naval text-white" style="background-color: #0e2649 !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-coral text-white p-2 rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="background-color: #ff6b6b !important;">
                            <span class="material-symbols-outlined text-white">receipt_long</span>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-white mb-0">Vista Previa de Comprobante</h5>
                            <small class="text-white opacity-75">Diseño de Impresión Digital Premium</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" @click="showTicketModal = false"></button>
                </div>
                
                <div class="modal-body p-4 overflow-auto" style="max-height: 75vh;">
                    @if(session()->has('error'))
                        <div class="alert alert-danger py-2 px-3 fw-bold small text-center mb-3">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- ASEL Premium Ticket Layout Simulation -->
                    <div class="border rounded-4 p-4 shadow-sm bg-white mb-4 position-relative overflow-hidden" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                        <!-- Logo & Branch Header -->
                        <div class="row align-items-start mb-4">
                            <div class="col-7">
                                <h1 class="brand-name fw-bold text-naval mb-0" style="font-size: 24px; color: #0e2649; letter-spacing: 0.5px;" x-text="activeTicket ? activeTicket.branch : 'ASEL POS'"></h1>
                                <div class="text-muted small mt-1" x-text="activeTicket ? activeTicket.branch : ''"></div>
                                <div class="text-secondary opacity-75" style="font-size: 11px;">Punto de Venta Cloud - Sucursal Emisora</div>
                            </div>
                            <div class="col-5 text-end">
                                <h2 class="invoice-title fw-bold text-coral mb-1" style="font-size: 20px; color: #ff6b6b; text-transform: uppercase;">Comprobante</h2>
                                <div class="small">
                                    <span class="text-muted fw-bold small">N° DE VENTA:</span>
                                    <strong class="text-coral" x-text="activeTicket ? '#' + String(activeTicket.id).padStart(6, '0') : ''"></strong>
                                </div>
                                <div class="small text-muted mt-1" x-text="activeTicket ? activeTicket.date : ''"></div>
                            </div>
                        </div>

                        <!-- Customer Info Box & Payment Info Box -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="bg-light rounded-3 p-3 h-100" style="background-color: #f8f9fa !important;">
                                    <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 9px; letter-spacing: 0.5px;">Facturado A:</span>
                                    <div class="fw-bold text-naval" style="font-size: 13px;" x-text="activeTicket ? activeTicket.client : ''"></div>
                                    <div class="text-secondary small mt-1" style="font-size: 11px;">Consumo de Caja POS General</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light rounded-3 p-3 h-100" style="background-color: #f8f9fa !important;">
                                    <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 9px; letter-spacing: 0.5px;">Detalles de Transacción:</span>
                                    <table class="table table-sm table-borderless mb-0 small" style="font-size: 11px;">
                                        <tbody>
                                            <tr>
                                                <td class="text-muted p-0">Método de Pago:</td>
                                                <td class="text-end fw-bold text-naval p-0 text-uppercase" x-text="activeTicket ? activeTicket.method : ''"></td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted p-0 pt-1">Estado de Pago:</td>
                                                <td class="text-end p-0 pt-1">
                                                    <span class="badge bg-success text-white px-2 py-0.5" style="font-size: 9px;" x-text="activeTicket ? activeTicket.status : ''"></span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="table-responsive mb-4">
                            <table class="table table-hover align-middle mb-0" style="font-size: 12px;">
                                <thead class="bg-naval text-white" style="background-color: #0e2649 !important;">
                                    <tr>
                                        <th class="ps-3 py-2 text-white">Artículo</th>
                                        <th class="text-center py-2 text-white" style="width: 10%;">Cant.</th>
                                        <th class="text-end py-2 text-white" style="width: 20%;">Precio Unit.</th>
                                        <th class="text-end pe-3 py-2 text-white" style="width: 20%;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-if="activeTicket && activeTicket.items && activeTicket.items.length > 0">
                                        <template x-for="item in activeTicket.items">
                                            <tr>
                                                <td class="ps-3 py-2">
                                                    <strong class="text-naval d-block" x-text="item.name"></strong>
                                                </td>
                                                <td class="text-center py-2" x-text="item.qty"></td>
                                                <td class="text-end py-2" x-text="'$' + item.price"></td>
                                                <td class="text-end pe-3 py-2 fw-bold text-naval" x-text="'$' + item.total"></td>
                                            </tr>
                                        </template>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <div class="row align-items-end pt-3 border-top">
                            <div class="col-7">
                                <div class="bg-light p-2 rounded-3 text-secondary" style="font-size: 10px; border-left: 3px solid #ff6b6b;">
                                    <strong>¡Gracias por su preferencia!</strong><br>
                                    Este comprobante digital ha sido procesado exitosamente por ASEL POS.
                                </div>
                            </div>
                            <div class="col-5">
                                <table class="table table-sm table-borderless mb-0 text-end small" style="font-size: 12px;">
                                    <tbody>
                                        <tr>
                                            <td class="text-muted fw-bold py-0.5">Subtotal:</td>
                                            <td class="fw-bold text-naval py-0.5" x-text="activeTicket ? activeTicket.subtotal : ''"></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-bold py-0.5">IVA (16%):</td>
                                            <td class="fw-bold text-naval py-0.5" x-text="activeTicket ? activeTicket.tax : ''"></td>
                                        </tr>
                                        <tr class="border-top">
                                            <td class="text-coral fw-bold fs-5 py-2">Total:</td>
                                            <td class="fw-black text-coral fs-5 py-2" x-text="activeTicket ? activeTicket.total : ''"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- AUDITED REPRINT FORM SECTION -->
                    <div class="bg-light p-4 rounded-4 border">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="material-symbols-outlined text-coral">warning</span>
                            <h6 class="fw-bold text-naval mb-0 text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">Formulario de Reimpresión Autorizada (Auditoría)</h6>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted small fw-medium">Tipo de Reimpresión</label>
                                <select wire:model.defer="reprintType" class="form-select bg-white">
                                    <option value="full">Copia Completa</option>
                                    <option value="copy_client">Copia Cliente</option>
                                    <option value="corte_caja">Copia de Auditoría</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label text-muted small fw-medium">Motivo de Reimpresión (Obligatorio)</label>
                                <input type="text" wire:model.defer="reprintReason" class="form-control bg-white" placeholder="Ej. El cliente extravió el ticket original o fallo de impresora" required>
                                @error('reprintReason') <small class="text-danger fw-bold d-block mt-1">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <div class="mt-3 text-secondary" style="font-size: 11px;">
                            <span class="material-symbols-outlined fs-6 align-middle">info</span>
                            Nota: Toda reimpresión queda grabada de forma permanente vinculando tu usuario, fecha y hora. Límite máximo: 5 reimpresiones por ticket.
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer border-0 pt-0 justify-content-end px-4 pb-4 gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" @click="showTicketModal = false">Cancelar</button>
                    <button class="btn btn-coral text-white rounded-pill fw-bold d-flex align-items-center gap-2 px-4 shadow-sm" x-text="activeTicket ? 'Confirmar & Descargar PDF' : ''" @click="
                        if ($wire.reprintReason.trim() === '') {
                            alert('Por favor, ingresa el motivo de la reimpresión.');
                            return;
                        }
                        $wire.reprintOrder(activeTicket.id);
                    ">
                    </button>
                </div>
            </div>
        </div>
    </div>

    @script
    <script>
        $wire.on('show-order-details', () => {
            var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('orderDetailsModal'));
            modal.show();
        });

        $wire.on('trigger-pdf-download', (event) => {
            var orderId = event.orderId;
            window.open('/vendedor/orders/' + orderId + '/ticket/pdf', '_blank');
            window.dispatchEvent(new CustomEvent('close-reprint-modal'));
        });
    </script>
    @endscript
</div>
