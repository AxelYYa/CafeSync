# Seguimiento del desarrollo de CaféSync

Documento de control del proyecto bajo el **Scrum adaptado** descrito en el README (§15–§17): backlog priorizado P1/P2, tablero de cuatro estados, registro por sprint, riesgos actualizados y resultado real de los casos de prueba.

> [!IMPORTANT]
> Este archivo registra **solo lo que se verificó**. Un elemento no pasa a *Terminado* ni a *CUMPLIDO* porque el código exista: debe cumplir su criterio de aceptación y contar con una prueba ejecutada (README §15.3). Las sesiones de trabajo se registran en la bitácora (§9); no se documentan reuniones ni ceremonias que no ocurrieron.

| Dato | Valor |
| :--- | :--- |
| Responsable (todas las funciones) | Axel Espinoza Blanco — desarrollo individual |
| Fecha de corte de esta revisión | 2026-11-10 |
| Rama de trabajo | `feature/autenticacion-seguridad` |
| Fuente funcional | `README.md` (especificación v2, 19-oct-2026) |
| Fuente de calidad | Rúbrica de la Unidad 3, nivel EXCELENTE (criterios tomados del prompt maestro; el documento de la rúbrica **no está en el repositorio**) |

### Convenciones

- **Tablero:** `Pendiente` → `En proceso` → `En revisión/pruebas` → `Terminado`.
- **Estado de un criterio:** **CUMPLIDO** (implementado y comprobado) · **PARCIAL** (existe implementación, falta completar o verificar) · **PENDIENTE** (no implementado) · **BLOQUEADO** (requiere credenciales, permisos o servicios externos no disponibles).
- **Severidad de defectos:** Crítico (bloquea P1 o la seguridad) · Alto · Medio · Bajo.

---

## 1. Fotografía del repositorio (2026-11-10)

| Elemento | Estado encontrado |
| :--- | :--- |
| PHP / Laravel | PHP 8.2.12 (XAMPP) · Laravel 12.70.0 |
| Paquetes directos | `laravel/sanctum` 4.3.3, `laravel/tinker`; dev: Pest 3.8.7, Pint 1.30.4, Boost 2.10.3, Sail, Pail |
| Frontend | Vite 7 + Tailwind CSS 4 (`resources/css/app.css` sin estilos propios; `app.js` solo importa `bootstrap.js`) |
| Base de datos activa | **SQLite** (`.env` y `.env.example`). Hay un servidor MySQL local respondiendo, pero sin credenciales configuradas en el proyecto |
| Migraciones | 7 migraciones ejecutadas en SQLite (`php artisan migrate:status`) |
| Docker | Docker Desktop instalado; no existe `compose.yaml` en el repositorio |
| GitHub CLI | `gh` instalado pero **sin sesión iniciada** → no se pudo consultar PRs ni protección de ramas |

### Historial de Git

| Commit | Fecha | Descripción |
| :--- | :--- | :--- |
| `01098eb` | 2026-10-19 | Inicializar CaféSync con Laravel y documentación |
| `40b6c0d` | 2026-10-19 | `docs:` incorporar documentación al README |
| `448cd3b` | 2026-10-19 | `docs:` segunda versión de CaféSync — **último commit en `origin/main`** |
| `f860f40` | 2026-11-03 | `feat:` modelo de datos, migraciones, enums y seeders (rama `feature/base-datos-modelos`) |
| `fcaf430` | 2026-11-03 | Merge de `feature/base-datos-modelos` en `main` **local** (sin Pull Request) |

- `main` local va **2 commits adelante** de `origin/main` (no se ha hecho `push`).
- En `feature/autenticacion-seguridad` hay **~60 archivos sin commitear** (controladores, servicios, middleware, observers, policy, repositorio, rutas, API). Es el trabajo del Sprint 2.

---

## 2. Inventario de lo implementado

### 2.1. Comprometido en Git (Sprint 1)

