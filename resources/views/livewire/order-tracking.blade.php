<div class="min-vh-100 py-5 bg-light" style="font-family: 'Inter', sans-serif;" wire:poll.4s>
    <div class="container py-4">
        @if(!$o)
            <!-- Ticket Search & Recovery Screen -->
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card border-0 shadow-lg rounded-4 p-5 bg-white text-center">
                        <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-4 bg-coral bg-opacity-10 text-coral" style="width: 80px; height: 80px;">
                            <span class="material-symbols-outlined text-coral" style="font-size: 40px; color: #ff6b6b;">receipt_long</span>
                        </div>
                        
                        <h3 class="fw-bold text-naval mb-2">Recuperar Pedido</h3>
                        <p class="text-secondary small mb-4">Ingresa tu número de ticket o recibo de compra para verificar el estado de tu pedido en tiempo real.</p>
                        
                        <!-- Search Form -->
                        <form wire:submit.prevent="recoverTicket">
                            <div class="mb-4 text-start">
                                <label class="form-label text-muted fw-bold text-uppercase small" style="font-size: 10px;">Número de Ticket / Folio</label>
                                <div class="input-group shadow-sm rounded-pill overflow-hidden border">
                                    <span class="input-group-text bg-light border-0 text-secondary fw-bold px-3">#</span>
                                    <input type="text" wire:model="searchTicketId" class="form-control border-0 bg-white form-control-lg fs-5 font-monospace" placeholder="10294" style="outline: none; box-shadow: none;">
                                </div>
                                @error('searchTicketId') <div class="text-danger small mt-1 fw-bold ps-2">{{ $message }}</div> @enderror
                            </div>

                            @if(session()->has('search_error'))
                                <div class="alert alert-danger border-0 rounded-3 small p-2 fw-semibold mb-4">
                                    {{ session('search_error') }}
                                </div>
                            @endif

                            <button type="submit" class="btn btn-coral w-100 rounded-pill py-3 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2 text-white">
                                <span class="material-symbols-outlined">search</span>
                                Buscar Pedido
                            </button>
                        </form>

                        <div class="mt-4 pt-3 border-top">
                            <a href="{{ route('kiosko') }}" class="btn btn-link text-decoration-none text-secondary small hover-link d-inline-flex align-items-center gap-1">
                                <span class="material-symbols-outlined fs-5">storefront</span>
                                Volver al Directorio de Tiendas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Main Tracking View -->
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    
                    <!-- Top Action Bar (No Print) -->
                    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                        <a href="{{ route('catalogo', ['tenant' => $o->tenant->slug]) }}" class="btn btn-link text-decoration-none text-secondary d-inline-flex align-items-center gap-1 small hover-link">
                            <span class="material-symbols-outlined fs-5">arrow_back</span>
                            Regresar a la Tienda
                        </a>
                        <button onclick="window.print()" class="btn btn-outline-naval rounded-pill px-4 d-flex align-items-center gap-2 fw-bold shadow-sm">
                            <span class="material-symbols-outlined fs-5">print</span>
                            Imprimir Ticket Completo
                        </button>
                    </div>

                    <!-- Header Card (No Print) -->
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white no-print">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <!-- Tenant Logo -->
                                @if($o->tenant->logo_path)
                                    <img src="{{ $o->tenant->logo_path }}" alt="Logo" width="55" height="55" class="rounded-3 shadow-sm object-fit-cover">
                                @else
                                    <div class="rounded-3 d-flex align-items-center justify-content-center fw-bold text-white fs-4 text-uppercase shadow-sm bg-coral" 
                                         style="width: 55px; height: 55px; background: linear-gradient(135deg, #0e2649 0%, #ff6b6b 100%);">
                                        {{ substr($o->tenant->name, 0, 2) }}
                                    </div>
                                @endif
                                <div>
                                    <h4 class="fw-bold text-naval mb-1">{{ $o->tenant->name }}</h4>
                                    <p class="text-secondary small mb-0 d-flex align-items-center gap-1">
                                        <span class="material-symbols-outlined fs-6">store</span>
                                        Sucursal: <strong>{{ $o->branch->name ?? 'Principal' }}</strong>
                                    </p>
                                </div>
                            </div>
                            <div class="text-md-end">
                                <span class="badge bg-naval text-white px-3 py-2 rounded-pill fw-bold mb-2">Pedido #{{ $o->id }}</span>
                                <div class="text-secondary small">Recibido: {{ $o->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Left Panel: Delivery Status Timeline (No Print) -->
                        <div class="col-md-6 no-print">
                            <div class="card border-0 shadow-lg rounded-4 p-4 h-100 bg-white">
                                <h5 class="fw-bold text-naval mb-4 d-flex align-items-center gap-2">
                                    <span class="material-symbols-outlined text-coral">local_shipping</span>
                                    Estado de tu Entrega
                                </h5>

                                @php
                                    $status_rank = [
                                        'pendiente' => 1,
                                        'preparando' => 2,
                                        'enviado' => 3,
                                        'entregado' => 4
                                    ];
                                    $current_rank = $status_rank[$o->delivery_status] ?? 1;
                                    
                                    // Progress heights for the green connecting line
                                    $lineHeight = '0%';
                                    if($current_rank == 2) $lineHeight = '33%';
                                    if($current_rank == 3) $lineHeight = '66%';
                                    if($current_rank >= 4) $lineHeight = '100%';
                                @endphp

                                <!-- Vertical Timeline -->
                                <div class="py-3 position-relative">
                                    <!-- Background grey line: Center of circles is at 15px left, 31px top/bottom -->
                                    <div class="position-absolute" style="left: 14px; top: 31px; bottom: 31px; width: 2px; background-color: #e9ecef; z-index: 1;"></div>
                                    
                                    <!-- Foreground green line showing progress -->
                                    @php
                                        $progressFraction = ($current_rank - 1) / 3;
                                    @endphp
                                    <div class="position-absolute" style="left: 14px; top: 31px; width: 2px; height: calc((100% - 62px) * {{ $progressFraction }}); background-color: #198754; z-index: 1; transition: height 0.5s ease;"></div>
                                    
                                    <div class="position-relative" style="z-index: 2;">
                                        
                                        <!-- Step 1 -->
                                        <div class="d-flex mb-4">
                                            <div class="flex-shrink-0">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                                                     style="width: 30px; height: 30px; background-color: {{ $current_rank >= 1 ? '#198754' : '#e9ecef' }}; color: {{ $current_rank >= 1 ? '#ffffff' : '#6c757d' }};">
                                                    <span class="material-symbols-outlined" style="font-size: 16px;">assignment_late</span>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3 pt-1">
                                                <h6 class="fw-bold mb-1 {{ $current_rank >= 1 ? 'text-naval' : 'text-secondary' }}">Pedido Recibido</h6>
                                                <p class="text-secondary small mb-0">Hemos registrado tu pedido y está a la espera de confirmación.</p>
                                            </div>
                                        </div>

                                        <!-- Step 2 -->
                                        <div class="d-flex mb-4">
                                            <div class="flex-shrink-0">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                                                     style="width: 30px; height: 30px; background-color: {{ $current_rank >= 2 ? '#198754' : '#e9ecef' }}; color: {{ $current_rank >= 2 ? '#ffffff' : '#6c757d' }};">
                                                    <span class="material-symbols-outlined" style="font-size: 16px;">soup_kitchen</span>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3 pt-1">
                                                <h6 class="fw-bold mb-1 {{ $current_rank >= 2 ? 'text-naval' : 'text-secondary' }}">Preparando Pedido</h6>
                                                <p class="text-secondary small mb-0">Tu mercancía o alimentos se están empacando/cocinando.</p>
                                            </div>
                                        </div>

                                        <!-- Step 3 -->
                                        <div class="d-flex mb-4">
                                            <div class="flex-shrink-0">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                                                     style="width: 30px; height: 30px; background-color: {{ $current_rank >= 3 ? '#198754' : '#e9ecef' }}; color: {{ $current_rank >= 3 ? '#ffffff' : '#6c757d' }};">
                                                    <span class="material-symbols-outlined" style="font-size: 16px;">sports_motorsports</span>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3 pt-1">
                                                <h6 class="fw-bold mb-1 {{ $current_rank >= 3 ? 'text-naval' : 'text-secondary' }}">En camino (Enviado)</h6>
                                                <p class="text-secondary small mb-0">El repartidor ha tomado tu pedido y va en camino a tu domicilio.</p>
                                            </div>
                                        </div>

                                        <!-- Step 4 -->
                                        <div class="d-flex">
                                            <div class="flex-shrink-0">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                                                     style="width: 30px; height: 30px; background-color: {{ $current_rank >= 4 ? '#198754' : '#e9ecef' }}; color: {{ $current_rank >= 4 ? '#ffffff' : '#6c757d' }};">
                                                    <span class="material-symbols-outlined" style="font-size: 16px;">task_alt</span>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3 pt-1">
                                                <h6 class="fw-bold mb-1 {{ $current_rank >= 4 ? 'text-naval' : 'text-secondary' }}">Pedido Entregado</h6>
                                                <p class="text-secondary small mb-0">¡Tu entrega ha concluido de forma exitosa y segura!</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Auto Update Alert -->
                                <div class="alert bg-light border text-center rounded-3 p-2 small text-secondary mb-0 mt-auto d-flex align-items-center justify-content-center gap-2">
                                    <div class="spinner-grow spinner-grow-sm text-coral" role="status" style="width: 12px; height: 12px;"></div>
                                    <span>Esta página se actualiza automáticamente.</span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Panel: Invoice Style Digital Ticket -->
                        <div class="col-md-6 col-12" id="digital-ticket-container">
                            <div class="card border border-light shadow-lg rounded-4 p-4 p-md-5 bg-white position-relative" id="digital-ticket" style="color: #333;">
                                
                                <!-- Header row -->
                                <div class="row mb-4">
                                    <div class="col-7">
                                        <h2 class="fw-bold text-naval mb-1" style="font-size: 24px; letter-spacing: 0.5px;">{{ strtoupper($o->tenant->name) }}</h2>
                                        <div class="text-secondary fw-bold small">{{ $o->branch->name ?? 'Principal' }}</div>
                                        @if($o->branch->address ?? false)
                                            <div class="text-secondary small mt-1" style="font-size: 11px;">{{ $o->branch->address }}</div>
                                        @endif
                                        @if($o->branch->phone ?? false)
                                            <div class="text-secondary small" style="font-size: 11px;">Tel: {{ $o->branch->phone }}</div>
                                        @endif
                                    </div>
                                    <div class="col-5 text-end">
                                        <h3 class="fw-bold text-coral text-uppercase mb-2" style="font-size: 20px;">Comprobante</h3>
                                        <div class="d-flex justify-content-end align-items-center gap-2 mb-1">
                                            <span class="text-secondary small fw-bold" style="font-size: 10px;">N° DE VENTA:</span>
                                            <span class="fw-bold text-coral">#{{ str_pad($o->id, 6, '0', STR_PAD_LEFT) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-end align-items-center gap-2">
                                            <span class="text-secondary small fw-bold" style="font-size: 10px;">FECHA:</span>
                                            <span class="fw-bold text-dark small">{{ $o->created_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Info boxes -->
                                <div class="row g-3 mb-4">
                                    <div class="col-sm-6">
                                        <div class="bg-light rounded-3 p-3 h-100">
                                            <span class="text-secondary fw-bold d-block mb-1" style="font-size: 10px; text-transform: uppercase;">Facturado A:</span>
                                            <div class="fw-bold text-naval mb-1">{{ $o->customer_name_manual ?? 'Público en General' }}</div>
                                            @if($o->customer_phone)
                                                <div class="text-secondary small" style="font-size: 11px;">Tel: {{ $o->customer_phone }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="bg-light rounded-3 p-3 h-100">
                                            <span class="text-secondary fw-bold d-block mb-2" style="font-size: 10px; text-transform: uppercase;">Detalles del Pago:</span>
                                            <div class="d-flex justify-content-between mb-1" style="font-size: 12px;">
                                                <span class="text-secondary">Método:</span>
                                                <span class="fw-bold text-naval">{{ strtoupper($o->payment_method) }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-1" style="font-size: 12px;">
                                                <span class="text-secondary">Estado:</span>
                                                @if($o->payment_status === 'paid')
                                                    <span class="badge bg-success rounded-pill" style="font-size: 10px;">PAGADO</span>
                                                @else
                                                    <span class="badge bg-warning text-dark rounded-pill" style="font-size: 10px;">PENDIENTE</span>
                                                @endif
                                            </div>
                                            <div class="d-flex justify-content-between" style="font-size: 12px;">
                                                <span class="text-secondary">Canal:</span>
                                                <span class="fw-bold">Catálogo Web</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Items Table -->
                                <div class="table-responsive mb-4">
                                    <table class="table table-borderless align-middle mb-0">
                                        <thead class="bg-naval text-white">
                                            <tr>
                                                <th class="py-2 px-3 small fw-bold rounded-start" style="font-size: 11px;">DESCRIPCIÓN DEL ARTÍCULO</th>
                                                <th class="py-2 px-3 small fw-bold text-center" style="width: 15%; font-size: 11px;">CANT.</th>
                                                <th class="py-2 px-3 small fw-bold text-end" style="width: 20%; font-size: 11px;">PRECIO UNIT.</th>
                                                <th class="py-2 px-3 small fw-bold text-end rounded-end" style="width: 20%; font-size: 11px;">SUBTOTAL</th>
                                            </tr>
                                        </thead>
                                        <tbody class="border-top-0">
                                            @forelse($o->items as $item)
                                                <tr class="border-bottom">
                                                    <td class="py-3 px-3">
                                                        <div class="fw-bold text-naval small">{{ $item->product_name_backup }}</div>
                                                        @if($item->product && $item->product->sku)
                                                            <div class="text-secondary mt-1" style="font-size: 10px;">SKU: {{ $item->product->sku }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="py-3 px-3 text-center small">{{ $item->quantity }}</td>
                                                    <td class="py-3 px-3 text-end small">${{ number_format($item->price, 2) }}</td>
                                                    <td class="py-3 px-3 text-end fw-bold small">${{ number_format($item->total, 2) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="py-4 text-center text-secondary small">
                                                        Consumo General de Artículos POS<br>
                                                        <span style="font-size: 10px;">Transacción de venta sin desglose</span>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Totals -->
                                <div class="row align-items-end">
                                    <div class="col-sm-6 mb-4 mb-sm-0">
                                        <div class="bg-light p-3 rounded-3 border-start border-4 border-coral h-100 d-flex flex-column justify-content-center">
                                            <strong class="text-naval d-block mb-1" style="font-size: 14px;">¡Gracias por su compra!</strong>
                                            <span class="text-secondary" style="font-size: 11px;">Conserve este comprobante para cualquier aclaración o seguimiento.</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-secondary fw-bold small">Subtotal:</span>
                                            <span class="fw-bold text-naval small">${{ number_format($o->subtotal, 2) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-secondary fw-bold small">IVA (16.00%):</span>
                                            <span class="fw-bold text-naval small">${{ number_format($o->tax, 2) }}</span>
                                        </div>
                                        @if($o->shipping_cost > 0 || $o->is_shipping_required)
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-secondary fw-bold small">Costo de Envío:</span>
                                                @if($o->shipping_cost > 0)
                                                    <span class="fw-bold text-naval small">${{ number_format($o->shipping_cost, 2) }}</span>
                                                @else
                                                    <span class="fw-bold text-coral small" style="font-size: 10px;">POR COTIZAR</span>
                                                @endif
                                            </div>
                                        @endif
                                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-2" style="border-color: #ff6b6b !important;">
                                            <span class="fw-bold text-coral" style="font-size: 16px;">Total a Pagar:</span>
                                            <span class="fw-bold text-coral" style="font-size: 18px;">${{ number_format($o->total, 2) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Print Action Button inside Ticket (No Print) -->
                                <div class="mt-5 pt-3 text-center no-print border-top">
                                    <button onclick="window.print()" class="btn btn-coral rounded-pill px-4 fw-bold w-100 d-flex align-items-center justify-content-center gap-2 shadow-sm">
                                        <span class="material-symbols-outlined">print</span>
                                        Imprimir Recibo
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Custom Styling, especially for serrated receipt paper edge and printing -->
    <style>
        .hover-link {
            transition: all 0.3s ease;
        }
        .hover-link:hover {
            color: #ff6b6b !important;
            transform: translateX(-4px);
        }
        .btn-outline-naval {
            color: #0e2649;
            border-color: #0e2649;
            background-color: transparent;
            transition: all 0.25s ease;
        }
        .btn-outline-naval:hover {
            background-color: #0e2649;
            color: white;
        }
        .btn-coral {
            background-color: #ff6b6b;
            border-color: #ff6b6b;
            color: white;
            transition: all 0.25s ease;
        }
        .btn-coral:hover {
            background-color: #e05353;
            border-color: #e05353;
            color: white;
        }
        .text-naval {
            color: #0e2649;
        }
        .text-coral {
            color: #ff6b6b;
        }
        
        /* Zigzag serrated edge effect */
        .receipt-zigzag-top {
            height: 10px;
            background: linear-gradient(-135deg, transparent 5px, #fff 0), linear-gradient(135deg, transparent 5px, #fff 0);
            background-size: 10px 10px;
            background-position: 0 100%;
            margin-bottom: 0;
            width: 100%;
        }
        .receipt-zigzag-bottom {
            height: 10px;
            background: linear-gradient(-45deg, transparent 5px, #fff 0), linear-gradient(45deg, transparent 5px, #fff 0);
            background-size: 10px 10px;
            background-position: 0 0;
            margin-top: 0;
            width: 100%;
        }
        
        .border-dashed {
            border-style: dashed !important;
        }

        /* High Fidelity CSS Print configuration */
        @media print {
            /* Hide entire DOM */
            body, html, .min-vh-100, .container, .row, .col-lg-10 {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            /* Hide other elements */
            body * {
                visibility: hidden;
            }
            /* Show only the ticket itself */
            #digital-ticket, #digital-ticket * {
                visibility: visible;
            }
            /* Position ticket perfectly */
            #digital-ticket {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                max-width: 80mm !important; /* Standard receipt printer width */
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
        }
    </style>
</div>
