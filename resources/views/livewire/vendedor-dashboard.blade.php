<div x-data="{ sidebarOpen: true }" class="d-flex w-100 min-vh-100 bg-light text-dark"
    style="font-family: 'Instrument Sans', sans-serif;">

    <!-- SideNavBar -->
    <x-sidebar active="vendedor.dashboard" />

    <!-- Main Content -->
    <main class="flex-grow-1 p-4 p-md-5 main-content w-100 transition-all"
        :style="sidebarOpen ? 'margin-left: 300px; max-width: calc(100% - 300px);' : 'margin-left: 80px; max-width: calc(100% - 80px);'"
        style="transition: all 0.3s ease;">
        <!-- Header -->
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
            <div class="d-flex align-items-center gap-3">
                <div>
                    <h2 class="fw-bold text-naval mb-1">Hola, {{ explode(' ', auth()->user()->name ?? 'Vendedor')[0] }}
                    </h2>
                    <p class="text-muted mb-0 small fw-medium">Resumen de ventas en: <strong
                            class="text-dark">{{ $tenantName }}</strong></p>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('vendedor.products') }}"
                    class="btn btn-coral d-flex align-items-center gap-2 fw-bold rounded-pill px-4 py-2 shadow-sm text-decoration-none text-white">
                    <span class="material-symbols-outlined fs-5">add</span>
                    Nuevo Producto
                </a>

                <!-- Dropdown de Perfil/Iniciales -->
                <div class="dropdown animate-hover">
                    <button
                        class="btn p-0 border-0 d-flex align-items-center justify-content-center rounded-circle border text-naval fw-bold bg-white shadow-sm"
                        type="button" data-bs-toggle="dropdown" aria-expanded="false"
                        style="width: 45px; height: 45px;">
                        @if(auth()->user()->tenant && auth()->user()->tenant->logo_path)
                            <img src="{{ asset('storage/' . auth()->user()->tenant->logo_path) }}" alt="Logo"
                                style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        @else
                            <span class="material-symbols-outlined text-naval" style="font-size: 24px;">store</span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 overflow-hidden mt-2">
                        @if(auth()->user()->hasRole('admin'))
                            <li>
                                <a class="dropdown-item py-2.5 d-flex align-items-center gap-2 fw-medium"
                                    href="{{ route('admin.settings') }}">
                                    <span class="material-symbols-outlined text-muted fs-5">person</span> Mi Perfil
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider opacity-10">
                            </li>
                        @endif
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit"
                                    class="dropdown-item py-2.5 d-flex align-items-center gap-2 fw-bold text-danger w-100 border-0 bg-transparent text-start">
                                    <span class="material-symbols-outlined text-danger fs-5">logout</span> Cerrar Sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        @if($isSuspended)
            {{-- Banner de Suspensión Premium --}}
            <section class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5" style="border-left: 5px solid #e2a500 !important;">
                <div class="card-body p-5 text-center" style="background: linear-gradient(135deg, #fffdf5 0%, #fff8e1 100%);">
                    <div class="bg-warning bg-opacity-10 text-warning p-4 rounded-circle d-inline-flex align-items-center justify-content-center mb-4 shadow-sm" style="width: 90px; height: 90px;">
                        <span class="material-symbols-outlined" style="font-size: 48px;">gpp_bad</span>
                    </div>
                    <h3 class="fw-bold text-naval mb-3">Servicio Suspendido</h3>
                    <p class="text-muted mb-4 mx-auto" style="max-width: 500px; line-height: 1.7;">
                        Tu cuenta se encuentra actualmente <strong class="text-warning">suspendida</strong>. 
                        Todos los módulos de operación, punto de venta y reportes están temporalmente deshabilitados.
                    </p>
                    <div class="bg-white rounded-4 p-4 mx-auto shadow-sm border border-warning border-opacity-25" style="max-width: 450px;">
                        <p class="fw-bold text-naval mb-2 small text-uppercase">¿Necesitas reactivar tu servicio?</p>
                        <p class="text-muted small mb-3">Comunícate con el equipo de soporte para revisar tu plan y reactivar tu tienda.</p>
                        <a href="mailto:soporte@aselpos.com?subject=Reactivar%20Servicio%20-%20{{ urlencode($tenantName) }}" 
                           class="btn fw-bold rounded-pill px-4 py-2 shadow-sm d-inline-flex align-items-center gap-2"
                           style="background-color: #e2a500; color: #fff; border: none;">
                            <span class="material-symbols-outlined fs-5">mail</span>
                            Contactar Soporte
                        </a>
                    </div>
                </div>
            </section>
        @else
        {{-- Bento Grid Metrics --}}
        <section class="row g-4 mb-5">
            <div class="col-sm-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div class="p-2 bg-coral text-white rounded-3 d-inline-flex align-items-center justify-content-center"
                            style="width: fit-content;">
                            <span class="material-symbols-outlined">payments</span>
                        </div>
                        <div class="mt-4">
                            <h3 class="display-6 fw-bold text-naval mb-1">${{ number_format($totalSalesToday, 2) }}</h3>
                            <p class="text-muted small fw-bold text-uppercase tracking-wider mb-0">Ventas de Hoy</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div class="p-2 bg-info bg-opacity-10 rounded-3 text-info d-inline-flex align-items-center justify-content-center"
                            style="width: fit-content;">
                            <span class="material-symbols-outlined">receipt_long</span>
                        </div>
                        <div class="mt-4">
                            <h3 class="display-6 fw-bold text-naval mb-1">{{ $ordersToday }}</h3>
                            <p class="text-muted small fw-bold text-uppercase tracking-wider mb-0">Tickets Hoy</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div class="p-2 bg-success bg-opacity-10 rounded-3 text-success d-inline-flex align-items-center justify-content-center"
                            style="width: fit-content;">
                            <span class="material-symbols-outlined">inventory_2</span>
                        </div>
                        <div class="mt-4">
                            <h3 class="display-6 fw-bold text-naval mb-1">{{ $productsCount }}</h3>
                            <p class="text-muted small fw-bold text-uppercase tracking-wider mb-0">Catálogo Activo</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div
                    class="card h-100 border-0 shadow-sm rounded-4 position-relative overflow-hidden {{ $lowStockCount > 0 ? 'bg-danger bg-opacity-10' : '' }}">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div class="p-2 {{ $lowStockCount > 0 ? 'bg-danger bg-opacity-25 text-danger' : 'bg-light text-muted' }} rounded-3 d-inline-flex align-items-center justify-content-center"
                            style="width: fit-content;">
                            <span class="material-symbols-outlined">warning</span>
                        </div>
                        <div class="mt-4">
                            <h3 class="display-6 fw-bold {{ $lowStockCount > 0 ? 'text-danger' : 'text-naval' }} mb-1">
                                {{ $lowStockCount }}</h3>
                            <p class="text-muted small fw-bold text-uppercase tracking-wider mb-0">Alertas Stock</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Recents Orders Table --}}
        <section class="card border-0 shadow-sm rounded-4 mb-5">
            <div class="card-header bg-white border-bottom p-4">
                <h5 class="fw-bold text-naval mb-1">Órdenes Recientes</h5>
                <p class="text-muted small mb-0">Últimos movimientos registrados</p>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">Folio</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">Canal</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">Total</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">Método</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($recentOrders as $order)
                            <tr>
                                <td class="py-3 px-4 fw-bold text-dark">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="py-3 px-4">
                                    @if($order->source == 'pos')
                                        <span class="badge bg-light text-secondary border px-2 py-1 text-uppercase">Punto de
                                            Venta</span>
                                    @else
                                        <span
                                            class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 text-uppercase">Catálogo</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 fw-bold">${{ number_format($order->total, 2) }}</td>
                                <td class="py-3 px-4 text-muted text-capitalize">{{ $order->payment_method }}</td>
                                <td class="py-3 px-4">
                                    @if($order->payment_status == 'paid')
                                        <span
                                            class="badge bg-success bg-opacity-10 text-success px-2 py-1 text-uppercase">Pagado</span>
                                    @else
                                        <span
                                            class="badge bg-warning bg-opacity-10 text-warning px-2 py-1 text-uppercase">Pendiente</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <span class="material-symbols-outlined fs-1 opacity-50 mb-3 d-block">receipt_long</span>
                                    <p class="fw-bold mb-0">No hay órdenes registradas aún.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        @endif

    </main>
</div>