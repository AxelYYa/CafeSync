# Seguimiento del desarrollo de CaféSync

Documento de control del proyecto bajo el **Scrum adaptado** del README (§15–§17): backlog priorizado P1/P2, tablero de cuatro estados, registro por sprint, plan y resultados de pruebas, riesgos y bitácora.

> [!IMPORTANT]
> Este archivo registra **solo lo que se verificó**. Un elemento no pasa a *Terminado* ni a *CUMPLIDO* porque el código exista: debe cumplir su criterio de aceptación y contar con una prueba ejecutada (README §15.3). Es un desarrollo individual: no se documentan reuniones, participantes ni aprobaciones que no ocurrieron.

| Dato | Valor |
| :--- | :--- |
| Responsable (todas las funciones) | Axel Espinoza Blanco — desarrollo individual |
| Fecha de corte | 2026-11-11 |
| Sprint actual | **Sprint 2 — Acceso y POS** (revisión de cierre, §3.3) |
| Rama de trabajo | `feature/autenticacion-seguridad` |
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

### Convenciones

- **Tablero:** `Pendiente` → `En proceso` → `En revisión/pruebas` → `Terminado`.
- **Estado de un criterio:** **CUMPLIDO** (implementado y comprobado) · **PARCIAL** (existe, falta completar o verificar) · **PENDIENTE** (no implementado) · **BLOQUEADO** (requiere credenciales, permisos o servicios externos no disponibles).
- **Severidad de defectos:** Crítico (bloquea P1 o la seguridad) · Alto · Medio · Bajo.

---

## 1. Fotografía del repositorio (2026-11-11)

| Elemento | Estado |
| :--- | :--- |
| PHP / Laravel | PHP 8.2.12 (XAMPP) · Laravel 12.70.0 |
| Paquetes directos | `laravel/sanctum` 4.3.3, `laravel/tinker`; dev: Pest 3.8.7, Pint 1.30.4, Boost 2.10.3, Sail, Pail. **No se agregaron dependencias.** |
| Frontend | Blade + Tailwind CSS 4 + JavaScript modular compilado con Vite 7 (`resources/js/modulos/*`) |
| Base de datos activa | **SQLite** en desarrollo y en pruebas. `.env.example` ya apunta a MySQL; falta la base MySQL local (D-05) |
| Rutas | 51 rutas; `php artisan route:list` funciona desde `191f315` |
| Pruebas | 212 pruebas Pest (47 unitarias + 165 de integración), todas pasan (§4.7) |
| GitHub CLI | `gh` instalado pero **sin sesión iniciada** → sin PR ni protección de ramas consultables |

### Historial de Git

| Commit | Fecha | Descripción |
| :--- | :--- | :--- |
| `01098eb` · `40b6c0d` · `448cd3b` | 2026-10-19 | Proyecto inicial y README v1/v2 — **último commit en `origin/main`** |
| `f860f40` · `fcaf430` | 2026-11-03 | `feat:` modelo de datos, migraciones, enums y seeders; merge local en `main` (sin PR) |
| `dcf164c` | 2026-11-10 | `feat:` autenticación por usuario o correo, MFA TOTP, multisesiones y middleware de seguridad |
| `83890fe` | 2026-11-10 | `feat:` recuperación de contraseña por correo, pregunta secreta, SMS y llamada |
| `a2d5de9` | 2026-11-10 | `feat:` POS, KDS por estación, Help Mode, cierre de venta e inventario por receta |
| `72a4533` | 2026-11-10 | `feat:` reloj checador con PIN, tareas por turno, reporte de ventas y rutas web |
| `bf5ed8e` | 2026-11-10 | `docs:` documento de seguimiento |
| `76a5427` | 2026-11-10 | `fix:` Twilio, vencimiento de tokens, `.env.example` para MySQL, excepción de PIN inválido |
| `270d1e4` | 2026-11-10 | `test:` 106 pruebas de dominio, seguridad y endpoints |
| `a403fe2` | 2026-11-10 | `docs:` incremento 1 del Sprint 2 |
| `98d8418` | 2026-11-10 | `feat:` identidad visual, layouts, componentes Blade, páginas de error y traducción al español |
| `8790dc9` | 2026-11-10 | `feat:` pantallas de acceso, panel, POS, órdenes, KDS, Help Mode, reloj, tareas y seguridad |
| `aad47a7` | 2026-11-10 | `test:` renderizado y visibilidad por rol de las pantallas |
| `a390b0c` | 2026-11-10 | `docs:` incremento 2 del Sprint 2 |
| `191f315` | 2026-11-11 | `fix:` activar solo las rutas administrativas del Sprint 2 (D-01, D-15) |
| `674b08f` | 2026-11-11 | `fix:` errores separados por formulario en seguridad de la cuenta (D-16) |
| `1504e5d` | 2026-11-11 | `feat:` administración de usuarios y roles, reporte de asistencias y tokens de pantallas |
| `fc277e9` | 2026-11-11 | `test:` pruebas unitarias y de administración, protección de rutas y seguridad |

