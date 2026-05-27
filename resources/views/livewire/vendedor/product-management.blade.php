<div x-data="{ sidebarOpen: true }" wire:poll.1s="checkMobileScans" @barcode-scanned.window="$wire.set('barcode', $event.detail.barcode)" class="d-flex w-100 min-vh-100 bg-light text-dark" style="font-family: 'Instrument Sans', sans-serif;">
    
    <!-- SideNavBar -->
    <x-sidebar active="vendedor.products" />

    <!-- Main Content -->
    <main class="flex-grow-1 p-4 p-md-5 main-content w-100 transition-all" :style="sidebarOpen ? 'margin-left: 300px; max-width: calc(100% - 300px);' : 'margin-left: 80px; max-width: calc(100% - 80px);'" style="transition: all 0.3s ease;">
        <!-- Header -->
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
            <div>
                <h2 class="fw-bold text-naval mb-1">Catálogo de Productos</h2>
                <p class="text-muted mb-0 small fw-medium">Administra tu mercancía y genera descripciones con IA.</p>
            </div>
            
            <button data-bs-toggle="modal" data-bs-target="#productModal" wire:click="resetForm" class="btn btn-coral d-flex align-items-center gap-2 fw-bold rounded-pill px-4 py-2 shadow-sm">
                <span class="material-symbols-outlined fs-5">add_circle</span>
                Nuevo Producto
            </button>
        </header>

        <!-- Messages -->
        @if (session()->has('message'))
            <div class="alert alert-success fw-bold d-flex align-items-center gap-2 mb-4 shadow-sm border-0 rounded-4">
                <span class="material-symbols-outlined">check_circle</span>
                {{ session('message') }}
            </div>
        @endif

        <!-- Products List -->
        <section class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center gap-3">
                <div class="position-relative w-100" style="max-width: 400px;">
                    <span class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y ms-3 text-muted">search</span>
                    <input wire:model.live="search" class="form-control form-control-lg bg-light border-0 ps-5 rounded-pill" placeholder="Buscar por título o SKU..." type="text">
                </div>
                <div class="text-muted fw-bold small">
                    Mostrando {{ $products->count() }} productos
                </div>
            </div>
            
            <div class="w-100 overflow-auto">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0" style="width: 80px;">Img</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">Producto</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">SKU / Barras</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0">Stock Sucursales</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0 text-end">Costo</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0 text-end">Precio Venta</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0 text-center">Estado</th>
                            <th class="py-3 px-4 text-muted small text-uppercase fw-bold border-0 text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($products as $product)
                        <tr wire:key="product-row-{{ $product->id }}">
                            <td class="py-3 px-4">
                                @if($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="img" class="rounded-3 object-fit-cover shadow-sm" width="48" height="48">
                                @else
                                    <div class="bg-light rounded-3 d-flex align-items-center justify-content-center text-muted" style="width: 48px; height: 48px;">
                                        <span class="material-symbols-outlined fs-5">image</span>
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <h6 class="mb-0 fw-bold text-dark">{{ $product->title }}</h6>
                                <small class="text-muted text-truncate d-inline-block" style="max-width: 200px;">{{ $product->description ?? 'Sin descripción' }}</small>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-monospace small bg-light px-2 py-1 rounded d-inline-block">{{ $product->sku ?? 'N/A' }}</div>
                            </td>
                            <td class="py-3 px-4" style="max-width: 300px;">
                                <div class="d-flex flex-wrap gap-2">
                                    @forelse($product->inventories as $inv)
                                        <span class="badge bg-white text-dark border px-2 py-1 rounded-pill d-inline-flex align-items-center shadow-sm">
                                            <span class="material-symbols-outlined text-muted me-1" style="font-size: 14px;">store</span>
                                            {{ optional($inv->branch)->name ?? 'N/A' }}
                                            <span class="badge ms-2 rounded-pill {{ $inv->stock_quantity <= $inv->alert_min_stock ? 'bg-danger text-white' : 'bg-success bg-opacity-10 text-success' }}">
                                                {{ $inv->stock_quantity }}
                                            </span>
                                        </span>
                                    @empty
                                        <span class="text-muted small"><span class="material-symbols-outlined align-middle fs-6">inventory_2</span> Sin stock asignado</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="py-3 px-4 text-end text-muted fw-medium">${{ number_format($product->cost, 2) }}</td>
                            <td class="py-3 px-4 text-end text-naval fw-bold fs-6">${{ number_format($product->price, 2) }}</td>
                            <td class="py-3 px-4 text-center">
                                @if($product->status === 'active')
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill text-uppercase fw-bold">Activo</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill text-uppercase fw-bold">Inactivo</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-end">
                                <div class="position-relative" x-data="{ open: false }" @click.outside="open = false">
                                    <button class="btn btn-light btn-sm rounded-circle p-2" type="button" @click="open = !open">
                                        <span class="material-symbols-outlined d-block">more_vert</span>
                                    </button>
                                    <ul class="dropdown-menu border-0 shadow-lg rounded-4 overflow-hidden" :class="open ? 'show' : ''" style="position: absolute; right: 0; top: 100%; margin-top: 5px; z-index: 1000; min-width: 160px; display: block;" x-show="open" x-transition>
                                        <li>
                                            <button wire:click="editProduct({{ $product->id }})" @click="open = false" class="dropdown-item py-2 d-flex align-items-center gap-2 fw-medium" data-bs-toggle="modal" data-bs-target="#productModal">
                                                <span class="material-symbols-outlined text-muted fs-5">edit</span> Editar
                                            </button>
                                        </li>
                                        <li>
                                            <button wire:click="toggleStatus({{ $product->id }})" @click="open = false" class="dropdown-item py-2 d-flex align-items-center gap-2 fw-medium">
                                                <span class="material-symbols-outlined text-muted fs-5">{{ $product->status === 'active' ? 'block' : 'check_circle' }}</span> 
                                                {{ $product->status === 'active' ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <span class="material-symbols-outlined fs-1 opacity-50 mb-3 d-block">inventory_2</span>
                                <p class="fw-bold mb-0">No se encontraron productos.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top p-3">
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
        </section>
    </main>

    <!-- Modal Form -->
    <div wire:ignore.self class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header border-bottom p-4">
                    <h5 class="modal-title fw-bold text-naval">{{ $productId ? 'Editar Producto' : 'Nuevo Producto' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body p-0 bg-light">
                    <!-- Sección Inteligencia Artificial -->
                    @if(!$productId)
                    <div class="bg-white p-4 border-bottom">
                        <h6 class="fw-bold text-naval d-flex align-items-center gap-2 mb-3">
                            <span class="material-symbols-outlined text-coral">smart_toy</span>
                            Asistente Mágico de Creación
                        </h6>
                        <div class="input-group mb-2 shadow-sm rounded-pill overflow-hidden border-0">
                            <input type="text" wire:model="aiPrompt" class="form-control form-control-lg border-0 bg-white" placeholder="Ej. Coca Cola 600ml">
                            <button wire:click="generateWithAI" class="btn btn-naval px-4 fw-bold d-flex align-items-center gap-2" :disabled="$wire.isGenerating">
                                <span x-show="!$wire.isGenerating" class="material-symbols-outlined">auto_awesome</span>
                                <span x-show="!$wire.isGenerating">Generar Detalle</span>
                                
                                <span x-show="$wire.isGenerating" class="spinner-border spinner-border-sm"></span>
                                <span x-show="$wire.isGenerating">Creando...</span>
                            </button>
                        </div>
                        @error('aiPrompt') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                        @if(session()->has('ai_message'))
                            <div class="text-success small fw-bold mt-2">{{ session('ai_message') }}</div>
                        @endif
                    </div>
                    @endif

                    <!-- Formulario Regular -->
                    <div class="p-4 row g-3">
                        <div class="col-md-8">
                            <label class="form-label text-muted fw-bold text-uppercase small">Título del Producto <span class="text-danger">*</span></label>
                            <input type="text" wire:model="title" class="form-control form-control-lg bg-white border-0 shadow-sm" placeholder="Ej. Coca Cola Regular 600ml">
                            @error('title') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted fw-bold text-uppercase small">Nombre Genérico</label>
                            <input type="text" wire:model="raw_title" class="form-control form-control-lg bg-white border-0 shadow-sm" placeholder="Ej. coca cola">
                            @error('raw_title') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label text-muted fw-bold text-uppercase small">Descripción Atractiva</label>
                            <textarea wire:model="description" class="form-control bg-white border-0 shadow-sm" rows="3"></textarea>
                            @error('description') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted fw-bold text-uppercase small">SKU (Código Interno)</label>
                            <input type="text" wire:model="sku" class="form-control bg-white border-0 shadow-sm font-monospace">
                            @error('sku') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fw-bold text-uppercase small d-flex justify-content-between align-items-center">
                                <span>Código de Barras</span>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-coral fw-bold d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#pairScannerModal">
                                    <span class="material-symbols-outlined fs-6">qr_code_scanner</span>
                                    Celular Escáner
                                </button>
                            </label>
                            <input type="text" wire:model="barcode" class="form-control bg-white border-0 shadow-sm font-monospace">
                            @error('barcode') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                            @if(session()->has('scanner_message'))
                                <div class="text-success small fw-bold mt-1 d-flex align-items-center gap-1">
                                    <span class="material-symbols-outlined fs-6">check_circle</span>
                                    {{ session('scanner_message') }}
                                </div>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-muted fw-bold text-uppercase small">Costo <span class="text-danger">*</span></label>
                            <div class="input-group shadow-sm rounded-3 overflow-hidden border-0">
                                <span class="input-group-text bg-white border-0">$</span>
                                <input type="number" step="0.01" wire:model="cost" class="form-control bg-white border-0">
                            </div>
                            @error('cost') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted fw-bold text-uppercase small">Precio Venta <span class="text-danger">*</span></label>
                            <div class="input-group shadow-sm rounded-3 overflow-hidden border-0">
                                <span class="input-group-text bg-white border-0">$</span>
                                <input type="number" step="0.01" wire:model="price" class="form-control bg-white border-0">
                            </div>
                            @error('price') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted fw-bold text-uppercase small">Estado</label>
                            <select wire:model="status" class="form-select bg-white border-0 shadow-sm">
                                <option value="active">Activo</option>
                                <option value="inactive">Inactivo</option>
                            </select>
                            @error('status') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 mt-3">
                            <label class="form-label text-muted fw-bold text-uppercase small">Imagen del Producto</label>
                            <input type="file" wire:model="image" class="form-control bg-white border-0 shadow-sm">
                            <div wire:loading wire:target="image" class="mt-2 text-primary small fw-bold">Subiendo imagen...</div>
                            @if ($image)
                                <div class="mt-2 text-center bg-white p-2 rounded-3 border">
                                    <img src="{{ $image->temporaryUrl() }}" class="img-fluid rounded" style="max-height: 120px;">
                                </div>
                            @endif
                            @error('image') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top p-4">
                    <button type="button" class="btn btn-light fw-bold px-4 rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" wire:click="saveProduct" class="btn btn-coral fw-bold px-4 rounded-pill shadow-sm">
                        {{ $productId ? 'Actualizar Producto' : 'Guardar Producto' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('close-modal', () => {
                var modal = bootstrap.Modal.getInstance(document.getElementById('productModal'));
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
        .dot-pulse {
            position: relative;
            width: 8px;
            height: 8px;
            border-radius: 5px;
            background-color: #ff6b6b;
            color: #ff6b6b;
            animation: pulse-dot 1.5s infinite linear;
        }
        @keyframes pulse-dot {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 107, 107, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(255, 107, 107, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 107, 107, 0); }
        }
    </style>

    <!-- Modal de Vinculación de Escáner -->
    <div wire:ignore.self class="modal fade" id="pairScannerModal" tabindex="-1" aria-labelledby="pairScannerModalLabel" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-bottom py-3 px-4 d-flex align-items-center bg-light" style="border-top-left-radius: 20px; border-top-right-radius: 20px;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-coral text-white p-2 rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="background-color: #ff6b6b !important;">
                            <span class="material-symbols-outlined">qr_code_scanner</span>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-naval" id="pairScannerModalLabel">Vincular Celular Escáner</h5>
                            <small class="text-muted">ASEL Scanner Link</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="text-secondary small mb-3">Escanea el código QR de abajo con la cámara de tu celular para autocompletar el campo de código de barras en tiempo real al escanear artículos.</p>
                    
                    @if(in_array(request()->getHost(), ['localhost', '127.0.0.1']))
                        <div class="alert alert-warning border-0 rounded-3 text-start small mb-3 p-3 shadow-sm" style="font-size: 11px; background-color: #fff3cd; color: #664d03;">
                            <div class="d-flex gap-2">
                                <span class="material-symbols-outlined text-warning fs-5 flex-shrink-0">warning</span>
                                <div>
                                    <strong class="d-block mb-1" style="color: #664d03;">¡Atención: Servidor en Local!</strong>
                                    Como estás en <code>{{ request()->getHost() }}</code>, tu celular no sabrá dónde conectarse. Sigue estos pasos:
                                    <ol class="ps-3 mt-1 mb-0">
                                        <li>Inicia el servidor en tu terminal con:<br><code class="bg-white px-2 py-0.5 rounded border">php artisan serve --host=0.0.0.0 --port=8080</code></li>
                                        <li>Entra a Productos en tu computadora usando tu **IP Local** (ej: <code>http://192.168.1.15:8080/vendedor/products</code>) en lugar de localhost.</li>
                                        <li>¡Vuelve a abrir este modal, escanea el QR y listo!</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Generación dinámica de QR -->
                    <div class="bg-light p-3 rounded-4 d-inline-block border mb-4">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode(route('scanner.mobile', $pairingToken)) }}" alt="QR Code" width="180" height="180" class="shadow-sm rounded-3">
                    </div>
                    
                    <div class="mb-4">
                        <span class="text-muted d-block small">Código de Vinculación</span>
                        <h4 class="fw-black text-naval tracking-wide mt-1" style="letter-spacing: 2px;">{{ substr($pairingToken, 4, 6) }}</h4>
                    </div>

                    <div class="d-flex align-items-center justify-content-center gap-2 text-naval fw-medium bg-light py-2 px-3 rounded-pill border" style="width: fit-content; margin: 0 auto;">
                        <span class="dot-pulse"></span>
                        <small style="font-size: 11px;">Esperando escaneo desde el móvil...</small>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0 justify-content-center">
                    <button type="button" class="btn btn-naval rounded-pill px-4 text-white" data-bs-dismiss="modal">Listo, volver al producto</button>
                </div>
            </div>
        </div>
    </div>
</div>
