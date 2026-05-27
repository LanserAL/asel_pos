<div class="min-vh-100 bg-light d-flex flex-column" style="font-family: 'Inter', sans-serif;">
    <!-- Public Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm sticky-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-3" href="/">
                @if($tenant && $tenant->logo_path)
                    <img src="{{ $tenant->logo_path }}" alt="Logo" width="45" height="45" class="rounded-3 shadow-sm object-fit-cover">
                @else
                    <div class="bg-coral rounded-3 d-flex align-items-center justify-content-center text-white shadow-sm" style="width: 45px; height: 45px; background-color: #ff6b6b;">
                        <span class="material-symbols-outlined">store</span>
                    </div>
                @endif
                <div>
                    <h1 class="h5 fw-bold text-naval mb-0 tracking-tight">{{ $tenant ? $tenant->name : 'ASEL POS' }}</h1>
                    <p class="text-secondary text-uppercase fw-bold mb-0" style="font-size: 9px; letter-spacing: 1px;">by ASEL POS</p>
                </div>
            </a>
            
            @if($selectedBranchId)
                <div class="d-flex align-items-center gap-3 ms-auto">
                    <!-- Current Branch Indicator -->
                    <button wire:click="changeBranch" class="btn btn-outline-secondary btn-sm rounded-pill px-3 d-flex align-items-center gap-1">
                        <span class="material-symbols-outlined fs-6">pin_drop</span>
                        <span class="small fw-semibold">{{ optional($branches->firstWhere('id', $selectedBranchId))->name }}</span>
                        <span class="material-symbols-outlined fs-6 text-coral">cached</span>
                    </button>

                    <!-- Cart Toggle Button -->
                    <button wire:click="$set('showCart', true)" class="btn btn-coral text-white rounded-pill px-4 py-2 d-flex align-items-center gap-2 fw-bold shadow-sm position-relative">
                        <span class="material-symbols-outlined">shopping_bag</span>
                        <span class="d-none d-md-inline">Carrito</span>
                        @if($cartCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </button>
                </div>
            @endif
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow-1 container py-5">
        @if(!$selectedBranchId)
            <!-- Branch Selection Overlay Screen -->
            <div class="row justify-content-center py-5">
                <div class="col-md-6 col-lg-5">
                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white text-center p-4 p-md-5">
                        <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-4 bg-coral bg-opacity-10 text-coral" style="width: 80px; height: 80px;">
                            <span class="material-symbols-outlined" style="font-size: 40px; color: #ff6b6b;">pin_drop</span>
                        </div>
                        
                        <h3 class="fw-bold text-naval mb-2">¡Bienvenido a nuestra tienda!</h3>
                        <p class="text-secondary mb-4">Para poder mostrarte los productos y existencias correctas, por favor selecciona tu sucursal más cercana:</p>
                        
                        <div class="d-flex flex-column gap-3">
                            @forelse($branches as $branch)
                                <button wire:click="selectBranch({{ $branch->id }})" class="btn btn-outline-naval w-100 rounded-pill py-3 fw-bold text-start px-4 d-flex align-items-center justify-content-between transition-all hover-scale shadow-sm">
                                    <span>{{ $branch->name }}</span>
                                    <span class="material-symbols-outlined text-coral">arrow_forward</span>
                                </button>
                            @empty
                                <div class="alert alert-info border-0 rounded-4">
                                    No hay sucursales activas en este momento.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Catalog Product Grid -->
            <div class="mb-5">
                <div class="row align-items-center g-3">
                    <div class="col-md-6">
                        <h2 class="fw-bold text-naval mb-1">Catálogo de Productos</h2>
                        <p class="text-secondary small mb-0">Existencias disponibles en la sucursal elegida.</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="position-relative d-inline-block w-100" style="max-width: 350px;">
                            <span class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y ms-3 text-muted">search</span>
                            <input wire:model.live="search" class="form-control form-control-lg bg-white border shadow-sm ps-5 rounded-pill" placeholder="Buscar productos..." type="text">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            @if(session()->has('success'))
                <div class="alert alert-success border-0 shadow-sm rounded-4 d-flex align-items-center gap-2 mb-4 p-3 fw-semibold">
                    <span class="material-symbols-outlined">check_circle</span>
                    {{ session('success') }}
                </div>
            @endif
            @if(session()->has('error'))
                <div class="alert alert-danger border-0 shadow-sm rounded-4 d-flex align-items-center gap-2 mb-4 p-3 fw-semibold">
                    <span class="material-symbols-outlined">warning</span>
                    {{ session('error') }}
                </div>
            @endif

            <div class="row g-4">
                @forelse($products as $product)
                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <div class="card h-100 border-0 shadow-sm rounded-4 hover-shadow transition-all overflow-hidden bg-white text-center">
                            <!-- Image Area -->
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center position-relative" style="height: 200px;">
                                @if(is_object($product) && $product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->title }}" class="img-fluid h-100 w-100 object-fit-cover">
                                @else
                                    <span class="material-symbols-outlined text-muted" style="font-size: 54px; opacity: 0.15;">inventory_2</span>
                                @endif
                                
                                <div class="position-absolute top-0 end-0 m-3">
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1.5 fw-bold shadow-sm" style="font-size: 10px;">
                                        Stock: {{ $product->stock }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Details Area -->
                            <div class="card-body p-4 d-flex flex-column justify-content-between">
                                <div class="mb-3">
                                    <h5 class="card-title fw-bold text-dark mb-1 fs-6 text-truncate" title="{{ $product->title }}">{{ $product->title }}</h5>
                                    <p class="card-text text-muted small mb-0 text-truncate">{{ $product->description ?? 'Sin descripción disponible.' }}</p>
                                </div>
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="fs-4 fw-black text-coral">${{ number_format($product->price, 2) }}</span>
                                    </div>
                                    <button wire:click="addToCart({{ $product->id }})" class="btn btn-naval w-100 rounded-pill fw-bold d-flex align-items-center justify-content-center gap-2 py-2">
                                        <span class="material-symbols-outlined">add_shopping_cart</span>
                                        Añadir al Carrito
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center py-5 bg-white rounded-4 shadow-sm border">
                            <span class="material-symbols-outlined mb-3 opacity-25" style="font-size: 64px;">search_off</span>
                            <h4 class="fw-bold text-naval">No hay productos disponibles</h4>
                            <p class="text-muted">No se encontraron productos disponibles en esta sucursal en este momento.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        @endif
    </main>

    <!-- Cart Sidebar / Modal Overlay Drawer -->
    @if($showCart)
        <div class="modal-backdrop fade show" wire:click="$set('showCart', false)" style="z-index: 1040;"></div>
        <div class="bg-white position-fixed top-0 end-0 h-100 shadow-lg d-flex flex-column border-start transition-all" style="width: 100%; max-width: 480px; z-index: 1050; animation: slideInRight 0.3s cubic-bezier(0.16, 1, 0.3, 1);">
            <!-- Sidebar Header -->
            <div class="p-4 border-bottom d-flex align-items-center justify-content-between bg-light">
                <div class="d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined text-coral">shopping_cart</span>
                    <h5 class="fw-bold text-naval mb-0">Mi Carrito</h5>
                </div>
                <button wire:click="$set('showCart', false)" class="btn-close"></button>
            </div>

            <!-- Sidebar Content -->
            <div class="flex-grow-1 overflow-auto p-4">
                @if(empty($cart))
                    <div class="text-center py-5">
                        <span class="material-symbols-outlined fs-1 text-muted opacity-25 mb-3">shopping_cart_off</span>
                        <h6 class="fw-bold text-naval">Tu carrito está vacío</h6>
                        <p class="text-secondary small">Agrega productos desde el catálogo para continuar con tu compra.</p>
                    </div>
                @else
                    <!-- Cart Items List -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-naval mb-3 border-bottom pb-2">Artículos Seleccionados</h6>
                        <div class="d-flex flex-column gap-3">
                            @foreach($cart as $item)
                                <div class="d-flex align-items-center gap-3 bg-light p-3 rounded-3 border">
                                    <div class="bg-white rounded border d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; flex-shrink: 0; overflow: hidden;">
                                        @if($item['image'])
                                            <img src="{{ asset('storage/' . $item['image']) }}" alt="img" class="img-fluid object-fit-cover h-100 w-100">
                                        @else
                                            <span class="material-symbols-outlined text-muted fs-5">inventory_2</span>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <h6 class="fw-bold text-dark text-truncate mb-0" style="font-size: 13px;">{{ $item['name'] }}</h6>
                                        <span class="text-coral fw-bold small">${{ number_format($item['price'], 2) }} c/u</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <button wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] - 1 }})" class="btn btn-light btn-sm rounded-circle p-1 d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">-</button>
                                        <span class="fw-bold px-2 text-naval" style="font-size: 13px;">{{ $item['quantity'] }}</span>
                                        <button wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] + 1 }})" class="btn btn-light btn-sm rounded-circle p-1 d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">+</button>
                                    </div>
                                    <button wire:click="removeFromCart({{ $item['id'] }})" class="btn btn-link text-danger p-0 ms-2">
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Checkout Form -->
                    <div class="border-top pt-4">
                        <h6 class="fw-bold text-naval mb-3 border-bottom pb-2">Información del Cliente</h6>
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label text-muted fw-bold text-uppercase small" style="font-size: 10px;">Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text" wire:model="customerName" class="form-control bg-light border-0 shadow-sm" placeholder="Ej. Juan Pérez">
                                @error('customerName') <div class="text-danger small mt-1 fw-bold" style="font-size: 11px;">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label text-muted fw-bold text-uppercase small" style="font-size: 10px;">Teléfono de Contacto <span class="text-danger">*</span></label>
                                <input type="text" wire:model="customerPhone" class="form-control bg-light border-0 shadow-sm font-monospace" placeholder="Ej. 5512345678">
                                @error('customerPhone') <div class="text-danger small mt-1 fw-bold" style="font-size: 11px;">{{ $message }}</div> @enderror
                            </div>

                            <!-- Shipping Toggle -->
                            <div class="col-12 my-3">
                                <div class="form-check form-switch p-3 bg-light rounded-3 border d-flex justify-content-between align-items-center">
                                    <div class="form-check-label d-flex align-items-center gap-2">
                                        <span class="material-symbols-outlined text-coral">local_shipping</span>
                                        <div>
                                            <strong class="text-naval d-block" style="font-size: 13px;">¿Requiere envío a domicilio?</strong>
                                            <small class="text-secondary d-block" style="font-size: 10px;">Cotizado internamente por personal</small>
                                        </div>
                                    </div>
                                    <input class="form-check-input ms-0" type="checkbox" wire:model.live="isShippingRequired" role="switch" style="width: 45px; height: 22px;">
                                </div>
                            </div>

                            <!-- Shipping Address Input -->
                            @if($isShippingRequired)
                                <div class="col-12" style="animation: fadeIn 0.3s ease;">
                                    <label class="form-label text-muted fw-bold text-uppercase small" style="font-size: 10px;">Dirección de Entrega <span class="text-danger">*</span></label>
                                    <textarea wire:model="shippingAddress" class="form-control bg-light border-0 shadow-sm" rows="3" placeholder="Calle, Número, Colonia, Municipio, CP..."></textarea>
                                    @error('shippingAddress') <div class="text-danger small mt-1 fw-bold" style="font-size: 11px;">{{ $message }}</div> @enderror
                                </div>
                            @endif

                            <!-- Payment Method Cards -->
                            <div class="col-12 my-3">
                                <label class="form-label text-muted fw-bold text-uppercase small" style="font-size: 10px;">Método de Pago <span class="text-danger">*</span></label>
                                
                                <div class="d-flex flex-column gap-2 mt-1">
                                    <!-- Tarjeta -->
                                    <label class="d-flex align-items-center justify-content-between p-3 border rounded-3 cursor-pointer transition-all {{ $paymentMethod === 'tarjeta' ? 'border-coral bg-coral bg-opacity-5' : 'bg-light' }}" style="cursor: pointer;">
                                        <div class="d-flex align-items-center gap-3">
                                            <input type="radio" wire:model.live="paymentMethod" value="tarjeta" class="form-check-input text-coral" {{ $paymentMethod === 'tarjeta' ? 'checked' : '' }}>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="material-symbols-outlined text-naval">credit_card</span>
                                                <strong class="text-naval small">Tarjeta Débito / Crédito</strong>
                                            </div>
                                        </div>
                                    </label>
                                    
                                    <!-- Transferencia -->
                                    <label class="d-flex align-items-center justify-content-between p-3 border rounded-3 cursor-pointer transition-all {{ $paymentMethod === 'transferencia' ? 'border-coral bg-coral bg-opacity-5' : 'bg-light' }}" style="cursor: pointer;">
                                        <div class="d-flex align-items-center gap-3">
                                            <input type="radio" wire:model.live="paymentMethod" value="transferencia" class="form-check-input text-coral" {{ $paymentMethod === 'transferencia' ? 'checked' : '' }}>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="material-symbols-outlined text-naval">account_balance</span>
                                                <strong class="text-naval small">Transferencia Bancaria</strong>
                                            </div>
                                        </div>
                                    </label>

                                    <!-- Efectivo -->
                                    <label class="d-flex align-items-center justify-content-between p-3 border rounded-3 cursor-pointer transition-all {{ $paymentMethod === 'efectivo' ? 'border-coral bg-coral bg-opacity-5' : 'bg-light' }}" style="cursor: pointer;">
                                        <div class="d-flex align-items-center gap-3">
                                            <input type="radio" wire:model.live="paymentMethod" value="efectivo" class="form-check-input text-coral" {{ $paymentMethod === 'efectivo' ? 'checked' : '' }}>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="material-symbols-outlined text-naval">payments</span>
                                                <strong class="text-naval small">Efectivo al recibir / Caja</strong>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                @error('paymentMethod') <div class="text-danger small mt-1 fw-bold" style="font-size: 11px;">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar Footer -->
            @if(!empty($cart))
                <div class="p-4 border-top bg-light">
                    <div class="border rounded-3 p-3 bg-white shadow-sm mb-3">
                        <div class="d-flex justify-content-between mb-2 text-secondary small">
                            <span>Subtotal</span>
                            <span>${{ number_format($cartSubtotal, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-secondary small">
                            <span>IVA (16%)</span>
                            <span>${{ number_format($cartSubtotal * 0.16, 2) }}</span>
                        </div>
                        @if($isShippingRequired)
                            <div class="d-flex justify-content-between mb-2 text-secondary small">
                                <span>Costo de Envío</span>
                                <span class="fw-bold text-coral">Por cotizar</span>
                            </div>
                        @endif
                        <hr class="my-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-naval">Total</span>
                            <span class="fs-4 fw-black text-coral">${{ number_format($cartSubtotal * 1.16, 2) }}</span>
                        </div>
                    </div>
                    
                    <button wire:click="checkout" class="btn btn-coral w-100 rounded-pill py-3 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm text-white transition-all hover-scale" style="background-color: #ff6b6b; border-color: #ff6b6b;">
                        <span class="material-symbols-outlined">shopping_cart_checkout</span>
                        Finalizar Compra
                    </button>
                </div>
            @endif
        </div>
    @endif

    <!-- Footer -->
    <footer class="bg-white border-top py-4 mt-auto">
        <div class="container text-center">
            <p class="text-secondary small fw-semibold mb-0">&copy; {{ date('Y') }} {{ $tenant ? $tenant->name : 'ASEL POS' }}. Todos los derechos reservados.</p>
        </div>
    </footer>

    <style>
        .btn-outline-naval {
            color: #0e2649;
            border-color: #0e2649;
            background-color: transparent;
            transition: all 0.25s ease;
        }
        .btn-outline-naval:hover {
            background-color: #0e2649;
            color: white;
            box-shadow: 0 4px 8px rgba(14, 38, 73, 0.15);
        }
        .btn-naval {
            background-color: #0e2649;
            border-color: #0e2649;
            color: white;
            transition: all 0.25s ease;
        }
        .btn-naval:hover {
            background-color: #1b3d6f;
            border-color: #1b3d6f;
            color: white;
            box-shadow: 0 4px 8px rgba(14, 38, 73, 0.2);
        }
        .text-naval {
            color: #0e2649;
        }
        .text-coral {
            color: #ff6b6b;
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
            box-shadow: 0 4px 8px rgba(255, 107, 107, 0.25);
        }
        .bg-coral {
            background-color: #ff6b6b;
        }
        .hover-shadow:hover {
            box-shadow: 0 1rem 2.5rem rgba(14,38,73,0.12)!important;
            transform: translateY(-4px);
        }
        .hover-scale:hover {
            transform: scale(1.02);
        }
        .transition-all {
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .fw-black {
            font-weight: 900;
        }
        @keyframes slideInRight {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</div>
