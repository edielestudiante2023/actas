# Roadmap — actas (PWA Consejos de Administración en Propiedad Horizontal)

> Marca cada casilla `[x]` al completar. Pensado para retomar el trabajo en cualquier momento (incluido Codex) sin perder contexto.

## Contexto rápido (leer antes de continuar)
- **Stack:** CodeIgniter 4 (PHP 8.4) · MySQL/MariaDB · PWA.
- **Repo:** https://github.com/edielestudiante2023/actas · **Producción:** https://actas.cycloidtalent.com/
- **Multi-tenant:** `tbl_clientes` = cada cliente de propiedad horizontal. Rol asignado **por cliente** vía `tbl_usuario_rol(id_usuario, id_rol, id_cliente)`; `id_cliente = NULL` ⇒ superadmin de plataforma.
- **Roles:** superadmin, administrador, presidente_consejo, consejero, revisor_fiscal, contador, abogado.
- **Regla de BD (obligatoria):** cambios de esquema SOLO por migraciones CLI (`php spark migrate` / seeders), NUNCA SQL manual. Orden: **LOCAL primero**, luego **PRODUCCIÓN** (DigitalOcean) con autorización.
- **Credenciales:** BD en `D:\DESARROLLO\KEYS\sql.txt`; SSH en `D:\DESARROLLO\KEYS\ssh.txt`. Nunca commitear secretos (van en `.env`).
- **Patrones de referencia aprobados:** PWA instalable desde login · Email SendGrid SDK **v7** (clase `EmailService`, click-tracking off) · Firmas por **email + WhatsApp**.
- **Modelo de inspiración:** `C:\xampp\htdocs\enterprisesst\docs\REPLICACION_MODULO_ACTAS_COMITES.md`.

---

## Recomendaciones para continuar (Codex)

**Antes de tocar la BD:** siempre migración/seeder + `php spark migrate` en LOCAL, verificar, y solo entonces en PRODUCCIÓN. Nunca SQL manual.

**Estado actual de rama:** el trabajo activo va en `cycloid`. Último hito implementado: verificación pública de actas firmadas por `codigo_verificacion`. `main`/producción deben actualizarse mediante el flujo de despliegue cuando se confirme cada hito.

**Flujo de despliegue (ya probado):**
1. Local en `cycloid`: programar → `git add . && git status && git commit -m "fix: ..."`
2. Integrar: `git checkout main && git merge cycloid && git push origin main && git checkout cycloid`.
3. Servidor (SSH `root@66.29.154.174`, llave `~/.ssh/id_ed25519`):
   ```bash
   cd /www/wwwroot/actas && git pull origin main
   chown -R www:www app public writable && chmod -R 775 writable
   # si hubo deps nuevas: COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader
   # si hubo migraciones: php spark migrate    (y db:seed si aplica)
   ```
3. El docroot del sitio ya apunta a `/public` (config aaPanel). NO tocar.

**Convenciones del proyecto:**
- Tablas con prefijo `tbl_`, motor InnoDB, `utf8mb4`. IDs `INT UNSIGNED`. FKs con `forge->addForeignKey`.
- **Multi-tenant:** toda consulta de datos de un cliente debe filtrar por `id_cliente`. El superadmin (rol con `id_cliente = NULL`) ve todo. `ClienteScope` centraliza acceso, cliente activo y clientes disponibles.
- Sesión disponible tras login: `isLoggedIn`, `id_usuario`, `nombre`, `email`, `roles` (array de códigos), `roles_full` (con `id_cliente`), `es_superadmin`, `cliente_activo`, `cliente_activo_nombre`, `cliente_activo_logo`.
- Vistas con Bootstrap 5 por CDN (aún sin layout base; conviene crear `app/Views/layouts/base.php` y migrar login/dashboard a `extend/section`).

