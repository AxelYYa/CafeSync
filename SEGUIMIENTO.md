# Seguimiento del desarrollo de CaféSync

Documento de control del proyecto bajo el **Scrum adaptado** del README (§15–§17): backlog priorizado P1/P2, tablero de cuatro estados, registro por sprint, plan y resultados de pruebas, riesgos y bitácora.

> [!IMPORTANT]
> Este archivo registra **solo lo que se verificó**. Un elemento no pasa a *Terminado* ni a *CUMPLIDO* porque el código exista: debe cumplir su criterio de aceptación y contar con una prueba ejecutada (README §15.3). Es un desarrollo individual: no se documentan reuniones, participantes ni aprobaciones que no ocurrieron.

| Dato | Valor |
| :--- | :--- |
| Responsable (todas las funciones) | Axel Espinoza Blanco — desarrollo individual |
| Fecha de corte | 2026-11-23 |
| Sprint actual | **Sprints 3 y 4 — KDS**: hito cumplido, en revisión de cierre (§3.4). Sprint 2 cerrado con pendientes trasladados (§3.3). El Sprint 5 no se ha iniciado |
| Ramas | Una rama por tarea creada desde `main` e integrada con `--no-ff` (§11) |
| Fuente funcional | `README.md` (especificación v2, 19-oct-2026) |
| Fuente de calidad | Rúbrica de la Unidad 3, nivel EXCELENTE. **El documento de la rúbrica no está en el repositorio**; sus criterios se tomaron de las instrucciones del responsable (ver §4.1). |

### Dónde está documentado cada resultado

| Qué buscas | Apartado |
| :--- | :--- |
| Plan del README frente a lo realizado | §2 |
| Registro de cada sprint (objetivo, tareas, archivos, pruebas, problemas, pendientes, conclusión) | §3 |
| Tipos de prueba exigidos, archivos de prueba, comandos y resultados | §4 |
| Casos CP-01 a CP-15 | §4.5 |
| Relación rúbrica ↔ casos CP ↔ pruebas automatizadas | §4.6 |
| Pruebas manuales pendientes | §4.8 |
| Estado de requisitos RF/RNF, MVP y criterios de la rúbrica | §5 |
| Defectos | §6 |
| Tablero | §7 |
| Decisiones tomadas y pendientes | §8 |
| Riesgos | §9 |
| Bitácora de sesiones | §10 |
| Control de versiones, ramas e historial | §11 |

### Convenciones

- **Tablero:** `Pendiente` → `En proceso` → `En revisión/pruebas` → `Terminado`.
- **Estado de un criterio:** **CUMPLIDO** (implementado y comprobado) · **PARCIAL** (existe, falta completar o verificar) · **PENDIENTE** (no implementado) · **BLOQUEADO** (requiere credenciales, permisos o servicios externos no disponibles).
- **Severidad de defectos:** Crítico (bloquea P1 o la seguridad) · Alto · Medio · Bajo.

---

## 1. Fotografía del repositorio (2026-11-11)

| Elemento | Estado |
| :--- | :--- |
| PHP / Laravel | PHP 8.2.12 (XAMPP) · Laravel 12.70.0 |
| Paquetes directos | `laravel/sanctum` 4.3.3, `laravel/tinker`, **`twilio/sdk` 8.14.0** (agregado el 2026-11-11 a petición del responsable para Twilio Verify); dev: Pest 3.8.7, Pint 1.30.4, Boost 2.10.3, Sail, Pail |
| Frontend | Blade + Tailwind CSS 4 + JavaScript modular compilado con Vite 7 (`resources/js/modulos/*`) |
| Base de datos | **MySQL 8.0.46** local: base `cafesync` (desarrollo, migrada y con datos de demostración) y `cafesync_pruebas` (suite automatizada). La suite también corre en SQLite en memoria (configuración por defecto de `phpunit.xml`) |
| Correo | SMTP del sandbox de **Mailtrap** en `.env` (pruebas). El SMTP productivo queda pendiente por decisión del responsable |
| Twilio | Twilio Verify integrado con el SDK oficial. `.env` tiene Account SID y Verify Service SID; **API Key y secreto vacíos** hasta regenerarlos (las credenciales anteriores se expusieron) |
| Rutas | 51 rutas; `php artisan route:list` funciona desde `43aa784` |
| Pruebas | 232 pruebas Pest (47 unitarias + 185 de integración), todas pasan en SQLite y en MySQL (§4.7) |
| GitHub CLI | `gh` instalado pero **sin sesión iniciada** → sin PR ni protección de ramas consultables |

### Historial de Git

| Commit | Fecha | Descripción |
| :--- | :--- | :--- |
| `01098eb` · `40b6c0d` · `448cd3b` | 2026-10-19 | Proyecto inicial y README v1/v2 — **último commit en `origin/main`** |
| `f860f40` · `fcaf430` | 2026-11-03 | `feat:` modelo de datos, migraciones, enums y seeders; merge local en `main` (sin PR) |
| `43b2f4d` | 2026-11-10 | `feat:` autenticación por usuario o correo, MFA TOTP, multisesiones y middleware de seguridad |
| `0625b48` | 2026-11-10 | `feat:` recuperación de contraseña por correo, pregunta secreta, SMS y llamada |
| `e677801` | 2026-11-10 | `feat:` POS, KDS por estación, Help Mode, cierre de venta e inventario por receta |
| `cd1f496` | 2026-11-10 | `feat:` reloj checador con PIN, tareas por turno, reporte de ventas y rutas web |
| `6c0c3e8` | 2026-11-10 | `docs:` documento de seguimiento |
| `e4a1de7` | 2026-11-10 | `fix:` Twilio, vencimiento de tokens, `.env.example` para MySQL, excepción de PIN inválido |
| `1d0fd37` | 2026-11-10 | `test:` 106 pruebas de dominio, seguridad y endpoints |
| `ffbf0fa` | 2026-11-10 | `docs:` incremento 1 del Sprint 2 |
| `911abbf` | 2026-11-10 | `feat:` identidad visual, layouts, componentes Blade, páginas de error y traducción al español |
| `983c53b` | 2026-11-10 | `feat:` pantallas de acceso, panel, POS, órdenes, KDS, Help Mode, reloj, tareas y seguridad |
| `d8534d9` | 2026-11-10 | `test:` renderizado y visibilidad por rol de las pantallas |
| `f0c54db` | 2026-11-10 | `docs:` incremento 2 del Sprint 2 |
| `43aa784` | 2026-11-11 | `fix:` activar solo las rutas administrativas del Sprint 2 (D-01, D-15) |
| `5b55a44` | 2026-11-11 | `fix:` errores separados por formulario en seguridad de la cuenta (D-16) |
| `44d8524` | 2026-11-11 | `feat:` administración de usuarios y roles, reporte de asistencias y tokens de pantallas |
| `6412274` | 2026-11-11 | `test:` pruebas unitarias y de administración, protección de rutas y seguridad |
| `9949a22` | 2026-11-11 | `docs:` cierre del Sprint 2, plan de pruebas y estado real en README |
| `74a463f` | 2026-11-11 | `fix:` folio temporal que no cabía en MySQL (D-17) |
| `ded5191` | 2026-11-11 | `feat:` recuperación por SMS y llamada con Twilio Verify (SDK oficial) |
| `20e8093` | 2026-11-11 | `test:` cancelación de órdenes por el empleado (D-09) |

Los hashes de esta tabla son los vigentes después de reescribir el historial el 2026-11-23 (§11.3). `main` local integra el Sprint 2 con el merge `cf23603`; el estado de publicación está en §11.4.

---

## 2. Planificación del README frente a lo realizado

| Sprint (README §17) | Actividades planificadas | Hito verificable | Estado real |
| :--- | :--- | :--- | :--- |
| Semana 1 — Preparación | Entorno, modelo de datos, estaciones, productos, ingredientes, receta de prueba; iniciar acceso y asistencia | Entorno ejecutable, estructura relacional definida y primer acceso validado | Realizado (`f860f40`). Verificado en SQLite; falta MySQL |
| **Semana 2 — Acceso y POS** | **Completar roles y reloj checador; registro de órdenes y clasificación por mostrador / Drive-Thru / delivery** | **Una orden puede crearse con sus productos y canal y queda almacenada** | **Hito cumplido con pruebas automatizadas** (§3.3) |
| Semanas 3 y 4 — KDS | Vistas de preparación, filtros por estación, estados, actualización sin recarga | La orden llega a la estación correcta y puede marcarse como lista | **Adelantado**: existe y tiene pruebas; falta la medición de tiempo (CP-07) y la administración de estaciones |
| Semana 5 — Inventario y tareas | Recetas, cálculo de consumo, cierre de venta con movimientos, checklists | La venta descuenta sin duplicación; checklist persiste | **Adelantado**: lógica y pruebas existen; faltan pantallas de administración de catálogo, recetas, inventario y asignación de tareas |
| Semana 6 — Validación y cierre | Casos integrales, permisos, errores, ajustes de interfaz, demostración | Versión demostrable y P1 verificados | Pendiente |

### Diferencia entre lo planificado y el código

Al iniciar el Sprint 2 el repositorio ya tenía, sin commitear, la lógica de negocio de los sprints 3 a 5 (KDS, cierre de venta, inventario, tareas, reportes) y referencias en `routes/web.php` a 11 controladores de administración inexistentes. Durante el Sprint 2 se les dio interfaz a esos módulos operativos y se probaron.

**Cómo se resuelve sin perder el planteamiento original:**

- El trabajo adelantado se conserva y queda marcado como **adelantado** en el tablero (§7). Sus hitos se declaran cumplidos en el sprint que les corresponde, después de completar lo que falta (medición del KDS, pantallas de administración).
- Las rutas de administración de sprints posteriores se **comentaron en `routes/web.php` con su sprint asignado** (`43aa784`) y se habilitarán al crear sus controladores. Así `route:list` funciona y el menú no enlaza pantallas inexistentes.
- A partir de este punto solo se desarrolla lo del sprint en curso.

---

## 3. Registro por sprint

### 3.1. Sprint 0 — Especificación

| Campo | Registro |
| :--- | :--- |
| 1. Objetivo | Documentar problema, alcance, requisitos, modelo de datos y plan. |
| 2. Planificado | Documento de proyecto (README). |
| 3. Realizado | Proyecto Laravel inicial; README v1 y v2. |
| 4. Funcionalidades | — |
| 5. Archivos | `README.md` |
| 6. Pruebas | No aplica. |
| 7. Problemas | — |
| 8. Pendientes | Enlace o capturas de Figma (README §18.4); confirmar los puntos abiertos de §11. |
| 9. Evidencia | Commits `01098eb`, `40b6c0d`, `448cd3b` (2026-10-19). |
| 10. Conclusión | Terminado. |

### 3.2. Sprint 1 — Preparación (Semana 1)

