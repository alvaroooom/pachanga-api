# pachanga-api

API del proyecto intermodular **Pachanga**, una app para organizar partidos de fútbol entre amigos. Está hecha con Laravel y es la parte del servidor: la usarán la web de administración (Angular) y la app móvil (Flutter).

## Qué tiene de momento

- Registro, login y logout con tokens (Laravel Sanctum).
- Seis tablas: campos (`venues`), partidos (`games`), inscripciones (`registrations`), valoraciones (`ratings`), posts y comentarios.
- Un panel de administración bajo `/api/admin` para ver estadísticas y gestionar usuarios, campos, partidos y posts. Solo entran los usuarios con rol `admin`.

Los modelos salen del fichero `draft.yaml`, generado con Blueprint.

## Cómo arrancarlo

Necesitas PHP 8.4, Composer y MySQL.

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Abre el `.env` y pon los datos de tu base de datos (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`). Luego:

```bash
php artisan migrate --seed
php artisan serve
```

La API queda en `http://localhost:8000/api`.

El seeder crea un usuario administrador para probar:

- email: `admin@email.com`
- contraseña: `12345678`

## Probarla con Postman

En la carpeta `docs/` está `Pachanga_API.postman_collection.json`. Impórtala en Postman, lanza primero la petición "Login admin" (guarda el token sola) y ya puedes usar las demás.

## Tests

```bash
php artisan test
```

## Repositorios del proyecto

- `pachanga-api`: este, el backend.
- `pachanga-web`: panel de administración en Angular.
- `pachanga-app`: app móvil en Flutter.
