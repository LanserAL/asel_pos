<div x-data="{ sidebarOpen: true }" class="d-flex w-100 min-vh-100 bg-light text-dark" style="font-family: 'Instrument Sans', sans-serif;">
    
    <!-- SideNavBar -->
    <x-sidebar active="admin.customers" />

    <!-- Main Content -->
    <main class="flex-grow-1 p-4 p-md-5 main-content w-100 transition-all" :style="sidebarOpen ? 'margin-left: 300px; max-width: calc(100% - 300px);' : 'margin-left: 80px; max-width: calc(100% - 80px);'" style="transition: all 0.3s ease;">
        
        <!-- Header -->
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                @if($tab === 'invoices')
                    <h2 class="fw-bold text-naval mb-1">Facturación Electrónica (CFDI)</h2>
                    <p class="text-muted mb-0 small fw-medium">Administra comprobantes fiscales emitidos, descargas y cancelaciones de timbrado.</p>
                @else
                    <h2 class="fw-bold text-naval mb-1">Gestión de Clientes y Créditos</h2>
                    <p class="text-muted mb-0 small fw-medium">Administra saldos de crédito de tienda y programas de fidelización.</p>
                @endif
            </div>
            
            @if(auth()->user()->hasRole('admin') && $tab !== 'invoices')
            <button data-bs-toggle="modal" data-bs-target="#customerModal" wire:click="resetForm" class="btn btn-coral d-flex align-items-center gap-2 fw-bold rounded-pill px-4 py-2 shadow-sm">
                <span class="material-symbols-outlined fs-5">person_add</span>
                Nuevo Cliente
            </button>
            @endif
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

        <!-- Tabs for Switching between Clientes and Facturación -->
        <div class="d-flex border-bottom mb-4 gap-2">
            <button wire:click="setTab('customers')" class="btn py-2.5 px-4 fw-bold transition-all d-flex align-items-center gap-2 border-bottom border-3 {{ $tab === 'customers' ? 'border-coral text-coral' : 'border-transparent text-secondary' }}" style="border-radius: 0; background: none; border-top: 0; border-left: 0; border-right: 0;">
                <span class="material-symbols-outlined fs-5">groups</span>
                Clientes y Créditos
            </button>
            <button wire:click="setTab('invoices')" class="btn py-2.5 px-4 fw-bold transition-all d-flex align-items-center gap-2 border-bottom border-3 {{ $tab === 'invoices' ? 'border-coral text-coral' : 'border-transparent text-secondary' }}" style="border-radius: 0; background: none; border-top: 0; border-left: 0; border-right: 0;">
                <span class="material-symbols-outlined fs-5">receipt</span>
                Facturación CFDI
            </button>
        </div>

        @if($tab === 'customers')
        <!-- Customer List Section -->
        <section class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom p-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                <div class="position-relative w-100" style="max-width: 400px;">
                    <span class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y ms-3 text-muted">search</span>
                    <input wire:model.live="search" class="form-control form-control-lg bg-light border-0 ps-5 rounded-pill" placeholder="Buscar por nombre, correo, teléfono o RFC..." type="text">
                </div>
            </div>
            
            <div class="w-100">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">Cliente</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">Información Fiscal</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0 text-center">Fidelidad (Puntos)</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0 text-center">Saldo Crédito</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0 text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($customers as $customer)
                        <tr>
                            <td class="py-3 px-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary fw-bold rounded-3" style="width: 45px; height: 45px;">
                                        {{ substr($customer->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">{{ $customer->name }}</h6>
                                        <span class="small text-muted d-block">{{ $customer->email ?: 'Sin correo' }}</span>
                                        <span class="small text-muted font-monospace d-block" style="font-size: 11px;">{{ $customer->phone ?: 'Sin teléfono' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                @if($customer->rfc)
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-naval font-monospace mb-1" style="font-size: 12px;">{{ $customer->rfc }}</span>
                                        <span class="small text-secondary fw-medium text-truncate" style="max-width: 200px;" title="{{ $customer->razon_social }}">{{ $customer->razon_social }}</span>
                                        <span class="small text-muted" style="font-size: 10px;">CP: {{ $customer->postal_code }}</span>
                                    </div>
                                @else
                                    <span class="badge bg-light text-muted border px-2 py-1">General (Sin Datos Fiscales)</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill d-inline-flex align-items-center gap-1.5 fw-bold">
                                    <span class="material-symbols-outlined fs-6">stars</span>
                                    {{ $customer->loyalty_points }} Pts
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="d-flex flex-column align-items-center">
                                    <span class="badge px-3 py-2 rounded-pill fw-black {{ $customer->credit_balance > 0 ? 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25' : 'bg-secondary bg-opacity-10 text-muted' }}">
                                        Deuda: ${{ number_format($customer->credit_balance, 2) }}
                                    </span>
                                    <small class="text-muted mt-1" style="font-size: 10px; font-weight: bold;">Límite: ${{ number_format($customer->credit_limit, 2) }}</small>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-end font-medium">
                                <button wire:click="viewDetails({{ $customer->id }})" class="btn btn-sm btn-outline-naval rounded-pill fw-bold px-3 me-1" data-bs-toggle="modal" data-bs-target="#ledgerModal">
                                    Historial
                                </button>
                                
                                <button wire:click="openPaymentModal({{ $customer->id }})" class="btn btn-sm btn-light border rounded-pill fw-bold text-success px-3 me-1" data-bs-toggle="modal" data-bs-target="#paymentModal">
                                    Abonar
                                </button>
                                
                                @if(auth()->user()->hasRole('admin'))
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-light btn-sm rounded-circle p-2" type="button" data-bs-toggle="dropdown">
                                        <span class="material-symbols-outlined d-block">more_vert</span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 overflow-hidden">
                                        <li>
                                            <button wire:click="editCustomer({{ $customer->id }})" class="dropdown-item py-2 d-flex align-items-center gap-2 fw-medium" data-bs-toggle="modal" data-bs-target="#customerModal">
                                                <span class="material-symbols-outlined text-muted fs-5">edit</span> Editar Datos
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider opacity-10"></li>
                                        <li>
                                            <button type="button" @click="
                                                Swal.fire({
                                                    title: '¿Eliminar Cliente?',
                                                    text: 'Se borrará el historial de créditos y puntos.',
                                                    icon: 'warning',
                                                    showCancelButton: true,
                                                    confirmButtonColor: '#ff6b6b',
                                                    cancelButtonColor: '#0e2649',
                                                    confirmButtonText: 'Sí, eliminar',
                                                    cancelButtonText: 'Cancelar',
                                                    reverseButtons: true
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        $wire.deleteCustomer({{ $customer->id }})
                                                    }
                                                })
                                            " class="dropdown-item py-2 d-flex align-items-center gap-2 text-danger fw-bold">
                                                <span class="material-symbols-outlined fs-5 text-danger">delete</span> Eliminar
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <span class="material-symbols-outlined fs-1 opacity-50 mb-3 d-block">group_off</span>
                                <p class="fw-bold mb-0">No se encontraron clientes.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer bg-white border-top p-3">
                {{ $customers->links('pagination::bootstrap-5') }}
            </div>
        </section>
        @endif

        @if($tab === 'invoices')
        <!-- Invoice Table Card -->
        <section class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom p-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                <div class="position-relative w-100" style="max-width: 400px;">
                    <span class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y ms-3 text-muted">search</span>
                    <input wire:model.live="search" class="form-control form-control-lg bg-light border-0 ps-5 rounded-pill" placeholder="Buscar por folio, UUID o cliente..." type="text">
                </div>
            </div>
            
            <div class="w-100">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">Comprobante</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">Folio Fiscal (UUID)</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">Cliente Receptor</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0 text-center">Estatus Timbrado</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0 text-end">Monto Total</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0 text-end">Descargas & Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($invoices as $inv)
                        <tr>
                            <td class="py-3 px-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-coral bg-opacity-10 text-coral p-2 rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <span class="material-symbols-outlined">receipt</span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">{{ $inv->series }}-{{ $inv->folio }}</h6>
                                        <small class="text-muted" style="font-size: 11px;">Ticket: #{{ str_pad($inv->order_id, 6, '0', STR_PAD_LEFT) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-monospace text-secondary" style="font-size: 11px;" title="{{ $inv->uuid }}">
                                    {{ substr($inv->uuid, 0, 8) }}...{{ substr($inv->uuid, -12) }}
                                </span>
                                <small class="text-muted d-block mt-0.5" style="font-size: 10px;">Fecha: {{ $inv->created_at->format('d/m/Y H:i') }}</small>
                            </td>
                            <td class="py-3 px-4">
                                <h6 class="mb-0 fw-bold text-dark" style="font-size: 13px;">{{ $inv->customer_name }}</h6>
                                <span class="small text-muted font-monospace" style="font-size: 10px;">RFC: {{ $inv->customer ? $inv->customer->rfc : 'N/A' }}</span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($inv->status === 'active')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill text-uppercase fw-bold">Vigente</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill text-uppercase fw-bold">Cancelada</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-end fw-black text-naval">
                                ${{ number_format($inv->order ? $inv->order->total : 0, 2) }}
                            </td>
                            <td class="py-3 px-4 text-end">
                                <button wire:click="downloadXml({{ $inv->id }})" class="btn btn-sm btn-outline-naval rounded-pill fw-bold px-3 py-1.5 me-1 d-inline-flex align-items-center gap-1">
                                    <span class="material-symbols-outlined fs-6">download</span> XML
                                </button>
                                <button wire:click="downloadPdf({{ $inv->id }})" class="btn btn-sm btn-outline-coral rounded-pill fw-bold px-3 py-1.5 me-1 d-inline-flex align-items-center gap-1">
                                    <span class="material-symbols-outlined fs-6">picture_as_pdf</span> PDF
                                </button>
                                
                                @if($inv->status === 'active')
                                    <button type="button" @click="
                                        Swal.fire({
                                            title: '¿Cancelar Factura?',
                                            text: 'Se enviará solicitud de revocación fiscal CFDI.',
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonColor: '#ff6b6b',
                                            cancelButtonColor: '#0e2649',
                                            confirmButtonText: 'Sí, cancelar',
                                            cancelButtonText: 'No, mantener',
                                            reverseButtons: true
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                $wire.cancelInvoice({{ $inv->id }})
                                            }
                                        })
                                    " class="btn btn-sm btn-outline-danger rounded-circle p-2" title="Cancelar CFDI">
                                        <span class="material-symbols-outlined d-block fs-6">block</span>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <span class="material-symbols-outlined fs-1 opacity-50 mb-3 d-block">receipt_long</span>
                                <p class="fw-bold mb-0">No se encontraron facturas emitidas.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer bg-white border-top p-3">
                {{ $invoices->links('pagination::bootstrap-5') }}
            </div>
        </section>
        @endif

        <!-- MODAL CLIENTE (CREAR / EDITAR) -->
        @if(auth()->user()->hasRole('admin'))
        <div wire:ignore.self class="modal fade" id="customerModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                    <div class="modal-header border-bottom p-4">
                        <h5 class="modal-title fw-bold text-naval">{{ $customerId ? 'Editar Cliente' : 'Registrar Nuevo Cliente' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form wire:submit.prevent="saveCustomer">
                        <div class="modal-body p-4 bg-light">
                            <h6 class="fw-bold text-naval mb-3">Datos de Contacto</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label text-muted fw-bold text-uppercase small">Nombre Completo <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.defer="name" class="form-control bg-white border-0 shadow-sm" placeholder="Ej. Juan Pérez" required>
                                    @error('name') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted fw-bold text-uppercase small">Correo Electrónico</label>
                                    <input type="email" wire:model.defer="email" class="form-control bg-white border-0 shadow-sm" placeholder="juan@correo.com">
                                    @error('email') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted fw-bold text-uppercase small">Teléfono</label>
                                    <input type="text" wire:model.defer="phone" class="form-control bg-white border-0 shadow-sm" placeholder="5512345678">
                                    @error('phone') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted fw-bold text-uppercase small">Límite de Crédito Autorizado ($) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" wire:model.defer="credit_limit" class="form-control bg-white border-0 shadow-sm font-monospace fw-bold text-coral text-center" required>
                                    @error('credit_limit') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <h6 class="fw-bold text-naval mb-3 mt-2">Información Fiscal (Para Facturación Electrónica CFDI)</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label text-muted fw-bold text-uppercase small">RFC</label>
                                    <input type="text" wire:model.defer="rfc" class="form-control bg-white border-0 shadow-sm font-monospace text-uppercase" placeholder="PEPJ800101XXX" maxlength="13">
                                    @error('rfc') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label text-muted fw-bold text-uppercase small">Razón Social o Denominación</label>
                                    <input type="text" wire:model.defer="razon_social" class="form-control bg-white border-0 shadow-sm" placeholder="Ej. JUAN PEREZ MARTINEZ">
                                    @error('razon_social') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label text-muted fw-bold text-uppercase small">Régimen Fiscal</label>
                                    <select wire:model.defer="regimen_fiscal" class="form-select bg-white border-0 shadow-sm">
                                        <option value="">-- Seleccionar Régimen Fiscal --</option>
                                        <option value="601">601 - General de Ley Personas Morales</option>
                                        <option value="603">603 - Personas Morales con Fines no Lucrativos</option>
                                        <option value="605">605 - Sueldos y Salarios e Ingresos Asimilados a Salarios</option>
                                        <option value="606">606 - Arrendamiento</option>
                                        <option value="612">612 - Personas Físicas con Actividades Empresariales y Profesionales</option>
                                        <option value="621">621 - Incorporación Fiscal</option>
                                        <option value="626">626 - Régimen Simplificado de Confianza (RESICO)</option>
                                    </select>
                                    @error('regimen_fiscal') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted fw-bold text-uppercase small">Código Postal Fiscal</label>
                                    <input type="text" wire:model.defer="postal_code" class="form-control bg-white border-0 shadow-sm font-monospace text-center" placeholder="01000" maxlength="10">
                                    @error('postal_code') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top p-4">
                            <button type="button" class="btn btn-light fw-bold px-4 rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-coral fw-bold px-4 rounded-pill shadow-sm">Guardar Cliente</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <!-- MODAL REGISTRAR ABONO -->
        <div wire:ignore.self class="modal fade" id="paymentModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
                <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden text-dark bg-white">
                    <div class="modal-header border-bottom py-3 px-4 bg-light">
                        <h5 class="modal-title fw-bold text-naval">Registrar Abono a Deuda</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form wire:submit.prevent="savePayment">
                        <div class="modal-body p-4">
                            <div class="mb-3 text-center">
                                <span class="material-symbols-outlined text-success fs-1 mb-2">payments</span>
                                <p class="text-secondary small mb-0">Captura el abono de dinero de forma segura para liquidar el saldo deudor.</p>
                            </div>
                            <div class="mb-4">
                                <label class="form-label text-muted small fw-bold text-uppercase">Monto del Abono ($) <span class="text-danger">*</span></label>
                                <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden border">
                                    <span class="input-group-text bg-white border-0 fw-bold text-muted">$</span>
                                    <input type="number" step="0.01" min="0.01" wire:model.defer="paymentAmount" class="form-control border-0 bg-white fw-bold text-success" placeholder="0.00" required>
                                </div>
                                @error('paymentAmount') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold text-uppercase">Comentarios o Referencia</label>
                                <textarea wire:model.defer="paymentNotes" class="form-control bg-light" rows="2" placeholder="Ej. Pago en caja, transferencia bancaria, abono quincenal"></textarea>
                                @error('paymentNotes') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-4 pt-0">
                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success text-white rounded-pill px-4 fw-bold">Registrar Pago</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL HISTORIAL Y ESTADO DE CUENTA (LEDGER) -->
        <div wire:ignore.self class="modal fade" id="ledgerModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden text-dark bg-white">
                    <div class="modal-header border-bottom p-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 45px; height: 45px;">
                                <span class="material-symbols-outlined">menu_book</span>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-naval mb-0">Estado de Cuenta & Trazabilidad</h5>
                                <small class="text-muted">Cliente: {{ $detailCustomer ? $detailCustomer->name : '' }}</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <div class="modal-body p-0">
                        <!-- Navigation Tabs inside Modal -->
                        <ul class="nav nav-tabs nav-fill bg-light border-bottom-0" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link py-3 fw-bold {{ $activeModalTab === 'credit' ? 'active bg-white text-naval border-bottom-0' : 'text-muted' }}" 
                                        wire:click="setModalTab('credit')" type="button" style="border-radius: 0;">
                                    <span class="material-symbols-outlined align-middle me-1">credit_score</span> Historial de Créditos y Abonos
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link py-3 fw-bold {{ $activeModalTab === 'loyalty' ? 'active bg-white text-naval border-bottom-0' : 'text-muted' }}" 
                                        wire:click="setModalTab('loyalty')" type="button" style="border-radius: 0;">
                                    <span class="material-symbols-outlined align-middle me-1">stars</span> Historial de Puntos de Fidelidad
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="p-4 bg-white" style="min-height: 400px; max-height: 550px; overflow-y: auto;">
                            @if($activeModalTab === 'credit')
                                <!-- Credit transactions ledger -->
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="py-2 px-3 small text-muted text-uppercase fw-bold border-0">Fecha / Hora</th>
                                                <th class="py-2 px-3 small text-muted text-uppercase fw-bold border-0">Tipo</th>
                                                <th class="py-2 px-3 small text-muted text-uppercase fw-bold border-0 text-center">Referencia Ticket</th>
                                                <th class="py-2 px-3 small text-muted text-uppercase fw-bold border-0">Motivo / Concepto</th>
                                                <th class="py-2 px-3 small text-muted text-uppercase fw-bold border-0">Procesado Por</th>
                                                <th class="py-2 px-3 small text-muted text-uppercase fw-bold border-0 text-end">Monto</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($creditTransactions as $t)
                                                <tr>
                                                    <td class="py-2 px-3">{{ $t->created_at->format('d/m/Y H:i:s') }}</td>
                                                    <td class="py-2 px-3">
                                                        @if($t->type === 'charge')
                                                            <span class="badge bg-danger bg-opacity-10 text-danger px-2.5 py-1.5 rounded-pill font-monospace fw-bold text-uppercase">Cargo</span>
                                                        @else
                                                            <span class="badge bg-success bg-opacity-10 text-success px-2.5 py-1.5 rounded-pill font-monospace fw-bold text-uppercase">Abono</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-2 px-3 text-center font-monospace">
                                                        @if($t->order_id)
                                                            <span class="fw-bold text-naval">#{{ str_pad($t->order_id, 6, '0', STR_PAD_LEFT) }}</span>
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-2 px-3 small text-secondary fw-medium">{{ $t->notes }}</td>
                                                    <td class="py-2 px-3 text-muted">{{ $t->processedBy ? $t->processedBy->name : 'N/A' }}</td>
                                                    <td class="py-2 px-3 text-end fw-black {{ $t->type === 'charge' ? 'text-danger' : 'text-success' }}">
                                                        {{ $t->type === 'charge' ? '-' : '+' }}${{ number_format($t->amount, 2) }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="6" class="text-center py-5 text-muted">No se registran transacciones de crédito en la cuenta.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <!-- Loyalty Points ledger -->
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="py-2 px-3 small text-muted text-uppercase fw-bold border-0">Fecha / Hora</th>
                                                <th class="py-2 px-3 small text-muted text-uppercase fw-bold border-0">Operación</th>
                                                <th class="py-2 px-3 small text-muted text-uppercase fw-bold border-0 text-center">Referencia Ticket</th>
                                                <th class="py-2 px-3 small text-muted text-uppercase fw-bold border-0 text-end">Puntos</th>
                                                <th class="py-2 px-3 small text-muted text-uppercase fw-bold border-0 text-end">Valor Equivalente</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($loyaltyTransactions as $lt)
                                                <tr>
                                                    <td class="py-2 px-3">{{ $lt->created_at->format('d/m/Y H:i:s') }}</td>
                                                    <td class="py-2 px-3">
                                                        @if($lt->type === 'earn')
                                                            <span class="badge bg-success bg-opacity-10 text-success px-2.5 py-1.5 rounded-pill font-monospace fw-bold text-uppercase">Sumados</span>
                                                        @else
                                                            <span class="badge bg-warning bg-opacity-10 text-warning px-2.5 py-1.5 rounded-pill font-monospace fw-bold text-uppercase">Canjeados</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-2 px-3 text-center font-monospace">
                                                        @if($lt->order_id)
                                                            <span class="fw-bold text-naval">#{{ str_pad($lt->order_id, 6, '0', STR_PAD_LEFT) }}</span>
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-2 px-3 text-end fw-bold {{ $lt->type === 'earn' ? 'text-success' : 'text-warning' }}">
                                                        {{ $lt->type === 'earn' ? '+' : '-' }}{{ $lt->points }} Pts
                                                    </td>
                                                    <td class="py-2 px-3 text-end fw-medium text-secondary">
                                                        ${{ number_format($lt->value_amount, 2) }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="5" class="text-center py-5 text-muted">No se registran movimientos de puntos de fidelidad.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="modal-footer border-top p-4">
                        <button type="button" class="btn btn-naval text-white rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('close-modal', () => {
                ['#customerModal', '#paymentModal', '#ledgerModal'].forEach(id => {
                    var element = document.querySelector(id);
                    if (element) {
                        var modal = bootstrap.Modal.getInstance(element);
                        if (modal) modal.hide();
                    }
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
            from { opacity: 0; transform: translateY(-10px) scaleY(0.9); }
            to { opacity: 1; transform: translateY(0) scaleY(1); }
        }
    </style>
</div>
