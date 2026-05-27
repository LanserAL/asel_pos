<div x-data="{ sidebarOpen: true }" class="d-flex w-100 min-vh-100 bg-light text-dark" style="font-family: 'Instrument Sans', sans-serif;">
    
    <!-- SideNavBar -->
    <x-sidebar active="vendedor.providers" />

    <!-- Main Content -->
    <main class="flex-grow-1 p-4 p-md-5 main-content w-100 transition-all" :style="sidebarOpen ? 'margin-left: 300px; max-width: calc(100% - 300px);' : 'margin-left: 80px; max-width: calc(100% - 80px);'" style="transition: all 0.3s ease;">
        <!-- Header -->
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
            <div>
                <h2 class="fw-bold text-naval mb-1">Proveedores</h2>
                <p class="text-muted mb-0 small fw-medium">Administra los contactos y empresas que te surten.</p>
            </div>
            
            <button data-bs-toggle="modal" data-bs-target="#providerModal" wire:click="resetForm" class="btn btn-coral d-flex align-items-center gap-2 fw-bold rounded-pill px-4 py-2 shadow-sm">
                <span class="material-symbols-outlined fs-5">person_add</span>
                Nuevo Proveedor
            </button>
        </header>

        <!-- Messages -->
        @if (session()->has('message'))
            <div class="alert alert-success fw-bold d-flex align-items-center gap-2 mb-4 shadow-sm border-0">
                <span class="material-symbols-outlined">check_circle</span>
                {{ session('message') }}
            </div>
        @endif

        <!-- Providers List -->
        <section class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center gap-3">
                <div class="position-relative w-100" style="max-width: 400px;">
                    <span class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y ms-3 text-muted">search</span>
                    <input wire:model.live="search" class="form-control form-control-lg bg-light border-0 ps-5 rounded-pill" placeholder="Buscar proveedor por nombre o empresa..." type="text">
                </div>
                <div class="text-muted fw-bold small">
                    Mostrando {{ $providers->count() }} proveedores
                </div>
            </div>
            
            <div class="w-100">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">Contacto / Empresa</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">Datos de Contacto</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0 text-center">Estado</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0 text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($providers as $provider)
                        <tr>
                            <td class="py-3 px-4">
                                <h6 class="mb-0 fw-bold text-dark">{{ $provider->name }}</h6>
                                <small class="text-muted">{{ $provider->company ?? 'Independiente' }}</small>
                            </td>
                            <td class="py-3 px-4">
                                <div class="d-flex flex-column gap-1">
                                    @if($provider->phone)
                                        <span class="small text-muted d-flex align-items-center gap-1"><span class="material-symbols-outlined fs-6">call</span> {{ $provider->phone }}</span>
                                    @endif
                                    @if($provider->email)
                                        <span class="small text-muted d-flex align-items-center gap-1"><span class="material-symbols-outlined fs-6">mail</span> {{ $provider->email }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($provider->status === 'active')
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill text-uppercase fw-bold">Activo</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill text-uppercase fw-bold">Inactivo</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-end">
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm rounded-circle p-2" type="button" data-bs-toggle="dropdown">
                                        <span class="material-symbols-outlined d-block">more_vert</span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 overflow-hidden">
                                        <li>
                                            <button wire:click="editProvider({{ $provider->id }})" class="dropdown-item py-2 d-flex align-items-center gap-2 fw-medium" data-bs-toggle="modal" data-bs-target="#providerModal">
                                                <span class="material-symbols-outlined text-muted fs-5">edit</span> Editar
                                            </button>
                                        </li>
                                        <li>
                                            <button wire:click="toggleStatus({{ $provider->id }})" class="dropdown-item py-2 d-flex align-items-center gap-2 fw-medium">
                                                <span class="material-symbols-outlined text-muted fs-5">{{ $provider->status === 'active' ? 'block' : 'check_circle' }}</span> 
                                                {{ $provider->status === 'active' ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <span class="material-symbols-outlined fs-1 opacity-50 mb-3 d-block">local_shipping</span>
                                <p class="fw-bold mb-0">No se encontraron proveedores.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top p-3">
                {{ $providers->links('pagination::bootstrap-5') }}
            </div>
        </section>
    </main>

    <!-- Modal Form -->
    <div wire:ignore.self class="modal fade" id="providerModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header border-bottom p-4">
                    <h5 class="modal-title fw-bold text-naval">{{ $providerId ? 'Editar Proveedor' : 'Nuevo Proveedor' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted fw-bold text-uppercase small">Nombre del Contacto <span class="text-danger">*</span></label>
                            <input type="text" wire:model="name" class="form-control bg-white border-0 shadow-sm" placeholder="Ej. Juan Pérez">
                            @error('name') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fw-bold text-uppercase small">Empresa</label>
                            <input type="text" wire:model="company" class="form-control bg-white border-0 shadow-sm" placeholder="Ej. Coca Cola">
                            @error('company') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fw-bold text-uppercase small">Teléfono</label>
                            <input type="text" wire:model="phone" class="form-control bg-white border-0 shadow-sm">
                            @error('phone') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fw-bold text-uppercase small">Email</label>
                            <input type="email" wire:model="email" class="form-control bg-white border-0 shadow-sm">
                            @error('email') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted fw-bold text-uppercase small">Dirección</label>
                            <textarea wire:model="address" class="form-control bg-white border-0 shadow-sm" rows="2"></textarea>
                            @error('address') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted fw-bold text-uppercase small">Estado</label>
                            <select wire:model="status" class="form-select bg-white border-0 shadow-sm">
                                <option value="active">Activo</option>
                                <option value="inactive">Inactivo</option>
                            </select>
                            @error('status') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top p-4">
                    <button type="button" class="btn btn-light fw-bold px-4 rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" wire:click="saveProvider" class="btn btn-coral fw-bold px-4 rounded-pill shadow-sm">
                        {{ $providerId ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('close-modal', () => {
                var modal = bootstrap.Modal.getInstance(document.getElementById('providerModal'));
                if (modal) modal.hide();
            });
        });
    </script>
    <style>
        .dropdown-menu {
            animation: fadeInDown 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            transform-origin: top;
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-10px) scaleY(0.9); }
            to { opacity: 1; transform: translateY(0) scaleY(1); }
        }
    </style>
</div>