`main` local va 2 commits adelante de `origin/main` y `feature/autenticacion-seguridad` no está publicada (D-06).

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
- Las rutas de administración de sprints posteriores se **comentaron en `routes/web.php` con su sprint asignado** (`191f315`) y se habilitarán al crear sus controladores. Así `route:list` funciona y el menú no enlaza pantallas inexistentes.
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
| 3. Realizado | **Incremento 1** (2026-11-10): commit del backend existente en 4 commits semánticos; corrección de configuración; 106 pruebas. **Incremento 2** (2026-11-10): sistema visual, layouts, 17 vistas operativas, páginas de error, JS modular y traducción `es`. **Incremento 3** (2026-11-11): administración de usuarios y roles, reporte de asistencias, tokens de pantallas, rutas por sprint, errores por formulario y 47 pruebas unitarias. |
| 4. Funcionalidades | **Acceso:** login por usuario o correo, bloqueo por intentos, logout seguro, MFA TOTP con códigos de recuperación, multisesiones con cierre remoto, recuperación por correo, pregunta, SMS y llamada, tokens Sanctum con permisos y vencimiento. **Roles:** alta y edición de usuarios, cambio de rol, activación y desactivación (cierra sesiones y revoca tokens), protección `rol:gerente`. **Reloj checador:** registro con PIN y hora del servidor, teclado táctil, historial por periodo y empleado con jornadas y horas. **POS:** catálogo táctil por estación, canal de origen, orden con precio del servidor, listado de órdenes con entrega, cierre y cancelación. |
| 5. Archivos | Controladores: `app/Http/Controllers/Auth/*`, `Cuenta/*`, `Admin/UsuarioController.php`, `Admin/AsistenciaReporteController.php`, `Admin/TokenApiController.php`, `PosController.php`, `OrdenController.php`, `AsistenciaController.php`, `DashboardController.php`. Servicios: `app/Services/Mfa/*`, `Sesiones/*`, `Recuperacion/*`, `Usuarios/UsuarioService.php`, `Asistencia/*`, `Ventas/OrdenService.php`. Middleware: `app/Http/Middleware/*`. Vistas: `resources/views/auth/*`, `cuenta/*`, `admin/usuarios/*`, `admin/asistencias/*`, `admin/tokens/*`, `pos/*`, `asistencia/*`, `dashboard/*`, `components/*`, `errors/*`. Otros: `routes/web.php`, `routes/api.php`, `config/services.php`, `config/sanctum.php`, `.env.example`, `lang/es/*`, `resources/css/app.css`, `resources/js/*`. |
| 6. Pruebas | **212 pruebas pasan, 0 fallan** (607 aserciones): 47 unitarias y 165 de integración. Detalle en §4. Pasan CP-01, CP-02 (rutas del Sprint 2), CP-03, CP-04, CP-05 y CP-13. |
| 7. Problemas y solución | D-01/D-15 (rutas a controladores inexistentes): rutas separadas por sprint. D-16 (un error se repetía en varios formularios): *error bags*. Usuarios con punto en el nombre rechazados por `alpha_dash`: se cambió a una expresión que acepta letras, números, punto, guion y guion bajo. Una prueba de sesiones no enviaba cookies: se usó `withCredentials()` y se reinició el singleton `StartSession`. Edge headless recorta a 390 px pero usa 496 px de viewport: las capturas de celular se toman a 500 px. |
| 8. Pendientes | (a) MySQL local — BLOQUEADO por credenciales (B-15). (b) Envío real de correo, SMS y llamada — BLOQUEADO por proveedores (RB-07, RB-09, RB-10). (c) `push`, PR y protección de `main` — BLOQUEADO por `gh auth login` (B-31). (d) Verificaciones manuales en navegador y pantalla táctil (§4.8). (e) Decisión sobre la cancelación de órdenes por el empleado (D-09). |
| 9. Evidencia | §4.5 (CP), §4.6 (matriz rúbrica ↔ pruebas), §4.7 (ejecuciones). Commits `dcf164c` … `fc277e9`. |
| 10. Conclusión | **Objetivo y hito del Sprint 2 cumplidos con pruebas automatizadas.** El cierre formal queda **en revisión** hasta que el responsable ejecute las verificaciones manuales de §4.8. Los pendientes (a), (b) y (c) son bloqueos externos documentados; no impiden el flujo del sprint, pero deben resolverse antes del Sprint 6. **No se inicia el Sprint 3 hasta confirmar este cierre.** |