| Campo | Registro |
| :--- | :--- |
| 1. Objetivo | Entorno ejecutable, estructura relacional definida y primer acceso validado. |
| 2. Planificado | Configurar entorno; revisar modelo de datos; definir estaciones, productos, ingredientes y receta de prueba; iniciar acceso y asistencia (T-01). |
| 3. Realizado | Migraciones del DER, 9 enums, 15 modelos con PK del diccionario, factories, seeders de demostración, Sanctum, `PinService`. |
| 4. Funcionalidades | Esquema de datos completo con restricciones; datos base (2 roles, 4 usuarios con PIN, 3 estaciones, 11 ingredientes, 8 productos con receta, 2 turnos, 4 plantillas de tareas). |
| 5. Archivos | `database/migrations/2026_10_20_00000{1..5}_*.php`, `app/Enums/*`, `app/Models/*`, `database/seeders/*`, `database/factories/*`, `app/Services/PinService.php`, `config/auth.php`, `config/sanctum.php`. |
| 6. Pruebas | Migraciones ejecutadas en SQLite. Sin pruebas automatizadas en ese momento; las del modelo se agregaron en el Sprint 2 (§4.4). |
| 7. Problemas | SQLite en lugar de MySQL (D-05); merge sin PR ni `push` (D-06). |
| 8. Pendientes | Verificar migraciones y seeders en MySQL; documentar las ampliaciones del diccionario en el README (realizado en el cierre del Sprint 2, README §23.16). |
| 9. Evidencia | Commit `f860f40` (2026-11-03); `php artisan migrate:status` con 7 migraciones ejecutadas. |
| 10. Conclusión | Hito cumplido en SQLite. Queda abierta la verificación en MySQL (B-15). |

### 3.3. Sprint 2 — Acceso y POS (Semana 2) · revisión de cierre

| Campo | Registro |
| :--- | :--- |
| 1. Objetivo | **Completar roles y reloj checador; desarrollar el registro de órdenes y la clasificación por canal.** Hito: *una orden puede crearse con sus productos y canal y queda almacenada*. Se integran aquí los requisitos de acceso de la rúbrica (autenticación, MFA, multisesiones, recuperación, tokens, autorización, OWASP). |
| 2. Planificado | T-02 (acceso por roles y asistencia) y T-03 (POS), más los requisitos de acceso y seguridad de la rúbrica. |
| 3. Realizado | **Incremento 1** (2026-11-10): commit del backend existente en 4 commits semánticos; corrección de configuración; 106 pruebas. **Incremento 2** (2026-11-10): sistema visual, layouts, 17 vistas operativas, páginas de error, JS modular y traducción `es`. **Incremento 3** (2026-11-11): administración de usuarios y roles, reporte de asistencias, tokens de pantallas, rutas por sprint, errores por formulario y 47 pruebas unitarias. **Incremento 4** (2026-11-11): base MySQL, suite completa en MySQL, prueba de concurrencia del cierre de venta, correo por SMTP de Mailtrap, SMS y llamada con Twilio Verify y confirmación de D-09. |
| 4. Funcionalidades | **Acceso:** login por usuario o correo, bloqueo por intentos, logout seguro, MFA TOTP con códigos de recuperación, multisesiones con cierre remoto, recuperación por correo, pregunta, SMS y llamada, tokens Sanctum con permisos y vencimiento. **Roles:** alta y edición de usuarios, cambio de rol, activación y desactivación (cierra sesiones y revoca tokens), protección `rol:gerente`. **Reloj checador:** registro con PIN y hora del servidor, teclado táctil, historial por periodo y empleado con jornadas y horas. **Recuperación telefónica:** Twilio Verify genera, envía y valida el código por SMS o llamada. **POS:** catálogo táctil por estación, canal de origen, orden con precio del servidor, listado de órdenes con entrega, cierre y cancelación. |
| 5. Archivos | Controladores: `app/Http/Controllers/Auth/*`, `Cuenta/*`, `Admin/UsuarioController.php`, `Admin/AsistenciaReporteController.php`, `Admin/TokenApiController.php`, `PosController.php`, `OrdenController.php`, `AsistenciaController.php`, `DashboardController.php`. Servicios: `app/Services/Mfa/*`, `Sesiones/*`, `Recuperacion/*`, `Usuarios/UsuarioService.php`, `Asistencia/*`, `Ventas/OrdenService.php`. Middleware: `app/Http/Middleware/*`. Vistas: `resources/views/auth/*`, `cuenta/*`, `admin/usuarios/*`, `admin/asistencias/*`, `admin/tokens/*`, `pos/*`, `asistencia/*`, `dashboard/*`, `components/*`, `errors/*`. Otros: `routes/web.php`, `routes/api.php`, `config/services.php`, `config/sanctum.php`, `.env.example`, `lang/es/*`, `resources/css/app.css`, `resources/js/*`. |
| 6. Pruebas | **217 pruebas pasan, 0 fallan** (650 aserciones) en SQLite **y en MySQL**: 47 unitarias y 170 de integración. Detalle en §4. Pasan CP-01, CP-02 (rutas del Sprint 2), CP-03, CP-04, CP-05 y CP-13; CP-10 pasa además con 6 procesos concurrentes en MySQL. |
| 7. Problemas y solución | D-01/D-15 (rutas a controladores inexistentes): rutas separadas por sprint. D-16 (un error se repetía en varios formularios): *error bags*. Usuarios con punto en el nombre rechazados por `alpha_dash`: se cambió a una expresión que acepta letras, números, punto, guion y guion bajo. Una prueba de sesiones no enviaba cookies: se usó `withCredentials()` y se reinició el singleton `StartSession`. Edge headless recorta a 390 px pero usa 496 px de viewport: las capturas de celular se toman a 500 px. **D-17:** en MySQL ninguna orden se guardaba porque el folio temporal medía 40 caracteres en una columna de 30; SQLite no lo detectaba. Se corrigió y desde entonces la suite se ejecuta también en MySQL. `composer require` quedó detenido en sus scripts posteriores: se detuvo el proceso y se regeneró el lock con `composer update --lock --no-scripts`. |
| 8. Pendientes | (a) ~~MySQL local~~ — resuelto. (b) Correo: el SMTP de Mailtrap aceptó un envío real; falta confirmar la bandeja (M-07) y configurar el SMTP productivo (pendiente por decisión del responsable). SMS y llamada — BLOQUEADOS hasta regenerar la API Key de Twilio (M-08). (c) `push`, PR y protección de `main` — BLOQUEADO por `gh auth login` (B-31). (d) Verificaciones manuales M-01 a M-08 (§4.8). (e) ~~D-09~~ — resuelto: el empleado puede cancelar órdenes no cerradas. (f) Actualizar componentes Symfony con avisos de seguridad (D-18), pendiente de aprobación. |
| 9. Evidencia | §4.5 (CP), §4.6 (matriz rúbrica ↔ pruebas), §4.7 (ejecuciones). Commits `43b2f4d` … `6412274`. |
| 10. Conclusión | **Objetivo y hito del Sprint 2 cumplidos con pruebas automatizadas.** El cierre formal queda **en revisión** hasta que el responsable ejecute las verificaciones manuales de §4.8. Los pendientes (b), (c) y (f) dependen de acciones externas o de una aprobación; no impiden el flujo del sprint, pero deben resolverse antes del Sprint 6. **Cierre (2026-11-23):** el responsable indicó continuar con el desarrollo. El Sprint 2 se **cierra con el hito cumplido** y estos pendientes **trasladados al Sprint 3-4**, sin marcarlos como aprobados: verificaciones manuales M-01 a M-08, regeneración de la API Key de Twilio, SMTP productivo, actualización de Symfony (D-18) y publicación en GitHub (§11.4). Integración en `main`: merge `cf23603`. |

### 3.4. Sprints 3 y 4 — KDS (revisión de cierre)

| Campo | Registro |
| :--- | :--- |
| 1. Objetivo | README §17: *desarrollar vistas de preparación, filtros por estación, estados de orden y mecanismo de actualización sin recarga manual.* Hito: **la orden llega a la estación correcta y puede marcarse como lista.** |
| 2. Planificado | T-05 (pantallas KDS por zona) y T-06 (Help Mode). Lo que falta, porque la lógica y las pantallas operativas se adelantaron en el Sprint 2 (§2): (1) administración de estaciones (B-36); (2) medición del tiempo envío → visualización, CP-07 y RNF-01 (B-23); (3) prueba de reconexión, CP-14 (B-28); (4) pendientes trasladados del Sprint 2. |
| 3. Realizado | 2026-11-23: preparación del flujo de ramas y cierre documental del Sprint 2 (rama `docs/flujo-ramas-y-cierre-sprint-2`). **B-36 administración de estaciones** (rama `feature/kds-administracion-estaciones`). **B-23 medición del tiempo del KDS** (rama `test/kds-medicion-tiempo`). **B-28 prueba de falla de red** (rama `test/kds-reconexion`). **D-19 recuperación inmediata al volver la red** (rama `fix/kds-recuperacion-inmediata`). **Flujo de preparación en navegador real** (rama `test/kds-flujo-preparacion`). |
| 4. Funcionalidades | **Estaciones:** el gerente crea, renombra, describe, activa y desactiva zonas de preparación. La lista muestra productos activos y pendientes de cada pantalla KDS. No se puede desactivar una estación con pendientes en órdenes en curso; al desactivarla, sus productos salen del POS y su pantalla de la lista del KDS. Las estaciones no se borran, para conservar el historial de lo vendido. **Medición del KDS:** script reproducible que abre la pantalla real en Edge y mide cuánto tarda una orden del POS en aparecer. |
| 5. Archivos | `.github/pull_request_template.md`; `app/Http/Controllers/Admin/EstacionController.php`, `app/Services/Catalogo/EstacionService.php`, `resources/views/admin/estaciones/*`, `routes/web.php`, `resources/js/modulos/formularios.js`; `tests/Feature/Http/Controllers/Admin/EstacionControllerTest.php`, `tests/Feature/Http/Middleware/EnsureRoleTest.php`; `tests/Rendimiento/escenario-kds.mjs`, `tests/Rendimiento/medir-kds.mjs`, `tests/Rendimiento/probar-reconexion-kds.mjs`, `tests/Rendimiento/probar-flujo-kds.mjs`; `SEGUIMIENTO.md`, `README.md` |
| 6. Pruebas | `EstacionControllerTest` (9) y 6 casos nuevos en `EnsureRoleTest`. Suite: **232 pasan** en SQLite y en MySQL (§4.7). **CP-07:** 2 series (20 y 30 órdenes), 50 de 50 visibles en menos de 1 s; p95 828 ms y 821 ms (§4.5). **CP-14:** 6 de 6 verificaciones pasan; la recuperación tras 6 s sin red tardó 4.6 s por la espera creciente entre reintentos (D-19); tras la corrección, 267–290 ms. **Flujo de preparación (hito del sprint):** 7 de 7 verificaciones en Edge real. |
| 7. Problemas y solución | D-19: la pantalla tardaba hasta ~6 s en recuperarse al volver la red; se corrigió escuchando `online`/`offline` (~0.3 s). D-20: los scripts de navegador fallaban o dejaban Edge abierto; se corrigió terminando solo los procesos del perfil de prueba, después de descartar un intento con `taskkill /T` que no alcanzaba al navegador real. Conteo N+1 en la lista de estaciones: se reemplazó por un `withCount`. El filtro que quitó la coautoría también reescribió los dos commits del Sprint 1 que ya estaban en `main`; la rama del Sprint 2 se reacomodó sobre el `main` real con `git rebase --committer-date-is-author-date` y se comprobó que el árbol final es idéntico (§11.3). |
| 8. Pendientes | Repetir CP-07 y CP-14 con dispositivos en red local y los trasladados del Sprint 2. |
| 9. Evidencia | Merges en `main`: `b0eee18` (estaciones), `147eb66` (medición), `72977c1` (reconexión), `32de233` (D-19), `9123876` y `7027ffa` (D-20), `5472137` (flujo de preparación) (§11.2). Resultados en §4.5 (CP-06, CP-07, CP-08, CP-14) y §4.7. Suite final en `main`: **232 pasan en SQLite y en MySQL** (2026-11-23). |
| 10. Conclusión | **Hito cumplido:** *la orden llega a la estación correcta y puede marcarse como lista*. Lo demuestran las pruebas automáticas (CP-06, `KdsControllerTest`, `PreparacionServiceTest`) y Edge real (`probar-flujo-kds.mjs`: los botones «Iniciar» y «Marcar lista» persisten). Actualización sin recarga en menos de 1 s (CP-07, p95 ≈ 0.83 s) y recuperación ante falla de red sin duplicados (CP-14). Pendiente de este sprint: repetir CP-07 y CP-14 con la caja y las pantallas en dispositivos distintos de la red local. Se mantienen los trasladados del Sprint 2. **El cierre formal y el inicio del Sprint 5 quedan a confirmación del responsable.** |