| Capa | Archivos | Observación |
| :--- | :--- | :--- |
| Migraciones | `database/migrations/2026_10_20_00000{1..5}_*.php` | Las 15 tablas del DER (§22) + `sessions`, `recuperaciones_password`, `personal_access_tokens`. PK `BIGINT UNSIGNED` con nombres del diccionario, `DECIMAL(10,2)` en importes, `DECIMAL(12,3)` en cantidades, `UNIQUE(id_producto, version)`, `UNIQUE(id_receta, id_ingrediente)`, `UNIQUE(id_detalle_orden, id_ingrediente, tipo)`, índices en FK y consultas KDS. La tabla `users` por defecto se eliminó (no hay tablas de usuarios duplicadas). |
| Ampliaciones al diccionario | `usuarios`: `username`, `telefono`, `pin_lookup`, `pregunta_secreta`, `respuesta_secreta_hash`, `mfa_secret`, `mfa_recovery_codes`, `mfa_confirmado_en` · `movimientos_inventario`: `incrementa`, `stock_resultante` | Necesarias para los requisitos de la rúbrica (login por usuario, MFA, recuperación) y para la trazabilidad del saldo. **Aún no documentadas en el README.** |
| Enums | `app/Enums/*` (9) | Canal, estados de orden/preparación/tarea, rol, tipo de asistencia y movimiento, canal de recuperación, `UnidadMedida` con conversión kg→g y l→ml. |
| Modelos | `app/Models/*` (15) | Tabla y PK personalizadas, `casts()`, relaciones Eloquent tipadas. `Usuario` es el modelo de autenticación (`config/auth.php`), con campos sensibles en `$hidden` y `mfa_*` cifrados. |
| Seeders / factories | `database/seeders/*`, `database/factories/*` | 2 roles, 4 usuarios demo con PIN, 3 estaciones, 11 ingredientes, 8 productos con receta, 2 turnos, 4 plantillas de tareas. |
| Servicio | `app/Services/PinService.php` | PIN con bcrypt (`pin_hash`) + índice HMAC-SHA256 (`pin_lookup`) para resolver al empleado sin recorrer hashes. |

### 2.2. Sin commitear (Sprint 2, en proceso)

| Módulo | Backend existente | Falta |
| :--- | :--- | :--- |
| Autenticación | `Auth/LoginController`: login por usuario **o** correo, bloqueo 5 intentos/5 min por login+IP, comprobación falsa contra enumeración por tiempo, `session()->regenerate()`, logout con `invalidate()` + `regenerateToken()`. Middleware `EnsureUserIsActive`, `EnsureRole` (`rol:gerente`), `SecurityHeaders`. | Vistas, pruebas |
| MFA | `Mfa/TotpService` (RFC 6238 propio), `Mfa/MfaService` (activación con confirmación, 8 códigos de recuperación con huella SHA-256, anti-reutilización del código TOTP), `Cuenta/MfaController`, desafío en el login. | Vistas, QR, pruebas |
| Multisesiones | `Sesiones/SesionService` sobre `SESSION_DRIVER=database`: listar sesiones con dispositivo/navegador/SO/IP/última actividad, cerrar una sesión, cerrar todas las demás (con contraseña). Al cambiar o restablecer la contraseña se cierran las demás sesiones. | Vista `cuenta.seguridad`, pruebas |
| Recuperación | Patrón Strategy: `EstrategiaRecuperacion` + `RecuperacionPorCorreo`, `…PorSms`, `…PorLlamada`, `…PorPregunta`. Códigos de 6 dígitos con hash, vigencia 10 min, máx. 5 intentos, token de restablecimiento SHA-256 de 15 min, respuestas genéricas y pregunta señuelo. `ProveedorTelefonia` (API REST de Twilio). | Vistas, configuración Twilio, pruebas |
| Tokens API | `routes/api.php` + `Api/KdsApiController` con Sanctum y habilidades `kds:read` / `kds:update`. | `Admin/TokenApiController` (emisión/revocación), vencimiento |
| POS / órdenes | `PosController`, `OrdenController`, `Ventas/OrdenService` (transacción, folio `CS-AAAAMMDD-NNNNN`, precio y estación tomados del servidor, valida receta y existencias antes de enviar). | Vistas, pruebas |
| KDS | `KdsController` + `Ventas/KdsService` (solo renglones de la estación), consulta periódica cada 800 ms con huella de versión, `PreparacionService` (pendiente → en preparación → lista). | Vistas + JS de polling, medición |
| Help Mode | `AyudaController` (vista completa o fragmento AJAX para abrir sobre el KDS). | Vistas |
| Cierre de venta | `Ventas/CierreVentaService`: transacción, `lockForUpdate`, idempotente, descuento receta × cantidad, guarda `id_receta_aplicada`, reversión exclusiva del gerente. | Pruebas CP-09/CP-10, UI |
| Inventario | `InventarioRepository` (único punto que modifica `stock_actual`, saldo nunca negativo), `InventarioService` (entradas/salidas/ajustes solo gerente, conversión de unidades, ajuste con motivo obligatorio), `RequerimientosService`, `DisponibilidadService`. | Controladores admin, vistas |
| Recetas | `Recetas/RecetaService`: nueva versión por cambio, desactiva la anterior, valida unidades y duplicados. | Controlador admin, vista |
| Asistencia | `AsistenciaController` + `Asistencia/AsistenciaService`: PIN, hora del servidor, secuencia entrada/salida, turno automático, límite de 5 PIN fallidos por IP. | Vista, pruebas |
| Tareas | `TareaController`, `Tareas/TareaService`, `AsignacionTareaPolicy`. | Controladores admin (plantillas/asignaciones), vistas |
| Observer | `OrdenObserver` (historial de estados) y `DetalleOrdenObserver` (orden → «lista» cuando todos los renglones lo están), registrados en `AppServiceProvider`. | Pruebas |
| Reportes (P2) | `Reportes/ReporteVentasService` (periodo, por canal, por producto, ticket promedio). | Controlador, vista, exportación |