### 3.4. Plantilla para los siguientes sprints

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
| Rendimiento KDS | Tiempo envío → visualización < 1 s | **Pendiente** (CP-07, Sprints 3-4) |

### 4.2. Cómo ejecutarlas

```powershell
php artisan test --compact                       # suite completa
php artisan test --compact --testsuite=Unit      # solo unitarias
php artisan test --compact --testsuite=Feature   # solo integración
php artisan test --compact tests/Feature/Services/Ventas/CierreVentaServiceTest.php   # un archivo
php artisan test --compact --filter="does not discount inventory twice"             # una prueba
```

Las pruebas usan SQLite en memoria (`phpunit.xml`) y `LazilyRefreshDatabase`: cada prueba empieza con la base vacía.

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

### 4.4. Pruebas de integración (165)

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
| `Feature/Http/Controllers/Auth/RecuperacionPasswordControllerTest.php` | 13 | Los 4 canales, vencimiento, intentos, no enumeración, cierre de sesiones, falla del proveedor |
| `Feature/Http/Controllers/Cuenta/SesionControllerTest.php` | 6 | Cierre remoto con efecto real (200 → 401), sesiones ajenas, contraseña requerida |
| `Feature/Http/Controllers/Cuenta/SeguridadControllerTest.php` | 9 | PIN y respuesta con hash, PIN duplicado, teléfono, contraseña débil, errores por formulario |
| `Feature/Http/Controllers/Admin/UsuarioControllerTest.php` | 11 | Alta con login y PIN funcionales, validaciones, duplicados, desactivación con revocación, autoprotección del gerente |
| `Feature/Http/Controllers/Admin/AsistenciaReporteControllerTest.php` | 3 | Jornadas y horas contra cálculo manual, filtro por empleado, periodo inválido |
| `Feature/Http/Controllers/Admin/TokenApiControllerTest.php` | 5 | Token real autentica la API con sus permisos, revocación, vencimiento, secreto mostrado una sola vez |
| `Feature/Http/Controllers/AsistenciaControllerTest.php` | 8 | Hora del servidor, PIN inválido o inactivo, secuencia, bloqueo, formato |
| `Feature/Http/Controllers/PosControllerTest.php` | 6 | Precio del servidor, validaciones, faltantes, catálogo por estación |
| `Feature/Http/Controllers/OrdenControllerTest.php` | 9 | Cierre doble, entrega, reversión por rol, listado y detalle |
| `Feature/Http/Controllers/KdsControllerTest.php` | 6 | Estaciones, renglones por estación, HTML solo al cambiar, avance y error 422 |
| `Feature/Http/Controllers/Api/KdsApiControllerTest.php` | 6 | 401 sin token, 403 sin permiso o con cuenta inactiva, ruteo, avance |
| `Feature/Http/Controllers/AyudaControllerTest.php` | 3 | Help Mode con la receta correcta |
| `Feature/Http/Controllers/TareaControllerTest.php` | 5 | Completar con quién y cuándo, tareas ajenas prohibidas, idempotencia |
| `Feature/Http/Controllers/DashboardControllerTest.php` | 2 | Cifras del gerente ocultas al empleado |
| `Feature/Http/Middleware/EnsureRoleTest.php` | 14 | Cada pantalla de `/admin`: gerente 200, empleado 403, invitado al login; formularios enviados directamente |
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
| CP-07 | Actualización KDS con medición | — | < 1 s sin recarga | No medido | **No ejecutado** (Sprints 3-4) | — |
| CP-08 | Help Mode | `AyudaControllerTest` | Ficha del producto correcto | Ingredientes, cantidades y pasos del producto pedido, no de otro | **Pasa** (adelantado) | 2026-11-11 |
| CP-09 | Cálculo de receta | `CierreVentaServiceTest` | Saldo − receta × unidades | 3 lattes: café 1000 → 946 g, leche 5000 → 4400 ml | **Pasa** (Sprint 5, adelantado) | 2026-11-11 |
| CP-10 | Doble descuento | `CierreVentaServiceTest`, `OrdenControllerTest` | Sin segundo conjunto de movimientos | 2 cierres → 1 conjunto; falla de stock → 0 movimientos | **Pasa en SQLite**; falta la prueba concurrente en MySQL | 2026-11-11 |
| CP-11 | Movimiento manual autorizado | `InventarioServiceTest` | Gerente opera; empleado denegado | Servicio correcto; sin ruta HTTP todavía | **Parcial** (Sprint 5) | 2026-11-11 |
| CP-12 | Checklist | `TareaControllerTest` | La asignación y su estado persisten | Marcar y persistir correcto; la asignación desde `/admin` es del Sprint 5 | **Parcial** (Sprint 5) | 2026-11-11 |
| CP-13 | Validación de datos | `PosControllerTest`, `UsuarioControllerTest`, `RecetaServiceTest`, `AsistenciaControllerTest`, `SeguridadControllerTest` | No guarda y explica | Negativos, ceros, vacíos y formatos inválidos rechazados; mensajes en español | **Pasa** | 2026-11-11 |
| CP-14 | Falla de red | — | Indica la incidencia y no duplica | Indicador implementado; no probado cortando la red | **No ejecutado** (Sprints 3-4) | — |
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
| RB-07 a RB-10 Recuperación (4 canales) | — | `RecuperacionPasswordControllerTest` (correo con `Mail::fake`, SMS y llamada con `Http::fake`, pregunta secreta) |
| RB-11 Autorización | CP-02, CP-11 | `EnsureRoleTest`, `OrdenControllerTest`, `TareaControllerTest`, `InventarioServiceTest`, `UsuarioControllerTest` |
| RB-12 OWASP | CP-13 | `SecurityHeadersTest` (XSS, CSRF, encabezados), `EnsureUserIsActiveTest`, `MfaServiceTest` y `SeguridadControllerTest` (secretos con hash), `UsuarioControllerTest` (hashes no expuestos) |
| RB-17 Pruebas unitarias | — | `tests/Unit/*` (47) |
| Procesos críticos (README P1) | CP-03 a CP-06, CP-08 a CP-10, CP-12 | `Services/Ventas/*`, `AsistenciaControllerTest`, `TareaControllerTest`, `ModelosTest`, `RequerimientosServiceTest` |

