/**
 * Menú lateral desplegable en pantallas pequeñas.
 */
export function iniciarNavegacion() {
    const menu = document.querySelector('[data-navegacion]');
    const fondo = document.querySelector('[data-cerrar-navegacion]');

    if (!menu || !fondo) {
        return;
    }

    const alternar = (abrir) => {
        menu.classList.toggle('hidden', !abrir);
        menu.classList.toggle('flex', abrir);
        fondo.classList.toggle('hidden', !abrir);
    };

    document.querySelectorAll('[data-abrir-navegacion]').forEach((boton) => boton.addEventListener('click', () => alternar(true)));
    fondo.addEventListener('click', () => alternar(false));
    document.addEventListener('keydown', (evento) => evento.key === 'Escape' && alternar(false));
}
