/**
 * Prueba de falla de red del KDS (README CP-14 / RNF-08 / RF-15).
 *
 * Con la pantalla KDS real abierta en Microsoft Edge, corta la red del navegador (protocolo DevTools), crea una
 * orden en el POS mientras la pantalla está desconectada y restablece la red. Verifica que:
 *   1. la pantalla avise «Sin conexión» y muestre el aviso de incidencia;
 *   2. la orden no aparezca mientras no hay red;
 *   3. al reconectar, el indicador vuelva a «Conectado» sin recargar la página;
 *   4. la orden aparezca exactamente una vez (sin duplicados) y el contador de órdenes no aumente de más.
 * Al terminar cancela la orden de prueba.
 *
 * Uso (con la aplicación corriendo y datos de demostración cargados):
 *   node tests/Rendimiento/probar-reconexion-kds.mjs --url=http://127.0.0.1:8000 --desconexion=6000
 */
import { esperar, opciones, prepararEscenario } from './escenario-kds.mjs';

const DESCONEXION_MS = Number(opciones.desconexion ?? 6000);

const red = (kds, enLinea) => kds.enviar('Network.emulateNetworkConditions', {
    offline: !enLinea,
    latency: 0,
    downloadThroughput: -1,
    uploadThroughput: -1,
});

async function principal() {
    const { pos, kds, idProducto, idEstacion } = await prepararEscenario();
    const resultados = [];
    let idOrden = null;
    const comprobar = (descripcion, cumple, detalle = '') => {
        resultados.push(cumple);
        console.log(`${cumple ? 'PASA ' : 'FALLA'} · ${descripcion}${detalle ? ` (${detalle})` : ''}`);
    };

    try {
        console.log(`Entorno: ${kds.version} · Node ${process.version} · estación ${idEstacion} · desconexión de ${DESCONEXION_MS} ms\n`);
        await kds.enviar('Network.enable');
        await kds.evaluar(`window.__recargas = 0; window.addEventListener('beforeunload', () => window.__recargas++);`);
        const totalAntes = Number(await kds.evaluar(`document.querySelector('[data-kds-total]').textContent`));

        const inicioCorte = Date.now();
        await red(kds, false);
        const avisado = await kds.esperarQue(`document.querySelector('[data-kds-conexion-texto]').textContent === 'Sin conexión'
            && !document.querySelector('[data-kds-aviso-red]').classList.contains('hidden')`, 5000);
        comprobar('La pantalla avisa «Sin conexión» y muestra el aviso de incidencia', avisado !== null, avisado ? `${avisado - inicioCorte} ms después del corte` : 'sin aviso');

        ({ idOrden } = await pos.crearOrden(idProducto, 'Prueba de reconexión del KDS'));
        await esperar(DESCONEXION_MS);
        const visibleSinRed = await kds.evaluar(`window.__apariciones[${JSON.stringify(idOrden)}] !== undefined`);
        comprobar('La orden creada durante el corte no aparece mientras no hay red', !visibleSinRed);

        const inicioReconexion = Date.now();
        await red(kds, true);
        const reconectado = await kds.esperarQue(`document.querySelector('[data-kds-conexion-texto]').textContent === 'Conectado'
            && document.querySelector('[data-kds-aviso-red]').classList.contains('hidden')`, 15000);
        comprobar('Al volver la red el indicador regresa a «Conectado» sin recargar', reconectado !== null && (await kds.evaluar('window.__recargas')) === 0,
            reconectado ? `${reconectado - inicioReconexion} ms después de reconectar` : 'no se recuperó');

        const recibida = await kds.esperarQue(`window.__apariciones[${JSON.stringify(idOrden)}] !== undefined`, 15000);
        comprobar('La orden pendiente llega a la pantalla al reconectar', recibida !== null, recibida ? `${recibida - inicioReconexion} ms después de reconectar` : 'no llegó');

        await esperar(3000);
        const tarjetas = await kds.evaluar(`document.querySelectorAll('[data-orden="${idOrden}"]').length`);
        const totalDespues = Number(await kds.evaluar(`document.querySelector('[data-kds-total]').textContent`));
        comprobar('La orden aparece exactamente una vez', tarjetas === 1, `${tarjetas} tarjeta(s)`);
        comprobar('El contador de órdenes aumenta solo en 1', totalDespues === totalAntes + 1, `${totalAntes} → ${totalDespues}`);

        const fallas = resultados.filter((cumple) => !cumple).length;
        console.log(`\nResultado: ${resultados.length - fallas} de ${resultados.length} verificaciones pasan.`);
        process.exitCode = fallas === 0 ? 0 : 1;
    } finally {
        await red(kds, true).catch(() => {});

        if (idOrden) {
            await pos.cancelarOrdenes([idOrden], 'Orden de prueba de reconexión');
            console.log('Orden de prueba cancelada.');
        }

        await kds.cerrar();
    }
}

principal().catch((error) => {
    console.error(`Error: ${error.message}`);
    process.exitCode = 2;
});