---

## 3. Defectos detectados en la auditoría

| ID | Severidad | Defecto | Evidencia | Estado |
| :--- | :--- | :--- | :--- | :--- |
| D-01 | Crítico | `routes/web.php` referencia 11 controladores `App\Http\Controllers\Admin\*` que no existen (Usuario, Estacion, Producto, RecetaAdmin, Ingrediente, MovimientoInventario, PlantillaTarea, AsignacionTarea, AsistenciaReporte, ReporteVentas, TokenApi). | `php artisan route:list` falla con `ReflectionException` en `Admin\UsuarioController`. | Abierto |
| D-02 | Crítico | No existe ninguna vista Blade de la aplicación (solo `welcome.blade.php` por defecto y el correo). Los controladores devuelven 17 vistas inexistentes (`auth.*`, `dashboard.index`, `cuenta.seguridad`, `pos.*`, `kds.*`, `ayuda.*`, `asistencia.index`, `tareas.index`) y no hay layout. | `resources/views/` | Abierto |
| D-03 | Alto | `config/services.php` no define la clave `twilio`; `ProveedorTelefonia::configurado()` siempre devuelve `false` aunque existan credenciales. `.env.example` tampoco declara `TWILIO_*`. | `config/services.php` | Abierto |
| D-04 | Alto | No hay pruebas del dominio. `tests/Feature/ExampleTest.php` falla (espera 200 y `/` redirige con 302). `RefreshDatabase` está comentado en `tests/Pest.php`. | `php artisan test --compact` → 1 falla, 1 pasa | Abierto |
| D-05 | Alto | El entorno usa SQLite, el README exige MySQL. En SQLite `lockForUpdate()` no bloquea filas, por lo que la protección de concurrencia del cierre de venta no se puede validar. | `.env`, `.env.example` | Abierto |
| D-06 | Medio | ~60 archivos del Sprint 2 sin commitear; `main` local con 2 commits sin `push`; el merge de `feature/base-datos-modelos` se hizo sin Pull Request. | `git status`, `git rev-list` | Abierto |
| D-07 | Medio | `.env.example` desactualizado: `APP_NAME=Laravel`, `APP_LOCALE=en`, sin bloque MySQL activo, sin `TWILIO_*` ni `SEED_DEMO_PASSWORD`. | `.env.example` | Abierto |
| D-08 | Medio | Los tokens de Sanctum no vencen (`sanctum.expiration = null`). | `config/sanctum.php` | Abierto |
| D-09 | Medio | El empleado puede cancelar cualquier orden no cerrada (`ordenes.cancelar` sin restricción de rol). La matriz del README no define esta acción. | `routes/web.php` | Requiere decisión (§8) |
| D-10 | Bajo | `lang/es` vacío: los mensajes de validación de Laravel saldrán en inglés. | `lang/es` | Abierto |
| D-11 | Bajo | MFA solo genera la URI `otpauth://`; no hay forma de mostrar QR sin una dependencia nueva. | `MfaService::uriAprovisionamiento` | Requiere decisión (§8) |
| D-12 | Bajo | `AsistenciaController` decide si cuenta un intento fallido comparando el texto del mensaje (`'PIN no válido.'`); es frágil ante un cambio de redacción. | `AsistenciaController::store` | Abierto |
| D-13 | Bajo | `welcome.blade.php` (plantilla de Laravel) ya no se usa. | `resources/views/welcome.blade.php` | Abierto |

---

## 4. Matriz de diagnóstico y cumplimiento

Se actualiza en cada sprint. Al cierre (Fase 8) se completan las columnas *Prueba* y *Evidencia* para formar la matriz final de aceptación.

### 4.1. Requisitos funcionales del README

