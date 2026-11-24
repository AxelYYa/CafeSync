/**
 * Relojes que avanzan a partir de la hora del servidor (data-servidor, en ms), no de la del dispositivo.
 * Solo es una referencia visual: las marcas y estados siempre usan la hora del servidor al guardarse.
 */
export function iniciarRelojes() {
    const relojes = [...document.querySelectorAll('[data-reloj][data-servidor]')];

    if (relojes.length === 0) {
        return;
    }

    const desfase = Number(relojes[0].dataset.servidor) - Date.now();

    const pintar = () => {
        const ahora = new Date(Date.now() + desfase);

        relojes.forEach((reloj) => {
            reloj.textContent = ahora.toLocaleTimeString('es-MX', {
                hour: '2-digit',
                minute: '2-digit',
                second: reloj.hasAttribute('data-segundos') ? '2-digit' : undefined,
                hour12: false,
            });
        });
    };

    pintar();
    setInterval(pintar, 1000);
}

/**
 * Milisegundos de diferencia entre el servidor y el dispositivo, para cálculos de tiempo transcurrido.
 */
export function desfaseServidor() {
    const reloj = document.querySelector('[data-reloj][data-servidor]');

    return reloj ? Number(reloj.dataset.servidor) - Date.now() : 0;
}
