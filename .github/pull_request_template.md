## Descripción

<!-- Qué cambia y por qué. Indica la necesidad del README que atiende (RF/RNF, regla de negocio o criterio de la rúbrica). -->

- **Sprint:**
- **Tarjeta del tablero (`SEGUIMIENTO.md` §7):**
- **Requisitos relacionados:**

## Tipo de cambio

- [ ] `feature/` — funcionalidad nueva
- [ ] `fix/` — corrección de un defecto (indica el D-xx)
- [ ] `docs/` — documentación
- [ ] `test/` — pruebas
- [ ] `refactor/` — reorganización sin cambio de comportamiento

## Evidencia de pruebas

<!-- Pega el resultado real; no marques una prueba como aprobada si no se ejecutó. -->

```text
php artisan test --compact
DB_CONNECTION=mysql DB_DATABASE=cafesync_pruebas php artisan test --compact
```

- Pruebas nuevas o modificadas:
- Casos CP del README cubiertos:
- Pruebas manuales realizadas (y resultado):

## Lista de revisión

- [ ] La rama sale de `main` y su nombre usa el prefijo correcto.
- [ ] Los commits usan `feat:`, `fix:`, `docs:`, `test:` o `refactor:`.
- [ ] La suite completa pasa en SQLite y en MySQL.
- [ ] Se ejecutó `vendor/bin/pint --dirty` y `npm run build` sin errores.
- [ ] La autorización se valida en el servidor (no solo ocultando botones).
- [ ] Las entradas se validan en el backend con mensajes en español.
- [ ] No se agregaron credenciales, tokens, `.env` ni datos personales.
- [ ] No se agregaron dependencias sin aprobación del responsable.
- [ ] `SEGUIMIENTO.md` actualizado (sprint, pruebas, defectos, tablero, bitácora).
- [ ] `README.md` actualizado si cambió el comportamiento, el modelo de datos o la forma de ejecutar el proyecto.

## Revisión

<!-- Proyecto individual: la revisión es una autoinspección del responsable. No se registran aprobaciones de personas que no participaron. -->

- Revisado por:
- Observaciones:
