<div x-data="{ sidebarOpen: true }" class="d-flex w-100 min-vh-100 bg-light text-dark" style="font-family: 'Instrument Sans', sans-serif;">
    
    <!-- SideNavBar -->
    <x-sidebar active="vendedor.branches" />

    <!-- Main Content -->
    <main class="flex-grow-1 p-4 p-md-5 main-content w-100 transition-all" :style="sidebarOpen ? 'margin-left: 300px; max-width: calc(100% - 300px);' : 'margin-left: 80px; max-width: calc(100% - 80px);'" style="transition: all 0.3s ease;">
        <!-- Header -->
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
            <div>
                <h2 class="fw-bold text-naval mb-1">Sucursales</h2>
                <p class="text-muted mb-0 small fw-medium">Administra las ubicaciones físicas de tu negocio.</p>
            </div>
            
            <button data-bs-toggle="modal" data-bs-target="#branchModal" wire:click="resetForm" class="btn btn-coral d-flex align-items-center gap-2 fw-bold rounded-pill px-4 py-2 shadow-sm">
                <span class="material-symbols-outlined fs-5">add_business</span>
                Nueva Sucursal
            </button>
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

        <!-- Branches List -->
        <section class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center gap-3">
                <div class="position-relative w-100" style="max-width: 400px;">
                    <span class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y ms-3 text-muted">search</span>
                    <input wire:model.live="search" class="form-control form-control-lg bg-light border-0 ps-5 rounded-pill" placeholder="Buscar sucursal..." type="text">
                </div>
                <div class="text-muted fw-bold small">
                    Mostrando {{ $branches->count() }} sucursales
                </div>
            </div>
            
            <div class="w-100">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">Nombre</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">Dirección</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">Teléfono</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0 text-center">Divisa</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0 text-center">Estado</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0 text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($branches as $branch)
                        <tr>
                            <td class="py-3 px-4 fw-bold text-dark">{{ $branch->name }}</td>
                            <td class="py-3 px-4 text-muted">{{ $branch->address ?? 'No registrada' }}</td>
                            <td class="py-3 px-4 font-monospace">{{ $branch->phone ?? 'N/A' }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="badge bg-light text-naval border px-3 py-2 rounded-pill font-monospace fw-bold">{{ $branch->currency ?? 'MXN' }}</span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($branch->status === 'active')
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill text-uppercase fw-bold">Activa</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill text-uppercase fw-bold">Inactiva</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-end">
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm rounded-circle p-2" type="button" data-bs-toggle="dropdown">
                                        <span class="material-symbols-outlined d-block">more_vert</span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 overflow-hidden">
                                        <li>
                                            <button wire:click="editBranch({{ $branch->id }})" class="dropdown-item py-2 d-flex align-items-center gap-2 fw-medium" data-bs-toggle="modal" data-bs-target="#branchModal">
                                                <span class="material-symbols-outlined text-muted fs-5">edit</span> Editar
                                            </button>
                                        </li>
                                        <li>
                                            <button wire:click="viewEmployees({{ $branch->id }})" class="dropdown-item py-2 d-flex align-items-center gap-2 fw-medium" data-bs-toggle="modal" data-bs-target="#viewEmployeesModal">
                                                <span class="material-symbols-outlined text-muted fs-5">groups</span> Ver Empleados
                                            </button>
                                        </li>
                                        <li>
                                            <button wire:click="openEmployeeModal({{ $branch->id }})" class="dropdown-item py-2 d-flex align-items-center gap-2 fw-medium" data-bs-toggle="modal" data-bs-target="#employeeModal">
                                                <span class="material-symbols-outlined text-muted fs-5">person_add</span> Agregar Empleado
                                            </button>
                                        </li>
                                        <li>
                                            <button wire:click="toggleStatus({{ $branch->id }})" class="dropdown-item py-2 d-flex align-items-center gap-2 fw-medium">
                                                <span class="material-symbols-outlined text-muted fs-5">{{ $branch->status === 'active' ? 'block' : 'check_circle' }}</span> 
                                                {{ $branch->status === 'active' ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <span class="material-symbols-outlined fs-1 opacity-50 mb-3 d-block">store_off</span>
                                <p class="fw-bold mb-0">No se encontraron sucursales.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top p-3">
                {{ $branches->links('vendor.livewire.bootstrap') }}
            </div>
        </section>
    </main>

    <!-- Modal Form -->
    <div wire:ignore.self class="modal fade" id="branchModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header border-bottom p-4">
                    <h5 class="modal-title fw-bold text-naval">{{ $branchId ? 'Editar Sucursal' : 'Nueva Sucursal' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold text-uppercase small">Nombre de la Sucursal <span class="text-danger">*</span></label>
                        <input type="text" wire:model="name" class="form-control bg-white border-0 shadow-sm" placeholder="Ej. Matriz Centro">
                        @error('name') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold text-uppercase small">Teléfono</label>
                        <input type="text" wire:model="phone" class="form-control bg-white border-0 shadow-sm">
                        @error('phone') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold text-uppercase small">Dirección Físca</label>
                        <textarea wire:model="address" class="form-control bg-white border-0 shadow-sm" rows="3"></textarea>
                        @error('address') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold text-uppercase small">Estado</label>
                        <select wire:model="status" class="form-select bg-white border-0 shadow-sm">
                            <option value="active">Activa</option>
                            <option value="inactive">Inactiva</option>
                        </select>
                        @error('status') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold text-uppercase small">Divisa de Operación <span class="text-danger">*</span></label>
                        <select wire:model="currency" class="form-select bg-white border-0 shadow-sm">
                            @foreach($allowedCurrencies as $curr)
                                <option value="{{ $curr }}">
                                    {{ $curr }} 
                                    @if($curr === 'MXN') (Peso Mexicano - $) 
                                    @elseif($curr === 'USD') (Dólar - US$) 
                                    @elseif($curr === 'EUR') (Euro - €) 
                                    @elseif($curr === 'COP') (Peso Colombiano - COL$) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('currency') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="modal-footer border-top p-4">
                    <button type="button" class="btn btn-light fw-bold px-4 rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" wire:click="saveBranch" class="btn btn-coral fw-bold px-4 rounded-pill shadow-sm">
                        {{ $branchId ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Empleado -->
    <div wire:ignore.self class="modal fade" id="employeeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header border-bottom p-4">
                    <h5 class="modal-title fw-bold text-naval">Agregar Empleado (Vendedor)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold text-uppercase small">Nombre Completo <span class="text-danger">*</span></label>
                        <input type="text" wire:model="empName" class="form-control bg-white border-0 shadow-sm" placeholder="Ej. Juan Pérez">
                        @error('empName') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold text-uppercase small">Correo Electrónico <span class="text-danger">*</span></label>
                        <input type="email" wire:model="empEmail" class="form-control bg-white border-0 shadow-sm" placeholder="empleado@tienda.com">
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
                    <button type="button" wire:click="saveEmployee" class="btn btn-coral fw-bold px-4 rounded-pill shadow-sm">Guardar Empleado</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Ver Empleados -->
    <div wire:ignore.self class="modal fade" id="viewEmployeesModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header border-bottom p-4">
                    <h5 class="modal-title fw-bold text-naval">Empleados de: {{ $viewingBranchName }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">Nombre</th>
                                <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">Correo</th>
                                <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0 text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($branchEmployees as $emp)
                                <tr>
                                    <td class="py-3 px-4 fw-bold">{{ $emp->name }}</td>
                                    <td class="py-3 px-4 text-muted">{{ $emp->email }}</td>
                                    <td class="py-3 px-4 text-end">
                                        <button type="button" @click="
                                            Swal.fire({
                                                title: '¿Eliminar Empleado?',
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
                                                    $wire.removeEmployee({{ $emp->id }})
                                                }
                                            })
                                        " class="btn btn-sm btn-outline-danger rounded-pill fw-bold">
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted fw-medium">No hay empleados registrados en esta sucursal.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer border-top p-4">
                    <button type="button" class="btn btn-naval fw-bold px-4 rounded-pill text-white" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('close-modal', () => {
                var branchModal = bootstrap.Modal.getInstance(document.getElementById('branchModal'));
                if (branchModal) branchModal.hide();
                
                var employeeModal = bootstrap.Modal.getInstance(document.getElementById('employeeModal'));
                if (employeeModal) employeeModal.hide();
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