### 3.5. Plantilla para los siguientes sprints

| Campo | Registro |
| :--- | :--- |
| 1. Objetivo | |
| 2. Planificado | |
| 3. Realizado | |
| 4. Funcionalidades | |
| 5. Archivos | |
| 6. Pruebas | |
| 7. Problemas y solución | |
| 8. Pendientes | |
| 9. Evidencia | |
| 10. Conclusión | |

---

## 4. Plan y resultados de pruebas

### 4.1. Tipos de prueba exigidos

El documento de la rúbrica 3 no está en el repositorio. Esta lista se construyó con los tipos de prueba que el responsable indicó que la rúbrica exige; **debe contrastarse con la rúbrica original en cuanto esté disponible** (decisión §8-8).

| Tipo | Qué verifica | Dónde está |
| :--- | :--- | :--- |
| Unitarias | Lógica aislada de enums, modelos y servicios sin base de datos ni framework | `tests/Unit` |
| Integración de servicios | Reglas de negocio con base de datos real (transacciones, idempotencia, relaciones) | `tests/Feature/Services` |
| Endpoints (HTTP) | Respuestas, redirecciones, persistencia y vistas de cada controlador | `tests/Feature/Http/Controllers` |
| Validación de formularios y errores | Campos requeridos, formatos, cantidades inválidas, mensajes en español | Endpoints (§4.4) |
| Autenticación | Login, MFA, logout, bloqueo por intentos, recuperación | `Auth/*`, `Services/Mfa`, `Unit/Services/Mfa` |
| Autorización y permisos por rol | Acceso directo a rutas de gerente, políticas, servicios | `Http/Middleware/EnsureRoleTest`, `OrdenControllerTest`, `TareaControllerTest` |
| Seguridad (OWASP) | CSRF, XSS, encabezados, secretos con hash, cuentas inactivas, tokens | `Http/Middleware/*`, `MfaServiceTest`, `SeguridadControllerTest` |
| Integridad de datos | Transacciones, reversión ante fallas, no doble descuento, snapshots de precio | `CierreVentaServiceTest`, `OrdenServiceTest` |
| Procesos críticos | Ventas, preparación, inventario, asistencia, tareas | `Services/Ventas/*`, `AsistenciaControllerTest`, `TareaControllerTest` |
| Interfaz y responsividad | Renderizado y visibilidad por rol (automática); capturas a 1920×1080 y 500 px (manual) | Endpoints + §4.7 |
| Rendimiento KDS | Tiempo entre la orden guardada y su aparición en la pantalla de la estación < 1 s | `tests/Rendimiento/medir-kds.mjs` (Edge real; se ejecuta a mano con la aplicación corriendo) |
| Falla de red KDS | Aviso de incidencia, recuperación sin recargar y sin duplicados | `tests/Rendimiento/probar-reconexion-kds.mjs` (Edge real con red cortada por DevTools) |
| Flujo de preparación en navegador | Botones «Iniciar» y «Marcar lista», persistencia al recargar, Help Mode sobre la pantalla | `tests/Rendimiento/probar-flujo-kds.mjs` (Edge real) |

### 4.2. Cómo ejecutarlas

```powershell
php artisan test --compact                       # suite completa
php artisan test --compact --testsuite=Unit      # solo unitarias
php artisan test --compact --testsuite=Feature   # solo integración
php artisan test --compact tests/Feature/Services/Ventas/CierreVentaServiceTest.php   # un archivo
php artisan test --compact --filter="does not discount inventory twice"             # una prueba
```

Por defecto las pruebas usan SQLite en memoria (`phpunit.xml`) y `LazilyRefreshDatabase`: cada prueba empieza con la base vacía. Para ejecutarlas en MySQL (gestor del proyecto) sobre la base `cafesync_pruebas`:

```bash
DB_CONNECTION=mysql DB_DATABASE=cafesync_pruebas php artisan test --compact   # Git Bash
```

```powershell
$env:DB_CONNECTION='mysql'; $env:DB_DATABASE='cafesync_pruebas'; php artisan test --compact; Remove-Item Env:DB_CONNECTION, Env:DB_DATABASE   # PowerShell
```

Medición del tiempo del KDS (requiere la aplicación corriendo con los datos de demostración y Microsoft Edge instalado; crea órdenes de prueba y al final las cancela):

```powershell
php artisan serve                                                    # en otra terminal
node tests/Rendimiento/medir-kds.mjs --url=http://127.0.0.1:8000 --ordenes=20
```

El script termina con código 0 solo si todas las órdenes aparecen y el percentil 95 es menor a 1 s.

Prueba de falla de red del KDS (corta la red del navegador, crea una orden durante el corte y restablece la red):

```powershell
node tests/Rendimiento/probar-reconexion-kds.mjs --url=http://127.0.0.1:8000 --desconexion=6000
```

Flujo de preparación en la pantalla (pulsa «Iniciar», «Marcar lista» y «Receta» como lo haría el personal):

```powershell
node tests/Rendimiento/probar-flujo-kds.mjs --url=http://127.0.0.1:8000
```

Los tres scripts comparten `tests/Rendimiento/escenario-kds.mjs` (sesiones, control de Edge y preparación de la pantalla).

Las pruebas de Twilio usan `tests/Fakes/ClienteHttpTwilioSimulado.php`: el SDK oficial arma las peticiones reales, pero nada sale a la red ni genera cargos.

### 4.3. Pruebas unitarias (47)

| Archivo | Pruebas | Qué verifica |
| :--- | :---: | :--- |
| `tests/Unit/Enums/UnidadMedidaTest.php` | 9 | Conversión kg→g y l→ml con 3 decimales; compatibilidad entre familias; unidades base; rechazo de cantidades no numéricas |
| `tests/Unit/Enums/EstadosTest.php` | 7 | Preparación solo avanza un paso; estados cerrables y activos de la orden; los 3 canales del README |
| `tests/Unit/Models/ModelosTest.php` | 13 | Turno diurno y nocturno (cruza medianoche); pasos de receta; ingrediente bajo mínimo |
| `tests/Unit/Services/RequerimientosServiceTest.php` | 4 | Receta × unidades vendidas, ingredientes compartidos, decimales; faltantes nombrados |
| `tests/Unit/Services/Mfa/TotpServiceTest.php` | 7 | Vectores de referencia del RFC 6238; tolerancia de reloj; códigos fuera de ventana o mal formados |
| `tests/Unit/Services/SeguridadUnitTest.php` | 6 | Normalización de la respuesta secreta; identificación de dispositivo, navegador y sistema |
| `tests/Unit/ExampleTest.php` | 1 | Prueba de ejemplo de Laravel (sin cambios) |

### 4.4. Pruebas de integración (185)

| Archivo | Pruebas | Qué verifica |
| :--- | :---: | :--- |
| `Feature/Services/Ventas/OrdenServiceTest.php` | 9 | Canal conservado (3 canales), folio, total y precio del catálogo, consolidación de renglones, sin descuento al crear, rechazos sin guardar |
| `Feature/Services/Ventas/CierreVentaServiceTest.php` | 6 | Descuento exacto, idempotencia, reversión total ante faltante, cierre solo desde «lista», reversión solo del gerente |
| `Feature/Services/Ventas/KdsServiceTest.php` | 5 | Cada estación ve solo sus renglones; órdenes cerradas fuera; huella de versión |
| `Feature/Services/Ventas/PreparacionServiceTest.php` | 4 | Transiciones, saltos rechazados, orden «lista» al terminar todas las estaciones (Observer) |
| `Feature/Services/Inventario/InventarioServiceTest.php` | 6 | Conversión de unidades, solo gerente, motivo obligatorio, saldo nunca negativo |
| `Feature/Services/Recetas/RecetaServiceTest.php` | 4 | Versionado, unidades incompatibles, duplicados, cantidad cero |
| `Feature/Services/Mfa/MfaServiceTest.php` | 5 | Activación con confirmación, secretos cifrados, anti-reutilización, códigos de un solo uso |
| `Feature/Http/Controllers/Auth/LoginControllerTest.php` | 14 | Login por usuario y correo, mensajes genéricos, bloqueo, cuenta inactiva, MFA, logout |
| `Feature/Http/Controllers/Auth/RecuperacionPasswordControllerTest.php` | 17 | Pregunta secreta y correo (código con hash, vencimiento, intentos, no enumeración); SMS y llamada con Twilio Verify simulado (petición con API Key, canal y locale; código aprobado, no aprobado o vencido; código mal formado sin llamar a Twilio; falla del proveedor; cuenta sin teléfono); cierre de sesiones |
| `Feature/Http/Controllers/Cuenta/SesionControllerTest.php` | 6 | Cierre remoto con efecto real (200 → 401), sesiones ajenas, contraseña requerida |
| `Feature/Http/Controllers/Cuenta/SeguridadControllerTest.php` | 9 | PIN y respuesta con hash, PIN duplicado, teléfono, contraseña débil, errores por formulario |
| `Feature/Http/Controllers/Admin/UsuarioControllerTest.php` | 11 | Alta con login y PIN funcionales, validaciones, duplicados, desactivación con revocación, autoprotección del gerente |
| `Feature/Http/Controllers/Admin/AsistenciaReporteControllerTest.php` | 3 | Jornadas y horas contra cálculo manual, filtro por empleado, periodo inválido |
| `Feature/Http/Controllers/Admin/EstacionControllerTest.php` | 9 | Lista con productos y pendientes; alta visible en el KDS; nombre duplicado, corto o vacío; renombrar conserva el historial; desactivar saca los productos del POS; no se desactiva con pendientes; sí se desactiva cuando sus órdenes terminaron |
| `Feature/Http/Controllers/Admin/TokenApiControllerTest.php` | 5 | Token real autentica la API con sus permisos, revocación, vencimiento, secreto mostrado una sola vez |
| `Feature/Http/Controllers/AsistenciaControllerTest.php` | 8 | Hora del servidor, PIN inválido o inactivo, secuencia, bloqueo, formato |
| `Feature/Http/Controllers/PosControllerTest.php` | 6 | Precio del servidor, validaciones, faltantes, catálogo por estación |
| `Feature/Http/Controllers/OrdenControllerTest.php` | 10 | Cierre doble, entrega, cancelación por el empleado sin tocar inventario, reversión por rol, listado y detalle |
| `Feature/Http/Controllers/KdsControllerTest.php` | 6 | Estaciones, renglones por estación, HTML solo al cambiar, avance y error 422 |
| `Feature/Http/Controllers/Api/KdsApiControllerTest.php` | 6 | 401 sin token, 403 sin permiso o con cuenta inactiva, ruteo, avance |
| `Feature/Http/Controllers/AyudaControllerTest.php` | 3 | Help Mode con la receta correcta |
| `Feature/Http/Controllers/TareaControllerTest.php` | 5 | Completar con quién y cuándo, tareas ajenas prohibidas, idempotencia |
| `Feature/Http/Controllers/DashboardControllerTest.php` | 2 | Cifras del gerente ocultas al empleado |
| `Feature/Http/Middleware/EnsureRoleTest.php` | 20 | Cada pantalla de `/admin`: gerente 200, empleado 403, invitado al login; formularios enviados directamente |
| `Feature/Http/Middleware/EnsureUserIsActiveTest.php` | 2 | Cuenta desactivada durante su sesión |
| `Feature/Http/Middleware/SecurityHeadersTest.php` | 3 | Encabezados y CSP, escape de HTML (XSS), CSRF con entorno de producción (419) |
| `Feature/ExampleTest.php` | 1 | `/` redirige al panel y el invitado va al login |

