/**
 * Medición del tiempo de actualización del KDS (README CP-07 / RNF-01: objetivo < 1 s).
 *
 * Abre la pantalla KDS real en Microsoft Edge (modo headless, protocolo DevTools) con la sesión de un empleado,
 * crea órdenes desde el POS con la sesión de otro empleado y mide el tiempo entre la respuesta del servidor que
 * confirma la orden guardada (t0) y el momento en que la tarjeta aparece en el DOM de la pantalla (t1).
 * Al terminar cancela las órdenes creadas. No requiere dependencias: usa fetch y WebSocket nativos de Node ≥ 22.
 *
 * Uso (con la aplicación corriendo y datos de demostración cargados):
 *   node tests/Rendimiento/medir-kds.mjs --url=http://127.0.0.1:8000 --ordenes=20
 *
 * Opciones: --url, --ordenes, --producto (nombre, por omisión Latte), --estacion (id; por omisión la del producto),
 *           --kds (usuario de la pantalla, barista), --pos (usuario que captura, cajero), --password,
 *           --edge (ruta del ejecutable), --puerto (DevTools, 9333)
 */
import { esperar, opciones, prepararEscenario, URL_BASE } from './escenario-kds.mjs';

const TOTAL = Number(opciones.ordenes ?? 20);
const LIMITE_MS = 1000;

const percentil = (valores, p) => valores[Math.min(valores.length - 1, Math.ceil((p / 100) * valores.length) - 1)];

async function principal() {
    const { pos, kds, idProducto, idEstacion, intervalo } = await prepararEscenario();
    const creadas = [];

    try {
        console.log(`Entorno: ${kds.version} · Node ${process.version} · ${URL_BASE} · intervalo de consulta ${intervalo} ms · estación ${idEstacion}`);

        const tiempos = [];

        for (let numero = 1; numero <= TOTAL; numero++) {
            const { idOrden, folio, t0 } = await pos.crearOrden(idProducto, 'Medición de rendimiento del KDS');
            creadas.push(idOrden);

            const t1 = await kds.esperarQue(`window.__apariciones[${JSON.stringify(idOrden)}] !== undefined`)
                && (await kds.evaluar(`window.__apariciones[${JSON.stringify(idOrden)}]`));
            const latencia = t1 ? t1 - t0 : null;
            tiempos.push(latencia);
            console.log(`Orden ${String(numero).padStart(2)} · ${folio} · ${latencia === null ? 'NO APARECIÓ' : `${latencia} ms`}`);

            await esperar(300 + Math.random() * 1200);
        }

        const validos = tiempos.filter((t) => t !== null).sort((a, b) => a - b);
        const promedio = Math.round(validos.reduce((suma, t) => suma + t, 0) / validos.length);

        console.log('\nResultado');
        console.log(`  Órdenes: ${TOTAL} · aparecieron: ${validos.length} · bajo ${LIMITE_MS} ms: ${validos.filter((t) => t < LIMITE_MS).length}`);
        console.log(`  Mínimo ${validos[0]} ms · mediana ${percentil(validos, 50)} ms · promedio ${promedio} ms · p95 ${percentil(validos, 95)} ms · máximo ${validos.at(-1)} ms`);
        process.exitCode = validos.length === TOTAL && percentil(validos, 95) < LIMITE_MS ? 0 : 1;
    } finally {
        await pos.cancelarOrdenes(creadas, 'Orden de medición de rendimiento');
        console.log(`  Órdenes de prueba canceladas: ${creadas.length}`);
        await kds.cerrar();
    }
}

principal().catch((error) => {
    console.error(`Error: ${error.message}`);
    process.exitCode = 2;
});
