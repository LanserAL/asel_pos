<div x-data="{ sidebarOpen: true }" class="d-flex w-100 min-vh-100 bg-light text-dark" style="font-family: 'Instrument Sans', sans-serif;">
    
    <!-- SideNavBar -->
    <x-sidebar active="vendedor.settings" />

    <!-- Main Content -->
    <main class="flex-grow-1 p-4 p-md-5 main-content w-100 transition-all" :style="sidebarOpen ? 'margin-left: 300px; max-width: calc(100% - 300px);' : 'margin-left: 80px; max-width: calc(100% - 80px);'" style="transition: all 0.3s ease;">
        <!-- Header -->
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
            <div>
                <h2 class="fw-bold text-naval mb-1">Mi Perfil</h2>
                <p class="text-muted mb-0 small fw-medium">Administra los detalles de tu negocio y carga el logotipo comercial.</p>
            </div>
        </header>

        <!-- Messages -->
        @if (session()->has('message'))
            <div class="alert alert-success fw-bold d-flex align-items-center gap-2 mb-4 shadow-sm border-0 rounded-4">
                <span class="material-symbols-outlined">check_circle</span>
                {{ session('message') }}
            </div>
        @endif

        <div class="row g-4">
            <!-- Columna Izquierda: Datos Generales -->
            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-bottom p-4">
                        <h5 class="fw-bold text-naval mb-0 d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined text-coral">store</span>
                            Datos de la Tienda
                        </h5>
                    </div>
                    <div class="card-body p-4 bg-light bg-opacity-50">
                        <!-- Logo Upload Section with initials -->
                        <div class="mb-4 text-center">
                            <label class="form-label text-muted fw-bold text-uppercase small d-block mb-3">Logo del Negocio</label>
                            
                            <div class="d-inline-block position-relative">
                                <!-- Círculo de las Iniciales / Logo actual -->
                                <div class="d-flex align-items-center justify-content-center rounded-circle border text-naval fw-bold bg-white shadow-sm overflow-hidden" style="width: 100px; height: 100px; font-size: 28px;">
                                    @if($logo)
                                        <img src="{{ $logo->temporaryUrl() }}" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">
                                    @elseif($currentLogoPath)
                                        <img src="{{ asset('storage/' . $currentLogoPath) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                        {{ substr($name, 0, 2) }}
                                    @endif
                                </div>
                                
                                <!-- Botón/Input para cargar logo -->
                                <label for="logo-upload" class="position-absolute bottom-0 end-0 bg-coral text-white p-2 rounded-circle shadow-sm border border-white cursor-pointer d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; cursor: pointer;">
                                    <span class="material-symbols-outlined fs-5">photo_camera</span>
                                    <input type="file" id="logo-upload" wire:model="logo" accept="image/*" class="d-none">
                                </label>
                            </div>
                            
                            @error('logo') <div class="text-danger small mt-2 fw-bold">{{ $message }}</div> @enderror
                            <div wire:loading wire:target="logo" class="text-muted small mt-2 fw-medium">Subiendo imagen...</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted fw-bold text-uppercase small">Nombre del Negocio</label>
                            <input type="text" wire:model="name" class="form-control form-control-lg bg-white border-0 shadow-sm rounded-3">
                            @error('name') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted fw-bold text-uppercase small">Descripción / Eslogan</label>
                            <textarea wire:model="description" class="form-control bg-white border-0 shadow-sm rounded-3" rows="4"></textarea>
                            @error('description') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Configuración IA -->
            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-bottom p-4">
                        <h5 class="fw-bold text-naval mb-0 d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined text-coral">smart_toy</span>
                            Módulo de Inteligencia Artificial (IA)
                        </h5>
                    </div>
                    <div class="card-body p-4 bg-light bg-opacity-50 d-flex flex-column justify-content-center">
                        @if($hasAiEnabled)
                            <!-- AI Enabled Premium Active Status -->
                            <div class="text-center py-4">
                                <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 70px; height: 70px;">
                                    <span class="material-symbols-outlined fs-1">verified_user</span>
                                </div>
                                <h5 class="fw-bold text-naval mb-2">¡Soporte de IA Activo!</h5>
                                <p class="text-muted small mb-4 px-3">Tu tienda cuenta con un plan de Inteligencia Artificial premium activo, configurado de manera centralizada por el soporte global de la plataforma.</p>
                                
                                <div class="row g-2 justify-content-center text-start">
                                    <div class="col-sm-6">
                                        <div class="bg-white p-3 rounded-3 shadow-sm border border-opacity-10">
                                            <span class="text-muted small fw-bold text-uppercase d-block mb-1">Proveedor de IA</span>
                                            <span class="fw-black text-naval text-capitalize">{{ $ai_provider }}</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="bg-white p-3 rounded-3 shadow-sm border border-opacity-10">
                                            <span class="text-muted small fw-bold text-uppercase d-block mb-1">Modelo Configurado</span>
                                            <span class="fw-black text-naval text-uppercase">{{ $ai_model ?: 'Por Defecto' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- AI Inactive Call to Action Banner -->
                            <div class="text-center py-4 px-3">
                                <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 70px; height: 70px;">
                                    <span class="material-symbols-outlined fs-1">smart_toy</span>
                                </div>
                                <h5 class="fw-bold text-danger mb-2">Sin Soporte de IA</h5>
                                <h4 class="fw-black text-naval mt-3 mb-3" style="line-height: 1.4;">
                                    Si aún no tienes apoyo de la IA, cámbiate de plan o comunícate con soporte.
                                </h4>
                                <p class="text-muted small mb-4">Potencia tus reportes, descripciones de catálogo y arqueos con algoritmos de Inteligencia Artificial avanzada. Sube de nivel tu tienda hoy mismo.</p>
                                
                                <a href="mailto:soporte@aselpos.com?subject=Upgrade%20Plan%20IA" class="btn btn-outline-coral fw-bold rounded-pill px-4 py-2 shadow-sm border-2">
                                    Contactar a Soporte
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-12 d-flex justify-content-end mt-4">
                <button wire:click="saveSettings" class="btn btn-coral btn-lg fw-bold px-5 rounded-pill shadow-sm d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined">save</span>
                    Guardar Cambios
                </button>
            </div>
        </div>
    </main>
</div>
