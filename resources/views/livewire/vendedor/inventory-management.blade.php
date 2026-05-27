<div x-data="{ sidebarOpen: true }" class="d-flex w-100 min-vh-100 bg-light text-dark" style="font-family: 'Instrument Sans', sans-serif;">
    
    <!-- SideNavBar -->
    <x-sidebar active="vendedor.inventory" />

    <!-- Main Content -->
    <main class="flex-grow-1 p-4 p-md-5 main-content w-100 transition-all" :style="sidebarOpen ? 'margin-left: 300px; max-width: calc(100% - 300px);' : 'margin-left: 80px; max-width: calc(100% - 80px);'" style="transition: all 0.3s ease;">
        <!-- Header -->
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
            <div>
                <h2 class="fw-bold text-naval mb-1">Niveles de Inventario</h2>
                <p class="text-muted mb-0 small fw-medium">Administra las existencias de productos por sucursal.</p>
            </div>
            
            <button data-bs-toggle="modal" data-bs-target="#inventoryModal" wire:click="resetForm" class="btn btn-coral d-flex align-items-center gap-2 fw-bold rounded-pill px-4 py-2 shadow-sm" {{ empty($branchIdFilter) ? 'disabled' : '' }}>
                <span class="material-symbols-outlined fs-5">assignment_add</span>
                Ajustar Inventario
            </button>
        </header>

        <!-- Messages -->
        @if (session()->has('message'))
            <div class="alert alert-success fw-bold d-flex align-items-center gap-2 mb-4 shadow-sm border-0 rounded-4">
                <span class="material-symbols-outlined">check_circle</span>
                {{ session('message') }}
            </div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger fw-bold d-flex align-items-center gap-2 mb-4 shadow-sm border-0 rounded-4">
                <span class="material-symbols-outlined">error</span>
                {{ session('error') }}
            </div>
        @endif

        <!-- Inventory List -->
        <section class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex gap-2 w-100" style="max-width: 600px;">
                    <div class="position-relative flex-grow-1">
                        <span class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y ms-3 text-muted">search</span>
                        <input wire:model.live="search" class="form-control form-control-lg bg-light border-0 ps-5 rounded-pill" placeholder="Buscar producto..." type="text">
                    </div>
                    <select wire:model.live="branchIdFilter" class="form-select form-select-lg bg-light border-0 rounded-pill px-4" style="max-width: 250px;">
                        <option value="">Todas las sucursales</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="text-muted fw-bold small">
                    Mostrando {{ $inventories->count() }} registros
                </div>
            </div>
            
            @if(empty($branchIdFilter))
                <div class="text-center py-5 text-muted">
                    <span class="material-symbols-outlined fs-1 opacity-50 mb-3 d-block text-naval">store</span>
                    <p class="fw-bold mb-0">Selecciona una sucursal</p>
                    <p class="small">Elige una sucursal en el filtro superior para ver y ajustar su inventario.</p>
                </div>
            @else
                <div class="w-100 overflow-auto">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">Sucursal</th>
                                <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">Producto</th>
                                <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0 text-end">Stock Actual</th>
                                <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0 text-end">Alerta Mínima</th>
                                <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0 text-center">Estado</th>
                                <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0 text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse($inventories as $inv)
                            <tr>
                                <td class="py-3 px-4 fw-bold text-naval">
                                    <span class="d-flex align-items-center gap-2">
                                        <span class="material-symbols-outlined fs-5 text-muted">store</span>
                                        {{ optional($inv->branch)->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <h6 class="mb-0 fw-bold text-dark">{{ optional($inv->product)->title ?? 'N/A' }}</h6>
                                    <small class="text-muted font-monospace">{{ optional($inv->product)->sku ?? '' }}</small>
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <span class="fs-5 fw-black {{ $inv->stock_quantity <= $inv->alert_min_stock ? 'text-danger' : 'text-success' }}">
                                        {{ $inv->stock_quantity }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-end text-muted fw-medium">{{ $inv->alert_min_stock }}</td>
                                <td class="py-3 px-4 text-center">
                                    @if($inv->stock_quantity <= 0)
                                        <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill text-uppercase fw-bold">Agotado</span>
                                    @elseif($inv->stock_quantity <= $inv->alert_min_stock)
                                        <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill text-uppercase fw-bold">Bajo Stock</span>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill text-uppercase fw-bold">Óptimo</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <button wire:click="editInventory({{ $inv->id }})" class="btn btn-light btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#inventoryModal">
                                        <span class="material-symbols-outlined text-muted fs-5">edit</span>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <span class="material-symbols-outlined fs-1 opacity-50 mb-3 d-block">inventory</span>
                                    <p class="fw-bold mb-0">No hay registros de inventario.</p>
                                    <p class="small">Asigna stock a un producto para comenzar.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-top p-3">
                    {{ $inventories->links('vendor.livewire.bootstrap') }}
                </div>
            @endif
        </section>
    </main>

    <!-- Modal Form -->
    <div wire:ignore.self class="modal fade" id="inventoryModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header border-bottom p-4">
                    <h5 class="modal-title fw-bold text-naval">{{ $inventoryId ? 'Actualizar Inventario' : 'Asignar Inventario' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body p-4 bg-light">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted fw-bold text-uppercase small">Sucursal <span class="text-danger">*</span></label>
                            <select wire:model="branch_id" class="form-select form-select-lg bg-white border-0 shadow-sm" {{ $inventoryId ? 'disabled' : '' }}>
                                <option value="">Selecciona una sucursal</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                            @error('branch_id') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label text-muted fw-bold text-uppercase small">Producto <span class="text-danger">*</span></label>
                            <select wire:model="product_id" class="form-select form-select-lg bg-white border-0 shadow-sm" {{ $inventoryId ? 'disabled' : '' }}>
                                <option value="">Selecciona un producto</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->title }} ({{ $p->sku }})</option>
                                @endforeach
                            </select>
                            @error('product_id') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted fw-bold text-uppercase small">Cantidad Física <span class="text-danger">*</span></label>
                            <input type="number" wire:model="stock_quantity" min="0" class="form-control form-control-lg bg-white border-0 shadow-sm text-end fw-black">
                            @error('stock_quantity') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-muted fw-bold text-uppercase small">Alerta (Mínimo)</label>
                            <input type="number" wire:model="alert_min_stock" min="0" class="form-control form-control-lg bg-white border-0 shadow-sm text-end text-muted">
                            <small class="text-muted d-block mt-1" style="font-size: 10px;">Avisar si baja de este número.</small>
                            @error('alert_min_stock') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top p-4">
                    <button type="button" class="btn btn-light fw-bold px-4 rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" wire:click="saveInventory" class="btn btn-coral fw-bold px-4 rounded-pill shadow-sm">
                        {{ $inventoryId ? 'Guardar Cambios' : 'Añadir al Inventario' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('close-modal', () => {
                var modal = bootstrap.Modal.getInstance(document.getElementById('inventoryModal'));
                if (modal) modal.hide();
            });
        });
    </script>
</div>
