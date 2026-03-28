// ============================================================
// barcode-scanner.js
// Detecta lectores de código de barras USB en modo HID teclado
// (ej: Advance ADV-6013N)
//
// Los scanners envían los caracteres del código muy rápido
// (< 30 ms entre teclas) y terminan con Enter.
// Este script distingue esa entrada del tipeo humano normal
// y dispara el evento personalizado 'barcodescan'.
// ============================================================

(function () {
    const SCAN_SPEED  = 50;  // ms máximo entre teclas del scanner
    const MIN_LENGTH  = 3;   // longitud mínima para considerar un escaneo

    let chars = [];   // buffer: [{k: char, t: timestamp}, ...]

    document.addEventListener('keydown', function (e) {

        // ---- Tecla Enter: evaluar si es fin de escaneo ----
        if (e.key === 'Enter') {
            if (chars.length >= MIN_LENGTH) {
                // Verificar que TODOS los intervalos son de scanner
                let esScan = true;
                for (let i = 1; i < chars.length; i++) {
                    if (chars[i].t - chars[i - 1].t > SCAN_SPEED) {
                        esScan = false;
                        break;
                    }
                }

                if (esScan) {
                    const code = chars.map(c => c.k).join('');
                    chars = [];
                    e.preventDefault();

                    // Limpiar caracteres que pudieron haberse colado en un input
                    const el = document.activeElement;
                    if (el && el.tagName === 'INPUT' && el.type === 'text') {
                        const len = code.length;
                        if (el.value.slice(-len).toLowerCase() === code.toLowerCase()) {
                            el.value = el.value.slice(0, -len);
                            el.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    }

                    // Disparar evento global
                    document.dispatchEvent(new CustomEvent('barcodescan', {
                        detail:  { code },
                        bubbles: true,
                    }));
                    return;
                }
            }
            chars = [];
            return;
        }

        // ---- Carácter imprimible ----
        if (e.key.length === 1) {
            const now = Date.now();

            // Si el último carácter fue hace mucho → reiniciar buffer
            if (chars.length > 0 && now - chars[chars.length - 1].t > SCAN_SPEED * 3) {
                chars = [];
            }

            chars.push({ k: e.key, t: now });
            return;
        }

        // Teclas no imprimibles (excepto modificadores) reinician el buffer
        if (!['Shift', 'CapsLock', 'Control', 'Alt', 'Meta', 'AltGraph'].includes(e.key)) {
            chars = [];
        }
    });
})();
