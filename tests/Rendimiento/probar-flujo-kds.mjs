/**
 * Prueba del flujo de preparación en la pantalla KDS real (README RF-05, RF-06, CP-08 y hito de los Sprints 3-4).
 *
 * En Microsoft Edge, con la pantalla de la estación abierta, pulsa los botones como lo haría el personal y verifica que:
 *   1. la orden creada en el POS aparece con el botón «Iniciar»;
 *   2. «Iniciar» la pasa a «En preparación» y ofrece «Marcar lista»;
 *   3. «Marcar lista» la deja lista y el cambio persiste al recargar la página;
 *   4. el servidor marca la orden como «Lista para entrega» (la ve el POS);
 *   5. «Receta» abre el Help Mode del producto correcto sobre la pantalla, y al cerrarlo se sigue en la misma pantalla.
 * Al terminar cancela la orden de prueba.
 *
 * Uso: node tests/Rendimiento/probar-flujo-kds.mjs --url=http://127.0.0.1:8000
 */
import { opciones, prepararEscenario } from './escenario-kds.mjs';

const PRODUCTO = opciones.producto ?? 'Latte';

async function principal() {
    const { pos, kds, idProducto, idEstacion } = await prepararEscenario();
    const resultados = [];
    let idOrden = null;
    const comprobar = (descripcion, cumple, detalle = '') => {
        resultados.push(cumple);
        console.log(`${cumple ? 'PASA ' : 'FALLA'} · ${descripcion}${detalle ? ` (${detalle})` : ''}`);
    };

    try {
        console.log(`Entorno: ${kds.version} · Node ${process.version} · estación ${idEstacion} · producto ${PRODUCTO}\n`);
        ({ idOrden } = await pos.crearOrden(idProducto, 'Prueba del flujo de preparación'));
        const tarjeta = `document.querySelector('[data-orden="${idOrden}"]')`;
        const boton = (texto) => `[...(${tarjeta}?.querySelectorAll('form[data-avanzar] button') ?? [])].find((b) => b.textContent.trim() === '${texto}')`;

        comprobar('La orden aparece en su estación con el botón «Iniciar»', (await kds.esperarQue(`!!(${boton('Iniciar')})`)) !== null);

        await kds.evaluar(`${boton('Iniciar')}.click()`);
        const enPreparacion = await kds.esperarQue(`${tarjeta}?.textContent.includes('En preparación') && !!(${boton('Marcar lista')})`);
        comprobar('«Iniciar» la pasa a «En preparación» y ofrece «Marcar lista»', enPreparacion !== null);

        await kds.evaluar(`${boton('Marcar lista')}.click()`);
        const lista = await kds.esperarQue(`${tarjeta}?.textContent.includes('Todo listo en esta estación') && !(${tarjeta}?.querySelector('form[data-avanzar]'))`);
        comprobar('«Marcar lista» deja el producto listo en la pantalla', lista !== null);

        await kds.ir(`/kds/${idEstacion}`);
        comprobar('El estado «lista» persiste al recargar la pantalla', await kds.evaluar(`${tarjeta}?.textContent.includes('Todo listo en esta estación') ?? false`));

        const detalle = await (await pos.pedir(`/pos/ordenes/${idOrden}`)).text();
        comprobar('El POS ve la orden como «Lista para entrega»', detalle.includes('Lista para entrega'));

        const rutaAntes = await kds.evaluar('location.pathname');
        await kds.evaluar(`${tarjeta}.querySelector('a[data-ayuda]').click()`);
        const abierta = await kds.esperarQue(`document.querySelector('[data-ayuda-dialogo]').open
            && document.querySelector('[data-ayuda-contenido]').textContent.includes(${JSON.stringify(PRODUCTO)})`);
        const ingredientes = await kds.evaluar(`document.querySelectorAll('[data-ayuda-contenido] li').length`);
        comprobar('«Receta» abre el Help Mode del producto sobre la pantalla', abierta !== null && ingredientes > 0, `${ingredientes} ingrediente(s) y paso(s)`);

        await kds.evaluar(`document.querySelector('[data-ayuda-dialogo] form[method=dialog] button').click()`);
        const cerrada = await kds.esperarQue(`!document.querySelector('[data-ayuda-dialogo]').open`);
        const rutaDespues = await kds.evaluar('location.pathname');
        comprobar('Al cerrar el Help Mode se sigue en la misma pantalla con la orden visible',
            cerrada !== null && rutaAntes === rutaDespues && (await kds.evaluar(`!!${tarjeta}`)), rutaDespues);

        const fallas = resultados.filter((cumple) => !cumple).length;
        console.log(`\nResultado: ${resultados.length - fallas} de ${resultados.length} verificaciones pasan.`);
        process.exitCode = fallas === 0 ? 0 : 1;
    } finally {
        if (idOrden) {
            await pos.cancelarOrdenes([idOrden], 'Orden de prueba del flujo de preparación');
            console.log('Orden de prueba cancelada.');
        }

        await kds.cerrar();
    }
}

principal().catch((error) => {
    console.error(`Error: ${error.message}`);
    process.exitCode = 2;
});