### 4.7. Ejecuciones registradas

| Fecha | Comando | Resultado |
| :--- | :--- | :--- |
| 2026-11-10 | `php artisan test --compact` (inicio) | 1 falla (`ExampleTest`: 302 ≠ 200), 1 pasa |
| 2026-11-10 | `php artisan route:list --except-vendor` | Falla: no existe `Admin\UsuarioController` |
| 2026-11-10 | `php artisan test --compact` (`270d1e4`) | 106 pasan, 0 fallan (322 aserciones) |
| 2026-11-10 | `php artisan test --compact` (`aad47a7`) | 134 pasan, 0 fallan (422 aserciones) |
| 2026-11-10 | Capturas Edge headless (1920×1080 y 500 px) | Sin elementos encimados; POS y KDS se reacomodan en una columna en celular |
| 2026-11-11 | `php artisan route:list --except-vendor` (`191f315`) | Funciona: 51 rutas |
| 2026-11-11 | `php artisan test --compact` (`fc277e9`) | Primera ejecución: 4 fallas → corregidas (2 aserciones mal planteadas, regla de usuario, prueba de token); final **212 pasan, 0 fallan** (607 aserciones) |
| 2026-11-11 | `php artisan test --compact --testsuite=Unit` | **47 pasan** (57 aserciones) |
| 2026-11-11 | `php artisan test --compact --testsuite=Feature` | **165 pasan** (550 aserciones) |
| 2026-11-11 | `npm run build` | Compila sin errores |

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

---

## 5. Matriz de cumplimiento

### 5.1. Requisitos funcionales