### 4.5. Casos de prueba del README (§19.1)

| ID | Caso | Prueba automatizada | Esperado | Obtenido | Resultado | Fecha |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| CP-01 | Acceso válido como gerente | `LoginControllerTest`, `DashboardControllerTest`, `EnsureRoleTest` | Abre el panel con sus funciones | Panel con ventas, alerta de inventario y menú de administración | **Pasa** | 2026-11-11 |
| CP-02 | Bloqueo de ruta administrativa | `EnsureRoleTest`, `OrdenControllerTest` | El servidor deniega sin exponer datos | 403 «No tienes permiso» en las 4 pantallas de `/admin` y en formularios enviados directamente; nada se guarda | **Pasa** (rutas del Sprint 2). El reporte de ventas se probará al habilitarlo (Sprint 6) | 2026-11-11 |
| CP-03 | Registro de asistencia | `AsistenciaControllerTest` | Dos marcas del empleado con hora del servidor | Entrada 08:15:00 y salida 14:50:00 del dueño del PIN; la hora del navegador se ignora | **Pasa** | 2026-11-11 |
| CP-04 | PIN inválido | `AsistenciaControllerTest` | Mensaje claro, sin marca | «PIN no válido.», 0 registros; igual con PIN de cuenta inactiva | **Pasa** | 2026-11-11 |
| CP-05 | Canal de pedido | `OrdenServiceTest`, `PosControllerTest`, `KdsControllerTest` | Cada orden conserva y muestra su canal | Las 3 conservan el canal; etiqueta visible en Órdenes y KDS | **Pasa** | 2026-11-11 |
| CP-06 | Ruteo a estaciones | `KdsServiceTest`, `KdsControllerTest`, `KdsApiControllerTest` | Cada KDS muestra solo su producto | Barra ve el latte, cocina el sándwich, misma orden | **Pasa** (Sprints 3-4, adelantado) | 2026-11-11 |
| CP-07 | Actualización KDS con medición | `tests/Rendimiento/medir-kds.mjs` | < 1 s sin recarga | Serie 1 (20 órdenes): mín. 243, mediana 573, promedio 528, p95 828, máx. 831 ms. Serie 2 (30 órdenes): mín. 218, mediana 550, promedio 543, p95 821, máx. 841 ms. 50/50 bajo 1 s, sin recargar. Entorno: Windows 11, una sola máquina (sin red local de por medio), `php artisan serve` (un proceso), MySQL 8.0.46, Edge 158 en modo headless a 1920×1080, Node 24.19, intervalo de consulta 800 ms | **Pasa** en el entorno descrito; repetir con dispositivos en red local | 2026-11-23 |
| CP-08 | Help Mode | `AyudaControllerTest`, `tests/Rendimiento/probar-flujo-kds.mjs` | Ficha del producto correcto | Ingredientes, cantidades y pasos del producto pedido, no de otro | **Pasa**; en Edge real el diálogo muestra la receta del producto y al cerrarlo se sigue en `/kds/1` con la orden visible | 2026-11-23 |
| CP-09 | Cálculo de receta | `CierreVentaServiceTest` | Saldo − receta × unidades | 3 lattes: café 1000 → 946 g, leche 5000 → 4400 ml | **Pasa** (Sprint 5, adelantado) | 2026-11-11 |
| CP-10 | Doble descuento | `CierreVentaServiceTest`, `OrdenControllerTest` + prueba de concurrencia manual | Sin segundo conjunto de movimientos | 2 cierres → 1 conjunto; falla de stock → 0 movimientos. **6 procesos simultáneos en MySQL** cerrando la misma venta de 2 lattes: café 5000 → 4964 g, leche 20000 → 19600 ml, 2 movimientos, orden cerrada | **Pasa** en SQLite y en MySQL | 2026-11-11 |
| CP-11 | Movimiento manual autorizado | `InventarioServiceTest` | Gerente opera; empleado denegado | Servicio correcto; sin ruta HTTP todavía | **Parcial** (Sprint 5) | 2026-11-11 |
| CP-12 | Checklist | `TareaControllerTest` | La asignación y su estado persisten | Marcar y persistir correcto; la asignación desde `/admin` es del Sprint 5 | **Parcial** (Sprint 5) | 2026-11-11 |
| CP-13 | Validación de datos | `PosControllerTest`, `UsuarioControllerTest`, `RecetaServiceTest`, `AsistenciaControllerTest`, `SeguridadControllerTest` | No guarda y explica | Negativos, ceros, vacíos y formatos inválidos rechazados; mensajes en español | **Pasa** | 2026-11-11 |
| CP-14 | Falla de red | `tests/Rendimiento/probar-reconexion-kds.mjs` | Indica la incidencia y no duplica | Aviso «Sin conexión» 806 ms después del corte; la orden creada durante 6 s sin red no aparece; al reconectar vuelve a «Conectado» sin recargar y la orden llega una sola vez (contador 0 → 1). Recuperación: 4565 ms tras volver la red; **tras corregir D-19: 288, 267 y 286 ms** | **Pasa** (6/6 en las 4 ejecuciones) | 2026-11-23 |
| CP-15 | Consulta de ventas | — | Total coincide | Servicio existe; sin pantalla | **No ejecutado** (Sprint 6, P2) | — |

### 4.6. Relación rúbrica ↔ casos CP ↔ pruebas

| Criterio de la rúbrica | CP | Pruebas automatizadas |
| :--- | :--- | :--- |
| RB-01 Login por usuario o correo | CP-01 | `LoginControllerTest` (*signs in with the username or the email*, mensajes genéricos) |
| RB-02 Intentos repetidos | — | `LoginControllerTest` (*locks the account…*), `AsistenciaControllerTest` (*blocks PIN attempts…*), `RecuperacionPasswordControllerTest` (*invalidates the code after five wrong attempts*) |
| RB-03 MFA | — | `LoginControllerTest` (grupo `mfa`), `MfaServiceTest`, `Unit/Services/Mfa/TotpServiceTest` |
| RB-04 Tokens | — | `TokenApiControllerTest`, `KdsApiControllerTest` |
| RB-05 Cierre de sesión | — | `LoginControllerTest` (*signs out…*) |
| RB-06 Multisesiones | — | `SesionControllerTest` (*denies access to a device whose session was closed remotely*), `Unit/Services/SeguridadUnitTest` |
| RB-07 a RB-10 Recuperación (4 canales) | — | `RecuperacionPasswordControllerTest` (correo con `Mail::fake`, SMS y llamada con Twilio Verify y `ClienteHttpTwilioSimulado`, pregunta secreta) + envío real por SMTP de Mailtrap (§4.7) |
| RB-11 Autorización | CP-02, CP-11 | `EnsureRoleTest`, `OrdenControllerTest`, `TareaControllerTest`, `InventarioServiceTest`, `UsuarioControllerTest` |
| RB-12 OWASP | CP-13 | `SecurityHeadersTest` (XSS, CSRF, encabezados), `EnsureUserIsActiveTest`, `MfaServiceTest` y `SeguridadControllerTest` (secretos con hash), `UsuarioControllerTest` (hashes no expuestos) |
| RB-17 Pruebas unitarias | — | `tests/Unit/*` (47) |
| Procesos críticos (README P1) | CP-03 a CP-06, CP-08 a CP-10, CP-12 | `Services/Ventas/*`, `AsistenciaControllerTest`, `TareaControllerTest`, `ModelosTest`, `RequerimientosServiceTest` |

### 4.7. Ejecuciones registradas

