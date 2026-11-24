/**
 * Utilidades compartidas por las pruebas del KDS en navegador real (tests/Rendimiento/*.mjs).
 *
 * - SesionHttp: cliente HTTP con cookies para operar el POS como un empleado.
 * - Navegador: Microsoft Edge en modo headless controlado por el protocolo DevTools (WebSocket nativo de Node ≥ 22).
 * - prepararEscenario(): inicia sesión en ambos lados, abre la pantalla KDS de la estación del producto y registra
 *   en `window.__apariciones` la hora en que aparece cada tarjeta de orden.
 */
import { spawn } from 'node:child_process';
import { mkdtempSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';

export const opciones = Object.fromEntries(process.argv.slice(2).map((argumento) => argumento.replace(/^--/, '').split('=')));
export const URL_BASE = (opciones.url ?? 'http://127.0.0.1:8000').replace(/\/$/, '');
const PRODUCTO = opciones.producto ?? 'Latte';
const USUARIO_KDS = opciones.kds ?? 'barista';
const USUARIO_POS = opciones.pos ?? 'cajero';
const PASSWORD = opciones.password ?? process.env.SEED_DEMO_PASSWORD ?? 'CafeSync#2026';
const EDGE = opciones.edge ?? 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';
const PUERTO = Number(opciones.puerto ?? 9333);

export const esperar = (ms) => new Promise((resolver) => setTimeout(resolver, ms));

/** Cliente HTTP mínimo con cookies, para la sesión del POS. */
export class SesionHttp {
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

    /**
     * Crea una orden de una unidad por el POS. t0 es el instante en que el servidor confirmó la orden guardada.
     *
     * @returns {Promise<{idOrden: string, folio: string, t0: number}>}
     */
    async crearOrden(idProducto, nota) {
        const { token } = await this.tokenDe('/pos');
        const respuesta = await this.pedir('/pos/ordenes', {
            metodo: 'POST',
            formulario: { _token: token, canal_origen: 'mostrador', 'items[0][id_producto]': idProducto, 'items[0][cantidad]': '1', nota },
        });
        const t0 = Date.now();
        const folio = (await (await this.pedir('/pos')).text()).match(/Orden (CS-\d{8}-(\d+)) enviada a preparación/);

        if (respuesta.status !== 302 || !folio) {
            throw new Error(`La orden no se guardó (HTTP ${respuesta.status}).`);
        }

        return { idOrden: String(Number(folio[2])), folio: folio[1], t0 };
    }

    async cancelarOrdenes(ids, motivo) {
        for (const idOrden of ids) {
            const { token } = await this.tokenDe('/pos/ordenes');
            await this.pedir(`/pos/ordenes/${idOrden}/cancelar`, { metodo: 'POST', formulario: { _token: token, motivo } });
        }
    }
}

/** Cliente mínimo del protocolo DevTools de Edge. */
export class Navegador {
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

    /** Espera hasta que la expresión sea verdadera en la página; devuelve el instante o null si vence el plazo. */
    async esperarQue(expresion, plazoMs = 10000) {
        const inicio = Date.now();

        while (Date.now() - inicio < plazoMs) {
            if (await this.evaluar(expresion)) {
                return Date.now();
            }

            await esperar(20);
        }

        return null;
    }

    /**
     * Cierra Edge y borra su perfil temporal. En Windows los procesos hijos de Edge pueden retener archivos
     * unos instantes: se espera la salida del proceso y se reintenta el borrado. Si aun así falla, solo se avisa,
     * porque el resultado de la prueba ya se calculó.
     */
    async cerrar() {
        const terminado = new Promise((resolver) => this.proceso.once('exit', resolver));
        await this.enviar('Browser.close').catch(() => {});
        await Promise.race([terminado, esperar(3000)]);
        this.proceso.kill();

        try {
            rmSync(this.perfil, { recursive: true, force: true, maxRetries: 10, retryDelay: 300 });
        } catch (error) {
            console.warn(`Aviso: no se pudo borrar el perfil temporal de Edge (${this.perfil}): ${error.code ?? error.message}`);
        }
    }
}

/**
 * Deja listo el escenario: POS con sesión de cajero y pantalla KDS de la estación del producto con sesión de barista.
 *
 * @returns {Promise<{pos: SesionHttp, kds: Navegador, idProducto: string, idEstacion: string, intervalo: string}>}
 */
export async function prepararEscenario() {
    const pos = new SesionHttp();
    await pos.iniciarSesion(USUARIO_POS);
    const htmlPos = (await pos.tokenDe('/pos')).html;
    const idProducto = htmlPos.match(new RegExp(`data-producto="(\\d+)"\\s+data-nombre="${PRODUCTO}"`))?.[1];

    if (!idProducto) {
        throw new Error(`El producto «${PRODUCTO}» no está disponible en el POS.`);
    }

    const idEstacion = opciones.estacion ?? (await estacionDelProducto(pos, htmlPos, idProducto));

    if (!idEstacion) {
        throw new Error(`No se encontró la estación de «${PRODUCTO}»; indícala con --estacion=<id>.`);
    }

    const kds = await Navegador.abrir();
    await kds.ir('/login');
    const sesionIniciada = kds.unaVez('Page.loadEventFired');
    await kds.evaluar(`(() => {
        document.querySelector('[name=login]').value = ${JSON.stringify(USUARIO_KDS)};
        document.querySelector('[name=password]').value = ${JSON.stringify(PASSWORD)};
        document.querySelector('form').submit();
    })()`);
    await sesionIniciada;
    await kds.ir(`/kds/${idEstacion}`);

    await kds.evaluar(`(() => {
        window.__apariciones = {};
        const marcar = () => document.querySelectorAll('[data-orden]').forEach((tarjeta) => {
            window.__apariciones[tarjeta.dataset.orden] ??= Date.now();
        });
        marcar();
        new MutationObserver(marcar).observe(document.querySelector('[data-kds-contenido]'), { childList: true, subtree: true });
    })()`);

    const intervalo = await kds.evaluar(`document.querySelector('[data-kds]').dataset.intervalo`);

    return { pos, kds, idProducto, idEstacion, intervalo };
}

/** Estación a la que el catálogo envía el producto (su tarjeta del POS está dentro de la sección de esa estación). */
async function estacionDelProducto(pos, htmlPos, idProducto) {
    const htmlKds = await (await pos.pedir('/kds')).text();
    const estaciones = [...htmlKds.matchAll(/href="[^"]*\/kds\/(\d+)"[\s\S]*?<h2[^>]*>([^<]+)<\/h2>/g)].map(([, id, nombre]) => ({ id, nombre: nombre.trim() }));
    const posicionProducto = htmlPos.indexOf(`data-producto="${idProducto}"`);

    return estaciones
        .map((estacion) => ({ ...estacion, posicion: htmlPos.lastIndexOf(estacion.nombre, posicionProducto) }))
        .filter((estacion) => estacion.posicion !== -1)
        .sort((a, b) => b.posicion - a.posicion)[0]?.id;
}