| ID | Requisito | Prio | Estado | Evidencia encontrada | Trabajo pendiente | Depende de |
| :--- | :--- | :---: | :--- | :--- | :--- | :--- |
| RF-01 | Autenticación y roles | P1 | PARCIAL | `LoginController`, `EnsureRole`, `Gate::define('administrar')` | Vista login, pruebas CP-01/CP-02 | D-02 |
| RF-02 | Clasificación por canal | P1 | PARCIAL | `CanalOrigen`, validación en `PosController`, enum casteado en `Orden` | Etiqueta visible en POS y KDS, CP-05 | D-02 |
| RF-03 | Captura de venta | P1 | PARCIAL | `OrdenService::crear` transaccional, folio único | Pantalla POS con carrito, pruebas | D-02 |
| RF-04 | Enrutamiento por estación | P1 | PARCIAL | `detalle_ordenes.id_estacion` copiado del producto; `KdsService::ordenesDeEstacion` | Vista KDS, CP-06 | D-02 |
| RF-05 | Actualización del KDS | P1 | PARCIAL | `kds.datos` con huella de versión, intervalo 800 ms | Vista + JS, persistencia del estado al recargar, CP-07 con medición | D-02 |
| RF-06 | Help Mode | P1 | PARCIAL | `AyudaController` con fragmento AJAX | Vistas `ayuda.show` / `ayuda._ficha`, CP-08 | D-02 |
| RF-07 | Configuración de recetas | P1 | PARCIAL | `RecetaService` con versionado y conversión de unidades | `Admin\RecetaAdminController` + vista | D-01, D-02 |
| RF-08 | Descuento por receta | P1 | PARCIAL | `CierreVentaService` (transacción, bloqueo, idempotencia, índice único) | CP-09 / CP-10 en MySQL | D-04, D-05 |
| RF-09 | Inventario administrativo | P1 | PARCIAL | `InventarioService::registrarManual`, `InventarioRepository` | Controladores de ingredientes y movimientos, CP-11 | D-01, D-02 |
| RF-10 | Registro de asistencia | P1 | PARCIAL | `AsistenciaService`, `PinService`, hora `now()` del servidor | Vista del reloj, CP-03/CP-04 | D-02 |
| RF-11 | Asignación y cumplimiento de tareas | P1 | PARCIAL | `TareaService`, `AsignacionTareaPolicy` | Controladores de plantillas y asignaciones, vistas, CP-12 | D-01, D-02 |
| RF-12 | Protección de reportes y administración | P1 | PARCIAL | Grupo `rol:gerente` en `/admin` | Que las rutas funcionen, pruebas de acceso directo | D-01 |
| RF-13 | Reporte operativo de ventas | P2 | PARCIAL | `ReporteVentasService` | Controlador, vista, exportación, CP-15 | D-01, D-02 |
| RF-14 | Aviso de bajo inventario | P2 | PARCIAL | `Ingrediente::scopeBajoMinimo`, dato en `DashboardController` | Vista con alerta | D-02 |
| RF-15 | Reconexión / contingencia | P2 | PENDIENTE | Polling sin indicador de conexión | Indicador y reintento en KDS, CP-14. Impresión de tickets: no hay hardware compatible | B-17 |

### 4.2. Requisitos no funcionales

| ID | Atributo | Prio | Estado | Evidencia / pendiente |
| :--- | :--- | :---: | :--- | :--- |
| RNF-01 | Actualización < 1 s | P1 | PENDIENTE | Diseño con polling de 800 ms; **no medido**. No se debe afirmar «tiempo real» hasta registrar la medición. |
| RNF-02 | Seguridad por rol | P1 | PARCIAL | Middleware y verificaciones en servicios; faltan pruebas de acceso directo. |
| RNF-03 | Usabilidad táctil 1920×1080 | P1 | PENDIENTE | No hay interfaz. |
| RNF-04 | Integridad transaccional | P1 | PARCIAL | Implementada; sin prueba de falla ni de repetición; SQLite no valida bloqueos. |
| RNF-05 | Consistencia de unidades | P1 | PARCIAL | `UnidadMedida` + validación en recetas y ajustes; sin pruebas contra cálculo manual. |
| RNF-06 | Validación de datos | P1 | PARCIAL | `validate()` en controladores; faltan mensajes en español y CP-13. |
| RNF-07 | Confidencialidad de credenciales | P1 | PARCIAL | Contraseña `hashed`, PIN bcrypt + HMAC, respuesta secreta con hash, `mfa_secret` cifrado, códigos con huella. Falta prueba que inspeccione la persistencia. |
| RNF-08 | Disponibilidad de red | P1 | PENDIENTE | Sin indicador de pérdida de comunicación. |
| RNF-09 | Mantenibilidad | P2 | PARCIAL | Lógica en servicios, controladores delgados. |
| RNF-10 | Rendimiento de consulta | P2 | PENDIENTE | Índices definidos; sin medición. |

### 4.3. MVP (README §7.1)

| # | Funcionalidad P1 | Estado |
| :---: | :--- | :--- |
| 1 | Acceso con roles y reloj checador | PARCIAL (backend) |
| 2 | Punto de Venta básico | PARCIAL (backend) |
| 3 | Pantallas KDS por zona | PARCIAL (backend) |
| 4 | Inventario por receta | PARCIAL (backend) |
| 5 | Separación de permisos | PARCIAL (rutas admin rotas, D-01) |
| 6 | Datos base de prueba | PARCIAL (seeders listos; verificados solo en SQLite) |

