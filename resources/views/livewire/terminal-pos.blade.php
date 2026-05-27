<div x-data="{ 
    sidebarOpen: true, 
    paymentMethod: 'cash',
    showOpenBoxModal: @entangle('showOpenBoxModal'),
    showCloseBoxModal: @entangle('showCloseBoxModal'),
    showTransactionModal: @entangle('showTransactionModal'),
    showDiscountModal: @entangle('showDiscountModal'),
    showScannerModal: false,
    transactionType: @entangle('transactionType'),
    discountType: @entangle('discountType')
}" wire:poll.1s="checkMobileScans" @barcode-scanned.window="$wire.addToCartByBarcode($event.detail.barcode)" class="d-flex w-100 vh-100 bg-light text-dark overflow-hidden" style="font-family: 'Instrument Sans', sans-serif;">
    
    <!-- DESKTOP NAVIGATION -->
    <x-sidebar active="pos" />

    <!-- MAIN CONTENT -->
    <main class="flex-grow-1 p-3 p-md-4 main-content w-100 transition-all d-flex flex-column flex-xl-row h-100 overflow-hidden" :style="sidebarOpen ? 'margin-left: 300px; max-width: calc(100% - 300px);' : 'margin-left: 80px; max-width: calc(100% - 80px);'" style="transition: all 0.3s ease;">
        
        <!-- LEFT: Product Selection Area -->
        <section class="flex-grow-1 d-flex flex-column h-100 overflow-hidden position-relative z-1 pb-5 pb-xl-0 bg-light">
            <!-- Top Bar / Search -->
            <div class="bg-white border-bottom p-3 p-md-4 sticky-top z-2 shadow-sm">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div>
                            <h2 class="h3 fw-bold text-naval mb-0">Punto de Venta</h2>
                            <p class="text-muted mb-0 small fw-medium">Atiende: {{ auth()->user()->name }}</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        @if($isBoxOpen)
                            <button type="button" wire:click="prepareCloseBox" class="btn btn-outline-danger rounded-pill fw-bold px-3 py-2 d-flex align-items-center gap-2 shadow-sm">
                                <span class="material-symbols-outlined fs-5">lock</span>
                                Cerrar Caja
                            </button>
                            <button type="button" @click="showTransactionModal = true" class="btn btn-outline-secondary rounded-pill fw-bold px-3 py-2 d-flex align-items-center gap-2 shadow-sm">
                                <span class="material-symbols-outlined fs-5">currency_exchange</span>
                                Movimiento Caja
                            </button>
                        @else
                            <button type="button" @click="showOpenBoxModal = true" class="btn btn-success text-white rounded-pill fw-bold px-3 py-2 d-flex align-items-center gap-2 shadow-sm">
                                <span class="material-symbols-outlined fs-5">lock_open</span>
                                Abrir Caja
                            </button>
                        @endif

                        <button type="button" class="btn btn-outline-naval rounded-pill fw-bold px-3 py-2 d-flex align-items-center gap-2 shadow-sm" @click="showScannerModal = true">
                            <span class="material-symbols-outlined fs-5">qr_code_scanner</span>
                            Escáner Móvil
                        </button>

                        <div class="position-relative">
                            <span class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 20px;">storefront</span>
                            <select wire:model.live="branchId" class="form-select form-select-lg bg-light border-0 fw-bold text-naval ps-5 rounded-pill pe-5 appearance-none" style="min-width: 200px;" {{ auth()->user()->hasRole('vendedor') ? 'disabled' : '' }}>
                                @if(!auth()->user()->hasRole('vendedor'))
                                    <option value="">Seleccionar Sucursal</option>
                                @endif
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mt-4 position-relative">
                    <span class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y ms-3 text-muted fs-4">search</span>
                    <input wire:model.live="search" type="text" class="form-control form-control-lg bg-light border-0 shadow-none ps-5 rounded-pill" placeholder="Buscar producto por nombre o código (SKU)...">
                </div>
            </div>



            <!-- Product Grid -->
            <div class="flex-grow-1 overflow-auto p-3 p-md-4 position-relative">
                @if(!$isBoxOpen)
                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-white bg-opacity-75 z-3" style="backdrop-filter: blur(8px); min-height: 300px;">
                        <div class="card border-0 shadow-lg p-5 text-center rounded-4" style="max-width: 400px; background: rgba(255,255,255,0.9);">
                            <span class="material-symbols-outlined text-coral mb-3" style="font-size: 72px;">point_of_sale</span>
                            <h3 class="fw-bold text-naval mb-2">Caja Registradora Cerrada</h3>
                            <p class="text-secondary small mb-4">Para poder registrar ventas, agregar productos al carrito y procesar cobros, es obligatorio realizar la apertura de caja.</p>
                            <button type="button" @click="showOpenBoxModal = true" class="btn btn-coral btn-lg text-white w-100 rounded-pill fw-bold py-3 shadow-sm d-flex align-items-center justify-content-center gap-2">
                                <span class="material-symbols-outlined">lock_open</span>
                                Abrir Caja Ahora
                            </button>
                        </div>
                    </div>
                @endif
                <div class="row g-3 g-md-4">
                    @forelse($products as $product)
                    <div class="col-6 col-md-4 col-lg-3">
                        <div wire:click="addToCart({{ $product->id }})" class="card h-100 border-0 shadow-sm rounded-4 text-center cursor-pointer hover-shadow transition-all {{ $product->stock > 0 ? '' : 'opacity-50' }}">
                            <!-- Image Area -->
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center rounded-top-4" style="height: 140px;">
                                @if($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->title }}" class="img-fluid h-100 w-100 object-fit-cover rounded-top-4">
                                @else
                                    <span class="material-symbols-outlined text-muted" style="font-size: 48px; opacity: 0.2;">storefront</span>
                                @endif
                            </div>
                            <!-- Details Area -->
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="card-title fw-bold text-dark text-truncate mb-1" title="{{ $product->title }}">{{ $product->title }}</h6>
                                    @if($product->sku)
                                        <small class="text-muted font-monospace d-block text-truncate">{{ $product->sku }}</small>
                                    @endif
                                </div>
                                <div class="mt-3 d-flex justify-content-between align-items-center">
                                    <span class="fw-bold fs-5 text-naval">{{ $this->formatPrice($product->price) }}</span>
                                    @if($product->stock > 0)
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill fw-bold">{{ $product->stock }} un.</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill fw-bold">Agotado</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="d-flex flex-column align-items-center justify-content-center py-5 text-muted">
                            <span class="material-symbols-outlined mb-3 opacity-50" style="font-size: 64px;">search_off</span>
                            <h4 class="fw-bold">No se encontraron productos</h4>
                            <p>Intenta con otra búsqueda o selecciona una sucursal con inventario.</p>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>
        </section>

        <!-- RIGHT: Shopping Cart Area (Desktop fixed, Mobile bottom drawer) -->
        <aside class="bg-white border-start d-flex flex-column shadow-lg z-3 h-100" style="width: 100%; max-width: 400px; min-width: 350px;">
            <div class="p-4 border-bottom bg-light bg-opacity-50">
                <h4 class="fw-bold text-naval mb-0 d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined text-coral">shopping_cart</span>
                    Ticket Actual
                </h4>
            </div>

            <!-- Customer Selection -->
            <div class="p-3 border-bottom bg-light bg-opacity-25">
                @if($selectedCustomer)
                    <div class="bg-white border rounded-4 p-3 shadow-sm position-relative text-dark">
                        <button type="button" wire:click="clearSelectedCustomer" class="btn-close position-absolute top-0 end-0 m-3" style="font-size: 10px;" title="Remover cliente"></button>
                        
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="material-symbols-outlined text-naval" style="font-variation-settings: 'FILL' 1;">person</span>
                            <span class="fw-bold text-dark text-truncate" style="max-width: 250px;">{{ $selectedCustomer->name }}</span>
                        </div>
                        
                        <div class="d-flex flex-column gap-1.5" style="font-size: 11px;">
                            <div class="d-flex justify-content-between text-muted">
                                <span>Saldo Deudor:</span>
                                <span class="fw-bold {{ $selectedCustomer->credit_balance > 0 ? 'text-danger' : 'text-success' }}">${{ number_format($selectedCustomer->credit_balance, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between text-muted border-bottom pb-2 mb-2">
                                <span>Límite Crédito:</span>
                                <span class="fw-bold">${{ number_format($selectedCustomer->credit_limit, 2) }}</span>
                            </div>
                            
                            <!-- Loyalty Pts -->
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="d-flex align-items-center gap-1"><span class="material-symbols-outlined fs-6 text-success">stars</span> Puntos de Fidelidad:</span>
                                <span class="badge bg-success bg-opacity-10 text-success fw-bold">{{ $selectedCustomer->loyalty_points }} Pts</span>
                            </div>

                            @if($selectedCustomer->loyalty_points > 0)
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" role="switch" id="payWithPointsSwitch" wire:model.live="isPayingWithPoints" wire:click="togglePayWithPoints">
                                    <label class="form-check-label fw-bold text-secondary cursor-pointer" for="payWithPointsSwitch" style="font-size: 10px;">Aplicar puntos como descuento</label>
                                </div>
                            @endif

                            <!-- Invoicing options -->
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" role="switch" id="shouldInvoiceSwitch" wire:model.live="shouldInvoiceImmediate" @disabled(!$selectedCustomer->rfc)>
                                <label class="form-check-label fw-bold text-secondary cursor-pointer" for="shouldInvoiceSwitch" style="font-size: 10px;">
                                    Facturación CFDI inmediata
                                    @if(!$selectedCustomer->rfc)
                                        <span class="text-danger small font-monospace d-block" style="font-size: 9px;">(Requiere datos fiscales)</span>
                                    @endif
                                </label>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="position-relative">
                        <span class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 18px;">person_search</span>
                        <input type="text" wire:model.live="customerQuery" class="form-control form-control-sm bg-white border ps-5 rounded-pill" placeholder="Vincular cliente a ticket...">
                        
                        <!-- Search dropdown results -->
                        @if(!empty($customersList))
                            <div class="position-absolute w-100 bg-white border shadow-lg rounded-4 overflow-hidden mt-1 z-3" style="max-height: 200px; overflow-y: auto;">
                                <div class="list-group list-group-flush">
                                    @foreach($customersList as $cust)
                                        <button type="button" wire:click="selectCustomer({{ $cust->id }})" class="list-group-item list-group-item-action py-2 px-3 text-start small border-0">
                                            <div class="fw-bold text-dark">{{ $cust->name }}</div>
                                            <div class="text-muted d-flex justify-content-between" style="font-size: 10px;">
                                                <span>{{ $cust->phone ?: 'Sin teléfono' }}</span>
                                                <span>Pts: {{ $cust->loyalty_points }} | Deuda: ${{ number_format($cust->credit_balance, 2) }}</span>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Cart Items -->
            <div class="flex-grow-1 overflow-auto p-3">
                @if(count($cart) > 0)
                    <div class="list-group list-group-flush gap-2">
                        @foreach($cart as $index => $item)
                        <div class="list-group-item bg-light border-0 rounded-3 p-3 d-flex justify-content-between align-items-start gap-3">
                            <div class="flex-grow-1">
                                <h6 class="fw-bold text-dark mb-1">{{ $item['name'] }}</h6>
                                <div class="text-naval fw-bold mb-2">{{ $this->formatPrice($item['price']) }} c/u</div>
                                <div class="d-flex align-items-center gap-2 bg-white rounded-pill px-2 py-1 shadow-sm border" style="width: fit-content;">
                                    <button wire:click="decreaseQuantity({{ $index }})" class="btn btn-sm btn-link text-muted p-0 text-decoration-none">
                                        <span class="material-symbols-outlined fs-5">remove</span>
                                    </button>
                                    <span class="fw-bold mx-2" style="min-width: 20px; text-align: center;">{{ $item['quantity'] }}</span>
                                    <button wire:click="increaseQuantity({{ $index }})" class="btn btn-sm btn-link text-muted p-0 text-decoration-none">
                                        <span class="material-symbols-outlined fs-5">add</span>
                                    </button>
                                </div>
                            </div>
                            <div class="d-flex flex-column align-items-end justify-content-between h-100">
                                <button wire:click="removeFromCart({{ $index }})" class="btn btn-sm btn-link text-danger p-0 text-decoration-none opacity-50 hover-opacity-100">
                                    <span class="material-symbols-outlined fs-5">delete</span>
                                </button>
                                <div class="fw-black text-coral fs-5 mt-auto">
                                    {{ $this->formatPrice($item['price'] * $item['quantity']) }}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="h-100 d-flex flex-column align-items-center justify-content-center text-muted">
                        <span class="material-symbols-outlined mb-3 opacity-25" style="font-size: 80px;">shopping_basket</span>
                        <p class="fw-bold fs-5 mb-0">El carrito está vacío</p>
                        <p class="small">Selecciona productos para comenzar a vender.</p>
                    </div>
                @endif
            </div>

            <!-- Cart Summary & Checkout -->
            <div class="p-4 border-top bg-white shadow-sm mt-auto">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted fw-bold">Subtotal</span>
                    <span class="fw-bold text-dark">{{ $this->formatPrice($subtotal) }}</span>
                </div>
                <!-- Automated Promotions List -->
                @if(!empty($appliedPromotions))
                    <div class="mb-3">
                        <small class="text-muted d-block fw-bold text-uppercase mb-2" style="font-size: 9px; letter-spacing: 0.5px;">Descuentos Aplicados</small>
                        <div class="d-flex flex-column gap-1.5">
                            @foreach($appliedPromotions as $promo)
                                <div class="d-flex justify-content-between align-items-center bg-success bg-opacity-10 p-2 rounded-3 text-success small fw-bold">
                                    <div class="d-flex align-items-center gap-1.5">
                                        <span class="material-symbols-outlined fs-6">percent</span>
                                        <span>{{ $promo['name'] }}</span>
                                    </div>
                                    <span>-{{ $this->formatPrice($promo['discount']) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Points Discount -->
                @if($pointsDiscountAmount > 0)
                    <div class="d-flex justify-content-between align-items-center bg-success bg-opacity-10 p-2 rounded-3 text-success small fw-bold mb-3 border border-success border-opacity-25 shadow-sm">
                        <div class="d-flex align-items-center gap-1.5">
                            <span class="material-symbols-outlined fs-6">stars</span>
                            <span>Descuento por Canje ({{ $pointsToRedeem }} Pts)</span>
                        </div>
                        <span>-{{ $this->formatPrice($pointsDiscountAmount) }}</span>
                    </div>
                @endif

                <div class="d-flex justify-content-between mb-3 border-bottom pb-3">
                    <span class="text-muted fw-bold">Impuestos (16%)</span>
                    <span class="fw-bold text-dark">{{ $this->formatPrice($tax) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-4">
                    <span class="fs-5 fw-bold text-naval">Total a Pagar</span>
                    <span class="display-6 fw-bold text-coral">{{ $this->formatPrice($total) }}</span>
                </div>

                <!-- Promo Code / Coupon Block -->
                <div class="mb-4">
                    <label class="form-label text-muted small fw-bold text-uppercase mb-2" style="font-size: 9px; letter-spacing: 0.5px;">Cupón de Descuento</label>
                    <div class="input-group">
                        <input type="text" 
                               wire:model.defer="couponCode" 
                               class="form-control bg-light border-0 py-2.5 rounded-start-4 fw-bold font-monospace text-naval text-uppercase" 
                               placeholder="EJ. BUENFIN15"
                               @disabled(!$isBoxOpen || count($cart) === 0)>
                        <button type="button" 
                                wire:click="calculateTotals" 
                                class="btn btn-naval text-white fw-bold px-3 py-2 rounded-end-4"
                                @disabled(!$isBoxOpen || count($cart) === 0)>
                            Aplicar
                        </button>
                    </div>
                    @if($couponCode && $appliedDiscount > 0 && collect($appliedPromotions)->contains('type', 'code'))
                        <div class="d-flex justify-content-between align-items-center mt-2 px-2 small text-success fw-bold">
                            <span>Cupón Activo</span>
                            <button type="button" wire:click="$set('couponCode', ''); calculateTotals();" class="btn btn-sm btn-link text-danger p-0 text-decoration-none">
                                Remover
                            </button>
                        </div>
                    @elseif($couponCode && !collect($appliedPromotions)->contains('type', 'code'))
                        <div class="text-danger small fw-bold mt-1 px-2">
                            Cupón inválido o no aplicable.
                        </div>
                    @endif
                </div>

                <!-- Payment Methods -->
                <div class="mb-4">
                    <p class="text-muted small fw-bold text-uppercase mb-2">Método de Pago</p>
                    <div class="d-flex gap-2 flex-wrap">
                        <button @click="paymentMethod = 'cash'; $wire.set('paymentMethod', 'Efectivo')" 
                                :class="paymentMethod === 'cash' ? 'btn-coral border-coral text-white' : 'btn-light border-secondary text-muted'"
                                class="btn flex-fill d-flex flex-column align-items-center py-3 rounded-4 fw-bold transition-all shadow-sm">
                            <span class="material-symbols-outlined mb-1">payments</span>
                            Efectivo
                        </button>
                        <button @click="paymentMethod = 'card'; $wire.set('paymentMethod', 'Tarjeta')" 
                                :class="paymentMethod === 'card' ? 'btn-naval border-naval text-white bg-naval' : 'btn-light border-secondary text-muted'"
                                class="btn flex-fill d-flex flex-column align-items-center py-3 rounded-4 fw-bold transition-all shadow-sm">
                            <span class="material-symbols-outlined mb-1">credit_card</span>
                            Tarjeta
                        </button>
                        @if($selectedCustomer)
                            <button @click="paymentMethod = 'credit'; $wire.set('paymentMethod', 'Credito')" 
                                    :class="paymentMethod === 'credit' ? 'btn-danger text-white bg-danger border-danger shadow-lg' : 'btn-light border-secondary text-muted'"
                                    class="btn flex-fill d-flex flex-column align-items-center py-3 rounded-4 fw-bold transition-all shadow-sm">
                                <span class="material-symbols-outlined mb-1">credit_score</span>
                                Crédito
                            </button>
                        @endif
                    </div>
                </div>

                <button wire:click="checkout" wire:loading.attr="disabled" @disabled(count($cart) === 0)
                    class="btn btn-coral btn-lg w-100 rounded-pill py-3 fw-bold fs-5 shadow-sm d-flex justify-content-center align-items-center gap-2">
                    <!-- Default state: shown when NOT loading -->
                    <span wire:loading.remove wire:target="checkout" class="d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined">point_of_sale</span>
                        Cobrar
                    </span>
                    <!-- Loading state: shown ONLY when loading checkout -->
                    <span wire:loading wire:target="checkout" class="d-flex align-items-center gap-2">
                        <span class="spinner-border spinner-border-sm" role="status"></span>
                        Procesando...
                    </span>
                </button>
                
                @if(session()->has('success') || session()->has('message'))
                    <div class="alert alert-success mt-3 py-2 px-3 fw-bold small text-center mb-0">
                        {{ session('success') ?? session('message') }}
                    </div>
                @endif
                @if(session()->has('error'))
                    <div class="alert alert-danger mt-3 py-2 px-3 fw-bold small text-center mb-0">
                        {{ session('error') }}
                    </div>
                @endif
            </div>
        </aside>
    </main>

    <style>
        .hover-shadow:hover {
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
            transform: translateY(-2px);
        }
        .cursor-pointer {
            cursor: pointer;
        }
        .hover-opacity-100:hover {
            opacity: 1 !important;
        }
        .fw-black {
            font-weight: 900;
        }
        .btn-outline-naval {
            border-color: #1a2b4c;
            color: #1a2b4c;
        }
        .btn-outline-naval:hover {
            background-color: #1a2b4c;
            color: white;
        }
        .dot-pulse {
            position: relative;
            width: 8px;
            height: 8px;
            border-radius: 5px;
            background-color: #ff6b6b;
            color: #ff6b6b;
            animation: pulse-dot 1.5s infinite linear;
        }
        @keyframes pulse-dot {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 107, 107, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(255, 107, 107, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 107, 107, 0); }
        }
    </style>

    <!-- Modal de Vinculación de Escáner -->
    <div x-show="showScannerModal" x-cloak
         class="position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center"
         :class="showScannerModal ? 'd-flex' : 'd-none'"
         style="background-color: rgba(0,0,0,0.6); z-index: 1055;"
         @click.self="showScannerModal = false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg bg-white" style="border-radius: 20px;">
                <div class="modal-header border-bottom py-3 px-4 d-flex align-items-center bg-light" style="border-top-left-radius: 20px; border-top-right-radius: 20px;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-coral text-white p-2 rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="background-color: #ff6b6b !important;">
                            <span class="material-symbols-outlined">qr_code_scanner</span>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-naval" id="pairScannerModalLabel">Vincular Celular Escáner</h5>
                            <small class="text-muted">ASEL Scanner Link</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" @click="showScannerModal = false"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="text-secondary small mb-3">Escanea el código QR de abajo con la cámara de tu celular para enlazarlo como lector de códigos inalámbrico al instante.</p>
                    
                    @if(in_array(request()->getHost(), ['localhost', '127.0.0.1']))
                        <div class="alert alert-warning border-0 rounded-3 text-start small mb-3 p-3 shadow-sm" style="font-size: 11px; background-color: #fff3cd; color: #664d03;">
                            <div class="d-flex gap-2">
                                <span class="material-symbols-outlined text-warning fs-5 flex-shrink-0">warning</span>
                                <div>
                                    <strong class="d-block mb-1" style="color: #664d03;">¡Atención: Servidor en Local!</strong>
                                    Como estás en <code>{{ request()->getHost() }}</code>, tu celular no sabrá dónde conectarse. Sigue estos pasos:
                                    <ol class="ps-3 mt-1 mb-0">
                                        <li>Inicia el servidor en tu terminal con:<br><code class="bg-white px-2 py-0.5 rounded border">php artisan serve --host=0.0.0.0 --port=8080</code></li>
                                        <li>Entra al POS en tu computadora usando tu **IP Local** (ej: <code>http://192.168.1.15:8080/pos</code>) en lugar de localhost.</li>
                                        <li>¡Vuelve a abrir este modal, escanea el QR y listo!</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Generación dinámica de QR -->
                    <div class="bg-light p-3 rounded-4 d-inline-block border mb-4">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode(route('scanner.mobile', $pairingToken)) }}" alt="QR Code" width="180" height="180" class="shadow-sm rounded-3">
                    </div>
                    
                    <div class="mb-4">
                        <span class="text-muted d-block small">Código de Vinculación</span>
                        <h4 class="fw-black text-naval tracking-wide mt-1" style="letter-spacing: 2px;">{{ substr($pairingToken, 4, 6) }}</h4>
                    </div>

                    <div class="d-flex align-items-center justify-content-center gap-2 text-naval fw-medium bg-light py-2 px-3 rounded-pill border" style="width: fit-content; margin: 0 auto;">
                        <span class="dot-pulse"></span>
                        <small style="font-size: 11px;">Esperando escaneo desde el móvil...</small>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0 justify-content-center">
                    <button type="button" class="btn btn-naval rounded-pill px-4 text-white" @click="showScannerModal = false">Entendido</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL ABRIR CAJA -->
    <div x-show="showOpenBoxModal" x-cloak
         class="position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center"
         :class="showOpenBoxModal ? 'd-flex' : 'd-none'"
         style="z-index: 1050; background-color: rgba(0,0,0,0.6);"
         @click.self="showOpenBoxModal = false">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden text-dark bg-white">
                <div class="modal-header border-bottom py-3 px-4 d-flex align-items-center bg-light">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success text-white p-2 rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="background-color: #198754 !important;">
                            <span class="material-symbols-outlined">point_of_sale</span>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-naval mb-0">Apertura de Caja</h5>
                            <small class="text-muted">Iniciar Turno Operativo</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" @click="showOpenBoxModal = false"></button>
                </div>
                <form wire:submit.prevent="openBox">
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-medium">Monto Inicial en Efectivo ({{ $activeCurrency }})</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light fw-bold text-muted">{{ $activeCurrency === 'EUR' ? '€' : ($activeCurrency === 'COP' ? 'COL$' : ($activeCurrency === 'USD' ? 'US$' : '$')) }}</span>
                                <input type="number" step="0.01" min="0" wire:model.defer="openingAmount" class="form-control bg-light fw-bold text-naval" placeholder="0.00" required>
                            </div>
                            <small class="text-secondary d-block mt-1">Efectivo base disponible para dar cambio a clientes.</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small fw-medium">Observaciones de Apertura</label>
                            <textarea wire:model.defer="openingNotes" class="form-control bg-light" rows="2" placeholder="Ej. Billetes y monedas sueltas. Todo en orden."></textarea>
                        </div>

                        <div class="mb-3 p-3 rounded-3 border" style="background: #fff8f0;">
                            <label class="form-label text-muted small fw-bold d-flex align-items-center gap-1 mb-2">
                                <span class="material-symbols-outlined fs-6">lock</span>
                                Confirmar con tu Contraseña
                            </label>
                            <input type="password"
                                   wire:model.defer="openingPassword"
                                   class="form-control bg-white fw-medium @error('openingPassword') is-invalid @enderror"
                                   placeholder="Ingresa tu contraseña de acceso"
                                   autocomplete="current-password"
                                   required>
                            @error('openingPassword')
                                <div class="invalid-feedback fw-bold">{{ $message }}</div>
                            @enderror
                            <small class="text-secondary d-block mt-1">Tu contraseña verifica tu identidad para aperturar la caja.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" @click="showOpenBoxModal = false">Cancelar</button>
                        <button type="submit" class="btn btn-coral text-white rounded-pill px-4 fw-bold">
                            <span class="material-symbols-outlined fs-6 align-middle">lock_open</span>
                            Confirmar Apertura
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL CERRAR CAJA -->
    <div x-show="showCloseBoxModal" x-cloak
         class="position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center"
         :class="showCloseBoxModal ? 'd-flex' : 'd-none'"
         style="z-index: 1050; background-color: rgba(0,0,0,0.6);"
         @click.self="showCloseBoxModal = false">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden text-dark bg-white">
                <div class="modal-header border-bottom py-3 px-4 d-flex align-items-center bg-light">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-danger text-white p-2 rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="background-color: #dc3545 !important;">
                            <span class="material-symbols-outlined">lock</span>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-naval mb-0">Cierre de Caja</h5>
                            <small class="text-muted">Corte de Caja y Arqueo</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" @click="showCloseBoxModal = false"></button>
                </div>
                <form wire:submit.prevent="closeBox">
                    <div class="modal-body p-4">
                        <!-- Desglose de Caja -->
                        <h6 class="fw-bold text-naval mb-3 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Resumen Operativo de Turno</h6>
                        <div class="bg-light rounded-3 p-3 mb-4">
                            <div class="d-flex justify-content-between mb-2 small text-secondary">
                                <span>(+) Monto Inicial de Apertura</span>
                                <span class="fw-medium">{{ $this->formatPrice((float)$openingAmount) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 small text-secondary">
                                <span>(+) Ventas Totales en Efectivo</span>
                                <span class="fw-medium text-success">+{{ $this->formatPrice($cashSalesTotal) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 small text-secondary">
                                <span>(+) Entradas Manuales de Efectivo</span>
                                <span class="fw-medium text-info">+{{ $this->formatPrice($manualInflows) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 small text-secondary border-bottom pb-2">
                                <span>(-) Salidas Manuales de Efectivo</span>
                                <span class="fw-medium text-danger">-{{ $this->formatPrice($manualOutflows) }}</span>
                            </div>
                            <div class="d-flex justify-content-between pt-2">
                                <strong class="text-naval">(=) Efectivo Esperado en Caja</strong>
                                <strong class="text-naval">{{ $this->formatPrice($expectedCash) }}</strong>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small fw-medium">Efectivo Real Contado en Caja ({{ $activeCurrency }})</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light fw-bold text-muted">{{ $activeCurrency === 'EUR' ? '€' : ($activeCurrency === 'COP' ? 'COL$' : ($activeCurrency === 'USD' ? 'US$' : '$')) }}</span>
                                <input type="number" step="0.01" min="0" wire:model.live="closingAmount" class="form-control bg-light fw-bold text-naval" placeholder="0.00" required>
                            </div>
                            <!-- Live calculated difference -->
                            @if(is_numeric($closingAmount))
                                <div class="mt-2 p-2 rounded text-center small {{ ($closingAmount - $expectedCash) == 0 ? 'bg-success bg-opacity-10 text-success' : (($closingAmount - $expectedCash) > 0 ? 'bg-info bg-opacity-10 text-info' : 'bg-danger bg-opacity-10 text-danger') }}">
                                    @if(($closingAmount - $expectedCash) == 0)
                                        <strong class="d-flex align-items-center justify-content-center gap-1"><span class="material-symbols-outlined fs-6">check_circle</span> Caja Cuadrada Perfecta</strong>
                                    @else
                                        <strong>Diferencia: {{ ($closingAmount - $expectedCash) > 0 ? '+' : '-' }}{{ $this->formatPrice(abs($closingAmount - $expectedCash)) }} ({{ ($closingAmount - $expectedCash) > 0 ? 'Sobrante' : 'Faltante' }})</strong>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small fw-medium">Reporte de Incidencias / Notas de Cierre</label>
                            <textarea wire:model.defer="closingNotes" class="form-control bg-light" rows="3" placeholder="Ej. Faltaron $5 pesos por redondeo o todo cuadra perfectamente."></textarea>
                        </div>

                        <div class="mb-3 p-3 rounded-3 border" style="background: #fff5f5;">
                            <label class="form-label text-muted small fw-bold d-flex align-items-center gap-1 mb-2">
                                <span class="material-symbols-outlined fs-6 text-danger">lock</span>
                                Confirmar con tu Contraseña (Obligatorio)
                            </label>
                            <input type="password"
                                   wire:model.defer="closingPassword"
                                   class="form-control bg-white fw-medium @error('closingPassword') is-invalid @enderror"
                                   placeholder="Ingresa tu contraseña de acceso"
                                   autocomplete="current-password"
                                   required>
                            @error('closingPassword')
                                <div class="invalid-feedback fw-bold">{{ $message }}</div>
                            @enderror
                            <small class="text-secondary d-block mt-1">Ingresa tu contraseña para autorizar el cierre y guardar el arqueo.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" @click="showCloseBoxModal = false">Cancelar</button>
                        <button type="submit" class="btn btn-danger text-white rounded-pill px-4 fw-bold">Confirmar Cierre de Caja</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL MOVIMIENTOS DE CAJA (CORTE PARCIAL) -->
    <div x-show="showTransactionModal" x-cloak
         class="position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center"
         :class="showTransactionModal ? 'd-flex' : 'd-none'"
         style="z-index: 1050; background-color: rgba(0,0,0,0.6);"
         @click.self="showTransactionModal = false">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden text-dark bg-white">
                <div class="modal-header border-bottom py-3 px-4 d-flex align-items-center bg-light">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-info text-white p-2 rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="background-color: #0dcaf0 !important;">
                            <span class="material-symbols-outlined">currency_exchange</span>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-naval mb-0">Movimiento de Efectivo</h5>
                            <small class="text-muted">Ingreso o Retiro Parcial</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" @click="showTransactionModal = false"></button>
                </div>
                <form wire:submit.prevent="registerTransaction">
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-medium">Tipo de Movimiento</label>
                            <div class="d-flex gap-2">
                                <button type="button" @click="transactionType = 'in'" 
                                        :class="transactionType === 'in' ? 'btn-info text-white bg-info border-info' : 'btn-light border-secondary text-muted'"
                                        class="btn flex-fill py-2 rounded-pill fw-bold transition-all">
                                    Ingresar Efectivo
                                </button>
                                <button type="button" @click="transactionType = 'out'" 
                                        :class="transactionType === 'out' ? 'btn-danger text-white bg-danger border-danger' : 'btn-light border-secondary text-muted'"
                                        class="btn flex-fill py-2 rounded-pill fw-bold transition-all">
                                    Retirar Efectivo
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small fw-medium">Monto del Movimiento ({{ $activeCurrency }})</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light fw-bold text-muted">{{ $activeCurrency === 'EUR' ? '€' : ($activeCurrency === 'COP' ? 'COL$' : ($activeCurrency === 'USD' ? 'US$' : '$')) }}</span>
                                <input type="number" step="0.01" min="0.01" wire:model.defer="transactionAmount" class="form-control bg-light fw-bold text-naval" placeholder="0.00" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small fw-medium">Concepto / Motivo del Movimiento</label>
                            <input type="text" wire:model.defer="transactionReason" class="form-control bg-light" placeholder="Ej. Aporte de cambio de monedas o Retiro para bóveda principal" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" @click="showTransactionModal = false">Cancelar</button>
                        <button type="submit" class="btn btn-coral text-white rounded-pill px-4 fw-bold">Registrar Movimiento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL APLICAR DESCUENTO -->
    <div x-show="showDiscountModal" x-cloak
         class="position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center"
         :class="showDiscountModal ? 'd-flex' : 'd-none'"
         style="z-index: 1050; background-color: rgba(0,0,0,0.6);"
         @click.self="showDiscountModal = false">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden text-dark bg-white">
                <div class="modal-header border-bottom py-3 px-4 d-flex align-items-center bg-light">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-coral text-white p-2 rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="background-color: #ff6b6b !important;">
                            <span class="material-symbols-outlined text-white">percent</span>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-naval mb-0">Aplicar Descuento</h5>
                            <small class="text-muted">Autorización y Reducción de Precios</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" @click="showDiscountModal = false"></button>
                </div>
                <form wire:submit.prevent="applyDiscount">
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-medium">Tipo de Descuento</label>
                            <div class="d-flex gap-2">
                                <button type="button" @click="discountType = 'percentage'" 
                                        :class="discountType === 'percentage' ? 'btn-coral text-white bg-coral border-coral' : 'btn-light border-secondary text-muted'"
                                        class="btn flex-fill py-2 rounded-pill fw-bold transition-all">
                                    Porcentaje (%)
                                </button>
                                <button type="button" @click="discountType = 'fixed'" 
                                        :class="discountType === 'fixed' ? 'btn-coral text-white bg-coral border-coral' : 'btn-light border-secondary text-muted'"
                                        class="btn flex-fill py-2 rounded-pill fw-bold transition-all">
                                    Monto Fijo ($)
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small fw-medium" x-text="discountType === 'percentage' ? 'Valor del Descuento (%)' : 'Monto Fijo de Descuento ({{ $activeCurrency }})'"></label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light fw-bold text-muted" x-text="discountType === 'percentage' ? '%' : '{{ $activeCurrency === 'EUR' ? '€' : ($activeCurrency === 'COP' ? 'COL$' : ($activeCurrency === 'USD' ? 'US$' : '$')) }}'"></span>
                                <input type="number" step="0.01" min="0" wire:model.defer="discountValue" class="form-control bg-light fw-bold text-naval" placeholder="0.00" required>
                            </div>
                            <small class="text-secondary d-block mt-1">
                                Nota: Los vendedores tienen un límite de 10% o $100.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small fw-medium">Motivo del Descuento (Obligatorio)</label>
                            <textarea wire:model.defer="discountReason" class="form-control bg-light" rows="3" placeholder="Ej. Cliente frecuente, artículo ligeramente rayado o promoción del día" required></textarea>
                            @error('discountReason') <small class="text-danger fw-bold d-block mt-1">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" @click="showDiscountModal = false">Cancelar</button>
                        <button type="submit" class="btn btn-coral text-white rounded-pill px-4 fw-bold">Aplicar Descuento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- FLOATING NOTIFICATIONS (TOAST PORTAL) -->
    <div class="position-fixed top-0 end-0 p-4" style="z-index: 9999; pointer-events: none;">
        @if(session()->has('success') || session()->has('message'))
        <div x-data="{ 
                show: true,
                init() {
                    setTimeout(() => this.show = false, 5000);
                }
             }"
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-4 scale-95"
             x-transition:enter-end="opacity-100 translate-x-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 translate-x-4 scale-95"
             class="card border-0 shadow-lg p-3 mb-3 d-flex flex-row align-items-center gap-3 bg-white"
             style="min-width: 320px; max-width: 400px; border-left: 5px solid #198754 !important; border-radius: 16px; pointer-events: auto;"
             x-cloak>
            <span class="material-symbols-outlined text-success fs-1">check_circle</span>
            <div class="flex-grow-1 me-2">
                <h6 class="fw-bold text-success mb-0" style="font-size: 14px;">¡Acción Confirmada!</h6>
                <small class="text-secondary fw-medium" style="font-size: 12px; line-height: 1.3;">{{ session('success') ?? session('message') }}</small>
            </div>
            <button type="button" class="btn-close align-self-start" @click="show = false" style="font-size: 10px;"></button>
        </div>
        @endif

        @if(session()->has('error'))
        <div x-data="{ 
                show: true,
                init() {
                    setTimeout(() => this.show = false, 6000);
                }
             }"
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-4 scale-95"
             x-transition:enter-end="opacity-100 translate-x-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 translate-x-4 scale-95"
             class="card border-0 shadow-lg p-3 mb-3 d-flex flex-row align-items-center gap-3 bg-white"
             style="min-width: 320px; max-width: 400px; border-left: 5px solid #dc3545 !important; border-radius: 16px; pointer-events: auto;"
             x-cloak>
            <span class="material-symbols-outlined text-danger fs-1">error</span>
            <div class="flex-grow-1 me-2">
                <h6 class="fw-bold text-danger mb-0" style="font-size: 14px;">Error Detectado</h6>
                <small class="text-secondary fw-medium" style="font-size: 12px; line-height: 1.3;">{{ session('error') }}</small>
            </div>
            <button type="button" class="btn-close align-self-start" @click="show = false" style="font-size: 10px;"></button>
        </div>
        @endif
    </div>
</div>
