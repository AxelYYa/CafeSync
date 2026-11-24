/**
 * - data-confirmar (en el formulario o en el botón): pide confirmación antes de una operación importante.
 * - data-confirmar-si-desactiva: pide confirmación solo si se desmarcó la casilla «activo» o «activa».
 * - data-enviar-una-vez: evita el doble envío por doble toque. El servidor sigue siendo idempotente;
 *   esto solo mejora la experiencia en pantallas táctiles.
 */
export function iniciarFormularios() {
    document.addEventListener('submit', (evento) => {
        const formulario = evento.target;

        if (!(formulario instanceof HTMLFormElement) || formulario.hasAttribute('data-avanzar')) {
            return;
        }

        const desactiva = formulario.dataset.confirmarSiDesactiva && !formulario.querySelector('input[type="checkbox"][name="activo"], input[type="checkbox"][name="activa"]')?.checked;
        const mensaje = evento.submitter?.dataset.confirmar ?? formulario.dataset.confirmar ?? (desactiva ? formulario.dataset.confirmarSiDesactiva : undefined);

        if (mensaje && !window.confirm(mensaje)) {
            evento.preventDefault();

            return;
        }

        if (!formulario.hasAttribute('data-enviar-una-vez')) {
            return;
        }

        if (formulario.dataset.enviando === 'si') {
            evento.preventDefault();

            return;
        }

        formulario.dataset.enviando = 'si';

        // Se deshabilita después del envío para no perder el name/value del botón pulsado.
        setTimeout(() => {
            formulario.querySelectorAll('button[type="submit"], button:not([type])').forEach((boton) => {
                boton.disabled = true;
                boton.setAttribute('aria-busy', 'true');
            });
        }, 0);
    });

    // Al volver con el botón «atrás», el navegador puede restaurar el formulario bloqueado.
    window.addEventListener('pageshow', () => {
        document.querySelectorAll('form[data-enviando]').forEach((formulario) => {
            delete formulario.dataset.enviando;
            formulario.querySelectorAll('button[aria-busy]').forEach((boton) => {
                boton.disabled = false;
                boton.removeAttribute('aria-busy');
            });
        });
    });
}
