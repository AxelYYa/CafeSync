const MAXIMO_POR_LINEA = 99;

const moneda = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });

/**
 * Carrito del POS. Solo arma la lista de productos y cantidades que se envía al servidor;
 * precio, estación, receta y existencias se validan y calculan de nuevo en el backend.
 */
export function iniciarPos(raiz) {
    const formulario = raiz.querySelector('[data-formulario-orden]');
    const lista = formulario.querySelector('[data-lineas]');
    const vacia = formulario.querySelector('[data-orden-vacia]');
    const plantilla = document.getElementById('plantilla-linea');
    const ocultos = document.createElement('div');
    formulario.append(ocultos);

    /** @type {Map<string, {id: string, nombre: string, centavos: number, maximo: number, cantidad: number}>} */
    const lineas = new Map();
    const productos = new Map([...raiz.querySelectorAll('[data-producto]')].map((boton) => [boton.dataset.producto, boton]));

    const limite = (linea) => Math.min(MAXIMO_POR_LINEA, linea.maximo);

    const cambiar = (id, delta) => {
        const boton = productos.get(id);

        if (!boton || boton.disabled) {
            return;
        }

        const linea = lineas.get(id) ?? {
            id,
            nombre: boton.dataset.nombre,
            centavos: Math.round(Number(boton.dataset.precio) * 100),
            maximo: Number(boton.dataset.maximo),
            cantidad: 0,
        };

        linea.cantidad = Math.max(0, Math.min(limite(linea), linea.cantidad + delta));
        linea.cantidad === 0 ? lineas.delete(id) : lineas.set(id, linea);
        pintar();
    };

    const pintar = () => {
        lista.replaceChildren();
        ocultos.replaceChildren();

        let unidades = 0;
        let centavos = 0;

        [...lineas.values()].forEach((linea, indice) => {
            const elemento = plantilla.content.firstElementChild.cloneNode(true);
            elemento.querySelector('[data-nombre]').textContent = linea.nombre;
            elemento.querySelector('[data-cantidad]').textContent = linea.cantidad;
            elemento.querySelector('[data-subtotal]').textContent =
                `${moneda.format(linea.centavos / 100)} × ${linea.cantidad} = ${moneda.format((linea.centavos * linea.cantidad) / 100)}`;
            elemento.querySelector('[data-restar]').addEventListener('click', () => cambiar(linea.id, -1));
            const sumar = elemento.querySelector('[data-sumar]');
            sumar.disabled = linea.cantidad >= limite(linea);
            sumar.addEventListener('click', () => cambiar(linea.id, 1));
            lista.append(elemento);

            ocultos.append(oculto(`items[${indice}][id_producto]`, linea.id), oculto(`items[${indice}][cantidad]`, linea.cantidad));

            unidades += linea.cantidad;
            centavos += linea.centavos * linea.cantidad;
        });

        vacia.hidden = lineas.size > 0;
        formulario.querySelector('[data-unidades]').textContent = unidades;
        formulario.querySelector('[data-total]').textContent = moneda.format(centavos / 100);
        formulario.querySelector('[data-enviar-orden]').disabled = lineas.size === 0;
        formulario.querySelector('[data-vaciar-orden]').disabled = lineas.size === 0;
    };

    productos.forEach((boton, id) => boton.addEventListener('click', () => cambiar(id, 1)));

    formulario.querySelector('[data-vaciar-orden]').addEventListener('click', () => {
        lineas.clear();
        pintar();
    });

    formulario.addEventListener('submit', (evento) => {
        if (lineas.size === 0 || formulario.dataset.enviando === 'si') {
            evento.preventDefault();

            return;
        }

        formulario.dataset.enviando = 'si';
        setTimeout(() => (formulario.querySelector('[data-enviar-orden]').disabled = true), 0);
    });

    // Si el servidor rechazó la orden, se recupera lo capturado.
    JSON.parse(raiz.dataset.itemsPrevios || '[]').forEach((item) => cambiar(String(item.id_producto), Number(item.cantidad) || 0));
    pintar();
}

function oculto(nombre, valor) {
    const campo = document.createElement('input');
    campo.type = 'hidden';
    campo.name = nombre;
    campo.value = valor;

    return campo;
}