| Fecha | Comando | Resultado |
| :--- | :--- | :--- |
| 2026-11-10 | `php artisan test --compact` (inicio) | 1 falla (`ExampleTest`: 302 ≠ 200), 1 pasa |
| 2026-11-10 | `php artisan route:list --except-vendor` | Falla: no existe `Admin\UsuarioController` |
| 2026-11-10 | `php artisan test --compact` (`1d0fd37`) | 106 pasan, 0 fallan (322 aserciones) |
| 2026-11-10 | `php artisan test --compact` (`d8534d9`) | 134 pasan, 0 fallan (422 aserciones) |
| 2026-11-10 | Capturas Edge headless (1920×1080 y 500 px) | Sin elementos encimados; POS y KDS se reacomodan en una columna en celular |
| 2026-11-11 | `php artisan route:list --except-vendor` (`43aa784`) | Funciona: 51 rutas |
| 2026-11-11 | `php artisan test --compact` (`6412274`) | Primera ejecución: 4 fallas → corregidas (2 aserciones mal planteadas, regla de usuario, prueba de token); final **212 pasan, 0 fallan** (607 aserciones) |
| 2026-11-11 | `php artisan test --compact --testsuite=Unit` | **47 pasan** (57 aserciones) |
| 2026-11-11 | `php artisan test --compact --testsuite=Feature` | **165 pasan** (550 aserciones) |
| 2026-11-11 | `npm run build` | Compila sin errores |
| 2026-11-11 | `php artisan migrate:fresh --seed` en MySQL `cafesync` | 7 migraciones y 4 seeders correctos; tipos verificados en `information_schema`: `bigint unsigned`, `decimal(10,2)`, `decimal(12,3)`, `int unsigned`; 26 llaves foráneas |
| 2026-11-11 | Suite en MySQL `cafesync_pruebas` (primera ejecución) | **43 fallan, 169 pasan**: `Data too long for column 'folio'` (D-17) |
| 2026-11-11 | Suite en MySQL tras corregir D-17 (`74a463f`) | **212 pasan, 0 fallan** |
| 2026-11-11 | Concurrencia: 6 procesos `tinker` en paralelo cierran la misma orden en MySQL | 6 terminan sin error; un solo descuento (2 movimientos); orden cerrada |
| 2026-11-11 | Envío real de recuperación por correo (`RecuperacionPasswordService::solicitar('gerente', correo)`) con SMTP de Mailtrap | El servidor SMTP aceptó el mensaje; la solicitud quedó vigente; sin errores en el log. La recepción en la bandeja se confirma en M-07 |
| 2026-11-11 | `php artisan test --compact` y suite en MySQL (`20e8093`) | **217 pasan, 0 fallan** (650 aserciones) en ambos gestores |
| 2026-11-23 | Suite antes de integrar el Sprint 2 en `main` | **217 pasan, 0 fallan** |
| 2026-11-23 | Suite en `feature/kds-administracion-estaciones`, SQLite y MySQL; `npm run build` | **232 pasan, 0 fallan** (689 aserciones) en ambos gestores; compila sin errores |
| 2026-11-23 | `node tests/Rendimiento/medir-kds.mjs --ordenes=20` | 20/20 bajo 1 s; p95 828 ms; máximo 831 ms; 20 órdenes de prueba canceladas |
| 2026-11-23 | `node tests/Rendimiento/medir-kds.mjs --ordenes=30` | 30/30 bajo 1 s; p95 821 ms; máximo 841 ms; 30 órdenes de prueba canceladas; código de salida 0 |
| 2026-11-23 | `node tests/Rendimiento/probar-reconexion-kds.mjs --desconexion=6000` | 6/6 verificaciones pasan; recuperación 4565 ms después de volver la red; orden de prueba cancelada |
| 2026-11-23 | `node tests/Rendimiento/medir-kds.mjs --ordenes=5` (tras mover el código común a `escenario-kds.mjs`) | 5/5 bajo 1 s; p95 807 ms |
| 2026-11-23 | `probar-reconexion-kds.mjs` ×3 con la corrección de D-19 | 6/6 en cada ejecución; recuperación 288, 267 y 286 ms |
| 2026-11-23 | `node tests/Rendimiento/probar-flujo-kds.mjs` | 7/7 verificaciones pasan; código de salida 0. Se mostró el aviso de limpieza del perfil temporal (D-20 sigue ocurriendo en forma de aviso) |
| 2026-11-23 | Primer intento de terminar Edge con `taskkill /T` sobre el PID lanzado | El script no terminó (se detuvo con `timeout`: código 124) y Edge siguió activo con 13 procesos del perfil; causa: el PID era del lanzador, no del navegador |
| 2026-11-23 | Suite completa en `main` tras integrar todas las ramas del Sprint 3-4 | **232 pasan, 0 fallan** (689 aserciones) en SQLite y en MySQL; 47 unitarias |
| 2026-11-23 | Corrección final de D-20: `probar-flujo-kds.mjs` ×3, `probar-reconexion-kds.mjs`, `medir-kds.mjs --ordenes=5` | 7/7, 7/7, 7/7, 6/6 y p95 666 ms; todos con código 0, sin avisos, 0 perfiles y 0 procesos de Edge de prueba restantes |
| 2026-11-23 | Con la corrección de D-20: `medir-kds.mjs --ordenes=3` ×3 y `probar-reconexion-kds.mjs --desconexion=3000` | Sin errores ni avisos de limpieza; p95 572, 527 y 749 ms; reconexión 6/6; sin perfiles temporales restantes |
| 2026-11-23 | `medir-kds.mjs --ordenes=10` con la corrección de D-19; `php artisan test --filter=Kds` | Mediana 528 ms, p95 841 ms; 20 pruebas PHP del KDS pasan. El script terminó con error al borrar el perfil temporal de Edge (`EPERM`) después de medir y cancelar: D-20 |

### 4.8. Pruebas manuales pendientes del Sprint 2 (las ejecuta el responsable)

Las pruebas automatizadas no ejecutan JavaScript en un navegador real. Antes de cerrar formalmente el Sprint 2:

| # | Prueba | Pasos | Esperado | Resultado |
| :---: | :--- | :--- | :--- | :--- |
| M-01 | Login en navegador | `php artisan migrate:fresh --seed` → `composer run dev` → entrar como `gerente` | Abre el panel | Pendiente |
| M-02 | Carrito del POS | Como `cajero`, tocar 2 productos, cambiar cantidades, elegir Drive-Thru, enviar | Mensaje «Orden … enviada»; aparece en Órdenes con su canal | Pendiente |
| M-03 | Reloj con teclado táctil | En Reloj checador, PIN 1111 con el teclado → Entrada | «Entrada registrada para Carlos…»; aparece en Asistencias del gerente | Pendiente |
| M-04 | Multisesión con dos navegadores | Entrar como `cajero` en dos navegadores; cerrar uno desde Seguridad de la cuenta | El otro navegador vuelve al login al navegar | Pendiente |
| M-05 | MFA con app real | Configurar con Google/Microsoft Authenticator y volver a entrar | Pide el código y lo acepta | Pendiente |
| M-06 | Alta de usuario | Como gerente, crear un empleado con PIN y entrar con él | Entra y registra asistencia con su PIN | Pendiente |
| M-07 | Correo en Mailtrap | Abrir la bandeja del sandbox de Mailtrap | Correo «Código para recuperar tu contraseña de CaféSync» para `gerente@cafesync.test` del 2026-11-11; repetir desde «¿Olvidaste tu contraseña?» y completar el cambio con el código | Pendiente |
| M-08 | SMS y llamada reales | Regenerar la API Key en Twilio, llenar `TWILIO_API_KEY` y `TWILIO_API_SECRET` en `.env`, registrar un teléfono verificado en «Seguridad de la cuenta» y recuperar por SMS y por llamada | Llega el código, se acepta y se cambia la contraseña | **Bloqueado** hasta regenerar la API Key |

---

## 5. Matriz de cumplimiento

### 5.1. Requisitos funcionales

| ID | Requisito | Prio | Sprint | Estado | Evidencia | Pendiente |
| :--- | :--- | :---: | :---: | :--- | :--- | :--- |
| RF-01 | Autenticación y roles | P1 | 2 | **CUMPLIDO** (automático) | `LoginControllerTest`, `EnsureRoleTest`, `UsuarioControllerTest` | M-01, M-06 |
| RF-02 | Clasificación por canal | P1 | 2 | **CUMPLIDO** (automático) | CP-05 | M-02 |
| RF-03 | Captura de venta | P1 | 2 | **CUMPLIDO** (automático) | `PosControllerTest`, `OrdenServiceTest` | M-02 |
| RF-04 | Enrutamiento por estación | P1 | 3-4 | **CUMPLIDO** (automático) | CP-06; administración de estaciones (`EstacionControllerTest`) | Prueba manual en navegador |
| RF-05 | Actualización del KDS | P1 | 3-4 | **CUMPLIDO** | `KdsControllerTest`; CP-07 medido en navegador real; cambio a «lista» desde los botones y persistencia al recargar (`probar-flujo-kds.mjs`) | Repetir en red local |
| RF-06 | Help Mode | P1 | 3-4 | **CUMPLIDO** | CP-08 automático y en Edge real | — |
| RF-07 | Configuración de recetas | P1 | 5 | PARCIAL (adelantado) | `RecetaServiceTest` | Pantalla de recetas |
| RF-08 | Descuento por receta | P1 | 5 | PARCIAL (adelantado) | CP-09, CP-10 (incluida concurrencia en MySQL) | Se cierra en el Sprint 5 con la pantalla de inventario |
| RF-09 | Inventario administrativo | P1 | 5 | PARCIAL (adelantado) | `InventarioServiceTest` | Pantallas, CP-11 |
| RF-10 | Registro de asistencia | P1 | 2 | **CUMPLIDO** (automático) | CP-03, CP-04, `AsistenciaReporteControllerTest` | M-03 |
| RF-11 | Asignación y cumplimiento de tareas | P1 | 5 | PARCIAL (adelantado) | `TareaControllerTest` | Asignación desde `/admin` |
| RF-12 | Protección de reportes y administración | P1 | 2 | **CUMPLIDO** para las rutas del Sprint 2 | `EnsureRoleTest` | Repetir al habilitar cada ruta nueva |
| RF-13 | Reporte de ventas | P2 | 6 | PARCIAL | `ReporteVentasService` | Pantalla, CP-15 |
| RF-14 | Aviso de bajo inventario | P2 | 5 | PARCIAL | `DashboardControllerTest` | — |
| RF-15 | Reconexión / contingencia | P2 | 3-4 | PARCIAL | CP-14 pasa: aviso, recuperación en ~0.3 s sin recargar y sin duplicados | Impresión de tickets de contingencia: no hay hardware compatible (README §6) |

### 5.2. Requisitos no funcionales

| ID | Atributo | Estado | Evidencia / pendiente |
| :--- | :--- | :--- | :--- |
| RNF-01 | Actualización < 1 s | **CUMPLIDO** en el entorno de prueba | 50 órdenes medidas con navegador real: p95 ≈ 0.83 s, máximo 0.84 s (CP-07). Pendiente repetir con dispositivos en red local |
| RNF-02 | Seguridad por rol | **CUMPLIDO** (Sprint 2) | `EnsureRoleTest`: acceso desde la interfaz y por ruta directa |
| RNF-03 | Usabilidad táctil 1920×1080 | PARCIAL | Controles de 44–64 px; capturas a 1920×1080 y 500 px; falta pantalla táctil real |
| RNF-04 | Integridad transaccional | **CUMPLIDO** | Reversión ante falla y repetición probadas en SQLite y MySQL; 6 cierres simultáneos en MySQL producen un solo descuento |
| RNF-05 | Consistencia de unidades | **CUMPLIDO** (lógica) | `UnidadMedidaTest`, `InventarioServiceTest`, `RecetaServiceTest` contra cálculo manual |
| RNF-06 | Validación de datos | **CUMPLIDO** | CP-13; mensajes en español |
| RNF-07 | Confidencialidad de credenciales | **CUMPLIDO** | Contraseña, PIN, respuesta y códigos con hash; `mfa_secret` cifrado; probado que no aparecen en vistas ni en claro en la BD |
| RNF-08 | Disponibilidad de red | **CUMPLIDO** | CP-14: el usuario recibe una indicación clara al perder la comunicación y la pantalla se recupera sola |
| RNF-09 | Mantenibilidad | PARCIAL | Lógica en servicios, controladores delgados |
| RNF-10 | Rendimiento de consulta | PENDIENTE | Índices definidos; sin medición |

### 5.3. MVP (README §7.1)

| # | Funcionalidad P1 | Estado |
| :---: | :--- | :--- |
| 1 | Acceso con roles y reloj checador | **CUMPLIDO** (automático); M-01, M-03, M-06 pendientes |
| 2 | Punto de Venta básico | **CUMPLIDO** (automático); M-02 pendiente |
| 3 | Pantallas KDS por zona | **CUMPLIDO** (automático + CP-07 y CP-14 en navegador real) |
| 4 | Inventario por receta | PARCIAL (faltan pantallas y MySQL) |
| 5 | Separación de permisos | **CUMPLIDO** para el Sprint 2 |
| 6 | Datos base de prueba | **CUMPLIDO** (seeders en MySQL) |

### 5.4. Criterios de la rúbrica

