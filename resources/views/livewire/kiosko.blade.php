<div class="min-vh-100 py-5 bg-light" style="font-family: 'Inter', sans-serif;">
    <div class="container py-4">
        <!-- Brand Header -->
        <header class="text-center mb-5">
            <span class="badge bg-coral text-white px-3 py-2 rounded-pill fw-bold mb-2 shadow-sm">PORTAL MULTI-TIENDA</span>
            <h1 class="fw-extrabold text-naval display-4 mb-2" style="font-weight: 800; letter-spacing: -1px;">Directorio de Tiendas ASEL</h1>
            <p class="text-secondary mx-auto fs-5" style="max-width: 600px;">
                Encuentra tu sucursal favorita, explora sus catálogos actualizados en tiempo real y realiza tu pedido al instante por WhatsApp.
            </p>
        </header>

        <!-- Search Bar Card -->
        <div class="row justify-content-center mb-5">
            <div class="col-md-8">
                <div class="card border-0 shadow-lg p-3 rounded-4 bg-white">
                    <div class="input-group input-group-lg border-0">
                        <span class="input-group-text bg-white border-0 text-muted ps-3">
                            <span class="material-symbols-outlined fs-3 text-secondary">search</span>
                        </span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-0 bg-white fs-5 ps-2 text-naval" placeholder="Buscar por nombre de tienda o descripción..." style="box-shadow: none;">
                        @if($search)
                            <button type="button" wire:click="$set('search', '')" class="btn bg-white border-0 text-secondary pe-3 d-flex align-items-center">
                                <span class="material-symbols-outlined fs-4">close</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Stores Grid -->
        <div class="row g-4 justify-content-center">
            @forelse($tenants as $t)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative hover-card bg-white" style="transition: all 0.3s ease;">
                        
                        <!-- Premium Card Accent -->
                        <div class="position-absolute top-0 start-0 w-100" style="height: 6px; background: linear-gradient(90deg, #ff6b6b, #1a2b4c);"></div>
                        
                        <div class="card-body p-4 d-flex flex-column align-items-center text-center mt-3">
                            <!-- Shop Logo / Initials Badge -->
                            <div class="mb-4 position-relative">
                                @if($t->logo_path)
                                    <img src="{{ asset('storage/' . $t->logo_path) }}" alt="{{ $t->name }}" class="rounded-circle shadow-sm border p-1" style="width: 90px; height: 90px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle shadow-sm d-flex align-items-center justify-content-center fw-bold text-white fs-2 text-uppercase border p-1" 
                                         style="width: 90px; height: 90px; background: linear-gradient(135deg, #1a2b4c 0%, #ff6b6b 100%);">
                                        {{ substr($t->name, 0, 2) }}
                                    </div>
                                @endif
                                <span class="position-absolute bottom-0 end-0 badge bg-success border border-white rounded-circle p-2" title="Tienda Activa">
                                    <span class="visually-hidden">Activo</span>
                                </span>
                            </div>

                            <!-- Shop Details -->
                            <h4 class="card-title fw-bold text-naval mb-2">{{ $t->name }}</h4>
                            <p class="card-text text-secondary small mb-4 px-2" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; height: 50px;">
                                {{ $t->description ?? 'Explora el catálogo de productos disponibles en nuestras sucursales y pide por WhatsApp hoy mismo.' }}
                            </p>

                            <!-- Custom Badge Grid -->
                            <div class="d-flex flex-wrap gap-2 justify-content-center mb-4 mt-auto">
                                <span class="badge bg-light text-secondary border px-3 py-1 rounded-pill small fw-medium" style="font-size: 11px;">
                                    <span class="material-symbols-outlined fs-6 align-middle me-1">storefront</span> {{ $t->branches()->where('status', 'active')->count() }} Sucursales
                                </span>
                                <span class="badge bg-light text-secondary border px-3 py-1 rounded-pill small fw-medium" style="font-size: 11px;">
                                    <span class="material-symbols-outlined fs-6 align-middle me-1">inventory_2</span> {{ $t->products()->where('status', 'active')->count() }} Productos
                                </span>
                            </div>

                            <!-- CTA Button -->
                            <a href="{{ route('catalogo', ['tenant' => $t->slug]) }}" class="btn btn-coral w-100 rounded-pill py-2.5 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm text-decoration-none text-white hover-button">
                                <span class="material-symbols-outlined">shopping_bag</span>
                                Visitar Tienda
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="mb-4">
                        <span class="material-symbols-outlined fs-1 text-secondary opacity-50" style="font-size: 80px !important;">store_slash</span>
                    </div>
                    <h3 class="fw-bold text-naval">No se encontraron tiendas</h3>
                    <p class="text-secondary">Prueba con otra palabra de búsqueda o verifica más tarde.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Hover styles and premium touches -->
    <style>
        .hover-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(26, 43, 76, 0.12) !important;
        }
        .hover-button {
            transition: all 0.2s ease;
        }
        .hover-button:hover {
            transform: scale(1.02);
            background-color: #fa5252 !important;
            box-shadow: 0 4px 10px rgba(255, 107, 107, 0.3) !important;
        }
    </style>
</div>
