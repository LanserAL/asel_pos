@props(['active' => null])
@php
    $routePrefix = auth()->check() && auth()->user()->hasRole('admin') ? 'admin' : 'vendedor';
@endphp

<nav :style="sidebarOpen ? 'width: 300px;' : 'width: 80px;'" class="d-none d-md-flex flex-column vh-100 bg-white border-end position-fixed z-3 shadow-sm sidebar-wrapper overflow-hidden transition-all" style="transition: all 0.3s ease;">
    <div class="d-flex flex-column h-100 transition-all" :class="sidebarOpen ? 'p-4' : 'p-2 py-4'">
        <div class="d-flex align-items-center mb-5" :class="sidebarOpen ? 'justify-content-between' : 'justify-content-center'">
            <div x-show="sidebarOpen" style="display: none;">
                <div class="d-flex align-items-center gap-3 transition-all">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" width="40" height="40" class="rounded-3 shadow-sm flex-shrink-0">
                    <div class="text-nowrap">
                        <h5 class="mb-0 fw-bold text-naval">ASEL POS</h5>
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 1px;">
                            {{ auth()->user()->tenant_id ? (auth()->user()->hasRole('admin') ? 'Admin Tienda' : 'Vendedor') : 'Súper Admin' }}
                        </small>
                    </div>
                </div>
            </div>
            <button @click="sidebarOpen = !sidebarOpen" class="btn btn-light border shadow-sm d-flex align-items-center justify-content-center p-2 rounded-3 transition-all sidebar-hover-effect">
                <span class="material-symbols-outlined" x-text="sidebarOpen ? 'menu_open' : 'menu'"></span>
            </button>
        </div>
        
        <ul class="nav nav-pills flex-column mb-auto gap-2">
            @if(auth()->user()->tenant_id)
                <!-- Menú General (Tenant) -->
                <li class="nav-item">
                    <a href="{{ route($routePrefix . '.dashboard') }}" :title="!sidebarOpen ? 'Dashboard' : ''" class="{{ ($active === 'vendedor.dashboard' || $active === 'admin.dashboard' || str_ends_with($active, '.dashboard')) ? 'nav-link bg-coral text-white fw-bold d-flex align-items-center rounded-pill transition-all shadow-sm active-mark' : 'nav-link text-secondary fw-medium d-flex align-items-center rounded-pill btn-light transition-all sidebar-hover-effect' }}" :class="sidebarOpen ? 'px-4 py-3 gap-3' : 'justify-content-center p-3'">
                        <span class="material-symbols-outlined flex-shrink-0" style="font-variation-settings: 'FILL' 1;">dashboard</span>
                        <div x-show="sidebarOpen"><span class="text-nowrap transition-all">Dashboard</span></div>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('pos') }}" :title="!sidebarOpen ? 'Punto de Venta' : ''" class="{{ $active === 'pos' ? 'nav-link bg-coral text-white fw-bold d-flex align-items-center rounded-pill transition-all shadow-sm active-mark' : 'nav-link text-secondary fw-medium d-flex align-items-center rounded-pill btn-light transition-all sidebar-hover-effect' }}" :class="sidebarOpen ? 'px-4 py-3 gap-3' : 'justify-content-center p-3'">
                        <span class="material-symbols-outlined flex-shrink-0">point_of_sale</span>
                        <div x-show="sidebarOpen"><span class="text-nowrap transition-all">Punto de Venta</span></div>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route($routePrefix . '.orders') }}" :title="!sidebarOpen ? 'Ventas' : ''" class="{{ ($active === 'vendedor.orders' || $active === 'admin.orders' || str_ends_with($active, '.orders')) ? 'nav-link bg-coral text-white fw-bold d-flex align-items-center rounded-pill transition-all shadow-sm active-mark' : 'nav-link text-secondary fw-medium d-flex align-items-center rounded-pill btn-light transition-all sidebar-hover-effect' }}" :class="sidebarOpen ? 'px-4 py-3 gap-3' : 'justify-content-center p-3'">
                        <span class="material-symbols-outlined flex-shrink-0">receipt_long</span>
                        <div x-show="sidebarOpen"><span class="text-nowrap transition-all">Ventas</span></div>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route($routePrefix . '.customers') }}" :title="!sidebarOpen ? 'Clientes y Facturación' : ''" class="{{ ($active === 'vendedor.customers' || $active === 'admin.customers' || str_ends_with($active, '.customers')) ? 'nav-link bg-coral text-white fw-bold d-flex align-items-center rounded-pill transition-all shadow-sm active-mark' : 'nav-link text-secondary fw-medium d-flex align-items-center rounded-pill btn-light transition-all sidebar-hover-effect' }}" :class="sidebarOpen ? 'px-4 py-3 gap-3' : 'justify-content-center p-3'">
                        <span class="material-symbols-outlined flex-shrink-0">groups</span>
                        <div x-show="sidebarOpen"><span class="text-nowrap transition-all">Clientes y Facturación</span></div>
                    </a>
                </li>

                @if(auth()->user()->hasRole('admin'))
                <!-- Opciones solo para Admin de Tienda -->
                <li class="nav-item">
                    <a href="{{ route($routePrefix . '.branches') }}" :title="!sidebarOpen ? 'Sucursales' : ''" class="{{ ($active === 'vendedor.branches' || $active === 'admin.branches' || str_ends_with($active, '.branches')) ? 'nav-link bg-coral text-white fw-bold d-flex align-items-center rounded-pill transition-all shadow-sm active-mark' : 'nav-link text-secondary fw-medium d-flex align-items-center rounded-pill btn-light transition-all sidebar-hover-effect' }}" :class="sidebarOpen ? 'px-4 py-3 gap-3' : 'justify-content-center p-3'">
                        <span class="material-symbols-outlined flex-shrink-0">store</span>
                        <div x-show="sidebarOpen"><span class="text-nowrap transition-all">Sucursales</span></div>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route($routePrefix . '.products') }}" :title="!sidebarOpen ? 'Productos' : ''" class="{{ ($active === 'vendedor.products' || $active === 'admin.products' || str_ends_with($active, '.products')) ? 'nav-link bg-coral text-white fw-bold d-flex align-items-center rounded-pill transition-all shadow-sm active-mark' : 'nav-link text-secondary fw-medium d-flex align-items-center rounded-pill btn-light transition-all sidebar-hover-effect' }}" :class="sidebarOpen ? 'px-4 py-3 gap-3' : 'justify-content-center p-3'">
                        <span class="material-symbols-outlined flex-shrink-0">inventory_2</span>
                        <div x-show="sidebarOpen"><span class="text-nowrap transition-all">Productos</span></div>
                    </a>
                </li>
                @if(auth()->user()->hasRole('admin'))
                <li class="nav-item">
                    <a href="{{ route('admin.promotions') }}" :title="!sidebarOpen ? 'Promociones' : ''" class="{{ ($active === 'admin.promotions' || str_ends_with($active, '.promotions')) ? 'nav-link bg-coral text-white fw-bold d-flex align-items-center rounded-pill transition-all shadow-sm active-mark' : 'nav-link text-secondary fw-medium d-flex align-items-center rounded-pill btn-light transition-all sidebar-hover-effect' }}" :class="sidebarOpen ? 'px-4 py-3 gap-3' : 'justify-content-center p-3'">
                        <span class="material-symbols-outlined flex-shrink-0">percent</span>
                        <div x-show="sidebarOpen"><span class="text-nowrap transition-all">Promociones</span></div>
                    </a>
                </li>
                @endif
                <li class="nav-item">
                    <a href="{{ route($routePrefix . '.inventory') }}" :title="!sidebarOpen ? 'Inventario' : ''" class="{{ ($active === 'vendedor.inventory' || $active === 'admin.inventory' || str_ends_with($active, '.inventory')) ? 'nav-link bg-coral text-white fw-bold d-flex align-items-center rounded-pill transition-all shadow-sm active-mark' : 'nav-link text-secondary fw-medium d-flex align-items-center rounded-pill btn-light transition-all sidebar-hover-effect' }}" :class="sidebarOpen ? 'px-4 py-3 gap-3' : 'justify-content-center p-3'">
                        <span class="material-symbols-outlined flex-shrink-0">warehouse</span>
                        <div x-show="sidebarOpen"><span class="text-nowrap transition-all">Inventario</span></div>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route($routePrefix . '.providers') }}" :title="!sidebarOpen ? 'Proveedores' : ''" class="{{ ($active === 'vendedor.providers' || $active === 'admin.providers' || str_ends_with($active, '.providers')) ? 'nav-link bg-coral text-white fw-bold d-flex align-items-center rounded-pill transition-all shadow-sm active-mark' : 'nav-link text-secondary fw-medium d-flex align-items-center rounded-pill btn-light transition-all sidebar-hover-effect' }}" :class="sidebarOpen ? 'px-4 py-3 gap-3' : 'justify-content-center p-3'">
                        <span class="material-symbols-outlined flex-shrink-0">local_shipping</span>
                        <div x-show="sidebarOpen"><span class="text-nowrap transition-all">Proveedores</span></div>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.reports') }}" :title="!sidebarOpen ? 'Reportes' : ''" class="{{ ($active === 'admin.reports' || str_ends_with($active, '.reports')) ? 'nav-link bg-coral text-white fw-bold d-flex align-items-center rounded-pill transition-all shadow-sm active-mark' : 'nav-link text-secondary fw-medium d-flex align-items-center rounded-pill btn-light transition-all sidebar-hover-effect' }}" :class="sidebarOpen ? 'px-4 py-3 gap-3' : 'justify-content-center p-3'">
                        <span class="material-symbols-outlined flex-shrink-0">analytics</span>
                        <div x-show="sidebarOpen"><span class="text-nowrap transition-all">Reportes</span></div>
                    </a>
                </li>

                @endif

                <li class="nav-item">
                    <a href="{{ route('catalogo') }}" :title="!sidebarOpen ? 'Catálogo Web' : ''" class="nav-link text-secondary fw-medium d-flex align-items-center rounded-pill btn-light transition-all sidebar-hover-effect" :class="sidebarOpen ? 'px-4 py-3 gap-3' : 'justify-content-center p-3'">
                        <span class="material-symbols-outlined flex-shrink-0">public</span>
                        <div x-show="sidebarOpen"><span class="text-nowrap transition-all">Catálogo Web</span></div>
                    </a>
                </li>
                

            @else
                <!-- Menú del Súper Admin -->
                <li class="nav-item">
                    <a href="{{ route('super-admin') }}" :title="!sidebarOpen ? 'Panel Global' : ''" class="{{ $active === 'super-admin' ? 'nav-link bg-coral text-white fw-bold d-flex align-items-center rounded-pill transition-all shadow-sm active-mark' : 'nav-link text-secondary fw-medium d-flex align-items-center rounded-pill btn-light transition-all sidebar-hover-effect' }}" :class="sidebarOpen ? 'px-4 py-3 gap-3' : 'justify-content-center p-3'">
                        <span class="material-symbols-outlined flex-shrink-0" style="font-variation-settings: 'FILL' 1;">dashboard</span>
                        <span x-show="sidebarOpen" class="text-nowrap transition-all">Panel Global</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('super-admin.tenants') }}" :title="!sidebarOpen ? 'Tiendas / Usuarios' : ''" class="{{ $active === 'super-admin.tenants' ? 'nav-link bg-coral text-white fw-bold d-flex align-items-center rounded-pill transition-all shadow-sm active-mark' : 'nav-link text-secondary fw-medium d-flex align-items-center rounded-pill btn-light transition-all sidebar-hover-effect' }}" :class="sidebarOpen ? 'px-4 py-3 gap-3' : 'justify-content-center p-3'">
                        <span class="material-symbols-outlined flex-shrink-0">storefront</span>
                        <span x-show="sidebarOpen" class="text-nowrap transition-all">Tiendas / Usuarios</span>
                    </a>
                </li>
            @endif
        </ul>
        
        @if(!auth()->user()->tenant_id)
        <div class="mt-auto pt-4 border-top">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" :title="!sidebarOpen ? 'Cerrar Sesión' : ''" class="btn btn-light text-secondary fw-medium d-flex align-items-center rounded-pill w-100 sidebar-hover-effect" :class="sidebarOpen ? 'px-4 py-3 gap-3' : 'justify-content-center p-3'">
                    <span class="material-symbols-outlined flex-shrink-0">logout</span>
                    <span x-show="sidebarOpen" class="text-nowrap">Cerrar Sesión</span>
                </button>
            </form>
        </div>
        @endif
    </div>
    
    <style>
        .sidebar-hover-effect {
            transition: all 0.3s ease;
        }
        .sidebar-hover-effect:hover {
            background-color: rgba(255, 107, 107, 0.1) !important;
            color: #ff6b6b !important;
            transform: translateX(4px);
        }
        .active-mark {
            position: relative;
        }
        .active-mark::before {
            content: '';
            position: absolute;
            left: -1rem;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 60%;
            background-color: #ff6b6b;
            border-radius: 0 4px 4px 0;
            box-shadow: 2px 0 8px rgba(255, 107, 107, 0.5);
        }
    </style>
</nav>
