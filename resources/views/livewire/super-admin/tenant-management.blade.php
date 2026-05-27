<div x-data="{ sidebarOpen: true }" class="d-flex w-100 min-vh-100 bg-light text-dark" style="font-family: 'Instrument Sans', sans-serif;">
    
    <!-- SideNavBar -->
    <x-sidebar active="super-admin.tenants" />

    <!-- Main Content -->
    <main class="flex-grow-1 p-4 p-md-5 main-content w-100 transition-all" :style="sidebarOpen ? 'margin-left: 300px; max-width: calc(100% - 300px);' : 'margin-left: 80px; max-width: calc(100% - 80px);'" style="transition: all 0.3s ease;">
        <!-- Header -->
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
            <div class="d-flex align-items-center gap-3">
                <div>
                    <h2 class="fw-bold text-naval mb-1">Gestión de Tiendas y Usuarios</h2>
                    <p class="text-muted mb-0 small fw-medium">Administra los accesos y límites de cada Tenant.</p>
                </div>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <button data-bs-toggle="modal" data-bs-target="#createTenantModal" class="btn btn-coral d-flex align-items-center gap-2 fw-bold rounded-pill px-4 py-2 shadow-sm">
                    <span class="material-symbols-outlined fs-5">person_add</span>
                    Registrar Tienda
                </button>
            </div>
        </header>

        <!-- Messages -->
        @if (session()->has('message'))
            <div class="alert alert-success fw-bold d-flex align-items-center gap-2 mb-4 shadow-sm border-0">
                <span class="material-symbols-outlined">check_circle</span>
                {{ session('message') }}
            </div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger fw-bold d-flex align-items-center gap-2 mb-4 shadow-sm border-0">
                <span class="material-symbols-outlined">error</span>
                {{ session('error') }}
            </div>
        @endif

        <!-- Tenants List Section -->
        <section class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom p-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                <div class="position-relative w-100" style="max-width: 400px;">
                    <span class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y ms-3 text-muted">search</span>
                    <input wire:model.live="search" class="form-control form-control-lg bg-light border-0 ps-5 rounded-pill" placeholder="Buscar tiendas o subdominios..." type="text">
                </div>
            </div>
            
            <div class="w-100">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">Tienda (Tenant)</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">Estado</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">Usuarios</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">Límites (Suc/Prod)</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0 text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($tenants as $tenant)
                        <tr>
                            <td class="py-3 px-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary fw-bold rounded-3" style="width: 45px; height: 45px;">
                                        {{ substr($tenant->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">{{ $tenant->name }}</h6>
                                        <a href="https://{{ $tenant->slug }}.aselpos.com" target="_blank" class="small text-decoration-none text-muted font-monospace hover-text-primary">{{ $tenant->slug }}.aselpos.com</a>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                @if($tenant->status === 'active')
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill text-uppercase fw-bold">Activo</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill text-uppercase fw-bold">Suspendido</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill d-inline-flex align-items-center gap-1">
                                    <span class="material-symbols-outlined" style="font-size: 14px;">group</span>
                                    {{ $tenant->users_count }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="d-flex flex-column">
                                    <span class="small text-muted fw-bold">Suc: <span class="text-dark">{{ $tenant->plan_capacity['max_branches'] ?? 1 }}</span></span>
                                    <span class="small text-muted fw-bold">Prod: <span class="text-dark">{{ $tenant->plan_capacity['max_products'] ?? 100 }}</span></span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-end">
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm rounded-circle p-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="material-symbols-outlined d-block">more_vert</span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 overflow-hidden">
                                        <li>
                                            <button wire:click="openManageModal({{ $tenant->id }})" class="dropdown-item py-2 d-flex align-items-center gap-2 fw-medium" data-bs-toggle="modal" data-bs-target="#manageTenantModal">
                                                <span class="material-symbols-outlined text-muted fs-5">settings</span> Administrar Tienda
                                            </button>
                                        </li>
                                        <li>
                                            <button wire:click="loadEditTenant({{ $tenant->id }})" class="dropdown-item py-2 d-flex align-items-center gap-2 fw-medium" data-bs-toggle="modal" data-bs-target="#editTenantModal">
                                                <span class="material-symbols-outlined text-muted fs-5">edit</span> Actualizar Datos
                                            </button>
                                        </li>
                                        <li>
                                            <button wire:click="loadEmployeeForm({{ $tenant->id }})" class="dropdown-item py-2 d-flex align-items-center gap-2 fw-medium" data-bs-toggle="modal" data-bs-target="#createEmployeeModal">
                                                <span class="material-symbols-outlined text-muted fs-5">person_add</span> Agregar Vendedor
                                            </button>
                                        </li>
                                        <li>
                                            <button wire:click="loadCapacity({{ $tenant->id }})" class="dropdown-item py-2 d-flex align-items-center gap-2 fw-medium" data-bs-toggle="modal" data-bs-target="#capacityModal">
                                                <span class="material-symbols-outlined text-muted fs-5">tune</span> Ajustar Límites
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider opacity-10"></li>
                                        <li>
                                            <button wire:click="toggleTenantStatus({{ $tenant->id }})" class="dropdown-item py-2 d-flex align-items-center gap-2 fw-bold {{ $tenant->status === 'active' ? 'text-danger' : 'text-success' }}">
                                                <span class="material-symbols-outlined fs-5">{{ $tenant->status === 'active' ? 'block' : 'check_circle' }}</span> 
                                                {{ $tenant->status === 'active' ? 'Suspender Servicio' : 'Activar Servicio' }}
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <span class="material-symbols-outlined fs-1 opacity-50 mb-3 d-block">storefront</span>
                                <p class="fw-bold mb-0">No se encontraron tiendas.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top p-3">
                {{ $tenants->links('pagination::bootstrap-5') }}
            </div>
        </section>
    </main>

    <!-- Create Tenant Modal (Migrated from Dashboard) -->
    <div wire:ignore.self class="modal fade" id="createTenantModal" tabindex="-1" aria-labelledby="createTenantModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header border-bottom p-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-coral text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
                            <span class="material-symbols-outlined">person_add</span>
                        </div>
                        <h5 class="modal-title fw-bold text-naval" id="createTenantModalLabel">Registrar Nueva Tienda (Tenant)</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4 bg-light">
                    @if($tempPassword)
                        <div class="alert alert-warning fw-bold d-flex flex-column gap-1 mb-4 border-0 shadow-sm">
                            <span>Por favor, comparte esta contraseña temporal con el usuario:</span>
                            <span class="fs-5 font-monospace bg-white px-3 py-2 rounded border text-center">{{ $tempPassword }}</span>
                        </div>
                    @endif

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted fw-bold text-uppercase small">Nombre Comercial</label>
                            <input type="text" wire:model="newTenantName" class="form-control form-control-lg bg-white border-0 shadow-sm" placeholder="Ej. Abarrotes San Juan">
                            @error('newTenantName') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fw-bold text-uppercase small">Subdominio / Slug</label>
                            <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden">
                                <input type="text" wire:model="newTenantSlug" class="form-control border-0 bg-white" placeholder="sanjuan">
                                <span class="input-group-text bg-light border-0 text-muted">.aselpos.com</span>
                            </div>
                            @error('newTenantSlug') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    
                    <h6 class="fw-bold text-naval mb-3 mt-4">Cuenta Administrador/Vendedor Inicial</h6>
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted fw-bold text-uppercase small">Correo Electrónico</label>
                            <input type="email" wire:model="newTenantEmail" class="form-control form-control-lg bg-white border-0 shadow-sm" placeholder="admin@sanjuan.com">
                            @error('newTenantEmail') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fw-bold text-uppercase small">Contraseña Temporal</label>
                            <input type="password" wire:model="newTenantPassword" class="form-control form-control-lg bg-white border-0 shadow-sm" placeholder="********">
                            @error('newTenantPassword') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <h6 class="fw-bold text-naval mb-3 mt-4">Capacidades (Límites de Uso)</h6>
                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <label class="form-label text-muted fw-bold text-uppercase small">Máx. Sucursales</label>
                            <input type="number" wire:model="newTenantMaxBranches" class="form-control bg-white border-0 shadow-sm text-center" min="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted fw-bold text-uppercase small">Máx. Productos</label>
                            <input type="number" wire:model="newTenantMaxProducts" class="form-control bg-white border-0 shadow-sm text-center" min="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted fw-bold text-uppercase small">Máx. Usuarios</label>
                            <input type="number" wire:model="newTenantMaxUsers" class="form-control bg-white border-0 shadow-sm text-center" min="1">
                        </div>
                    </div>

                    <h6 class="fw-bold text-naval mb-3 mt-4">Monedas Permitidas <span class="text-danger">*</span></h6>
                    <div class="d-flex flex-wrap gap-4 mb-2 bg-white p-3 rounded shadow-sm">
                        @foreach($systemCurrencies as $currency)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="{{ $currency }}" id="currency_{{ $currency }}" wire:model="newTenantCurrencies">
                                <label class="form-check-label fw-bold text-dark" for="currency_{{ $currency }}">
                                    {{ $currency }} 
                                    @if($currency === 'MXN') (Peso Mexicano - $) 
                                    @elseif($currency === 'USD') (Dólar - US$) 
                                    @elseif($currency === 'EUR') (Euro - €) 
                                    @elseif($currency === 'COP') (Peso Colombiano - COL$) @endif
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('newTenantCurrencies') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                </div>
                
                <div class="modal-footer border-top p-4">
                    <button type="button" class="btn btn-light fw-bold px-4 rounded-pill" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" wire:click="createTenant" class="btn btn-coral fw-bold px-4 rounded-pill shadow-sm">
                        <span wire:loading.remove wire:target="createTenant">Registrar</span>
                        <span wire:loading wire:target="createTenant">Procesando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Tenant Data Modal -->
    <div wire:ignore.self class="modal fade" id="editTenantModal" tabindex="-1" aria-labelledby="editTenantModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header border-bottom p-4">
                    <h5 class="modal-title fw-bold text-naval">Actualizar Datos de Tienda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold text-uppercase small">Nombre Comercial</label>
                        <input type="text" wire:model="editTenantName" class="form-control bg-white border-0 shadow-sm">
                        @error('editTenantName') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold text-uppercase small">Slug (Subdominio)</label>
                        <input type="text" wire:model="editTenantSlug" class="form-control bg-white border-0 shadow-sm">
                        @error('editTenantSlug') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3 mt-4">
                        <label class="form-label text-muted fw-bold text-uppercase small d-block mb-3">Monedas Permitidas <span class="text-danger">*</span></label>
                        <div class="d-flex flex-column gap-2 bg-white p-3 rounded shadow-sm">
                            @foreach($systemCurrencies as $currency)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="{{ $currency }}" id="edit_currency_{{ $currency }}" wire:model="editTenantCurrencies">
                                    <label class="form-check-label fw-bold text-dark" for="edit_currency_{{ $currency }}">
                                        {{ $currency }} 
                                        @if($currency === 'MXN') (Peso Mexicano - $) 
                                        @elseif($currency === 'USD') (Dólar - US$) 
                                        @elseif($currency === 'EUR') (Euro - €) 
                                        @elseif($currency === 'COP') (Peso Colombiano - COL$) @endif
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('editTenantCurrencies') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                    </div>

                    <!-- AI Configuration fields -->
                    <h6 class="fw-bold text-naval mb-3 mt-4">Configuración de Inteligencia Artificial (Premium)</h6>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold text-uppercase small">Proveedor de IA</label>
                        <select wire:model="editTenantAiProvider" class="form-select bg-white border-0 shadow-sm rounded-3">
                            <option value="">Ninguno / Desactivado</option>
                            <option value="gemini">Google Gemini</option>
                            <option value="openai">OpenAI (ChatGPT)</option>
                            <option value="claude">Anthropic Claude</option>
                        </select>
                        @error('editTenantAiProvider') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="mb-3" x-data="{ showEditKey: false }">
                        <label class="form-label text-muted fw-bold text-uppercase small">Clave API (API Key)</label>
                        <div class="input-group shadow-sm rounded overflow-hidden">
                            <span class="input-group-text bg-white border-0 text-muted"><span class="material-symbols-outlined fs-5">vpn_key</span></span>
                            <input :type="showEditKey ? 'text' : 'password'" wire:model="editTenantAiApiKey" class="form-control bg-white border-0 shadow-none ps-0" placeholder="Ingresa clave secreta del tenant">
                            <button type="button" @click="showEditKey = !showEditKey" class="btn btn-white bg-white border-0 text-muted">
                                <span class="material-symbols-outlined fs-5" x-text="showEditKey ? 'visibility_off' : 'visibility'"></span>
                            </button>
                        </div>
                        @error('editTenantAiApiKey') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold text-uppercase small">Modelo (Opcional)</label>
                        <input type="text" wire:model="editTenantAiModel" class="form-control bg-white border-0 shadow-sm rounded-3" placeholder="Ej. gemini-1.5-flash">
                        @error('editTenantAiModel') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="modal-footer border-top p-4">
                    <button type="button" class="btn btn-light fw-bold px-4 rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" wire:click="updateTenant" class="btn btn-primary fw-bold px-4 rounded-pill shadow-sm">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Adjust Capacity Modal -->
    <div wire:ignore.self class="modal fade" id="capacityModal" tabindex="-1" aria-labelledby="capacityModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header border-bottom p-4">
                    <h5 class="modal-title fw-bold text-naval">Ajustar Límites del Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted fw-bold text-uppercase small">Máximas Sucursales</label>
                            <input type="number" wire:model="capMaxBranches" class="form-control bg-white border-0 shadow-sm text-center">
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted fw-bold text-uppercase small">Máximos Productos</label>
                            <input type="number" wire:model="capMaxProducts" class="form-control bg-white border-0 shadow-sm text-center">
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted fw-bold text-uppercase small">Máximos Usuarios</label>
                            <input type="number" wire:model="capMaxUsers" class="form-control bg-white border-0 shadow-sm text-center">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top p-4">
                    <button type="button" class="btn btn-light fw-bold px-4 rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" wire:click="updateCapacity" class="btn btn-success fw-bold px-4 rounded-pill shadow-sm">Aplicar Nuevos Límites</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Employee Modal -->
    <div wire:ignore.self class="modal fade" id="createEmployeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header border-bottom p-4">
                    <h5 class="modal-title fw-bold text-naval">Agregar Vendedor a Tienda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold text-uppercase small">Sucursal Asignada <span class="text-danger">*</span></label>
                        <select wire:model="empBranchId" class="form-select bg-white border-0 shadow-sm">
                            <option value="">-- Seleccionar Sucursal --</option>
                            @foreach($tenantBranches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        @error('empBranchId') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold text-uppercase small">Nombre Completo <span class="text-danger">*</span></label>
                        <input type="text" wire:model="empName" class="form-control bg-white border-0 shadow-sm" placeholder="Ej. Juan Pérez">
                        @error('empName') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold text-uppercase small">Correo Electrónico <span class="text-danger">*</span></label>
                        <input type="email" wire:model="empEmail" class="form-control bg-white border-0 shadow-sm" placeholder="vendedor@tienda.com">
                        @error('empEmail') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold text-uppercase small">Contraseña <span class="text-danger">*</span></label>
                        <input type="password" wire:model="empPassword" class="form-control bg-white border-0 shadow-sm">
                        @error('empPassword') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="modal-footer border-top p-4 d-flex justify-content-center">
                    <button type="button" class="btn btn-light fw-bold px-4 rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" wire:click="saveEmployee" class="btn btn-coral fw-bold px-4 rounded-pill shadow-sm">Guardar Vendedor</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Manage Tenant Modal -->
    <div wire:ignore.self class="modal fade" id="manageTenantModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header border-bottom p-4">
                    <h5 class="modal-title fw-bold text-naval">Administrar: {{ $manageTenantName }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <!-- Tabs -->
                    <ul class="nav nav-tabs nav-fill bg-light border-bottom-0" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-3 fw-bold {{ $manageActiveTab === 'users' ? 'active bg-white text-naval border-bottom-0' : 'text-muted' }}" 
                                    wire:click="setManageTab('users')" type="button" style="border-radius: 0;">
                                <span class="material-symbols-outlined align-middle me-1">groups</span> Usuarios/Empleados
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-3 fw-bold {{ $manageActiveTab === 'branches' ? 'active bg-white text-naval border-bottom-0' : 'text-muted' }}" 
                                    wire:click="setManageTab('branches')" type="button" style="border-radius: 0;">
                                <span class="material-symbols-outlined align-middle me-1">store</span> Sucursales
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="p-4 bg-white" style="min-height: 300px;">
                        @if (session()->has('manage_message'))
                            <div class="alert alert-success fw-bold py-2 px-3 small d-flex align-items-center gap-2 mb-3">
                                <span class="material-symbols-outlined fs-5">check_circle</span>
                                {{ session('manage_message') }}
                            </div>
                        @endif

                        @if($manageActiveTab === 'users')
                            <!-- Users Table -->
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="py-2 px-3 small text-muted text-uppercase fw-bold border-0">Nombre</th>
                                            <th class="py-2 px-3 small text-muted text-uppercase fw-bold border-0">Correo</th>
                                            <th class="py-2 px-3 small text-muted text-uppercase fw-bold border-0">Rol</th>
                                            <th class="py-2 px-3 small text-muted text-uppercase fw-bold border-0 text-end">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($manageUsers as $u)
                                            <tr>
                                                <td class="py-2 px-3 fw-bold">{{ $u->name }}</td>
                                                <td class="py-2 px-3 text-muted">{{ $u->email }}</td>
                                                <td class="py-2 px-3"><span class="badge bg-secondary bg-opacity-10 text-dark">{{ ucfirst($u->role) }}</span></td>
                                                <td class="py-2 px-3 text-end">
                                                    <button type="button" @click="
                                                        Swal.fire({
                                                            title: '¿Eliminar Usuario?',
                                                            text: 'Esta acción no se puede deshacer.',
                                                            icon: 'warning',
                                                            showCancelButton: true,
                                                            confirmButtonColor: '#ff6b6b',
                                                            cancelButtonColor: '#0e2649',
                                                            confirmButtonText: 'Sí, eliminar',
                                                            cancelButtonText: 'Cancelar',
                                                            reverseButtons: true
                                                        }).then((result) => {
                                                            if (result.isConfirmed) {
                                                                $wire.deleteManageUser({{ $u->id }})
                                                            }
                                                        })
                                                    " class="btn btn-sm btn-outline-danger rounded-pill fw-bold">Eliminar</button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center py-4 text-muted">No hay usuarios registrados.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <!-- Branches Table -->
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="py-2 px-3 small text-muted text-uppercase fw-bold border-0">Nombre</th>
                                            <th class="py-2 px-3 small text-muted text-uppercase fw-bold border-0">Estado</th>
                                            <th class="py-2 px-3 small text-muted text-uppercase fw-bold border-0 text-end">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($manageBranches as $b)
                                            <tr>
                                                <td class="py-2 px-3 fw-bold">{{ $b->name }}</td>
                                                <td class="py-2 px-3">
                                                    @if($b->status === 'active')
                                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2">Activa</span>
                                                    @else
                                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2">Inactiva</span>
                                                    @endif
                                                </td>
                                                <td class="py-2 px-3 text-end">
                                                    <button type="button" wire:click="toggleManageBranchStatus({{ $b->id }})" class="btn btn-sm btn-light rounded-pill fw-bold me-2">
                                                        {{ $b->status === 'active' ? 'Suspender' : 'Activar' }}
                                                    </button>
                                                    <button type="button" @click="
                                                        Swal.fire({
                                                            title: '¿Eliminar Sucursal?',
                                                            text: 'Se perderá el acceso a esta sucursal.',
                                                            icon: 'warning',
                                                            showCancelButton: true,
                                                            confirmButtonColor: '#ff6b6b',
                                                            cancelButtonColor: '#0e2649',
                                                            confirmButtonText: 'Sí, eliminar',
                                                            cancelButtonText: 'Cancelar',
                                                            reverseButtons: true
                                                        }).then((result) => {
                                                            if (result.isConfirmed) {
                                                                $wire.deleteManageBranch({{ $b->id }})
                                                            }
                                                        })
                                                    " class="btn btn-sm btn-outline-danger rounded-pill fw-bold">Eliminar</button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center py-4 text-muted">No hay sucursales registradas.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer border-top p-4">
                    <button type="button" class="btn btn-naval fw-bold px-4 rounded-pill text-white" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('close-modal', () => {
            ['#createTenantModal', '#editTenantModal', '#capacityModal', '#createEmployeeModal', '#manageTenantModal'].forEach(id => {
                var modal = bootstrap.Modal.getInstance(document.querySelector(id));
                if (modal) modal.hide();
            });
        });
    });
</script>

<style>
    .dropdown-menu {
        animation: fadeInDown 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        transform-origin: top;
    }
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-10px) scaleY(0.9);
        }
        to {
            opacity: 1;
            transform: translateY(0) scaleY(1);
        }
    }
</style>
