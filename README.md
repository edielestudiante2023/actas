# actas

Aplicativo **PWA** para la gestión de **actas de reunión de los consejos de administración** en propiedad horizontal.

## Stack
- **Backend:** CodeIgniter 4 (PHP 8.4)
- **Base de datos:** MySQL / MariaDB
- **Frontend:** PWA instalable (manifest + service worker)

## Actores / Roles
`superadmin`, `administrador`, `presidente_consejo`, `consejero`, `revisor_fiscal`, `contador`, `abogado`.

El aplicativo es **multi-tenant**: una sola instalación atiende varios clientes de propiedad horizontal. Un usuario puede tener varios roles, y el rol se asigna **por cliente**.

## Puesta en marcha (local)
```bash
composer install
cp env .env            # configurar base de datos local
php spark key:generate
php spark migrate
php spark db:seed DatabaseSeeder
php spark serve        # http://localhost:8080
```

## Reglas de base de datos
- Los cambios de esquema se aplican **solo** vía migraciones (`php spark migrate`), nunca SQL manual.
- Orden: primero **LOCAL**, y solo si queda OK, **PRODUCCIÓN**.