**Flujo de extremo a extremo (§7.3): no demostrable todavía** porque no hay pantallas (D-02) ni rutas administrativas funcionales (D-01).

### 4.4. Criterios de la rúbrica (nivel EXCELENTE)

| ID | Criterio | Estado | Evidencia encontrada | Trabajo pendiente |
| :--- | :--- | :--- | :--- | :--- |
| RB-01 | Login con usuario o correo y contraseña | PARCIAL | `LoginController::store` | Vista, pruebas |
| RB-02 | Protección contra intentos repetidos | PARCIAL | `RateLimiter` (5 / 300 s) + `throttle:30,1` | Pruebas |
| RB-03 | MFA como segunda verificación real | PARCIAL | TOTP + códigos de recuperación + desafío previo al login | Vistas, QR (D-11), pruebas |
| RB-04 | Tokens de autenticación diferenciados de la sesión | PARCIAL | Sanctum con habilidades para KDS | Emisión/revocación (`TokenApiController`), vencimiento (D-08), documentar relación sesión ↔ token |
| RB-05 | Cierre de sesión seguro | PARCIAL | `logout` + `invalidate` + `regenerateToken` | Prueba |
| RB-06 | Multisesiones y cierre remoto con efecto real | PARCIAL | `SesionService` elimina la fila de `sessions` | Vista, prueba que confirme que la cookie revocada pierde acceso |
| RB-07 | Recuperación por correo | PARCIAL / BLOQUEADO | Estrategia completa; `MAIL_MAILER=log` | Credenciales SMTP reales para el envío; pruebas con `Mail::fake()` |
| RB-08 | Recuperación por pregunta secreta | PARCIAL | Estrategia completa con hash y señuelo | Vista, pruebas |
| RB-09 | Recuperación por SMS | BLOQUEADO | Estrategia + cliente Twilio | Corregir D-03; cuenta y número de Twilio |
| RB-10 | Recuperación por llamada | BLOQUEADO | Estrategia + cliente Twilio (TwiML `<Say>`) | Corregir D-03; cuenta y número de Twilio |
| RB-11 | Autorización y rutas protegidas en el backend | PARCIAL | Middleware, Gate, Policy, verificaciones en servicios | D-01, pruebas de acceso directo por rol |
| RB-12 | Medidas OWASP | PARCIAL | CSRF (grupo web), Eloquent con parámetros, encabezados de seguridad, `$hidden`, escape en TwiML | Páginas de error propias, `SESSION_SECURE_COOKIE`/`SESSION_ENCRYPT` para producción, CSP también en local, pruebas |
| RB-13 | 2–3 patrones de diseño justificados | PARCIAL | **Observer** (`OrdenObserver`, `DetalleOrdenObserver`) · **Repository** (`InventarioRepository`) · **Strategy** (`EstrategiaRecuperacion` y sus 4 canales) | Documentar nombre, propósito, problema, ubicación, clases, ejemplo y justificación en el README |
| RB-14 | Interfaz atractiva, responsiva y por módulo | PENDIENTE | — | Layout, componentes, todas las pantallas |
| RB-15 | GitHub Flow, PR, protección de ramas | PARCIAL | Ramas `feature/`, commits semánticos | Plantilla de PR, PR reales, `push`, protección de `main` (requiere `gh auth login`) |
| RB-16 | Metodología y análisis de riesgos | PARCIAL | Este documento | Mantenerlo en cada sprint |
| RB-17 | Pruebas verificables | PENDIENTE | Solo pruebas de ejemplo (D-04) | Suite Pest por módulo + CP-01…CP-15 |
| RB-18 | README refleja el estado real | PENDIENTE | README es especificación | Estado real, decisiones, ampliaciones del modelo, patrones, seguridad, instalación |
| RB-19 | MySQL como gestor | PARCIAL | Migraciones compatibles | Configurar y verificar en MySQL (D-05) |
| RB-20 | `.env.example` correcto, sin secretos en Git | PARCIAL | `.env` ignorado por Git | D-07 |

---

## 5. Casos de prueba del README (§19.1)

