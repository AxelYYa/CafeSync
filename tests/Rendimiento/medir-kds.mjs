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
import { spawn } from 'node:child_process';
import { mkdtempSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';

const opciones = Object.fromEntries(process.argv.slice(2).map((argumento) => argumento.replace(/^--/, '').split('=')));
const URL_BASE = (opciones.url ?? 'http://127.0.0.1:8000').replace(/\/$/, '');
const TOTAL = Number(opciones.ordenes ?? 20);
const PRODUCTO = opciones.producto ?? 'Latte';
const USUARIO_KDS = opciones.kds ?? 'barista';
const USUARIO_POS = opciones.pos ?? 'cajero';
const PASSWORD = opciones.password ?? process.env.SEED_DEMO_PASSWORD ?? 'CafeSync#2026';
const EDGE = opciones.edge ?? 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';
const PUERTO = Number(opciones.puerto ?? 9333);
const LIMITE_MS = 1000;
const ESPERA_MAXIMA_MS = 10000;

const esperar = (ms) => new Promise((resolver) => setTimeout(resolver, ms));

/** Cliente HTTP mínimo con cookies, para la sesión del POS. */
class SesionHttp {
    cookies = new Map();

    async pedir(ruta, { metodo = 'GET', formulario = null } = {}) {
        const respuesta = await fetch(URL_BASE + ruta, {
            method: metodo,
            redirect: 'manual',
            headers: {
                Cookie: [...this.cookies].map(([nombre, valor]) => `${nombre}=${valor}`).join('; '),
                ...(formulario ? { 'Content-Type': 'application/x-www-form-urlencoded' } : {}),
            },
            body: formulario ? new URLSearchParams(formulario) : undefined,
        });

        for (const cookie of respuesta.headers.getSetCookie()) {
            const [par] = cookie.split(';');
            const separador = par.indexOf('=');
            this.cookies.set(par.slice(0, separador), par.slice(separador + 1));
        }

        return respuesta;
    }

    async tokenDe(ruta) {
        const html = await (await this.pedir(ruta)).text();
        const token = html.match(/name="_token" value="([^"]+)"/)?.[1];

        if (!token) {
            throw new Error(`No se encontró el token CSRF en ${ruta}`);
        }

        return { html, token };
    }

    async iniciarSesion(usuario) {
        const { token } = await this.tokenDe('/login');
        const respuesta = await this.pedir('/login', { metodo: 'POST', formulario: { _token: token, login: usuario, password: PASSWORD } });

        if (respuesta.status !== 302 || !respuesta.headers.get('location')?.includes('/panel')) {
            throw new Error(`No se pudo iniciar sesión como ${usuario} (HTTP ${respuesta.status}).`);
        }
    }
}

/** Cliente mínimo del protocolo DevTools de Edge. */
class Navegador {
    #socket;
    #siguienteId = 1;
    #pendientes = new Map();
    #oyentes = new Map();

    static async abrir() {
        const perfil = mkdtempSync(join(tmpdir(), 'cafesync-kds-'));
        const proceso = spawn(EDGE, ['--headless=new', `--remote-debugging-port=${PUERTO}`, `--user-data-dir=${perfil}`, '--no-first-run', '--window-size=1920,1080', 'about:blank'], { stdio: 'ignore' });

        let destino;
        for (let intento = 0; intento < 50 && !destino; intento++) {
            await esperar(200);
            destino = await fetch(`http://127.0.0.1:${PUERTO}/json/list`).then((r) => r.json()).then((lista) => lista.find((p) => p.type === 'page')).catch(() => undefined);
        }

        if (!destino) {
            proceso.kill();
            throw new Error('Edge no abrió el puerto de depuración.');
        }

        const version = await fetch(`http://127.0.0.1:${PUERTO}/json/version`).then((r) => r.json());
        const navegador = new Navegador();
        navegador.proceso = proceso;
        navegador.perfil = perfil;
        navegador.version = version.Browser;
        await navegador.#conectar(destino.webSocketDebuggerUrl);
        await navegador.enviar('Page.enable');
        await navegador.enviar('Runtime.enable');

        return navegador;
    }