| ID | Criterio | Estado | Evidencia | Pendiente |
| :--- | :--- | :--- | :--- | :--- |
| RB-01 | Login con usuario o correo | **CUMPLIDO** (automático) | §4.6 | M-01 |
| RB-02 | Intentos repetidos | **CUMPLIDO** | Bloqueo de login, PIN y código | — |
| RB-03 | MFA real | **CUMPLIDO** (automático) | Desafío obligatorio, RFC 6238, anti-reutilización | M-05; QR (D-11) |
| RB-04 | Tokens distintos de la sesión | **CUMPLIDO** | Emisión, permisos, vencimiento y revocación probados con token real | — |
| RB-05 | Cierre de sesión seguro | **CUMPLIDO** | `logout` + `invalidate` + `regenerateToken` | — |
| RB-06 | Multisesiones y cierre remoto | **CUMPLIDO** (automático) | Cookie revocada pasa de 200 a 401 | M-04 |
| RB-07 | Recuperación por correo | **CUMPLIDO en sandbox** | Flujo probado con `Mail::fake`; envío real aceptado por el SMTP de Mailtrap | M-07; SMTP productivo (pendiente por decisión del responsable) |
| RB-08 | Recuperación por pregunta | **CUMPLIDO** | Flujo completo | — |
| RB-09 | Recuperación por SMS | BLOQUEADO | Twilio Verify con SDK oficial; flujo probado con cliente HTTP simulado | Regenerar la API Key (M-08) |
| RB-10 | Recuperación por llamada | BLOQUEADO | Twilio Verify con SDK oficial (canal `call`); probado con cliente simulado | Regenerar la API Key (M-08) |
| RB-11 | Autorización en el backend | **CUMPLIDO** para el Sprint 2 | `EnsureRoleTest` y pruebas por rol | Repetir con cada ruta nueva |
| RB-12 | OWASP | PARCIAL | XSS, CSRF, encabezados, hashes, cuentas inactivas, errores propios | `SESSION_SECURE_COOKIE`/`SESSION_ENCRYPT` en producción; revisión final (Sprint 6) |
| RB-13 | 2–3 patrones de diseño | PARCIAL | Observer, Repository y Strategy implementados y documentados en README §13.6 | Revisión final |
| RB-14 | Interfaz atractiva y responsiva | PARCIAL | Sistema visual, componentes, capturas | Pantallas de los sprints 3 a 6; pantalla táctil real |
| RB-15 | GitHub Flow, PR, protección | PARCIAL / BLOQUEADO | Una rama por tarea con prefijos del README, commits semánticos, merges `--no-ff`, plantilla de PR con lista de revisión (`.github/pull_request_template.md`) | Crear PR y proteger `main` requiere una cuenta de `gh` con permiso de escritura (§11.4) |
| RB-16 | Metodología y riesgos | PARCIAL | Este documento | Mantener por sprint |
| RB-17 | Pruebas verificables (incluidas unitarias) | PARCIAL | 212 pruebas (§4) | CP-07, CP-14, CP-15; MySQL |
| RB-18 | README refleja el estado real | PARCIAL | README actualizado al cierre del Sprint 2 (estado, ejecución, ampliaciones, decisiones, patrones) | Actualizar en cada sprint |
| RB-19 | MySQL | **CUMPLIDO** | Base `cafesync` migrada y sembrada; 217 pruebas pasan en MySQL; concurrencia verificada | — |
| RB-20 | `.env.example` sin secretos | **CUMPLIDO** | `e4a1de7`; `.env` ignorado | — |

---

## 6. Defectos

| ID | Severidad | Defecto | Estado |
| :--- | :--- | :--- | :--- |
| D-01 | Crítico | `routes/web.php` referenciaba 11 controladores inexistentes; `route:list` fallaba | **Corregido** (`43aa784` + `44d8524`): 3 controladores del Sprint 2 creados; el resto de rutas comentadas con su sprint |
| D-02 | Crítico | No existían vistas Blade | **Corregido** para el Sprint 2 (`983c53b`, `44d8524`); las vistas de sprints posteriores se crearán en su sprint |
| D-03 | Alto | Twilio sin configuración | **Corregido** (`e4a1de7`) |
| D-04 | Alto | Sin pruebas de dominio | **Corregido** (`1d0fd37`, `6412274`) |
| D-05 | Alto | SQLite en lugar de MySQL | **Corregido** (2026-11-11): `.env` local apunta a MySQL 8.0.46; la suite corre en ambos gestores |
| D-06 | Medio | Trabajo sin commitear; sin `push` ni PR | Parcial: todo commiteado e integrado en `main` local (`cf23603`); falta publicar y abrir PR (§11.4) |
| D-07 | Medio | `.env.example` desactualizado | **Corregido** (`e4a1de7`) |
| D-08 | Medio | Tokens sin vencimiento | **Corregido** (`e4a1de7`) |
| D-09 | Medio | El empleado puede cancelar órdenes no cerradas | **Resuelto por decisión del responsable** (2026-11-11): es el comportamiento esperado; prueba *lets an employee cancel an order that is not closed yet* (`20e8093`) |
| D-10 | Bajo | Mensajes de validación en inglés | **Corregido** (`911abbf`) |
| D-11 | Bajo | MFA sin QR | Requiere decisión (§8-5); se ofrece clave manual y enlace `otpauth://` |
| D-12 | Bajo | PIN fallido detectado por texto del mensaje | **Corregido** (`e4a1de7`) |
| D-13 | Bajo | `welcome.blade.php` sin uso | **Corregido** (`911abbf`) |
| D-14 | Bajo | Sesiones vacías si el driver no es `database` | **Corregido** (`983c53b`): la pantalla lo explica |
| D-15 | Alto | Menú del gerente enlazaba pantallas con error 500 | **Corregido** (`43aa784`): el menú solo muestra rutas registradas |
| D-16 | Bajo | Un error de `password_actual` se repetía en todos los formularios | **Corregido** (`5b55a44`): *error bags*; prueba *shows a form error only in the form that caused it* |
| D-17 | Crítico | En MySQL ninguna orden se guardaba: el folio temporal `TMP-<uuid>` (40 caracteres) excedía `ordenes.folio` VARCHAR(30). SQLite no valida la longitud | **Corregido** (`74a463f`): folio temporal `TMP<ULID>` de 29 caracteres; detectado al correr la suite en MySQL |
| D-19 | Bajo | Al volver la red, el KDS tardaba hasta ~6 s en recuperarse porque esperaba el siguiente reintento (la espera crece de 1.6 s a 10 s) | **Corregido** (rama `fix/kds-recuperacion-inmediata`): la pantalla escucha `offline`/`online` y consulta de inmediato al volver la red; recuperación medida 267–290 ms en 3 ejecuciones de CP-14 (antes 4565 ms) |
| D-20 | Bajo | Los scripts de `tests/Rendimiento` terminaban con error, o dejaban Edge abierto y su carpeta temporal, porque el ejecutable lanzado solo es un **lanzador**: el navegador real tiene otro PID y sus procesos hijos retienen el perfil | **Corregido** en dos pasos: `fix/scripts-kds-limpieza-perfil` (el fallo de limpieza ya no falla la prueba) y `fix/scripts-kds-cerrar-procesos` (se cierra el WebSocket y se terminan solo los procesos de Edge que usan ese perfil). Un intento intermedio con `taskkill /T` sobre el PID del lanzador no funcionó y dejó el script sin terminar; se descartó. Verificado: 5 ejecuciones seguidas con código 0, sin avisos, sin perfiles ni procesos restantes |
| D-21 | Alto | `composer run dev` fallaba en Windows: lanzaba `php artisan pail`, que exige la extensión `pcntl` (inexistente en PHP para Windows), y por `--kill-others` se apagaban también el servidor, la cola y Vite. Lo reportó el responsable al preparar las pruebas manuales | **Corregido** (rama `fix/composer-dev-windows`): se quitó Pail del script; verificado: servidor 200, cola y Vite activos, estilos servidos por Vite (200) |
| D-18 | Medio | `composer audit` reporta 8 avisos de seguridad en 5 paquetes Symfony que trae Laravel (`http-foundation`, `http-kernel`, `mime`, `routing`, `yaml`); ninguno en `twilio/sdk` | Abierto — actualizar dependencias requiere aprobación (§8.2-9) |

---

## 7. Tablero

### Terminado

| ID | Tarjeta | Sprint | Evidencia |
| :--- | :--- | :---: | :--- |
| B-00 | Especificación (README v2) | 0 | `448cd3b` |
| B-14 | Commit del Sprint 2 en commits semánticos | 2 | `43b2f4d` … `cd1f496` |
| B-16 | Base de pruebas | 2 | `1d0fd37` |
| B-17 | Sistema visual, layouts y componentes | 2 | `911abbf` |
| B-25 | Configuración Twilio, `.env.example`, vencimiento de tokens | 2 | `e4a1de7` |
| B-26 | Emisión y revocación de tokens | 2 | `44d8524`; `TokenApiControllerTest` |
| B-27 | Traducción `es` | 2 | `911abbf` |
| B-33 | Administración de usuarios y roles | 2 | `44d8524`; `UsuarioControllerTest`, `EnsureRoleTest` |
| B-34 | Reporte de asistencias del gerente | 2 | `44d8524`; `AsistenciaReporteControllerTest` |
| B-35 | Pruebas unitarias de reglas de negocio | 2 | `6412274`; 47 pruebas |
| B-15 | MySQL local y suite en MySQL | 2 | `74a463f`; 217 pruebas en MySQL; concurrencia CP-10 |
| B-36 | Administración de estaciones | 3-4 | `EstacionControllerTest` (9), 232 pruebas en SQLite y MySQL |
| B-23 | Medición del tiempo del KDS en navegador real | 3-4 | `tests/Rendimiento/medir-kds.mjs`; 50/50 bajo 1 s |
| B-28 | Prueba de falla de red del KDS | 3-4 | `tests/Rendimiento/probar-reconexion-kds.mjs`; 6/6 |
| B-07 | Help Mode | 3-4 | CP-08 en Edge real (`probar-flujo-kds.mjs`) |
| B-41 | Flujo de preparación en navegador real | 3-4 | `tests/Rendimiento/probar-flujo-kds.mjs`; 7/7 |
| B-40 | Twilio Verify con SDK oficial | 2 | `ded5191`; 7 pruebas con cliente simulado |

### En revisión/pruebas (falta verificación manual o un bloqueo externo)

| ID | Tarjeta | Sprint | Falta |
| :--- | :--- | :---: | :--- |
| B-01 | Modelo relacional, migraciones y seeders | 1 | — (verificado en MySQL el 2026-11-11; pasa a *Terminado* al cerrar el Sprint 2) |
| B-02 | Login, logout y bloqueo de intentos | 2 | M-01 |
| B-03 | Autorización por rol | 2 | M-06 |
| B-04 | Reloj checador con PIN | 2 | M-03 |
| B-05 | POS con canal de origen | 2 | M-02 |
| B-10 | MFA | 2 | M-05 |
| B-11 | Multisesiones | 2 | M-04 |
| B-12 | Recuperación de contraseña | 2 | M-07 (bandeja de Mailtrap) y M-08 (API Key de Twilio) |

### Adelantado (existe; se cierra en su sprint)

| ID | Tarjeta | Sprint | Falta |
| :--- | :--- | :---: | :--- |
| B-06 | KDS por estación, polling y API | 3-4 | Repetir CP-07 con dispositivos en red local |
| B-08 | Cierre de venta e inventario por receta | 5 | MySQL concurrente, pantallas de ingredientes y movimientos |
| B-09 | Tareas del empleado | 5 | Plantillas y asignación desde `/admin` |
| B-13 | Reporte de ventas (servicio) | 6 | Pantalla, exportación, CP-15 |