| ID | Caso | Prueba automatizada | Resultado | Fecha | Defecto |
| :--- | :--- | :--- | :--- | :--- | :--- |
| CP-01 | Acceso válido como gerente | — | No ejecutado | — | D-02 |
| CP-02 | Bloqueo de ruta administrativa | — | No ejecutado | — | D-01 |
| CP-03 | Registro de asistencia | — | No ejecutado | — | — |
| CP-04 | PIN inválido | — | No ejecutado | — | — |
| CP-05 | Canal de pedido | — | No ejecutado | — | — |
| CP-06 | Ruteo a estaciones | — | No ejecutado | — | — |
| CP-07 | Actualización KDS (con medición) | — | No ejecutado | — | D-02 |
| CP-08 | Help Mode | — | No ejecutado | — | D-02 |
| CP-09 | Cálculo de receta | — | No ejecutado | — | — |
| CP-10 | Protección contra doble descuento | — | No ejecutado | — | D-05 |
| CP-11 | Movimiento manual autorizado | — | No ejecutado | — | D-01 |
| CP-12 | Checklist | — | No ejecutado | — | D-01 |
| CP-13 | Validación de datos | — | No ejecutado | — | — |
| CP-14 | Falla de red | — | No ejecutado | — | RF-15 |
| CP-15 | Consulta de ventas | — | No ejecutado | — | D-01 |

### Ejecuciones registradas

| Fecha | Comando | Resultado |
| :--- | :--- | :--- |
| 2026-11-10 | `php artisan test --compact` | 1 falla (`ExampleTest`: 302 ≠ 200), 1 pasa |
| 2026-11-10 | `php artisan route:list --except-vendor` | Falla: no existe `App\Http\Controllers\Admin\UsuarioController` |
| 2026-11-10 | `php artisan migrate:status` | 7 migraciones ejecutadas (SQLite) |

---

## 6. Tablero

Límite recomendado por el README: 1–2 tarjetas en proceso. Actualmente se excede porque el Sprint 2 abrió todo el backend a la vez; la prioridad es **cerrar** esas tarjetas (commit + pruebas + vista) antes de abrir nuevas.

### Terminado

| ID | Tarjeta | Evidencia |
| :--- | :--- | :--- |
| B-00 | Especificación del proyecto (README v2) | `448cd3b` |

### En revisión/pruebas

| ID | Tarjeta | Prio | Falta para *Terminado* |
| :--- | :--- | :---: | :--- |
| B-01 | Modelo relacional, migraciones, modelos, enums y seeders (T-01) | P1 | Ejecutar migraciones y seeders en MySQL; documentar ampliaciones del diccionario |

### En proceso (backend escrito, sin commit, sin vista ni pruebas)

| ID | Tarjeta | Prio | Tarea README |
| :--- | :--- | :---: | :--- |
| B-02 | Login, logout, bloqueo de intentos | P1 | T-02 |
| B-03 | Autorización: middleware, Gate, Policy | P1 | T-02 |
| B-04 | Reloj checador con PIN | P1 | T-02 |
| B-05 | POS: creación de órdenes y canal | P1 | T-03 |
| B-06 | KDS por estación + polling + API con token | P1 | T-05 |
| B-07 | Help Mode | P1 | T-06 |
| B-08 | Cierre de venta e inventario por receta | P1 | T-04 |
| B-09 | Tareas del empleado | P1 | T-07 |
| B-10 | MFA (TOTP + códigos de recuperación) | Rúbrica | — |
| B-11 | Multisesiones y cierre remoto | Rúbrica | — |
| B-12 | Recuperación de contraseña (4 canales) | Rúbrica | — |
| B-13 | Reporte de ventas (servicio) | P2 | — |

### Pendiente (orden propuesto)

| ID | Tarjeta | Prio | Depende de |
| :--- | :--- | :---: | :--- |
| B-14 | Commitear el Sprint 2 en commits semánticos y abrir PR | Proceso | — |
| B-15 | Configurar MySQL local y verificar migraciones + seeders | P1 | Credenciales (§8) |
| B-16 | Habilitar `RefreshDatabase`, reemplazar `ExampleTest`, pruebas de servicios P1 | P1 | B-14 |
| B-17 | Layout Blade, identidad visual y componentes reutilizables | P1 | — |
| B-18 | Vistas de acceso: login, desafío MFA, recuperación (3 pasos) | P1 | B-17 |
| B-19 | Pantalla POS táctil | P1 | B-17 |
| B-20 | Pantallas KDS + JS de polling + Help Mode superpuesto | P1 | B-17 |
| B-21 | Reloj checador, tareas, panel y cuenta/seguridad | P1 | B-17 |
| B-22 | Controladores y vistas `Admin\*` (11) | P1 | B-17 |
| B-23 | Medición del KDS (RNF-01, CP-07) | P1 | B-20 |
| B-24 | Casos CP-01…CP-15 y pruebas de acceso directo por rol | P1 | B-18…B-22 |
| B-25 | `config/services.php` + `.env.example` (Twilio, MySQL, locale `es`, nombre de la app) | Rúbrica | — |
| B-26 | Emisión y revocación de tokens; vencimiento de Sanctum | Rúbrica | B-22 |
| B-27 | Traducciones `lang/es` | P1 | — |
| B-28 | Indicador de conexión y reintento del KDS (RF-15) | P2 | B-20 |
| B-29 | Exportación del reporte de ventas | P2 | B-22 |
| B-30 | Recurrencia y revisión de tareas por el gerente | P2 | B-22 |
| B-31 | Plantilla de PR, checklist de revisión, protección de `main` | Proceso | `gh auth login` |
| B-32 | Actualizar README con el estado real y los patrones | Documentación | B-24 |