    #conectar(url) {
        this.#socket = new WebSocket(url);
        this.#socket.addEventListener('message', ({ data }) => {
            const mensaje = JSON.parse(data);

            if (mensaje.id && this.#pendientes.has(mensaje.id)) {
                const { resolver, rechazar } = this.#pendientes.get(mensaje.id);
                this.#pendientes.delete(mensaje.id);
                mensaje.error ? rechazar(new Error(mensaje.error.message)) : resolver(mensaje.result);
            } else if (mensaje.method) {
                (this.#oyentes.get(mensaje.method) ?? []).forEach((oyente) => oyente(mensaje.params));
                this.#oyentes.delete(mensaje.method);
            }
        });

        return new Promise((resolver) => this.#socket.addEventListener('open', resolver, { once: true }));
    }

    enviar(metodo, parametros = {}) {
        const id = this.#siguienteId++;
        this.#socket.send(JSON.stringify({ id, method: metodo, params: parametros }));

        return new Promise((resolver, rechazar) => this.#pendientes.set(id, { resolver, rechazar }));
    }

    unaVez(evento) {
        return new Promise((resolver) => this.#oyentes.set(evento, [...(this.#oyentes.get(evento) ?? []), resolver]));
    }

    async ir(ruta) {
        const cargada = this.unaVez('Page.loadEventFired');
        await this.enviar('Page.navigate', { url: URL_BASE + ruta });
        await cargada;
    }

    async evaluar(expresion) {
        const { result, exceptionDetails } = await this.enviar('Runtime.evaluate', { expression: expresion, returnByValue: true, awaitPromise: true });

        if (exceptionDetails) {
            throw new Error(exceptionDetails.exception?.description ?? exceptionDetails.text);
        }

        return result.value;
    }

    async cerrar() {
        await this.enviar('Browser.close').catch(() => {});
        this.proceso.kill();
        await esperar(500);
        rmSync(this.perfil, { recursive: true, force: true });
    }
}

const percentil = (valores, p) => valores[Math.min(valores.length - 1, Math.ceil((p / 100) * valores.length) - 1)];

async function principal() {
    const pos = new SesionHttp();
    await pos.iniciarSesion(USUARIO_POS);
    const { html: htmlPos } = await pos.tokenDe('/pos');
    const producto = htmlPos.match(new RegExp(`data-producto="(\\d+)"\\s+data-nombre="${PRODUCTO}"`))?.[1];

    if (!producto) {
        throw new Error(`El producto «${PRODUCTO}» no está disponible en el POS.`);
    }

    const kds = await Navegador.abrir();
    const creadas = [];

    try {
        await kds.ir('/login');
        await kds.evaluar(`(() => {
            document.querySelector('[name=login]').value = ${JSON.stringify(USUARIO_KDS)};
            document.querySelector('[name=password]').value = ${JSON.stringify(PASSWORD)};
            document.querySelector('form').submit();
        })()`);
        await kds.unaVez('Page.loadEventFired');

        const idEstacion = opciones.estacion ?? (await buscarEstacionDelProducto(pos, producto));

        if (!idEstacion) {
            throw new Error(`No se encontró la estación de «${PRODUCTO}»; indícala con --estacion=<id>.`);
        }

        await kds.ir(`/kds/${idEstacion}`);
        const intervalo = await kds.evaluar(`document.querySelector('[data-kds]').dataset.intervalo`);

        await kds.evaluar(`(() => {
            window.__apariciones = {};
            const marcar = () => document.querySelectorAll('[data-orden]').forEach((tarjeta) => {
                window.__apariciones[tarjeta.dataset.orden] ??= Date.now();
            });
            marcar();
            new MutationObserver(marcar).observe(document.querySelector('[data-kds-contenido]'), { childList: true, subtree: true });
        })()`);

        console.log(`Entorno: ${kds.version} · Node ${process.version} · ${URL_BASE} · intervalo de consulta ${intervalo} ms · estación ${idEstacion}`);

        const tiempos = [];

        for (let numero = 1; numero <= TOTAL; numero++) {
            const { token } = await pos.tokenDe('/pos');
            const respuesta = await pos.pedir('/pos/ordenes', {
                metodo: 'POST',
                formulario: { _token: token, canal_origen: 'mostrador', 'items[0][id_producto]': producto, 'items[0][cantidad]': '1', nota: 'Medición de rendimiento del KDS' },
            });
            const t0 = Date.now();

            const confirmacion = await (await pos.pedir('/pos')).text();
            const folio = confirmacion.match(/Orden (CS-\d{8}-(\d+)) enviada a preparación/);

            if (respuesta.status !== 302 || !folio) {
                throw new Error(`La orden ${numero} no se guardó (HTTP ${respuesta.status}).`);
            }

            const idOrden = String(Number(folio[2]));
            creadas.push(idOrden);

            let t1;
            while (!t1 && Date.now() - t0 < ESPERA_MAXIMA_MS) {
                await esperar(20);
                t1 = await kds.evaluar(`window.__apariciones[${JSON.stringify(idOrden)}] ?? null`);
            }

            const latencia = t1 ? t1 - t0 : null;
            tiempos.push(latencia);
            console.log(`Orden ${String(numero).padStart(2)} · ${folio[1]} · ${latencia === null ? 'NO APARECIÓ' : `${latencia} ms`}`);

            await esperar(300 + Math.random() * 1200);
        }

        const validos = tiempos.filter((t) => t !== null).sort((a, b) => a - b);
        const promedio = Math.round(validos.reduce((suma, t) => suma + t, 0) / validos.length);

        console.log('\nResultado');
        console.log(`  Órdenes: ${TOTAL} · aparecieron: ${validos.length} · bajo ${LIMITE_MS} ms: ${validos.filter((t) => t < LIMITE_MS).length}`);
        console.log(`  Mínimo ${validos[0]} ms · mediana ${percentil(validos, 50)} ms · promedio ${promedio} ms · p95 ${percentil(validos, 95)} ms · máximo ${validos.at(-1)} ms`);
        process.exitCode = validos.length === TOTAL && percentil(validos, 95) < LIMITE_MS ? 0 : 1;
    } finally {
        for (const idOrden of creadas) {
            const { token } = await pos.tokenDe('/pos/ordenes');
            await pos.pedir(`/pos/ordenes/${idOrden}/cancelar`, { metodo: 'POST', formulario: { _token: token, motivo: 'Orden de medición de rendimiento' } });
        }

        console.log(`  Órdenes de prueba canceladas: ${creadas.length}`);
        await kds.cerrar();
    }
}

/** Estación a la que el catálogo envía el producto medido (la tarjeta del POS está dentro de la sección de su estación). */
async function buscarEstacionDelProducto(pos, idProducto) {
    const html = await (await pos.pedir('/kds')).text();
    const estaciones = [...html.matchAll(/href="[^"]*\/kds\/(\d+)"[\s\S]*?<h2[^>]*>([^<]+)<\/h2>/g)].map(([, id, nombre]) => ({ id, nombre: nombre.trim() }));
    const htmlPos = await (await pos.pedir('/pos')).text();
    const posicionProducto = htmlPos.indexOf(`data-producto="${idProducto}"`);
    const encabezados = estaciones
        .map((estacion) => ({ ...estacion, posicion: htmlPos.lastIndexOf(estacion.nombre, posicionProducto) }))
        .filter((estacion) => estacion.posicion !== -1)
        .sort((a, b) => b.posicion - a.posicion);

    return encabezados[0]?.id;
}

principal().catch((error) => {
    console.error(`Error: ${error.message}`);
    process.exitCode = 2;
});
