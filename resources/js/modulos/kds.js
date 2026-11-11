import { desfaseServidor } from './reloj';

const MINUTOS_ALERTA = 10;
const ESPERA_MAXIMA_MS = 10000;

/**
 * Pantalla KDS por estación.
 * - Consulta periódica (polling): pide /kds/{estacion}/datos con la huella de versión actual; el servidor
 *   solo devuelve HTML cuando algo cambió. La siguiente consulta se agenda al terminar la anterior.
 * - Si la red falla, avisa en pantalla y reintenta con espera creciente; al reconectar, la huella evita duplicados.
 * - Los cambios de estado se envían por fetch y se fuerza una consulta inmediata.
 * - Help Mode abre la receta en un diálogo sobre la orden, sin salir de la pantalla.
 */
export function iniciarKds(raiz) {
    const url = raiz.dataset.urlDatos;
    const intervalo = Number(raiz.dataset.intervalo) || 1000;
    const contenido = raiz.querySelector('[data-kds-contenido]');
    const dialogo = document.querySelector('[data-ayuda-dialogo]');
    const token = document.querySelector('meta[name="csrf-token"]').content;

    let version = raiz.dataset.version;
    let fallos = 0;
    let temporizador = null;
    let consultando = false;

    const consultar = async () => {
        clearTimeout(temporizador);

        if (consultando) {
            return;
        }

        consultando = true;

        try {
            const respuesta = await fetch(`${url}?version=${encodeURIComponent(version)}`, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
                cache: 'no-store',
            });

            if (respuesta.status === 401 || respuesta.status === 419) {
                window.location.reload();

                return;
            }

            if (!respuesta.ok) {
                throw new Error(`HTTP ${respuesta.status}`);
            }

            const datos = await respuesta.json();
            fallos = 0;
            conexion(true);

            if (datos.cambio) {
                version = datos.version;
                contenido.innerHTML = datos.html;
                raiz.querySelector('[data-kds-total]').textContent = datos.total;
                actualizarTiempos();
            }
        } catch {
            fallos += 1;
            conexion(false);
        } finally {
            consultando = false;
            const espera = fallos === 0 ? intervalo : Math.min(intervalo * 2 ** fallos, ESPERA_MAXIMA_MS);
            temporizador = setTimeout(consultar, espera);
        }
    };

    const conexion = (enLinea) => {
        raiz.querySelector('[data-kds-aviso-red]').classList.toggle('hidden', enLinea);
        const indicador = raiz.querySelector('[data-kds-conexion]');
        indicador.classList.toggle('bg-emerald-500/15', enLinea);
        indicador.classList.toggle('text-emerald-300', enLinea);
        indicador.classList.toggle('bg-red-500/20', !enLinea);
        indicador.classList.toggle('text-red-200', !enLinea);
        raiz.querySelector('[data-kds-punto]').classList.toggle('bg-emerald-400', enLinea);
        raiz.querySelector('[data-kds-punto]').classList.toggle('bg-red-400', !enLinea);
        raiz.querySelector('[data-kds-conexion-texto]').textContent = enLinea ? 'Conectado' : 'Sin conexión';
    };

    const mostrarError = (mensaje) => {
        const aviso = raiz.querySelector('[data-kds-error]');
        aviso.textContent = mensaje;
        aviso.classList.toggle('hidden', !mensaje);

        if (mensaje) {
            setTimeout(() => mostrarError(''), 5000);
        }
    };

    const actualizarTiempos = () => {
        const ahora = Date.now() + desfaseServidor();

        contenido.querySelectorAll('[data-creada]').forEach((tarjeta) => {
            const minutos = Math.max(0, Math.floor((ahora - Number(tarjeta.dataset.creada) * 1000) / 60000));
            const excedido = minutos >= MINUTOS_ALERTA;
            const etiqueta = tarjeta.querySelector('[data-transcurrido]');
            etiqueta.textContent = excedido ? `${minutos} min · tiempo excedido` : `${minutos} min`;
            etiqueta.classList.toggle('text-red-400', excedido);
            tarjeta.classList.toggle('border-red-500', excedido);
        });
    };

    contenido.addEventListener('submit', async (evento) => {
        const formulario = evento.target.closest('form[data-avanzar]');

        if (!formulario) {
            return;
        }

        evento.preventDefault();
        const boton = formulario.querySelector('button');
        boton.disabled = true;

        try {
            const respuesta = await fetch(formulario.action, {
                method: 'POST',
                body: new FormData(formulario),
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token },
                credentials: 'same-origin',
            });

            if (!respuesta.ok) {
                const datos = await respuesta.json().catch(() => ({}));
                mostrarError(datos.message ?? 'No se pudo actualizar el estado. Inténtalo de nuevo.');
            }
        } catch {
            mostrarError('No se pudo actualizar la pantalla; revisa la conexión.');
            conexion(false);
        } finally {
            boton.disabled = false;
            consultar();
        }
    });

    contenido.addEventListener('click', async (evento) => {
        const enlace = evento.target.closest('a[data-ayuda]');

        if (!enlace || !dialogo) {
            return;
        }

        evento.preventDefault();

        try {
            const respuesta = await fetch(enlace.href, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
                credentials: 'same-origin',
            });

            if (!respuesta.ok) {
                throw new Error(`HTTP ${respuesta.status}`);
            }

            dialogo.querySelector('[data-ayuda-contenido]').innerHTML = await respuesta.text();
            dialogo.showModal();
        } catch {
            mostrarError('No se pudo abrir la receta. Revisa la conexión.');
        }
    });

    actualizarTiempos();
    setInterval(actualizarTiempos, 15000);
    temporizador = setTimeout(consultar, intervalo);
}