### Pendiente

| ID | Tarjeta | Sprint | Depende de |
| :--- | :--- | :---: | :--- |
| B-31 | `push`, PR, plantilla de PR y protección de `main` | 2 (bloqueado) | `gh auth login` (§8-6) |
| B-37 | Administración de productos y recetas | 5 | Sprints 3-4 |
| B-38 | Administración de ingredientes y movimientos | 5 | Sprints 3-4 |
| B-39 | Plantillas y asignación de tareas | 5 | Sprints 3-4 |
| B-30 | Recurrencia y revisión de tareas (P2) | 5 | B-39 |
| B-29 | Reporte y exportación de ventas (P2) | 6 | — |
| B-24 | Casos integrales y demostración | 6 | Todo lo anterior |

---

## 8. Decisiones

### 8.1. Decisiones tomadas

| Fecha | Decisión | Motivo | Impacto |
| :--- | :--- | :--- | :--- |
| 2026-11-03 | `usuarios` con PK `id_usuario` como único modelo de autenticación; ampliaciones del diccionario | README §23.2 y requisitos de la rúbrica | Documentado en README §23.16 |
| 2026-11-10 | Cierre solo desde «lista» o «entregada»; no se crea ni cierra una orden sin receta; el stock insuficiente bloquea la venta; una venta cerrada solo se cancela por reversión del gerente | Resuelve el «Punto por confirmar» del README §11 | Documentado en README §11.4 |
| 2026-11-10 | KDS con consulta periódica de 800 ms | README §24.4: probar primero el polling | Falta medir (CP-07) |
| 2026-11-10 | MFA con TOTP propio, sin dependencias; sin QR | No cambiar dependencias sin aprobación | D-11 |
| 2026-11-10 | JS en el bundle de Vite y fuentes del sistema | CSP `script-src 'self'` | Sin scripts en línea |
| 2026-11-10 | Trabajar en SQLite hasta tener credenciales de MySQL | Bloqueo externo | D-05 |
| 2026-11-11 | Rutas de administración de sprints posteriores comentadas con su sprint | Respetar la planificación y que `route:list` funcione | §2 |
| 2026-11-11 | El gerente no puede quitarse su rol ni desactivar su propia cuenta | Evitar quedarse sin administrador | `UsuarioControllerTest` |
| 2026-11-11 | Desactivar una cuenta o cambiarle la contraseña cierra sus sesiones y revoca sus tokens | Que la revocación tenga efecto real | `UsuarioControllerTest` |
| 2026-11-11 | El nombre de usuario acepta letras, números, punto, guion y guion bajo | Los datos existentes usan punto | `UsuarioController` |
| 2026-11-11 | MySQL local con el usuario del responsable; base `cafesync_pruebas` separada para la suite | Indicación del responsable | Credenciales solo en `.env` |
| 2026-11-11 | Correo de pruebas con el sandbox SMTP de Mailtrap; el SMTP productivo queda para después | Indicación del responsable | RB-07 en sandbox |
| 2026-11-11 | SMS y llamada con **Twilio Verify** mediante el SDK oficial `twilio/sdk`, autenticado con API Key; el código lo genera y valida Twilio | Indicación del responsable | Se reemplazó la integración anterior con la API de mensajes y llamadas |
| 2026-11-11 | No usar la API Key expuesta: `TWILIO_API_KEY` y `TWILIO_API_SECRET` quedan vacíos hasta regenerarlos | Indicación del responsable | Canales SMS y llamada «no disponibles» hasta entonces |
| 2026-11-11 | El empleado puede cancelar órdenes que aún no se cierran (D-09) | Decisión del responsable | README §11.4 |
| 2026-11-23 | Los commits solo muestran la identidad Git del responsable; se quitó del historial la línea `Co-Authored-By` agregada por el asistente | Indicación del responsable | Historial reescrito (§11.3) |
| 2026-11-23 | Una rama por tarea, creada desde `main`, con los prefijos del README §26.1 e integrada con `--no-ff` | Indicación del responsable; GitHub Flow del README | §11.1 |
| 2026-11-23 | Cerrar el Sprint 2 con pendientes trasladados e iniciar el Sprint 3-4 | El responsable indicó continuar con el desarrollo | §3.3, §3.4 |
| 2026-11-23 | Se conserva la consulta periódica de 800 ms y **no se incorpora Reverb** | La medición cumple RNF-01 (README §24.4: evaluar Reverb solo si el polling no alcanza la meta) | README §24.5 |
| 2026-11-23 | La medición del KDS es un script de Node sin dependencias, en `tests/Rendimiento/`, y no forma parte de la suite Pest | Necesita un navegador real y la aplicación corriendo; no se agregan herramientas de prueba nuevas | §4.2 |
| 2026-11-23 | Una estación con pendientes en órdenes en curso no puede desactivarse; las estaciones no se borran | Evitar que una preparación quede sin pantalla y conservar el historial (README §22.4) | README §11.4; `EstacionControllerTest` |

### 8.2. Decisiones pendientes del responsable

| # | Decisión | Por qué importa | Propuesta |
| :---: | :--- | :--- | :--- |
| 1 | ~~Credenciales de MySQL~~ | — | **Resuelto** el 2026-11-11 |
| 2 | SMTP productivo | Mailtrap es un sandbox: no entrega a buzones reales | Configurarlo cuando se decida el proveedor (pendiente por indicación del responsable) |
| 3 | Regenerar la API Key de Twilio | RB-09 y RB-10 bloqueados; la clave anterior se expuso | Crear una API Key nueva, revocar la expuesta y llenar `TWILIO_API_KEY` y `TWILIO_API_SECRET` solo en `.env` |
| 4 | ~~Cancelación de órdenes por el empleado (D-09)~~ | — | **Resuelto**: sí puede cancelarlas |
| 5 | QR para MFA (D-11) | Dependencia nueva | `bacon/bacon-qr-code` o mantener la clave manual |
| 6 | Acceso de `gh` | PR y protección de `main` | `gh auth login`; un segundo revisor obligatorio no puede cumplirse sin un revisor autorizado |
| 7 | `push` de `main` y de la rama del Sprint 2 | Copia remota y PR | PR de `feature/base-datos-modelos` y de `feature/autenticacion-seguridad` |
| 8 | Documento de la rúbrica 3 | Contrastar §4.1 con la rúbrica original | Agregar la rúbrica al repositorio o compartirla |
| 9 | Actualizar Symfony por avisos de seguridad (D-18) | Vulnerabilidades conocidas en dependencias | `composer update "symfony/*" --with-dependencies` y volver a correr la suite |

---

## 9. Análisis de riesgos (actualizado 2026-11-11)

| ID | Riesgo | Prob. | Impacto | Prevención | Mitigación | Contingencia | Estado |
| :--- | :--- | :---: | :---: | :--- | :--- | :--- | :--- |
| R-01 | Falla de red en hora pico | Media | Alto | Probar reconexión | Indicador y reintentos en el KDS (CP-14 pasa) | Comunicar la incidencia; ticket solo con impresora | **Mitigado y probado**; recuperación en ~0.3 s tras corregir D-19 |
| R-02 | Órdenes no llegan al KDS a tiempo | Media | Alto | Polling de 800 ms con huella | Medición con navegador real (CP-07): p95 ≈ 0.83 s | Reducir el intervalo o usar Reverb si la red local lo empeora | **Mitigado** en el entorno de prueba; repetir en red local |
| R-03 | Recetas o unidades incompatibles | Media | Alto | Unidad base por ingrediente | Validación en recetas y ajustes | Nueva versión y ajuste | Mitigado y probado |
| R-04 | Doble descuento | Media | Alto | Cierre transaccional e idempotente | Bloqueo, verificación e índice único | Revisar historial | Probado en SQLite; falta MySQL |
| R-05 | Empleado accede a funciones de gerente | Baja/Media | Alto | `rol:gerente` y verificaciones en servicios | `EnsureRoleTest` | Revocar sesión y corregir | **Mitigado y probado** para el Sprint 2 |
| R-06 | PIN compartido o ambiguo | Media | Medio | PIN único con índice HMAC | Límite de intentos | Reemplazar el PIN | Mitigado y probado |
| R-07 | Interfaz compleja | Media | Bajo/Medio | Controles grandes, etiquetas directas | Help Mode | Simplificar | Mitigado; falta prueba con usuarios |
| R-08 | Alcance mayor que la capacidad | Alta | Alto | Backlog P1/P2 | Solo trabajo del sprint en curso | Aplazar P2 | **Materializado** (trabajo adelantado); controlado con §2 |
| R-09 | Consultas lentas | Baja/Media | Alto | Índices | Carga ansiosa | Optimizar | Sin medición |
| R-10 | Asistencias o tareas no se guardan | Media | Medio | Transacciones | Pruebas de persistencia | Corregir | **Mitigado y probado** |
| R-11 | Proveedores externos no disponibles | Alta | Alto | Canales «no disponibles» sin credenciales | Fakes en pruebas; Mailtrap para correo | Declarar BLOQUEADO | Correo mitigado con Mailtrap; SMS y llamada abiertos hasta regenerar la API Key |
| R-12 | SQLite distinto de MySQL | Alta | Medio | Migraciones sin SQL de un motor | Suite también en MySQL | Pruebas críticas en MySQL antes de la entrega | **Materializado y mitigado**: D-17 solo aparecía en MySQL; la suite se ejecuta en ambos |
| R-13 | Pérdida del trabajo sin commitear | Media | Alto | Commits frecuentes | Todo commiteado | `push` | Mitigado; falta `push` |
| R-14 | Exposición de credenciales | Media | Alto | Secretos solo en `.env` (ignorado por Git); revisión del historial antes de publicar | Regenerar claves expuestas (Twilio) | Revocar y rotar | Abierto: la API Key de Twilio debe regenerarse; el historial de Git no contiene secretos (verificado el 2026-11-11) |
| R-15 | Dependencias con vulnerabilidades conocidas | Media | Medio | `composer audit` en cada sprint | Actualizar parches de Symfony (D-18) | Revisar avisos y aplicar mitigaciones | Abierto |

---

## 10. Bitácora de sesiones

