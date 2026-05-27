<div x-data="{ sidebarOpen: true }" class="d-flex w-100 min-vh-100 bg-light text-dark" style="font-family: 'Instrument Sans', sans-serif;">
    
    <!-- SideNavBar -->
    <x-sidebar active="super-admin" />

    <!-- Main Content -->
    <main class="flex-grow-1 p-4 p-md-5 main-content w-100 transition-all" :style="sidebarOpen ? 'margin-left: 300px; max-width: calc(100% - 300px);' : 'margin-left: 80px; max-width: calc(100% - 80px);'" style="transition: all 0.3s ease;">
        <!-- Header -->
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
            <div class="d-flex align-items-center gap-3">
                <div>
                    <h2 class="fw-bold text-naval mb-1">Centro de Control Global</h2>
                    <p class="text-muted mb-0 small fw-medium">Gestión de suscripciones y tenants activos.</p>
                </div>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('super-admin.tenants') }}" class="btn btn-coral d-flex align-items-center gap-2 fw-bold rounded-pill px-4 py-2 shadow-sm text-decoration-none text-white">
                    <span class="material-symbols-outlined fs-5">manage_accounts</span>
                    Gestionar Tiendas
                </a>
                <button class="btn btn-light border rounded-circle p-2 position-relative shadow-sm d-flex align-items-center justify-content-center">
                    <span class="material-symbols-outlined">notifications</span>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-coral border border-light rounded-circle">
                        <span class="visually-hidden">Nuevas alertas</span>
                    </span>
                </button>
                <div class="d-flex align-items-center justify-content-center rounded-circle border border-primary text-primary fw-bold bg-primary bg-opacity-10 shadow-sm" style="width: 45px; height: 45px;">
                    SA
                </div>
            </div>
        </header>

        <!-- Bento Grid Metrics -->
        <section class="row g-4 mb-5">
            <!-- Revenue Card -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 position-relative overflow-hidden">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-start z-1">
                            <div class="p-2 bg-success bg-opacity-10 rounded-3 text-success d-flex align-items-center justify-content-center">
                                <span class="material-symbols-outlined">payments</span>
                            </div>
                            <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1 rounded-pill d-flex align-items-center gap-1">
                                <span class="material-symbols-outlined" style="font-size: 14px;">trending_up</span> +12.5%
                            </span>
                        </div>
                        <div class="mt-4 z-1">
                            <h3 class="display-6 fw-bold text-naval mb-1">${{ number_format($totalRevenue / 1000000, 2) }}M</h3>
                            <p class="text-muted small fw-bold text-uppercase tracking-wider mb-0">Ingresos (MRR)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Tenants Card -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="p-2 bg-info bg-opacity-10 rounded-3 text-info d-flex align-items-center justify-content-center">
                                <span class="material-symbols-outlined">store</span>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h3 class="display-6 fw-bold text-naval mb-1">{{ $activeTenantsCount }}</h3>
                            <p class="text-muted small fw-bold text-uppercase tracking-wider mb-0">Tenants Activos</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Insight Card -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow rounded-4 bg-naval text-white">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="badge bg-white bg-opacity-10 text-coral fw-bold px-3 py-2 rounded-pill d-flex align-items-center gap-1 text-uppercase">
                                <span class="material-symbols-outlined" style="font-size: 14px;">auto_awesome</span> Insight
                            </span>
                        </div>
                        <div class="mt-4">
                            <h3 class="display-6 fw-bold text-white mb-1">{{ $expiringSubscriptions }}</h3>
                            <p class="text-white-50 small mb-0">Suscripciones expiran en 7 días.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>
</div>