**Próximo trabajo sugerido (orden):**
1. Commit del estado actual en `cycloid` antes de seguir acumulando cambios.
2. Configurar `email.fromEmail`, `email.fromName` y `email.SMTPPass` en `.env` local/producción para validar envío real.
3. Recuperación de contraseña por email (usa `EmailService` SendGrid v7).
4. Reenviar/cancelar enlaces de firma.
5. Layout base + menú por rol.

**Hitos inmediatos (siguiente ejecución):**
- [x] **Hito A — Usuarios por cliente:** modelos `RolModel`/`UsuarioRolModel`, listado de usuarios, crear/editar, asignar roles por cliente, bloquear/inactivar usuario.
- [x] **Hito B — Consejo por cliente:** definir miembros del consejo usando usuarios existentes y roles `presidente_consejo`/`consejero`.
- [x] **Hito C — Base de actas:** migraciones de actas usando `id_cliente` obligatorio y `ClienteScope`/filtro `cliente`.
- [x] **Hito D — PDF/logo:** integrar `tbl_clientes.logo` en encabezado de actas y exportación PDF (Dompdf v3; respaldo con marca de la app).

**Gotchas conocidos:**
- DigitalOcean exige SSL: activado por `database.default.ssl=true` en `.env` (ver `app/Config/Database.php`). Local no lo usa.
- En el server, `.user.ini` es inmutable: `chown -R` falla en ese archivo (ignorar, no es error real).
- Git en server pidió `safe.directory` (ya configurado para `/www/wwwroot/actas`).
- URLs salen con `/index.php/...`. Para URLs limpias: `Config/App.php` → `$indexPage = ''` (pendiente, opcional).
- Credenciales: BD `D:\DESARROLLO\KEYS\sql.txt`, SSH `D:\DESARROLLO\KEYS\ssh.txt`. Nunca commitear; van en `.env` (gitignored).

**Archivos clave creados:** `app/Controllers/{Auth,Dashboard,Clientes,Usuarios,ClienteConsejo,Actas,ActaAsistentes,ActaCompromisos,ActaFirmas}.php`, `app/Commands/TestEmail.php`, `app/Filters/{AuthFilter,RoleFilter,ClienteFilter}.php`, `app/Libraries/{ClienteScope,EmailService}.php`, `app/Models/{UsuarioModel,ClienteModel,RolModel,UsuarioRolModel,ClienteConsejoModel,ActaModel,ActaAsistenteModel,ActaCompromisoModel,ActaAuditoriaModel}.php`, `app/Views/{auth/login,dashboard/index,clientes/*,usuarios/*,actas/*,emails/*}.php`, `app/Database/Migrations/2026-06-12-*` (incluye núcleo de actas), `app/Database/Seeds/{Roles,Superadmin,Database}Seeder.php`, `public/{manifest_login.json,sw_login.js,assets/icons/*}`.

---

## Fase 0 — Cimientos (setup)
- [x] Instalar CodeIgniter 4 (appstarter v4.7.3)
- [x] Configurar `.env` local + clave de encriptación
- [x] Crear base de datos local `actas`
- [x] Migración `tbl_clientes` (renombrada desde `tbl_conjuntos` por migración)
- [x] Migración `tbl_roles`
- [x] Migración `tbl_usuarios`
- [x] Migración `tbl_usuario_rol` (pivote con FKs)
- [x] Seeder de roles (7)
- [x] Seeder de superadmin (Edison Cuervo)
- [x] Repo en GitHub + primer push (rama `main`)
- [x] Desplegar en servidor de producción (git clone/pull, composer install, `.env` prod, docroot → `/public`)
- [x] Migraciones + seeders en producción (DigitalOcean)
- [x] Verificar que `https://actas.cycloidtalent.com/` sirve la app (no el index por defecto)

