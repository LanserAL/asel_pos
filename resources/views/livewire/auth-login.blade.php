<div class="container-fluid vh-100 p-0" 
    x-data="{ 
        showLoader: false,
        handleLoginSuccess(e) {
            this.showLoader = true;
            const targetUrl = e.detail.url;
            
            // Optimización: Precargar la vista del destino en segundo plano
            fetch(targetUrl, {credentials: 'same-origin', headers: {'X-Requested-With': 'XMLHttpRequest'}})
                .catch(() => {});
                
            setTimeout(() => {
                window.location.href = targetUrl;
            }, 2000);
        }
    }" 
    @login-success.window="handleLoginSuccess">
    <div class="row g-0 h-100">
        <!-- Left Column (Branding & Lottie) -->
        <div class="col-lg-5 d-none d-lg-flex flex-column justify-content-between p-5 position-relative overflow-hidden"
            style="background-image: linear-gradient(rgba(14, 38, 73, 0.85), rgba(14, 38, 73, 0.85)), url('{{ asset('assets/images/login-bg.jpg') }}'); background-size: cover; background-position: center;">
            <!-- Branding -->
            <div class="d-flex align-items-center gap-3 z-3">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" width="40" height="40" class="rounded-3 shadow-sm">
                <span class="text-white fw-bold fs-4 tracking-tight">ASEL POS</span>
            </div>

            <!-- Lottie & Copy -->
            <div class="text-center z-3 mt-4">
                <h1 class="display-5 fw-bold text-white mt-4">El futuro de tu<br>Punto de Venta</h1>
                <p class="text-white-50 mt-3 fs-5">Gestiona tu negocio de forma inteligente, rápida y escalable.</p>
            </div>

            <!-- Footer left -->
            <div class="text-white-50 small z-3">
                &copy; {{ date('Y') }} ASEL POS. Todos los derechos reservados.
            </div>

            <!-- Decorative Blobs (Using native css properties or inline styles since Tailwind is gone) -->
            <div class="position-absolute bg-coral rounded-circle opacity-25"
                style="width: 60%; height: 60%; top: -20%; left: -20%; filter: blur(100px);"></div>
            <div class="position-absolute bg-primary rounded-circle opacity-25"
                style="width: 50%; height: 50%; bottom: -10%; right: -10%; filter: blur(80px);"></div>
        </div>

        <!-- Right Column (Form) -->
        <div class="col-lg-7 d-flex align-items-center justify-content-center bg-light position-relative p-4 p-lg-5">
            <div class="w-100 bg-white p-4 p-lg-5 rounded-4 shadow-sm border border-white"
                style="max-width: 500px; z-index: 2;">
                <!-- Mobile Logo -->
                <div class="d-lg-none d-flex justify-content-center mb-4">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" width="80" height="80"
                        class="rounded-3 shadow-sm">
                </div>

                <div class="mb-4 text-center text-lg-start">
                    <p class="text-coral fw-bold text-uppercase small tracking-widest mb-1">Punto de Venta</p>
                    <h2 class="display-6 fw-bold text-naval mb-2">Iniciar Sesión</h2>
                    <p class="text-secondary">Ingresa tus credenciales para acceder a tu panel.</p>
                </div>

                <form wire:submit="login">
                    <div class="mb-4">
                        <label for="email" class="form-label text-muted fw-bold text-uppercase small">Correo
                            Electrónico</label>
                        <input wire:model="email" id="email" type="email" required autofocus
                            class="form-control form-control-lg bg-light border-0 @error('email') is-invalid @enderror"
                            placeholder="Ej. admin@tienda.com">
                        @error('email') <div class="invalid-feedback fw-bold">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="password"
                                class="form-label text-muted fw-bold text-uppercase small mb-0">Contraseña</label>
                            <a href="#" class="text-decoration-none small fw-bold">¿Olvidaste tu contraseña?</a>
                        </div>
                        <input wire:model="password" id="password" type="password" required
                            class="form-control form-control-lg bg-light border-0 @error('password') is-invalid @enderror"
                            placeholder="••••••••">
                        @error('password') <div class="invalid-feedback fw-bold">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-check mb-4">
                        <input wire:model="remember" class="form-check-input" type="checkbox" id="remember">
                        <label class="form-check-label text-muted fw-bold small" for="remember">
                            Mantener sesión iniciada
                        </label>
                    </div>

                    <button type="submit"
                        class="btn btn-coral btn-lg w-100 fw-bold shadow-sm d-flex justify-content-center align-items-center gap-2">
                        Iniciar Sesión
                    </button>
                </form>

                <div class="mt-5 pt-4 border-top text-center">
                    <p class="text-muted fw-bold text-uppercase small mb-3">Cuentas de prueba disponibles</p>
                    <div class="d-flex flex-column gap-2 font-monospace small">
                        <div class="d-flex justify-content-between bg-light p-2 rounded border">
                            <span class="text-naval fw-bold">superadmin@omnipos.com</span>
                            <span class="text-muted">pass: password</span>
                        </div>
                        <div class="d-flex justify-content-between bg-light p-2 rounded border">
                            <span class="text-naval fw-bold">admin@tienda.com</span>
                            <span class="text-muted">pass: password</span>
                        </div>
                        <div class="d-flex justify-content-between bg-light p-2 rounded border">
                            <span class="text-naval fw-bold">vendedor@tienda.com</span>
                            <span class="text-muted">pass: password</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Fullscreen Loader Overlay (aparece al iniciar sesión exitosamente) --}}
    <div x-show="showLoader" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999;">
        <div class="w-100 vh-100 d-flex flex-column align-items-center justify-content-center"
             style="background: rgba(14, 38, 73, 0.92); backdrop-filter: blur(12px);">
            
            {{-- Ícono de Store animado --}}
            <div class="login-loader-icon mb-4">
                <div class="d-flex align-items-center justify-content-center rounded-4 shadow-lg" 
                     style="width: 100px; height: 100px; background: linear-gradient(135deg, #ff6b6b 0%, #ff8e53 100%);">
                    <span class="material-symbols-outlined text-white" style="font-size: 52px; font-variation-settings: 'FILL' 1;">store</span>
                </div>
            </div>

            {{-- Texto --}}
            <h5 class="text-white fw-bold mb-3" style="letter-spacing: 0.5px;">Iniciando sesión</h5>
            
            {{-- Dots loader --}}
            <div class="login-dots-loader d-flex align-items-center gap-2">
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
        </div>
    </div>

    <style>
        /* Pulsing icon animation */
        .login-loader-icon {
            animation: pulseScale 1.8s ease-in-out infinite;
        }
        @keyframes pulseScale {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.08); opacity: 0.85; }
        }

        /* Bouncing dots loader */
        .login-dots-loader .dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #ff6b6b;
            animation: dotBounce 1.4s ease-in-out infinite both;
        }
        .login-dots-loader .dot:nth-child(1) { animation-delay: 0s; }
        .login-dots-loader .dot:nth-child(2) { animation-delay: 0.16s; }
        .login-dots-loader .dot:nth-child(3) { animation-delay: 0.32s; }

        @keyframes dotBounce {
            0%, 80%, 100% { 
                transform: scale(0.6); 
                opacity: 0.4; 
            }
            40% { 
                transform: scale(1); 
                opacity: 1; 
            }
        }
    </style>
</div>