---

## 7. Registro por sprint

El cronograma de seis semanas del README (§17) se conserva como referencia. No hay fechas comprometidas; cada sprint cierra cuando su objetivo se verifica.

### Sprint 0 — Especificación

| Campo | Registro |
| :--- | :--- |
| Objetivo | Documentar problema, alcance, requisitos, modelo de datos y plan. |
| Tareas realizadas | Proyecto Laravel inicial; README v1 y v2. |
| Entregables | `README.md` (commits `01098eb`, `40b6c0d`, `448cd3b`, 2026-10-19). |
| Resultado | Terminado. |
| Pendientes heredados | Enlace o capturas de Figma (§18.4); confirmar los puntos abiertos de §11. |

### Sprint 1 — Preparación y base de datos (Semana 1)

| Campo | Registro |
| :--- | :--- |
| Objetivo | Entorno ejecutable y estructura relacional definida. |
| Actividades | Migraciones del DER, enums, modelos con PK personalizadas, factories, seeders de demostración, Sanctum. |
| Tareas realizadas | T-01. |
| Entregables | Commit `f860f40` (2026-11-03), merge local `fcaf430`. |
| Pruebas | Migraciones ejecutadas en SQLite. Sin pruebas automatizadas. |
| Defectos | D-05 (SQLite en lugar de MySQL), D-06 (merge sin PR, sin `push`). |
| Riesgos | R-12. |
| Decisiones | Tabla `usuarios` con PK `id_usuario` como único modelo de autenticación; ampliaciones del diccionario para la rúbrica (§2.1). |
| Pendientes | Verificar en MySQL; documentar ampliaciones. |

### Sprint 2 — Acceso, seguridad y lógica de negocio (Semana 2, en curso)

| Campo | Registro |
| :--- | :--- |
| Objetivo | Roles, reloj checador, POS y requisitos de seguridad de la rúbrica. |
| Actividades | Backend de autenticación, MFA, sesiones, recuperación, autorización, POS, KDS, cierre de venta, inventario, asistencia, tareas, reportes y API. |
| Tareas realizadas | Backend de T-02 a T-07 (sin commitear). |
| Entregables | Pendiente de commit (B-14). |
| Pruebas | 2026-11-10: suite con 1 falla; `route:list` falla. |
| Defectos | D-01 a D-04, D-07 a D-12. |
| Riesgos | R-05, R-08, R-11, R-13. |
| Decisiones (tomadas en código, por documentar) | Cierre solo desde «lista» o «entregada»; no se crea ni se cierra una orden sin receta configurada; stock insuficiente bloquea la venta sin permitir saldos negativos; una venta cerrada solo se cancela mediante reversión del gerente; KDS por consulta periódica de 800 ms; MFA con TOTP propio sin dependencias. |
| Pendientes | Ver tablero §6. |

### Plantilla para los siguientes sprints

| Campo | Registro |
| :--- | :--- |
| Objetivo | |
| Actividades | |
| Tareas realizadas | |
| Entregables | |
| Resultados | |
| Pruebas | |
| Defectos | |
| Riesgos | |
| Decisiones | |
| Pendientes | |

---

## 8. Decisiones pendientes del responsable

| # | Decisión | Por qué importa | Propuesta |
| :---: | :--- | :--- | :--- |
| 1 | Credenciales de MySQL local | Sin ellas no se verifica el gestor exigido ni el bloqueo de filas del cierre de venta. | Crear la base `cafesync` (utf8mb4) y un usuario propio; configurar solo `.env`. |
| 2 | Proveedor de correo real | Sin SMTP el correo solo se escribe en el log (válido en desarrollo, no como evidencia). | Mailtrap o una cuenta SMTP de prueba, mediante variables de entorno. |
| 3 | Cuenta de Twilio para SMS y llamada | RB-09 y RB-10 quedan BLOQUEADOS sin ella; no se debe simular el envío. | Cuenta de prueba de Twilio con número verificado. |
| 4 | ¿El empleado puede cancelar órdenes no cerradas? (D-09) | Afecta la matriz de permisos. | Permitirlo solo en estado «capturada» o «en preparación», o restringirlo al gerente. |
| 5 | QR para MFA (D-11) | Requiere una dependencia nueva (las reglas del proyecto piden aprobación). | Añadir `bacon/bacon-qr-code` o mostrar la clave para captura manual. |
| 6 | Acceso de `gh` a GitHub | Necesario para PR, plantillas y protección de `main`. | Ejecutar `gh auth login`. Al ser un proyecto individual, la aprobación obligatoria de un segundo revisor no podrá cumplirse sin un revisor autorizado. |
| 7 | `push` de `main` local | `origin/main` no tiene el Sprint 1. | Abrir PR de `feature/base-datos-modelos` o hacer `push` de `main`. |