| ID | Requisito | Prio | Sprint | Estado | Evidencia | Pendiente |
| :--- | :--- | :---: | :---: | :--- | :--- | :--- |
| RF-01 | Autenticación y roles | P1 | 2 | **CUMPLIDO** (automático) | `LoginControllerTest`, `EnsureRoleTest`, `UsuarioControllerTest` | M-01, M-06 |
| RF-02 | Clasificación por canal | P1 | 2 | **CUMPLIDO** (automático) | CP-05 | M-02 |
| RF-03 | Captura de venta | P1 | 2 | **CUMPLIDO** (automático) | `PosControllerTest`, `OrdenServiceTest` | M-02 |
| RF-04 | Enrutamiento por estación | P1 | 3-4 | PARCIAL (adelantado) | CP-06 | Administración de estaciones |
| RF-05 | Actualización del KDS | P1 | 3-4 | PARCIAL (adelantado) | `KdsControllerTest` | CP-07 |
| RF-06 | Help Mode | P1 | 3-4 | PARCIAL (adelantado) | CP-08 | Prueba en navegador |
| RF-07 | Configuración de recetas | P1 | 5 | PARCIAL (adelantado) | `RecetaServiceTest` | Pantalla de recetas |
| RF-08 | Descuento por receta | P1 | 5 | PARCIAL (adelantado) | CP-09, CP-10 | MySQL concurrente |
| RF-09 | Inventario administrativo | P1 | 5 | PARCIAL (adelantado) | `InventarioServiceTest` | Pantallas, CP-11 |
| RF-10 | Registro de asistencia | P1 | 2 | **CUMPLIDO** (automático) | CP-03, CP-04, `AsistenciaReporteControllerTest` | M-03 |
| RF-11 | Asignación y cumplimiento de tareas | P1 | 5 | PARCIAL (adelantado) | `TareaControllerTest` | Asignación desde `/admin` |
| RF-12 | Protección de reportes y administración | P1 | 2 | **CUMPLIDO** para las rutas del Sprint 2 | `EnsureRoleTest` | Repetir al habilitar cada ruta nueva |
| RF-13 | Reporte de ventas | P2 | 6 | PARCIAL | `ReporteVentasService` | Pantalla, CP-15 |
| RF-14 | Aviso de bajo inventario | P2 | 5 | PARCIAL | `DashboardControllerTest` | — |
| RF-15 | Reconexión / contingencia | P2 | 3-4 | PARCIAL | Indicador en `kds.js` | CP-14; impresión: no hay hardware |

### 5.2. Requisitos no funcionales

| ID | Atributo | Estado | Evidencia / pendiente |
| :--- | :--- | :--- | :--- |
| RNF-01 | Actualización < 1 s | PENDIENTE | Polling de 800 ms; **no medido** (Sprints 3-4) |
| RNF-02 | Seguridad por rol | **CUMPLIDO** (Sprint 2) | `EnsureRoleTest`: acceso desde la interfaz y por ruta directa |
| RNF-03 | Usabilidad táctil 1920×1080 | PARCIAL | Controles de 44–64 px; capturas a 1920×1080 y 500 px; falta pantalla táctil real |
| RNF-04 | Integridad transaccional | PARCIAL | Reversión ante falla y repetición probadas en SQLite; falta MySQL |
| RNF-05 | Consistencia de unidades | **CUMPLIDO** (lógica) | `UnidadMedidaTest`, `InventarioServiceTest`, `RecetaServiceTest` contra cálculo manual |
| RNF-06 | Validación de datos | **CUMPLIDO** | CP-13; mensajes en español |
| RNF-07 | Confidencialidad de credenciales | **CUMPLIDO** | Contraseña, PIN, respuesta y códigos con hash; `mfa_secret` cifrado; probado que no aparecen en vistas ni en claro en la BD |
| RNF-08 | Disponibilidad de red | PARCIAL | Indicador y reintentos; falta CP-14 |
| RNF-09 | Mantenibilidad | PARCIAL | Lógica en servicios, controladores delgados |
| RNF-10 | Rendimiento de consulta | PENDIENTE | Índices definidos; sin medición |

### 5.3. MVP (README §7.1)

| # | Funcionalidad P1 | Estado |
| :---: | :--- | :--- |
| 1 | Acceso con roles y reloj checador | **CUMPLIDO** (automático); M-01, M-03, M-06 pendientes |
| 2 | Punto de Venta básico | **CUMPLIDO** (automático); M-02 pendiente |
| 3 | Pantallas KDS por zona | PARCIAL (falta CP-07) |
| 4 | Inventario por receta | PARCIAL (faltan pantallas y MySQL) |
| 5 | Separación de permisos | **CUMPLIDO** para el Sprint 2 |
| 6 | Datos base de prueba | PARCIAL (solo SQLite) |

### 5.4. Criterios de la rúbrica