| Fecha | Actividad | Resultado | Siguiente paso |
| :--- | :--- | :--- | :--- |
| 2026-11-10 | Auditoría inicial y diagnóstico: lectura del README, revisión del código, pruebas, `route:list`, `migrate:status`. Sin cambios de código. | Documento creado; 13 defectos; ningún criterio CUMPLIDO. | Commit del Sprint 2 y base de pruebas |
| 2026-11-10 | Incremento 1 del Sprint 2: 4 commits `feat:`, correcciones D-03, D-07, D-08, D-12, 106 pruebas. Se confirmó que el cierre remoto invalida una cookie real (200 → 401). | 106/106 pruebas pasan. | Sistema visual y pantallas |
| 2026-11-10 | Incremento 2 del Sprint 2: interfaz de los módulos operativos y 28 pruebas de pantallas; revisión con capturas de Edge headless. | 134/134 pruebas pasan; D-10, D-13 corregidos; D-15, D-16 registrados. | Administración |
| 2026-11-11 | Revisión de la planificación (README §17) frente al código: se documentó el trabajo adelantado y se decidió limitar el desarrollo al Sprint 2. Incremento 3: rutas por sprint, usuarios y roles, reporte de asistencias, tokens, errores por formulario, 47 pruebas unitarias y pruebas de seguridad. Reestructura de este documento y actualización del README. | 212/212 pruebas pasan; D-01, D-02, D-14, D-15, D-16 corregidos; hito del Sprint 2 cumplido con pruebas automatizadas. | El responsable ejecuta M-01 a M-06 (§4.8) y resuelve §8.2; después se cierra el Sprint 2 y se abre el Sprint 3-4 (KDS) |
| 2026-11-11 | Incremento 4 del Sprint 2 con datos del responsable: MySQL 8.0.46 (bases `cafesync` y `cafesync_pruebas`), Mailtrap y Twilio Verify con SDK oficial; confirmación de D-09. La suite en MySQL detectó D-17 (crítico) y se corrigió. Concurrencia del cierre de venta verificada con 6 procesos. Se comprobó que ningún secreto está en el historial de Git. | 217/217 pruebas pasan en SQLite y MySQL; RB-19, RNF-04 cumplidos; RB-07 en sandbox; D-05, D-09, D-17 resueltos; D-18 registrado. | Regenerar la API Key de Twilio; ejecutar M-01 a M-08; decidir D-18; cerrar el Sprint 2 |
| 2026-11-23 | A petición del responsable: se quitó la coautoría del asistente en los 21 commits del Sprint 2 (historial reescrito localmente, código idéntico) y se adoptó una rama por tarea. Merge local del Sprint 2 en `main` (`cf23603`). Rama `docs/flujo-ramas-y-cierre-sprint-2`: plantilla de PR, §11 de control de versiones, cierre del Sprint 2 y apertura del Sprint 3-4. | 217/217 pruebas pasan antes del merge. El `push --force` de la rama reescrita lo bloqueó el sistema de permisos del asistente: queda para el responsable (§11.4). | Sprint 3-4: administración de estaciones (rama `feature/kds-administracion-estaciones`) |
| 2026-11-23 | Sprint 3-4, rama `feature/kds-administracion-estaciones`: administración de estaciones (B-36) con la regla de no desactivar una estación con pendientes; listado con un solo conteo agregado, sin N+1; se habilitó su ruta comentada; 15 pruebas nuevas. | 232/232 pruebas pasan en SQLite y MySQL; RF-04 cumplido con pruebas automáticas. | Medición del tiempo del KDS (B-23, rama `test/kds-medicion-tiempo`) |
| 2026-11-23 | Sprint 3-4, rama `test/kds-medicion-tiempo`: script `tests/Rendimiento/medir-kds.mjs` (Edge real, protocolo DevTools, sin dependencias). Dos series: 20 y 30 órdenes. | 50/50 bajo 1 s; p95 828 y 821 ms. CP-07 pasa; RNF-01 y RF-05 cumplidos en el entorno de prueba; se descarta Reverb. | Reconexión del KDS (B-28, CP-14) |
| 2026-11-23 | Sprint 3-4, rama `test/kds-reconexion`: script de falla de red con Edge real y módulo común `escenario-kds.mjs`. | CP-14 pasa 6/6; RNF-08 cumplido; recuperación de 4.6 s registrada como D-19. | Recuperación inmediata al volver la red (D-19, rama `fix/kds-recuperacion-inmediata`) |
| 2026-11-23 | Rama `fix/kds-recuperacion-inmediata`: el KDS escucha `offline`/`online`; si una consulta está en curso al volver la red, la siguiente se agenda sin espera. | CP-14 6/6 en 3 ejecuciones; recuperación de 4565 ms a ~280 ms; latencia CP-07 sin cambios. Se detectó D-20. | Limpieza del perfil temporal de Edge (D-20) |
| 2026-11-23 | Rama `fix/scripts-kds-limpieza-perfil`: D-20 corregido; README §24.5 con el resultado de la falla de red. | 4 ejecuciones de los scripts sin error. | Pendientes trasladados y repetición en red local |
| 2026-11-23 | Rama `test/kds-flujo-preparacion`: script que pulsa «Iniciar», «Marcar lista» y «Receta» en Edge real. | 7/7; RF-05 y RF-06 cumplidos en navegador. El aviso de limpieza reapareció: D-20 pasa a parcial. | Terminar el árbol de procesos de Edge (D-20) |
| 2026-11-23 | Rama `fix/scripts-kds-cerrar-procesos`. El primer intento (`taskkill /T`) dejó colgado el script: se detuvo la tarea, se terminaron solo los procesos de prueba (perfil `cafesync-kds-`) y se encontró la causa (PID del lanzador). Corrección: cerrar el WebSocket y terminar los procesos de ese perfil. | D-20 corregido; 5 ejecuciones limpias. | Cierre de los Sprints 3-4 |
| 2026-11-23 | Rama `docs/cierre-sprints-3-4`: revisión de cierre del Sprint 3-4 con la suite final en `main` (232 en SQLite y MySQL). | Hito del Sprint 3-4 cumplido; pendiente la repetición en red local. | Confirmación del responsable para cerrar e iniciar el Sprint 5 |
| 2026-11-23 | El responsable reportó que `composer run dev` se detenía al iniciar (Pail requiere `pcntl`). Rama `fix/composer-dev-windows`: se quitó Pail del script `dev` y se documentó en README §27.5. | `composer run dev` verificado: servidor, cola y Vite activos; login 200 y estilos desde Vite. D-21 corregido. | Pruebas manuales M-01 a M-08 del responsable |

---

## 11. Control de versiones

### 11.1. Estrategia de ramas

Se sigue el GitHub Flow del README §26:

1. Cada tarea se hace en su propia rama, creada desde `main` y nombrada con el prefijo de su tipo: `feature/`, `fix/`, `docs/`, `test/` o `refactor/`.
2. Los commits usan los prefijos `feat:`, `fix:`, `docs:`, `test:` y `refactor:`, y solo llevan la identidad Git del responsable.
3. Antes de integrar se ejecutan la suite en SQLite y en MySQL, Pint y `npm run build`, y se revisa la lista de `.github/pull_request_template.md`.
4. La integración se hace con `git merge --no-ff` para que cada rama quede visible en la historia. Cuando haya una cuenta de `gh` con permiso de escritura, la integración será por Pull Request.
5. Cada rama actualiza `SEGUIMIENTO.md` y, si cambia el comportamiento, el README.

### 11.2. Ramas

| Rama | Tipo | Contenido | Estado |
| :--- | :--- | :--- | :--- |
| `feature/base-datos-modelos` | feature | Sprint 1: esquema, modelos y datos base | Integrada en `main` (`fcaf430`) |
| `feature/autenticacion-seguridad` | feature | Sprint 2 completo: acceso, seguridad, POS, interfaz, pruebas, MySQL, Mailtrap, Twilio Verify. **Concentró trabajo de varios temas en una sola rama**; desde el 2026-11-23 cada tema va en su propia rama | Integrada en `main` (`cf23603`) |
| `docs/flujo-ramas-y-cierre-sprint-2` | docs | Plantilla de PR, esta sección, cierre del Sprint 2 | Integrada en `main` (`24825c4`) |
| `feature/kds-administracion-estaciones` | feature | Sprint 3-4, B-36: administración de estaciones | Integrada en `main` (`b0eee18`) |
| `test/kds-medicion-tiempo` | test | Sprint 3-4, B-23: medición del tiempo del KDS (CP-07) | Integrada en `main` (`147eb66`) |
| `test/kds-reconexion` | test | Sprint 3-4, B-28: prueba de falla de red (CP-14) y módulo común de los scripts | Integrada en `main` (`72977c1`) |
| `fix/kds-recuperacion-inmediata` | fix | D-19: reconsultar en cuanto vuelve la red | Integrada en `main` (`32de233`) |
| `fix/scripts-kds-limpieza-perfil` | fix | D-20: limpieza robusta del perfil temporal de Edge; resultado de CP-14 en el README | Integrada en `main` (`9123876`) |
| `test/kds-flujo-preparacion` | test | Flujo de preparación y Help Mode en Edge real (hito de los Sprints 3-4) | Integrada en `main` (`5472137`) |
| `fix/scripts-kds-cerrar-procesos` | fix | D-20: cierre fiable de Edge en Windows | Integrada en `main` (`7027ffa`) |
| `docs/cierre-sprints-3-4` | docs | Revisión de cierre del Sprint 3-4 | Integrada en `main` (`a8a6a13`) |
| `fix/composer-dev-windows` | fix | D-21: `composer run dev` sin Pail para Windows | Integrada en `main` |

### 11.3. Reescritura del historial (2026-11-23)

A petición del responsable se eliminó de los mensajes de commit la línea `Co-Authored-By` que había agregado el asistente, para que solo aparezca su identidad de GitHub. Se usó `git filter-branch --msg-filter` en la rama del Sprint 2, se reacomodaron sus commits sobre el `main` real con `git rebase --committer-date-is-author-date` y se verificó que el árbol de archivos final es idéntico al anterior. Autor, fechas y contenido no cambiaron; los hashes sí:

| Hash anterior | Hash actual |
| :--- | :--- |
| `bf5ed8e` | `6c0c3e8` |
| `1088e83` | `b049465` |
| `bc4e14d` | `9949a22` |
| `a403fe2` | `ffbf0fa` |
| `a390b0c` | `f0c54db` |
| `a2d5de9` | `e677801` |
| `1504e5d` | `44d8524` |
| `dcf164c` | `43b2f4d` |
| `98d8418` | `911abbf` |
| `8790dc9` | `983c53b` |
| `83890fe` | `0625b48` |
| `b0d4a90` | `ded5191` |
| `72a4533` | `cd1f496` |
| `191f315` | `43aa784` |
| `76a5427` | `e4a1de7` |
| `e2405fc` | `74a463f` |
| `674b08f` | `5b55a44` |
| `aad47a7` | `d8534d9` |
| `270d1e4` | `1d0fd37` |
| `b74f734` | `20e8093` |
| `fc277e9` | `6412274` |

Se conserva localmente la etiqueta `respaldo/antes-de-quitar-coautoria` (no se publica) por si hiciera falta recuperar el estado previo.

### 11.4. Publicación en GitHub

| Elemento | Estado |
| :--- | :--- |
| `origin/feature/autenticacion-seguridad` | Contiene la versión **anterior** a la reescritura. Para publicar la nueva, el responsable debe ejecutar `git push --force-with-lease origin feature/autenticacion-seguridad`: el sistema de permisos del asistente bloquea los `push --force`. |
| `origin/main` | Sin el Sprint 1 ni el Sprint 2. Publicar con `git push origin main` cuando el responsable lo decida. |
| Pull Requests | No se pueden crear con la sesión actual de `gh` (cuenta `ElAxelYSuGit`, permiso de **lectura** sobre `AxelYYa/CafeSync`). Se requiere `gh auth login` con la cuenta propietaria. |
| Protección de `main` | No configurada (la API respondió 404). Requiere la cuenta propietaria. La aprobación obligatoria por un segundo revisor no puede cumplirse en un proyecto individual sin un revisor autorizado. |
