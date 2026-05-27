<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'ASEL POS' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">
    
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    
    <!-- Lottie Player for Animations -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>
    @auth
        @if(session('tenant_suspended'))
            {{-- Overlay semitransparente que bloquea la interacción con los módulos --}}
            <div id="suspended-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(14, 38, 73, 0.08); z-index: 1050; pointer-events: none;"></div>
            
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Toast persistente amarillo de advertencia
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top',
                        showConfirmButton: false,
                        timer: false,
                        timerProgressBar: false,
                        didOpen: (toast) => {
                            toast.style.zIndex = '9999';
                            toast.style.marginTop = '16px';
                        }
                    });

                    Toast.fire({
                        icon: 'warning',
                        title: 'Servicio suspendido',
                        text: 'Tu cuenta se encuentra suspendida. Contacta al administrador de la plataforma para reactivar tu servicio.',
                        background: '#fff3cd',
                        color: '#856404',
                        iconColor: '#e2a500',
                        showCloseButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    });

                    // Bloquear clicks en todos los links del sidebar y botones de acción
                    document.querySelectorAll('.sidebar-wrapper .nav-link, .btn-coral, a[href*="/admin/"], a[href*="/vendedor/"], a[href*="/pos"]').forEach(el => {
                        // No bloquear el dashboard ni logout
                        const href = el.getAttribute('href') || '';
                        const isDashboard = href.match(/\/(admin|vendedor)\/?$/) || href === '/';
                        const isLogout = el.closest('form[action*="logout"]');
                        const isSettings = href.includes('/settings');
                        
                        if (!isDashboard && !isLogout && !isSettings) {
                            el.style.opacity = '0.4';
                            el.style.pointerEvents = 'none';
                            el.style.cursor = 'not-allowed';
                            el.style.filter = 'grayscale(100%)';
                            el.setAttribute('tabindex', '-1');
                        }
                    });

                    // También desactivar el overlay para el sidebar y el header del dashboard
                    const overlay = document.getElementById('suspended-overlay');
                    if (overlay) overlay.remove();
                });
            </script>
        @endif
    @endauth

    {{ $slot }}
    
    <script src="{{ asset('js/barcode-listener.js') }}"></script>
    @livewireScripts
</body>
</html>