---

## 9. Análisis de riesgos (actualizado 2026-11-10)

| ID | Riesgo | Prob. | Impacto | Prevención | Mitigación | Contingencia | Estado |
| :--- | :--- | :---: | :---: | :--- | :--- | :--- | :--- |
| R-01 | Falla de red en hora pico | Media | Alto | Probar reconexión | Indicador de conexión en KDS (B-28) | Comunicar la incidencia; ticket solo con impresora configurada | Abierto, sin mitigación implementada |
| R-02 | Órdenes no llegan al KDS a tiempo | Media | Alto | Polling de 800 ms con huella de versión (no reenvía HTML si nada cambió) | Medir (B-23) antes de decidir Reverb | Reducir intervalo o usar Reverb | Abierto, sin medición |
| R-03 | Recetas o unidades incompatibles | Media | Alto | Unidad base por ingrediente; conversión validada | `RecetaService` rechaza unidades incompatibles y duplicados | Nueva versión de receta + ajuste autorizado | Mitigado en código, sin pruebas |
| R-04 | Doble descuento por reintento | Media | Alto | Cierre transaccional e idempotente | `lockForUpdate`, verificación previa e índice único | Revisar historial antes de ajustar | Mitigado en código; falta validar en MySQL |
| R-05 | Empleado accede a funciones de gerente | Baja/Media | Alto | `rol:gerente` + verificación en servicios | Pruebas de acceso directo (B-24) | Revocar sesión y corregir permiso | Abierto (rutas admin rotas, D-01) |
| R-06 | PIN compartido o ambiguo | Media | Medio | PIN único por usuario (índice HMAC único) | Límite de intentos por IP | Reemplazar el PIN comprometido | Mitigado en código, sin pruebas |
| R-07 | Interfaz compleja para el personal | Media | Bajo/Medio | Controles grandes, etiquetas directas | Help Mode contextual | Simplificar pantallas | Abierto (no hay UI) |
| R-08 | Alcance mayor que la capacidad individual | Alta | Alto | Backlog P1/P2 | Cerrar tarjetas abiertas antes de abrir nuevas | Aplazar P2 sin sacrificar pruebas | **Materializándose**: 12 tarjetas en proceso |
| R-09 | Consultas lentas | Baja/Media | Alto | Índices en FK y en `(id_estacion, estado_preparacion)` | Carga ansiosa (`preventLazyLoading` activo fuera de producción) | Optimizar la consulta crítica | Mitigado en diseño, sin medición |
| R-10 | Asistencias o tareas no se guardan | Media | Medio | Transacciones y validación de secuencia | Pruebas de persistencia (B-24) | Corregir y repetir con varios empleados | Abierto |
| R-11 | Proveedores externos (SMTP, Twilio) no disponibles | Alta | Alto | Canales que se declaran «no disponibles» sin credenciales | Variables de entorno; pruebas con fakes | Declarar BLOQUEADO y documentar cómo verificarlo | **Nuevo** — abierto |
| R-12 | Entorno SQLite distinto del gestor objetivo MySQL | Alta | Medio | Migraciones sin SQL específico de un motor | Migrar `.env` a MySQL (B-15) | Ejecutar las pruebas críticas en MySQL antes de la entrega | **Nuevo** — abierto |
| R-13 | Pérdida del trabajo sin commitear | Media | Alto | Commits pequeños y frecuentes | Commitear el Sprint 2 (B-14) | — | **Nuevo** — abierto |

---

## 10. Bitácora de sesiones

| Fecha | Actividad | Resultado | Siguiente paso |
| :--- | :--- | :--- | :--- |
| 2026-11-10 | Auditoría inicial (Fase 1) y diagnóstico (Fase 2): lectura completa del README, revisión de migraciones, modelos, servicios, controladores, rutas, configuración y Git; ejecución de pruebas, `route:list` y `migrate:status`. Sin cambios de código. | Se creó este documento; 13 defectos registrados; ningún criterio en estado CUMPLIDO. | Resolver las decisiones §8 (1, 4 y 6) y ejecutar B-14 → B-16 → B-17. |