| ID | Criterio | Estado | Evidencia | Pendiente |
| :--- | :--- | :--- | :--- | :--- |
| RB-01 | Login con usuario o correo | **CUMPLIDO** (automático) | §4.6 | M-01 |
| RB-02 | Intentos repetidos | **CUMPLIDO** | Bloqueo de login, PIN y código | — |
| RB-03 | MFA real | **CUMPLIDO** (automático) | Desafío obligatorio, RFC 6238, anti-reutilización | M-05; QR (D-11) |
| RB-04 | Tokens distintos de la sesión | **CUMPLIDO** | Emisión, permisos, vencimiento y revocación probados con token real | — |
| RB-05 | Cierre de sesión seguro | **CUMPLIDO** | `logout` + `invalidate` + `regenerateToken` | — |
| RB-06 | Multisesiones y cierre remoto | **CUMPLIDO** (automático) | Cookie revocada pasa de 200 a 401 | M-04 |
| RB-07 | Recuperación por correo | PARCIAL / BLOQUEADO | Flujo completo con `Mail::fake` | SMTP real (§8-2) |
| RB-08 | Recuperación por pregunta | **CUMPLIDO** | Flujo completo | — |
| RB-09 | Recuperación por SMS | BLOQUEADO | Integración con Twilio probada con `Http::fake` | Cuenta Twilio (§8-3) |
| RB-10 | Recuperación por llamada | BLOQUEADO | Integración con Twilio probada con `Http::fake` | Cuenta Twilio (§8-3) |
| RB-11 | Autorización en el backend | **CUMPLIDO** para el Sprint 2 | `EnsureRoleTest` y pruebas por rol | Repetir con cada ruta nueva |
| RB-12 | OWASP | PARCIAL | XSS, CSRF, encabezados, hashes, cuentas inactivas, errores propios | `SESSION_SECURE_COOKIE`/`SESSION_ENCRYPT` en producción; revisión final (Sprint 6) |
| RB-13 | 2–3 patrones de diseño | PARCIAL | Observer, Repository y Strategy implementados y documentados en README §13.6 | Revisión final |
| RB-14 | Interfaz atractiva y responsiva | PARCIAL | Sistema visual, componentes, capturas | Pantallas de los sprints 3 a 6; pantalla táctil real |
| RB-15 | GitHub Flow, PR, protección | PARCIAL / BLOQUEADO | Ramas `feature/`, commits semánticos | `gh auth login` (§8-6) |
| RB-16 | Metodología y riesgos | PARCIAL | Este documento | Mantener por sprint |
| RB-17 | Pruebas verificables (incluidas unitarias) | PARCIAL | 212 pruebas (§4) | CP-07, CP-14, CP-15; MySQL |
| RB-18 | README refleja el estado real | PARCIAL | README actualizado al cierre del Sprint 2 (estado, ejecución, ampliaciones, decisiones, patrones) | Actualizar en cada sprint |
| RB-19 | MySQL | BLOQUEADO | Migraciones compatibles | Credenciales (§8-1) |
| RB-20 | `.env.example` sin secretos | **CUMPLIDO** | `76a5427`; `.env` ignorado | — |

---

## 6. Defectos

| ID | Severidad | Defecto | Estado |
| :--- | :--- | :--- | :--- |
| D-01 | Crítico | `routes/web.php` referenciaba 11 controladores inexistentes; `route:list` fallaba | **Corregido** (`191f315` + `1504e5d`): 3 controladores del Sprint 2 creados; el resto de rutas comentadas con su sprint |
| D-02 | Crítico | No existían vistas Blade | **Corregido** para el Sprint 2 (`8790dc9`, `1504e5d`); las vistas de sprints posteriores se crearán en su sprint |
| D-03 | Alto | Twilio sin configuración | **Corregido** (`76a5427`) |
| D-04 | Alto | Sin pruebas de dominio | **Corregido** (`270d1e4`, `fc277e9`) |
| D-05 | Alto | SQLite en lugar de MySQL | Abierto — BLOQUEADO por credenciales (§8-1) |
| D-06 | Medio | Trabajo sin commitear; sin `push` ni PR | Parcial: todo commiteado; falta `push` y PR (§8-6, §8-7) |
| D-07 | Medio | `.env.example` desactualizado | **Corregido** (`76a5427`) |
| D-08 | Medio | Tokens sin vencimiento | **Corregido** (`76a5427`) |
| D-09 | Medio | El empleado puede cancelar órdenes no cerradas | Requiere decisión (§8-4) |
| D-10 | Bajo | Mensajes de validación en inglés | **Corregido** (`98d8418`) |
| D-11 | Bajo | MFA sin QR | Requiere decisión (§8-5); se ofrece clave manual y enlace `otpauth://` |
| D-12 | Bajo | PIN fallido detectado por texto del mensaje | **Corregido** (`76a5427`) |
| D-13 | Bajo | `welcome.blade.php` sin uso | **Corregido** (`98d8418`) |
| D-14 | Bajo | Sesiones vacías si el driver no es `database` | **Corregido** (`8790dc9`): la pantalla lo explica |
| D-15 | Alto | Menú del gerente enlazaba pantallas con error 500 | **Corregido** (`191f315`): el menú solo muestra rutas registradas |
| D-16 | Bajo | Un error de `password_actual` se repetía en todos los formularios | **Corregido** (`674b08f`): *error bags*; prueba *shows a form error only in the form that caused it* |

