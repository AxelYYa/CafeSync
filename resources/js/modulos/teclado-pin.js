/**
 * Teclado numérico en pantalla para capturar el PIN con pantalla táctil.
 */
export function iniciarTecladoPin(formulario) {
    const campo = formulario.querySelector('input[name="pin"]');
    const longitudMaxima = Number(campo.getAttribute('maxlength') ?? 6);

    formulario.addEventListener('click', (evento) => {
        const boton = evento.target.closest('button[type="button"]');

        if (!boton) {
            return;
        }

        if (boton.dataset.digito !== undefined && campo.value.length < longitudMaxima) {
            campo.value += boton.dataset.digito;
        } else if (boton.hasAttribute('data-borrar')) {
            campo.value = campo.value.slice(0, -1);
        } else if (boton.hasAttribute('data-limpiar')) {
            campo.value = '';
        }

        campo.focus();
    });
}
