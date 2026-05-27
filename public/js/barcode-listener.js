// Escáner de Código de Barras Físico - Emulación de Teclado
// Este script detecta ráfagas rápidas de pulsaciones de teclado (propias de un escáner)
// y despacha un evento personalizado de ventana: 'barcode-scanned'.

(function() {
    let barcodeBuffer = '';
    let lastKeyTime = Date.now();
    const SCAN_TIMEOUT = 50; // milisegundos entre teclas para considerarlo escaneo

    window.addEventListener('keydown', function(e) {
        const currentTime = Date.now();
        
        // Si el tiempo transcurrido desde la última tecla es mayor al timeout, reseteamos el buffer
        if (currentTime - lastKeyTime > SCAN_TIMEOUT) {
            barcodeBuffer = '';
        }
        
        lastKeyTime = currentTime;

        // Si es Enter y tenemos un buffer con contenido, procesamos el escaneo
        if (e.key === 'Enter') {
            if (barcodeBuffer.length >= 3) {
                e.preventDefault();
                e.stopPropagation();
                
                const scannedBarcode = barcodeBuffer.trim();
                barcodeBuffer = '';
                
                // Despachar el evento a nivel global
                const event = new CustomEvent('barcode-scanned', {
                    detail: { barcode: scannedBarcode }
                });
                window.dispatchEvent(event);
                
                console.log('Código de barras escaneado (físico):', scannedBarcode);
            }
            return;
        }

        // Ignorar teclas de control o especiales
        if (e.key.length > 1) {
            return;
        }

        // Agregar carácter al buffer
        barcodeBuffer += e.key;
    }, true); // Usar captación para interceptar antes de otros listeners
})();
