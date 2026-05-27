<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escáner Inalámbrico - ASEL POS</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- HTML5-QRCODE Library -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <style>
        body {
            background-color: #F8F9FA;
            color: #2D3A56;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }
        .scanner-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
            max-width: 480px;
            margin: 0 auto;
            width: 100%;
        }
        .scanner-card {
            background-color: #ffffff;
            border-radius: 28px;
            box-shadow: 0 10px 30px rgba(45, 58, 86, 0.08);
            width: 100%;
            padding: 28px 24px;
            text-align: center;
            border: 1px solid rgba(45, 58, 86, 0.05);
        }
        #reader {
            width: 100%;
            border-radius: 18px;
            overflow: hidden;
            border: none !important;
            background-color: #f1f3f7;
            position: relative;
        }
        #reader video {
            border-radius: 18px;
            object-fit: cover !important;
        }
        .brand-header {
            margin-bottom: 20px;
        }
        .brand-logo {
            width: 52px;
            height: 52px;
            background-color: #ff6b6b;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            box-shadow: 0 4px 15px rgba(255,107,107,0.3);
        }
        .pairing-badge {
            background-color: rgba(255, 107, 107, 0.1);
            color: #ff6b6b;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 8px 18px;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            margin-bottom: 24px;
        }
        .success-toast {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background-color: #4caf50;
            color: white;
            padding: 14px 24px;
            border-radius: 50px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 5px 20px rgba(76,175,80,0.3);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            z-index: 9999;
            opacity: 0;
        }
        .success-toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
        .scan-focus-overlay {
            position: absolute;
            top: 10%;
            left: 10%;
            right: 10%;
            bottom: 10%;
            border: 2px dashed #ff6b6b;
            border-radius: 12px;
            pointer-events: none;
            box-shadow: 0 0 0 9999px rgba(255, 255, 255, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }
        .scan-laser {
            position: absolute;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: #ff6b6b;
            box-shadow: 0 0 8px #ff6b6b;
            animation: scanning 2s linear infinite;
        }
        @keyframes scanning {
            0% { top: 0%; }
            50% { top: 100%; }
            100% { top: 0%; }
        }
        .btn-coral-mobile {
            background-color: #ff6b6b;
            color: white;
            border: none;
            border-radius: 50px;
            padding: 14px 28px;
            font-weight: 700;
            font-size: 15px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(255,107,107,0.4);
            transition: all 0.2s ease;
            width: 100%;
            justify-content: center;
        }
        .btn-coral-mobile:active {
            transform: scale(0.98);
            background-color: #fa5252;
        }
        .btn-camera-switch {
            background-color: #f1f3f7;
            color: #2D3A56;
            border: 1px solid rgba(45, 58, 86, 0.1);
            border-radius: 50px;
            padding: 8px 18px;
            font-weight: 600;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            margin-top: 15px;
        }
        .btn-camera-switch:active {
            background-color: #e9ecef;
        }
        .instruction-step {
            display: flex;
            gap: 12px;
            text-align: left;
            margin-bottom: 12px;
            font-size: 13px;
        }
        .step-num {
            width: 22px;
            height: 22px;
            background-color: rgba(45, 58, 86, 0.1);
            color: #2D3A56;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 11px;
            flex-shrink: 0;
            margin-top: 2px;
        }
    </style>
</head>
<body>

    <div class="scanner-container">
        <div class="brand-header text-center">
            <div class="brand-logo">
                <span class="material-symbols-outlined text-white fs-3">qr_code_scanner</span>
            </div>
            <h4 class="fw-bold mb-0 text-naval">ASEL Scanner Link</h4>
            <p class="text-secondary mb-0" style="font-size: 13px; font-weight: 500;">Escáner de código de barras para tu negocio</p>
        </div>

        <div class="scanner-card">
            <div class="pairing-badge">
                <span class="material-symbols-outlined fs-6">sync</span>
                VINCULADO: {{ substr($token, 0, 6) }}
            </div>

            <!-- Pantalla Inicial / Onboarding (Solicitar Permisos) -->
            <div id="onboarding-screen">
                <div class="text-center py-3 mb-4">
                    <span class="material-symbols-outlined text-coral" style="font-size: 56px; margin-bottom: 16px;">photo_camera</span>
                    <h5 class="fw-bold text-naval">Activar Cámara del Teléfono</h5>
                    <p class="text-muted small">Necesitamos acceder a tu cámara para poder leer los códigos de barra de tus productos.</p>
                </div>

                <!-- Lista de Instrucciones -->
                <div class="mb-4 bg-light p-3 rounded-4">
                    <div class="instruction-step">
                        <div class="step-num">1</div>
                        <div>Presiona el botón **"Iniciar Escáner"** de abajo.</div>
                    </div>
                    <div class="instruction-step">
                        <div class="step-num">2</div>
                        <div>Cuando aparezca el aviso del navegador, selecciona **"Permitir"** o **"Autorizar"**.</div>
                    </div>
                    <div class="instruction-step">
                        <div class="step-num">3</div>
                        <div>Apunta con tu cámara a cualquier código de barras.</div>
                    </div>
                </div>

                <button id="btn-request-camera" class="btn btn-coral-mobile">
                    <span class="material-symbols-outlined">center_focus_strong</span>
                    Iniciar Escáner
                </button>
            </div>

            <!-- Pantalla del Escáner (Lector activo) -->
            <div id="scanner-screen" style="display: none;">
                <div class="position-relative">
                    <div id="reader"></div>
                    <div class="scan-focus-overlay" id="overlay" style="display:none;">
                        <div class="scan-laser"></div>
                    </div>
                </div>

                <div id="camera-controls" class="d-none">
                    <button id="switch-camera-btn" class="btn btn-camera-switch">
                        <span class="material-symbols-outlined">flip_camera_ios</span>
                        Cambiar Cámara
                    </button>
                </div>

                <div class="mt-4">
                    <div class="d-flex align-items-center justify-content-center gap-2 text-success small mb-2" id="status-indicator">
                        <span class="dot bg-success rounded-circle" style="width: 8px; height: 8px; display: inline-block;"></span>
                        Lector listo y transmitiendo...
                    </div>
                    <p class="text-muted small mb-3" style="font-size: 11px;">Enfoca el código de barras dentro del recuadro para transmitirlo automáticamente a la computadora.</p>
                    
                    <!-- Banner de Tips de Escaneo -->
                    <div class="bg-light p-3 rounded-4 text-start border shadow-2xs mt-3">
                        <div class="d-flex gap-2 align-items-start">
                            <span class="material-symbols-outlined text-coral fs-5 flex-shrink-0" style="color: #ff6b6b;">lightbulb</span>
                            <div style="font-size: 11px; line-height: 1.4;">
                                <strong class="d-block text-naval mb-1">Consejos para escaneo rápido:</strong>
                                <ul class="ps-3 mb-0" style="color: #6C757D;">
                                    <li>Centra el código **de forma horizontal** dentro del rectángulo punteado.</li>
                                    <li>Aléjalo a unos **15 - 20 cm** del celular. Si está muy cerca se verá borroso y no enfocará.</li>
                                    <li>Asegúrate de contar con **buena luz** y evitar reflejos de focos en empaques brillantes.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mensaje de Error de Contexto Inseguro (HTTP en celular) -->
            <div id="secure-context-error-screen" style="display: none;">
                <div class="alert alert-warning border-0 rounded-4 text-start p-3 mb-3" style="background-color: #fff3cd; color: #664d03;">
                    <div class="d-flex gap-2">
                        <span class="material-symbols-outlined fs-3 flex-shrink-0" style="color: #664d03;">lock</span>
                        <div>
                            <strong class="d-block mb-1" style="color: #664d03;">Requiere Conexión Segura (HTTPS)</strong>
                            <p class="mb-0 small" style="font-size: 11px;">Los navegadores móviles (especialmente **Safari en iOS**) bloquean de forma estricta el acceso a la cámara en páginas web que no utilicen protocolo seguro HTTPS.</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-light p-3 rounded-4 text-start small mb-4">
                    <h6 class="fw-bold text-naval mb-2" style="font-size: 12px;">¿Cómo solucionarlo en 2 minutos?</h6>
                    <p class="text-muted mb-2" style="font-size: 11px;">Usa una herramienta gratuita como **ngrok** para crear una URL HTTPS segura que reenvíe las peticiones a tu servidor local:</p>
                    <code class="d-block bg-white p-2 rounded border font-monospace text-center mb-3" style="font-size: 12px; color: #ff6b6b;">ngrok http 8080</code>
                    <p class="text-muted mb-0" style="font-size: 11px;">Entra al POS en tu computadora usando esa URL HTTPS de ngrok (ej: <code>https://xxxx.ngrok-free.app/pos</code>), vuelve a abrir el modal de vinculación y escanea el nuevo código QR. ¡Safari abrirá la cámara al instante!</p>
                </div>
            </div>

            <!-- Mensaje de Error de Permisos (Si el usuario rechaza la cámara) -->
            <div id="permission-error-screen" style="display: none;">
                <div class="alert alert-danger border-0 rounded-4 text-start p-3 mb-3">
                    <div class="d-flex gap-2">
                        <span class="material-symbols-outlined text-danger fs-3 flex-shrink-0">dangerous</span>
                        <div>
                            <strong class="d-block mb-1">Permiso de Cámara Denegado</strong>
                            <p class="mb-0 small">El navegador no tiene permisos para abrir la cámara. Para solucionarlo:</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-light p-3 rounded-4 text-start small mb-4">
                    <h6 class="fw-bold text-naval mb-2" style="font-size: 12px;">Cómo activar en Safari (iOS):</h6>
                    <p class="text-muted mb-3" style="font-size: 11px;">Toca el icono de las letras <code>aA</code> o el candado al lado de la barra de direcciones web y selecciona <strong>"Configuración del sitio web"</strong> -> Activar Cámara en <strong>"Permitir"</strong>.</p>
                    
                    <h6 class="fw-bold text-naval mb-2" style="font-size: 12px;">Cómo activar en Chrome (Android):</h6>
                    <p class="text-muted mb-0" style="font-size: 11px;">Toca los tres puntos de la esquina superior derecha -> <strong>Configuración</strong> -> <strong>Configuración del sitio</strong> -> <strong>Cámara</strong> -> y permite el acceso para esta dirección IP.</p>
                </div>

                <button onclick="window.location.reload();" class="btn btn-coral-mobile">
                    <span class="material-symbols-outlined">refresh</span>
                    Reintentar Conexión
                </button>
            </div>

        </div>
    </div>

    <!-- Toast de Escaneo Exitoso -->
    <div class="success-toast" id="toast">
        <span class="material-symbols-outlined">check_circle</span>
        <span>Código enviado: <strong id="scanned-code"></strong></span>
    </div>

    <!-- Sonido beep base64 (Sine wave de 800Hz por 0.15s) para máxima compatibilidad -->
    <audio id="beep-sound" src="data:audio/wav;base64,UklGRl9vT19XQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQ9vT18AZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZm"></audio>

    <!-- Bootstrap & Custom Script -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        function playBeep() {
            try {
                const oscillator = audioCtx.createOscillator();
                const gainNode = audioCtx.createGain();
                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(850, audioCtx.currentTime); // 850Hz
                gainNode.gain.setValueAtTime(0.1, audioCtx.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.15);
                oscillator.connect(gainNode);
                gainNode.connect(audioCtx.destination);
                oscillator.start();
                oscillator.stop(audioCtx.currentTime + 0.15);
            } catch (e) {
                document.getElementById('beep-sound').play();
            }
        }

        const pairingToken = "{{ $token }}";
        const toast = document.getElementById('toast');
        const scannedCodeSpan = document.getElementById('scanned-code');
        const overlay = document.getElementById('overlay');
        
        const onboardingScreen = document.getElementById('onboarding-screen');
        const scannerScreen = document.getElementById('scanner-screen');
        const secureContextErrorScreen = document.getElementById('secure-context-error-screen');
        const permissionErrorScreen = document.getElementById('permission-error-screen');
        
        let html5QrCode = null;
        let currentCameraId = null;
        let cameras = [];
        let isProcessing = false;

        // VERIFICAR CONTEXTO SEGURO (HTTPS / LOCALHOST)
        // iOS y Safari PROHÍBEN la cámara en HTTP si el Host es una IP local (ej. 192.168.x.x)
        const isSecure = window.isSecureContext;
        const isLocalHost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';

        if (!isSecure && !isLocalHost) {
            onboardingScreen.style.display = 'none';
            secureContextErrorScreen.style.display = 'block';
        }

        function showSuccessToast(code) {
            scannedCodeSpan.textContent = code;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 2000);
        }

        function sendBarcodeToServer(barcode) {
            if (isProcessing) return;
            isProcessing = true;
            
            playBeep();
            showSuccessToast(barcode);

            fetch('/api/scanner/scan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    pairing_token: pairingToken,
                    barcode: barcode
                })
            })
            .then(res => res.json())
            .then(data => {
                console.log('Envío exitoso:', data);
                setTimeout(() => {
                    isProcessing = false;
                }, 1200);
            })
            .catch(err => {
                console.error('Error al enviar el código:', err);
                isProcessing = false;
            });
        }

        document.getElementById('btn-request-camera').addEventListener('click', () => {
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }

            Html5Qrcode.getCameras().then(devices => {
                if (devices && devices.length > 0) {
                    cameras = devices;
                    
                    let backCamera = devices.find(device => device.label.toLowerCase().includes('back') || device.label.toLowerCase().includes('trasera') || device.label.toLowerCase().includes('environment'));
                    let selectedDevice = backCamera || devices[devices.length - 1];
                    currentCameraId = selectedDevice.id;

                    if (devices.length > 1) {
                        document.getElementById('camera-controls').classList.remove('d-none');
                    }

                    onboardingScreen.style.display = 'none';
                    scannerScreen.style.display = 'block';

                    startScanner(currentCameraId);
                } else {
                    alert('No se detectaron cámaras en este dispositivo.');
                }
            }).catch(err => {
                console.error('Error al obtener cámaras:', err);
                onboardingScreen.style.display = 'none';
                scannerScreen.style.display = 'none';
                permissionErrorScreen.style.display = 'block';
            });
        });

        function startScanner(cameraId) {
            if (html5QrCode) {
                html5QrCode.stop().then(() => {
                    initializeAndStart(cameraId);
                });
            } else {
                initializeAndStart(cameraId);
            }
        }

        function initializeAndStart(cameraId) {
            // Configurar formatos explícitos para optimizar el escáner de códigos de barra estándar (EAN/UPC)
            let scannerOptions = {};
            if (typeof Html5QrcodeSupportedFormats !== 'undefined') {
                scannerOptions.formatsToSupport = [
                    Html5QrcodeSupportedFormats.QR_CODE,
                    Html5QrcodeSupportedFormats.EAN_13,
                    Html5QrcodeSupportedFormats.EAN_8,
                    Html5QrcodeSupportedFormats.UPC_A,
                    Html5QrcodeSupportedFormats.UPC_E,
                    Html5QrcodeSupportedFormats.CODE_128,
                    Html5QrcodeSupportedFormats.CODE_39,
                    Html5QrcodeSupportedFormats.ITF
                ];
            }

            html5QrCode = new Html5Qrcode("reader", scannerOptions);
            
            const config = {
                fps: 30, // Mayor FPS para capturar códigos de barras en movimiento rápido
                qrbox: function(width, height) {
                    // Rectángulo más ancho para códigos de barra de productos
                    return { width: Math.min(width * 0.9, 320), height: 140 };
                },
                aspectRatio: 1.0,
                experimentalFeatures: {
                    // Activar decodificador nativo por hardware de Chrome/Safari (BarcodeDetector API)
                    // Hace que la lectura de códigos de barra sea 10 veces más rápida y precisa
                    useBarCodeDetectorIfSupported: true
                }
            };

            html5QrCode.start(
                cameraId, 
                config,
                (decodedText, decodedResult) => {
                    sendBarcodeToServer(decodedText);
                },
                (errorMessage) => {
                    // Fallos silenciosos de escaneo periódico
                }
            ).then(() => {
                overlay.style.display = 'block';
            }).catch(err => {
                console.error('Error al encender la cámara:', err);
                onboardingScreen.style.display = 'none';
                scannerScreen.style.display = 'none';
                permissionErrorScreen.style.display = 'block';
            });
        }

        document.getElementById('switch-camera-btn').addEventListener('click', () => {
            if (cameras.length > 1) {
                let currentIndex = cameras.findIndex(cam => cam.id === currentCameraId);
                let nextIndex = (currentIndex + 1) % cameras.length;
                currentCameraId = cameras[nextIndex].id;
                startScanner(currentCameraId);
            }
        });
    </script>
</body>
</html>