## Fase 1 — Autenticación, roles y PWA base
- [x] Modelos base de usuario/roles (`UsuarioModel`, `RolModel`, `UsuarioRolModel`)
- [x] Sesiones (CI4 file sessions por defecto; no requiere tabla en BD)
- [x] Vista + controlador de **login** (email + password, verificación bcrypt)
- [x] Filtro `auth` (proteger rutas autenticadas) — aplicado a `/dashboard`
- [x] Filtro `rol` / RBAC (autorización por rol)
- [x] Selección de cliente activo (multi-tenant) y guard de alcance por cliente
- [x] Logout
- [x] **PWA instalable desde el login** (`manifest_login.json` + `sw_login.js`, íconos PNG 192/512)
- [ ] Recuperación de contraseña por email (cambia la clave temporal `actas123`)
- [x] Dashboard inicial por rol (básico)

## Fase 2 — Administración (CRUD maestros)
- [x] CRUD de **clientes** (solo superadmin; incluye logo)
- [x] Datos del cliente (logo, NIT, dirección, ciudad, teléfono, email)
- [x] CRUD de **usuarios** + asignación de **roles por cliente**
- [x] Gestión de **consejo de administración** por cliente (miembros: presidente, consejeros)
- [x] Integrar logo/datos del cliente en encabezado del PDF del acta — pendiente: logo en ecosistema post-login (nav)

## Fase 3 — Núcleo de Actas
- [x] Migraciones: `tbl_actas`, `tbl_acta_asistentes`, `tbl_acta_compromisos`, `tbl_acta_votaciones`, `tbl_acta_anexos`, `tbl_actas_plantillas_orden`, `tbl_actas_auditoria`
- [x] Crear acta (borrador): número/consecutivo, fecha, lugar, modalidad
- [x] Orden del día inicial (plantillas reutilizables pendientes)
- [x] Registro de asistencia y cálculo de **quórum**
- [x] Desarrollo / conclusiones / observaciones
- [x] **Compromisos/tareas** (responsable, vencimiento, estado, avance)
- [x] **Votaciones/decisiones** (favor/contra/abstención, resultado por mayoría)
- [x] Anexos (adjuntos) — subida/descarga/eliminar, almacenamiento en writable, listados en el PDF
- [ ] Estados del acta: `borrador → pendiente_firma → firmada` (+ `en_edicion`, `anulada`)

## Fase 4 — Firmas (email + WhatsApp)
- [x] Migración `tbl_actas_tokens` (tokens de firma) — solicitudes reapertura/ausente pendientes
- [x] Generación de tokens de firma por asistente (al cerrar el acta)
- [x] Página pública de firma por token (canvas, guarda base64 + IP + fecha)
- [x] `EmailService` con SendGrid SDK v7 (envío de enlaces de firma) — pendiente configurar API key/remitente para validar envío real
- [x] Envío de enlaces de firma por **WhatsApp** — compartir enlace con `wa.me`, sin API externa
- [x] Panel de estado de firmas (con enlaces para copiar; reenviar/cancelar pendiente)
- [x] Cierre automático del acta al completar firmas + `codigo_verificacion`
- [x] Verificación pública del acta por código
- [ ] Solicitud de reapertura y marcar ausente (aprobación por token)

## Fase 5 — Exportación y notificaciones
- [x] Exportar acta a **PDF** (Dompdf) — incluye logo, datos, asistentes/quórum, votaciones, compromisos
- [ ] Exportar acta a **Word** (.doc)
- [ ] Migración `tbl_actas_notificaciones` (cola de emails)
- [ ] Comando cron `actas:notificaciones` (recordatorios de firma y tareas)
- [ ] Configurar cron en el servidor

## Fase 6 — PWA completa y pulido
- [ ] Manifest + service worker de la app principal (post-login)
- [ ] Caché offline básica
- [ ] Auditoría/log de acciones (`tbl_actas_auditoria`)
- [ ] Validaciones y manejo de errores
- [ ] Pruebas (PHPUnit) de flujos críticos
- [ ] Auditoría de seguridad (CSRF, permisos por cliente, escapado)

## Fase 7 — Lanzamiento
- [ ] Checklist de despliegue (composer install --no-dev, migraciones prod, permisos `writable/`)
- [ ] Backups de BD
- [ ] Monitoreo / logs
- [ ] Documentación de usuario por rol