---

## 7. Tablero

### Terminado

| ID | Tarjeta | Sprint | Evidencia |
| :--- | :--- | :---: | :--- |
| B-00 | Especificación (README v2) | 0 | `448cd3b` |
| B-14 | Commit del Sprint 2 en commits semánticos | 2 | `dcf164c` … `72a4533` |
| B-16 | Base de pruebas | 2 | `270d1e4` |
| B-17 | Sistema visual, layouts y componentes | 2 | `98d8418` |
| B-25 | Configuración Twilio, `.env.example`, vencimiento de tokens | 2 | `76a5427` |
| B-26 | Emisión y revocación de tokens | 2 | `1504e5d`; `TokenApiControllerTest` |
| B-27 | Traducción `es` | 2 | `98d8418` |
| B-33 | Administración de usuarios y roles | 2 | `1504e5d`; `UsuarioControllerTest`, `EnsureRoleTest` |
| B-34 | Reporte de asistencias del gerente | 2 | `1504e5d`; `AsistenciaReporteControllerTest` |
| B-35 | Pruebas unitarias de reglas de negocio | 2 | `fc277e9`; 47 pruebas |

### En revisión/pruebas (falta verificación manual o un bloqueo externo)

| ID | Tarjeta | Sprint | Falta |
| :--- | :--- | :---: | :--- |
| B-01 | Modelo relacional, migraciones y seeders | 1 | MySQL (B-15) |
| B-02 | Login, logout y bloqueo de intentos | 2 | M-01 |
| B-03 | Autorización por rol | 2 | M-06 |
| B-04 | Reloj checador con PIN | 2 | M-03 |
| B-05 | POS con canal de origen | 2 | M-02 |
| B-10 | MFA | 2 | M-05 |
| B-11 | Multisesiones | 2 | M-04 |
| B-12 | Recuperación de contraseña | 2 | SMTP y Twilio reales (§8-2, §8-3) |

### Adelantado (existe; se cierra en su sprint)

| ID | Tarjeta | Sprint | Falta |
| :--- | :--- | :---: | :--- |
| B-06 | KDS por estación, polling y API | 3-4 | Medición CP-07, CP-14, administración de estaciones |
| B-07 | Help Mode | 3-4 | Prueba en navegador |
| B-08 | Cierre de venta e inventario por receta | 5 | MySQL concurrente, pantallas de ingredientes y movimientos |
| B-09 | Tareas del empleado | 5 | Plantillas y asignación desde `/admin` |
| B-13 | Reporte de ventas (servicio) | 6 | Pantalla, exportación, CP-15 |

### Pendiente

| ID | Tarjeta | Sprint | Depende de |
| :--- | :--- | :---: | :--- |
| B-15 | MySQL local y pruebas críticas en MySQL | 2 (bloqueado) | Credenciales (§8-1) |
| B-31 | `push`, PR, plantilla de PR y protección de `main` | 2 (bloqueado) | `gh auth login` (§8-6) |
| B-36 | Administración de estaciones | 3-4 | Cierre del Sprint 2 |
| B-23 | Medición del KDS en navegador real | 3-4 | Cierre del Sprint 2 |
| B-28 | Prueba de reconexión del KDS | 3-4 | B-23 |
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

### 8.2. Decisiones pendientes del responsable

| # | Decisión | Por qué importa | Propuesta |
| :---: | :--- | :--- | :--- |
| 1 | Credenciales de MySQL local | Gestor exigido y prueba de concurrencia | Base `cafesync` (utf8mb4) y usuario propio, solo en `.env` |
| 2 | Proveedor de correo real | Sin SMTP el correo solo se escribe en el log | Mailtrap o SMTP de prueba |
| 3 | Cuenta de Twilio | RB-09 y RB-10 bloqueados | Cuenta de prueba con número verificado |
| 4 | ¿El empleado puede cancelar órdenes no cerradas? (D-09) | Matriz de permisos | Permitirlo solo antes de «lista», o restringirlo al gerente |
| 5 | QR para MFA (D-11) | Dependencia nueva | `bacon/bacon-qr-code` o mantener la clave manual |
| 6 | Acceso de `gh` | PR y protección de `main` | `gh auth login`; un segundo revisor obligatorio no puede cumplirse sin un revisor autorizado |
| 7 | `push` de `main` y de la rama del Sprint 2 | Copia remota y PR | PR de `feature/base-datos-modelos` y de `feature/autenticacion-seguridad` |
| 8 | Documento de la rúbrica 3 | Contrastar §4.1 con la rúbrica original | Agregar la rúbrica al repositorio o compartirla |

