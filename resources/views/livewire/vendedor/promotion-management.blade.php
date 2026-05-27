<div x-data="{ 
    sidebarOpen: true,
    showCreateModal: @entangle('showCreateModal')
}" class="d-flex w-100 vh-100 bg-light text-dark overflow-hidden" style="font-family: 'Instrument Sans', sans-serif;">
    
    <!-- DESKTOP NAVIGATION -->
    <x-sidebar active="admin.promotions" />

    <!-- MAIN CONTENT -->
    <main class="flex-grow-1 p-3 p-md-4 main-content w-100 transition-all d-flex flex-column h-100 overflow-hidden" :style="sidebarOpen ? 'margin-left: 300px; max-width: calc(100% - 300px);' : 'margin-left: 80px; max-width: calc(100% - 80px);'" style="transition: all 0.3s ease;">
        
        <!-- Header -->
        <header class="bg-white border-bottom p-4 rounded-4 shadow-sm mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="h3 fw-bold text-naval mb-0 d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined text-coral fs-2">percent</span>
                    Configuración de Promociones y Combos
                </h2>
                <p class="text-muted mb-0 small fw-medium">Administra cupones de descuento, precios de mayoreo por volumen y paquetes/combos especiales.</p>
            </div>
            <button type="button" wire:click="openNewPromotionModal" class="btn btn-coral text-white rounded-pill fw-bold px-4 py-2.5 d-flex align-items-center gap-2 shadow-sm">
                <span class="material-symbols-outlined fs-5">add_circle</span>
                Crear Promoción / Combo
            </button>
        </header>

        <!-- Search and List Area -->
        <section class="flex-grow-1 d-flex flex-column overflow-hidden bg-white rounded-4 shadow-sm border p-4">
            
            <!-- Filters -->
            <div class="row g-3 align-items-center mb-4">
                <div class="col-md-6 col-lg-4 position-relative">
                    <span class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y ms-3 text-muted fs-5">search</span>
                    <input wire:model.live="search" type="text" class="form-control bg-light border-0 shadow-none ps-5 rounded-pill" placeholder="Buscar promoción por nombre o código...">
                </div>
            </div>

            <!-- List Grid -->
            <div class="flex-grow-1 overflow-auto">
                <div class="row g-3 g-md-4">
                    @forelse($promotions as $promo)
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="card h-100 border-0 shadow-sm rounded-4 hover-shadow transition-all border-top border-5" 
                                 style="border-color: {{ $promo->type === 'code' ? '#ffc107' : ($promo->type === 'quantity' ? '#0dacf0' : '#ff6b6b') }} !important;">
                                
                                <div class="card-body p-4 d-flex flex-column justify-content-between">
                                    <div>
                                        <!-- Header info -->
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <span class="badge rounded-pill px-3 py-1.5 fw-bold d-flex align-items-center gap-1.5" 
                                                  style="background: {{ $promo->type === 'code' ? 'rgba(255, 193, 7, 0.15)' : ($promo->type === 'quantity' ? 'rgba(13, 172, 240, 0.15)' : 'rgba(255, 107, 107, 0.15)') }};
                                                         color: {{ $promo->type === 'code' ? '#856404' : ($promo->type === 'quantity' ? '#004085' : '#721c24') }};">
                                                <span class="material-symbols-outlined fs-6">
                                                    {{ $promo->type === 'code' ? 'confirmation_number' : ($promo->type === 'quantity' ? 'group_work' : 'package_2') }}
                                                </span>
                                                {{ $promo->type === 'code' ? 'Cupón / Código' : ($promo->type === 'quantity' ? 'Mayoreo / Volumen' : 'Combo / Paquete') }}
                                            </span>
                                            
                                            <button wire:click="toggleStatus({{ $promo->id }})" class="border-0 bg-transparent p-0">
                                                <span class="badge rounded-pill fw-bold cursor-pointer transition-all {{ $promo->status === 'active' ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary' }}">
                                                    {{ $promo->status === 'active' ? 'Activo' : 'Inactivo' }}
                                                </span>
                                            </button>
                                        </div>

                                        <h5 class="fw-bold text-naval mb-2">{{ $promo->name }}</h5>
                                        
                                        <!-- Specific Promo Value Details -->
                                        <div class="bg-light rounded-3 p-3 mb-3 border">
                                            <div class="d-flex justify-content-between mb-1.5 text-secondary small fw-medium">
                                                <span>Descuento / Beneficio</span>
                                                <strong class="text-naval">
                                                    @if($promo->discount_type === 'percentage')
                                                        {{ number_format($promo->discount_value, 0) }}% Descuento
                                                    @elseif($promo->discount_type === 'fixed_discount')
                                                        -${{ number_format($promo->discount_value, 2) }} Descuento
                                                    @elseif($promo->discount_type === 'fixed_price')
                                                        ${{ number_format($promo->discount_value, 2) }} Precio Fijo
                                                    @endif
                                                </strong>
                                            </div>
                                            
                                            @if($promo->type === 'code')
                                                <div class="d-flex justify-content-between mb-1.5 text-secondary small fw-medium">
                                                    <span>Código del Cupón</span>
                                                    <span class="badge bg-warning text-dark font-monospace fw-bold">{{ $promo->code }}</span>
                                                </div>
                                            @endif

                                            @if($promo->type === 'quantity')
                                                <div class="d-flex justify-content-between mb-1.5 text-secondary small fw-medium">
                                                    <span>Mínimo de Piezas</span>
                                                    <strong class="text-naval">{{ $promo->min_quantity }} pzs o más</strong>
                                                </div>
                                            @endif

                                            @if($promo->start_date || $promo->end_date)
                                                <div class="d-flex justify-content-between text-secondary small fw-medium border-top pt-1.5 mt-1.5">
                                                    <span>Vigencia</span>
                                                    <span style="font-size: 11px;">
                                                        {{ $promo->start_date ? $promo->start_date->format('d/m/y') : 'Indefinida' }} - 
                                                        {{ $promo->end_date ? $promo->end_date->format('d/m/y') : 'Indefinida' }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Affected Products List -->
                                        @if($promo->products->isNotEmpty())
                                            <div class="mb-3">
                                                <small class="text-muted d-block fw-bold text-uppercase mb-1.5" style="font-size: 9px; letter-spacing: 0.5px;">Productos Incluidos</small>
                                                <div class="d-flex flex-wrap gap-1.5 overflow-hidden" style="max-height: 52px;">
                                                    @foreach($promo->products as $p)
                                                        <span class="badge bg-light border text-dark rounded-pill fw-medium" style="font-size: 10px;">
                                                            {{ $p->title }}
                                                            @if($promo->type === 'combo')
                                                                (x{{ $p->pivot->quantity }})
                                                            @endif
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Action buttons -->
                                    <div class="d-flex gap-2 border-top pt-3 mt-3 justify-content-end">
                                        <button type="button" wire:click="editPromotion({{ $promo->id }})" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold d-flex align-items-center gap-1">
                                            <span class="material-symbols-outlined fs-6">edit</span>
                                            Editar
                                        </button>
                                        <button type="button" 
                                                onclick="confirm('¿Estás seguro de eliminar permanentemente esta promoción?') || event.stopImmediatePropagation()" 
                                                wire:click="deletePromotion({{ $promo->id }})" 
                                                class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold d-flex align-items-center gap-1">
                                            <span class="material-symbols-outlined fs-6">delete</span>
                                            Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5">
                            <span class="material-symbols-outlined text-muted opacity-50 mb-3" style="font-size: 64px;">percent</span>
                            <h4 class="fw-bold text-naval">No hay promociones registradas</h4>
                            <p class="text-secondary">Haz clic en "Crear Promoción" para configurar cupones o combos.</p>
                        </div>
                    @endforelse
                </div>
                
                <!-- Pagination -->
                <div class="mt-4">
                    {{ $promotions->links() }}
                </div>
            </div>
        </section>
    </main>

    <!-- FORM MODAL (CREATE / EDIT) -->
    <div x-show="showCreateModal" x-cloak
         class="position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center"
         :class="showCreateModal ? 'd-flex' : 'd-none'"
         style="z-index: 1050; background-color: rgba(0,0,0,0.6);"
         @click.self="showCreateModal = false">
        
        <div class="modal-dialog modal-dialog-centered" style="max-width: 600px; width: 100%;">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden text-dark bg-white">
                <div class="modal-header border-bottom py-3 px-4 d-flex align-items-center bg-light">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-coral text-white p-2 rounded-circle d-flex align-items-center justify-content-center shadow-sm">
                            <span class="material-symbols-outlined">percent</span>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-naval mb-0">{{ $promotionId ? 'Editar Promoción' : 'Nueva Promoción / Combo' }}</h5>
                            <small class="text-muted">Configuración de Descuentos Especiales</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" @click="showCreateModal = false"></button>
                </div>
                
                <form wire:submit.prevent="savePromotion">
                    <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
                        
                        <!-- Name Input -->
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold">Nombre de la Promoción (Interno)</label>
                            <input type="text" wire:model.defer="name" class="form-control bg-light border-0 py-2.5 rounded-3 fw-medium text-naval" placeholder="Ej. Combo Desayuno Completo o Descuento de Buen Fin" required>
                            @error('name') <small class="text-danger fw-bold d-block mt-1">{{ $message }}</small> @enderror
                        </div>

                        <!-- Type Selector (Tab-style) -->
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold">Tipo de Promoción</label>
                            <div class="d-flex gap-2">
                                <button type="button" wire:click="$set('type', 'code')" 
                                        :class="$wire.type === 'code' ? 'btn-coral text-white bg-coral border-coral' : 'btn-light border-secondary text-muted'"
                                        class="btn flex-fill py-2.5 rounded-3 fw-bold transition-all d-flex align-items-center justify-content-center gap-1.5">
                                    <span class="material-symbols-outlined fs-5">confirmation_number</span>
                                    Cupón / Código
                                </button>
                                <button type="button" wire:click="$set('type', 'quantity')" 
                                        :class="$wire.type === 'quantity' ? 'btn-coral text-white bg-coral border-coral' : 'btn-light border-secondary text-muted'"
                                        class="btn flex-fill py-2.5 rounded-3 fw-bold transition-all d-flex align-items-center justify-content-center gap-1.5">
                                    <span class="material-symbols-outlined fs-5">group_work</span>
                                    Mayoreo / Volumen
                                </button>
                                <button type="button" wire:click="$set('type', 'combo')" 
                                        :class="$wire.type === 'combo' ? 'btn-coral text-white bg-coral border-coral' : 'btn-light border-secondary text-muted'"
                                        class="btn flex-fill py-2.5 rounded-3 fw-bold transition-all d-flex align-items-center justify-content-center gap-1.5">
                                    <span class="material-symbols-outlined fs-5">package_2</span>
                                    Combo / Paquete
                                </button>
                            </div>
                        </div>

                        <!-- CONDITIONAL INPUTS -->
                        
                        <!-- A. Code/Coupon Mode -->
                        <div x-show="$wire.type === 'code'" class="mb-4">
                            <label class="form-label text-muted small fw-bold">Código Único del Cupón</label>
                            <input type="text" wire:model.defer="code" class="form-control bg-light border-0 py-2.5 rounded-3 font-monospace fw-bold text-naval" placeholder="Ej. ASEL20">
                            <small class="text-secondary d-block mt-1">El cliente o cajero deberá escribir este código exacto para activar el beneficio.</small>
                            @error('code') <small class="text-danger fw-bold d-block mt-1">{{ $message }}</small> @enderror
                        </div>

                        <!-- B. Quantity/Volume Mode -->
                        <div x-show="$wire.type === 'quantity'" class="mb-4">
                            <label class="form-label text-muted small fw-bold">Cantidad Mínima de Productos</label>
                            <input type="number" min="2" wire:model.defer="min_quantity" class="form-control bg-light border-0 py-2.5 rounded-3 fw-bold text-naval" placeholder="3">
                            <small class="text-secondary d-block mt-1">Número mínimo de piezas del mismo artículo que se deben comprar para activar la promoción.</small>
                            @error('min_quantity') <small class="text-danger fw-bold d-block mt-1">{{ $message }}</small> @enderror
                        </div>

                        <!-- C. Product Selection List (Only for quantity & combo) -->
                        <div x-show="$wire.type !== 'code'" class="mb-4 p-3 rounded-4 border bg-light bg-opacity-50">
                            <label class="form-label text-muted small fw-bold d-block mb-2">Seleccionar Producto(s)</label>
                            
                            <select class="form-select border-0 bg-white py-2.5 rounded-3 mb-3 text-naval fw-medium shadow-sm"
                                    x-on:change="$wire.addProductToPromotion($event.target.value); $event.target.value = '';">
                                <option value="">-- Buscar y Agregar Producto --</option>
                                @foreach($allProducts as $prod)
                                    @if(!in_array($prod->id, $selectedProducts))
                                        <option value="{{ $prod->id }}">{{ $prod->title }} (${{ number_format($prod->price, 2) }})</option>
                                    @endif
                                @endforeach
                            </select>

                            <!-- Selected List -->
                            <div class="d-flex flex-column gap-2">
                                @forelse($selectedProducts as $prodId)
                                    @php $prodObj = $allProducts->firstWhere('id', $prodId); @endphp
                                    @if($prodObj)
                                        <div class="d-flex align-items-center justify-content-between p-2.5 rounded-3 bg-white shadow-sm border">
                                            <span class="text-naval fw-bold small flex-grow-1">{{ $prodObj->title }}</span>
                                            
                                            <!-- Quantity selector for combos -->
                                            <div x-show="$wire.type === 'combo'" class="d-flex align-items-center gap-1.5 me-3">
                                                <small class="text-secondary fw-bold" style="font-size: 11px;">Req:</small>
                                                <input type="number" min="1" 
                                                       wire:model="comboQuantities.{{ $prodId }}" 
                                                       class="form-control form-control-sm bg-light text-center border-0 fw-bold" 
                                                       style="width: 60px;">
                                                <small class="text-muted small">uds</small>
                                            </div>

                                            <button type="button" wire:click="removeProductFromPromotion({{ $prodId }})" class="btn btn-sm btn-link text-danger p-0">
                                                <span class="material-symbols-outlined fs-5 align-middle">delete</span>
                                            </button>
                                        </div>
                                    @endif
                                @empty
                                    <div class="text-center py-3 text-secondary small">
                                        Ningún producto seleccionado aún.
                                    </div>
                                @endforelse
                            </div>
                            @error('selectedProducts') <small class="text-danger fw-bold d-block mt-2">{{ $message }}</small> @enderror
                        </div>

                        <!-- Discount Config -->
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <label class="form-label text-muted small fw-bold">Tipo de Beneficio</label>
                                <select wire:model.live="discount_type" class="form-select bg-light border-0 py-2.5 rounded-3 fw-medium text-naval shadow-none">
                                    <option value="percentage">Porcentaje (%)</option>
                                    <option value="fixed_discount">Monto Fijo de Descuento ($)</option>
                                    <option x-show="$wire.type !== 'code'" value="fixed_price">Precio Fijo del Combo/Línea ($)</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted small fw-bold" x-text="$wire.discount_type === 'percentage' ? 'Porcentaje (%)' : ($wire.discount_type === 'fixed_price' ? 'Precio Final ($)' : 'Descuento ($)')"></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0 fw-bold text-muted" x-text="$wire.discount_type === 'percentage' ? '%' : '$'"></span>
                                    <input type="number" step="0.01" min="0.01" wire:model.defer="discount_value" class="form-control bg-light border-0 py-2.5 rounded-end-3 fw-bold text-naval" required>
                                </div>
                                @error('discount_value') <small class="text-danger fw-bold d-block mt-1">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <!-- Date range & status -->
                        <div class="row g-3 mb-2">
                            <div class="col-6">
                                <label class="form-label text-muted small fw-bold">Fecha de Inicio (Vigencia)</label>
                                <input type="datetime-local" wire:model.defer="start_date" class="form-control bg-light border-0 py-2.5 rounded-3 text-naval fw-medium">
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted small fw-bold">Fecha de Fin (Vigencia)</label>
                                <input type="datetime-local" wire:model.defer="end_date" class="form-control bg-light border-0 py-2.5 rounded-3 text-naval fw-medium">
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="form-label text-muted small fw-bold">Estado</label>
                            <select wire:model.defer="status" class="form-select bg-light border-0 py-2.5 rounded-3 fw-medium text-naval shadow-none">
                                <option value="active">Activo (Disponible de inmediato)</option>
                                <option value="inactive">Inactivo (Guardado pero deshabilitado)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" @click="showCreateModal = false">Cancelar</button>
                        <button type="submit" class="btn btn-coral text-white rounded-pill px-4 fw-bold d-flex align-items-center gap-1.5">
                            <span class="material-symbols-outlined fs-5">save</span>
                            {{ $promotionId ? 'Guardar Cambios' : 'Crear Promoción' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- FLOATING NOTIFICATIONS (TOAST PORTAL) -->
    <div class="position-fixed top-0 end-0 p-4" style="z-index: 9999; pointer-events: none;">
        @if(session()->has('success') || session()->has('message'))
        <div x-data="{ 
                show: true,
                init() {
                    setTimeout(() => this.show = false, 5000);
                }
             }"
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-4 scale-95"
             x-transition:enter-end="opacity-100 translate-x-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 translate-x-4 scale-95"
             class="card border-0 shadow-lg p-3 mb-3 d-flex flex-row align-items-center gap-3 bg-white"
             style="min-width: 320px; max-width: 400px; border-left: 5px solid #198754 !important; border-radius: 16px; pointer-events: auto;"
             x-cloak>
            <span class="material-symbols-outlined text-success fs-1">check_circle</span>
            <div class="flex-grow-1 me-2">
                <h6 class="fw-bold text-success mb-0" style="font-size: 14px;">¡Acción Confirmada!</h6>
                <small class="text-secondary fw-medium" style="font-size: 12px; line-height: 1.3;">{{ session('success') ?? session('message') }}</small>
            </div>
            <button type="button" class="btn-close align-self-start" @click="show = false" style="font-size: 10px;"></button>
        </div>
        @endif

        @if(session()->has('error'))
        <div x-data="{ 
                show: true,
                init() {
                    setTimeout(() => this.show = false, 6000);
                }
             }"
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-4 scale-95"
             x-transition:enter-end="opacity-100 translate-x-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 translate-x-4 scale-95"
             class="card border-0 shadow-lg p-3 mb-3 d-flex flex-row align-items-center gap-3 bg-white"
             style="min-width: 320px; max-width: 400px; border-left: 5px solid #dc3545 !important; border-radius: 16px; pointer-events: auto;"
             x-cloak>
            <span class="material-symbols-outlined text-danger fs-1">error</span>
            <div class="flex-grow-1 me-2">
                <h6 class="fw-bold text-danger mb-0" style="font-size: 14px;">Error Detectado</h6>
                <small class="text-secondary fw-medium" style="font-size: 12px; line-height: 1.3;">{{ session('error') }}</small>
            </div>
            <button type="button" class="btn-close align-self-start" @click="show = false" style="font-size: 10px;"></button>
        </div>
        @endif
    </div>
</div>