---

## 9. Análisis de riesgos (actualizado 2026-11-11)

| ID | Riesgo | Prob. | Impacto | Prevención | Mitigación | Contingencia | Estado |
| :--- | :--- | :---: | :---: | :--- | :--- | :--- | :--- |
| R-01 | Falla de red en hora pico | Media | Alto | Probar reconexión | Indicador y reintentos en el KDS | Comunicar la incidencia; ticket solo con impresora | Mitigado en código; falta CP-14 |
| R-02 | Órdenes no llegan al KDS a tiempo | Media | Alto | Polling de 800 ms con huella | Medir (B-23) | Reducir intervalo o usar Reverb | Abierto, sin medición |
| R-03 | Recetas o unidades incompatibles | Media | Alto | Unidad base por ingrediente | Validación en recetas y ajustes | Nueva versión y ajuste | Mitigado y probado |
| R-04 | Doble descuento | Media | Alto | Cierre transaccional e idempotente | Bloqueo, verificación e índice único | Revisar historial | Probado en SQLite; falta MySQL |
| R-05 | Empleado accede a funciones de gerente | Baja/Media | Alto | `rol:gerente` y verificaciones en servicios | `EnsureRoleTest` | Revocar sesión y corregir | **Mitigado y probado** para el Sprint 2 |
| R-06 | PIN compartido o ambiguo | Media | Medio | PIN único con índice HMAC | Límite de intentos | Reemplazar el PIN | Mitigado y probado |
| R-07 | Interfaz compleja | Media | Bajo/Medio | Controles grandes, etiquetas directas | Help Mode | Simplificar | Mitigado; falta prueba con usuarios |
| R-08 | Alcance mayor que la capacidad | Alta | Alto | Backlog P1/P2 | Solo trabajo del sprint en curso | Aplazar P2 | **Materializado** (trabajo adelantado); controlado con §2 |
| R-09 | Consultas lentas | Baja/Media | Alto | Índices | Carga ansiosa | Optimizar | Sin medición |
| R-10 | Asistencias o tareas no se guardan | Media | Medio | Transacciones | Pruebas de persistencia | Corregir | **Mitigado y probado** |
| R-11 | Proveedores externos no disponibles | Alta | Alto | Canales «no disponibles» sin credenciales | Fakes en pruebas | Declarar BLOQUEADO | Abierto |
| R-12 | SQLite distinto de MySQL | Alta | Medio | Migraciones sin SQL de un motor | Migrar a MySQL | Pruebas críticas en MySQL antes de la entrega | Abierto |
| R-13 | Pérdida del trabajo sin commitear | Media | Alto | Commits frecuentes | Todo commiteado | `push` | Mitigado; falta `push` |

---

## 10. Bitácora de sesiones

| Fecha | Actividad | Resultado | Siguiente paso |
| :--- | :--- | :--- | :--- |
| 2026-11-10 | Auditoría inicial y diagnóstico: lectura del README, revisión del código, pruebas, `route:list`, `migrate:status`. Sin cambios de código. | Documento creado; 13 defectos; ningún criterio CUMPLIDO. | Commit del Sprint 2 y base de pruebas |
| 2026-11-10 | Incremento 1 del Sprint 2: 4 commits `feat:`, correcciones D-03, D-07, D-08, D-12, 106 pruebas. Se confirmó que el cierre remoto invalida una cookie real (200 → 401). | 106/106 pruebas pasan. | Sistema visual y pantallas |
| 2026-11-10 | Incremento 2 del Sprint 2: interfaz de los módulos operativos y 28 pruebas de pantallas; revisión con capturas de Edge headless. | 134/134 pruebas pasan; D-10, D-13 corregidos; D-15, D-16 registrados. | Administración |
| 2026-11-11 | Revisión de la planificación (README §17) frente al código: se documentó el trabajo adelantado y se decidió limitar el desarrollo al Sprint 2. Incremento 3: rutas por sprint, usuarios y roles, reporte de asistencias, tokens, errores por formulario, 47 pruebas unitarias y pruebas de seguridad. Reestructura de este documento y actualización del README. | 212/212 pruebas pasan; D-01, D-02, D-14, D-15, D-16 corregidos; hito del Sprint 2 cumplido con pruebas automatizadas. | El responsable ejecuta M-01 a M-06 (§4.8) y resuelve §8.2; después se cierra el Sprint 2 y se abre el Sprint 3-4 (KDS) |
