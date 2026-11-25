# Cambios para la memoria (v1.2)

Texto listo para copiar a la memoria. Cada bloque indica el apartado donde va.
El diagrama de clases es `docs/diagrama_clases.svg` (Word permite insertar SVG con *Insertar → Imágenes*).

---

## Hoja de control de cambios (añadir fila)

| Versión | Descripción | Fecha |
|---|---|---|
| 1.2 | Entrega 2: modelo de datos de la API (Blueprint), colección de Postman y primera versión del panel de administración web | (poner la fecha de entrega) |

---

## 3.1. Visión general del sistema

**C4 - Base de datos relacional:** almacena usuarios, partidos, recintos, publicaciones y valoraciones. *(Las consultas geográficas quedan como ampliación futura; en la versión actual cada recinto guarda su ciudad y su dirección.)*

**C5 - Servicios en tiempo real:** mensajería instantánea, caché y envío de notificaciones push. *(Ampliación futura: no forma parte de la versión actual.)*

---

## 3.6. Requisitos no funcionales

**RNF-02 Rendimiento:** el tiempo de respuesta de la API deberá ser inferior a 200 ms en las búsquedas de campos y partidos.

**RNF-03 Seguridad:** todas las comunicaciones se cifrarán mediante TLS/HTTPS, los datos sensibles se cifrarán en reposo y la autenticación se basará en tokens de acceso personales mediante Laravel Sanctum; las contraseñas se almacenarán con un algoritmo de hash seguro.

---

## 3.7. Arquitectura y stack tecnológico

Se propone una arquitectura cliente-servidor en la que los dos clientes (app móvil y panel web) consumen una API REST común. El proyecto se organiza en tres repositorios independientes, uno por cada parte: `pachanga-api`, `pachanga-web` y `pachanga-app`.

La tecnología utilizada es la siguiente:

- **Backend y API REST (`pachanga-api`):** PHP 8.4 con el framework **Laravel 13**, con una arquitectura por capas: rutas, controladores, modelos Eloquent y migraciones.
- **Base de datos relacional:** **MySQL**, gestionada mediante migraciones de Laravel y modelada con Eloquent. La integridad referencial se garantiza con claves foráneas y borrado en cascada.
- **Autenticación:** **Laravel Sanctum**, que emite tokens de acceso personales. Las contraseñas se guardan con hash seguro (bcrypt) y la comunicación se realizará mediante HTTPS.
- **Generación de código:** **Laravel Blueprint**, que genera modelos, migraciones y factories a partir del fichero `draft.yaml`.
- **Pruebas y documentación de la API:** PHPUnit para las pruebas automatizadas y **Postman** para probar y documentar los endpoints.
- **Panel web de administración (`pachanga-web`):** **Angular** con **Tailwind CSS** para los estilos, que consume la API mediante `HttpClient`.
- **App móvil (`pachanga-app`):** **Flutter**, con una única base de código para Android e iOS (pendiente de desarrollar).
- **Mejoras futuras previstas:** servicios en tiempo real para el chat (por ejemplo Redis o Firebase), notificaciones push con Firebase Cloud Messaging y despliegue en contenedores Docker en la nube.

---

## 4.2. Diagrama de clases del modelo de dominio de la API

*(Insertar aquí `docs/diagrama_clases.svg`.)*

El modelo de dominio se compone de siete clases:

- **User:** persona registrada. Tiene un rol (`admin`, `user` o `guest`).
- **Venue (campo):** recinto donde se juega. Tiene nombre, dirección, ciudad y tipo de superficie (`artificial`, `natural` o `indoor`).
- **Game (partido):** partido organizado por un usuario en un campo, con fecha, número máximo de jugadores, precio, descripción y estado (`open`, `full` o `cancelled`).
- **Registration (inscripción):** apunta a un usuario a un partido, con estado `confirmed` o `waitlist`. Un usuario no puede inscribirse dos veces en el mismo partido.
- **Rating (valoración):** puntuación y comentario que un usuario da a otro jugador tras un partido.
- **Post:** publicación de un usuario en el muro social.
- **Comment:** comentario de un usuario en un post.

Relaciones principales:

- Un usuario organiza muchos partidos (1 a N) y un campo acoge muchos partidos (1 a N).
- Un usuario y un partido se relacionan mediante la inscripción (N a M, resuelta con la clase `Registration`).
- Cada valoración pertenece a un partido y relaciona a dos usuarios: quien valora y quien es valorado.
- Un usuario escribe muchos posts y comentarios, y un post tiene muchos comentarios.
- Al borrar un usuario, un partido o un post se borran en cascada los datos que dependen de ellos.

---

## 5.1. Diagrama de clases del diseño

*(Usar el mismo diagrama `docs/diagrama_clases.svg`, que ya incluye los tipos de datos, las claves foráneas, las multiplicidades y los nombres de rol.)*

Direccionalidad: todas las asociaciones son bidireccionales en Eloquent. Por ejemplo, `Game` accede a sus inscripciones con `registrations()` y `Registration` accede a su partido con `game()`.

---

## 6.1. Descripción de paquetes y clases

### `pachanga-api` (Laravel)

- **`app/Models`:** clases del modelo de datos (`User`, `Venue`, `Game`, `Registration`, `Rating`, `Post`, `Comment`). Definen los campos rellenables y las relaciones Eloquent (`hasMany`, `belongsTo`).
- **`app/Http/Controllers/Auth`:** registro, inicio y cierre de sesión. El inicio de sesión devuelve un token de Sanctum.
- **`app/Http/Controllers/Admin`:** controladores del panel de administración. `UserController`, `VenueController`, `GameController` y `PostController` implementan el CRUD de cada entidad; además hay controladores para las inscripciones, las valoraciones, los comentarios y las estadísticas.
- **`app/Http/Middleware/EnsureUserIsAdmin`:** restringe las rutas de administración a usuarios con rol `admin` (devuelve 403 al resto y 401 si no hay token).
- **`routes/api.php` y `routes/auth.php`:** definición de los endpoints. Todos los de administración cuelgan de `/api/admin`.
- **`database/migrations`:** creación de las tablas con claves foráneas y borrado en cascada.
- **`database/factories` y `database/seeders`:** datos de prueba y usuario administrador inicial.
- **`tests/Feature`:** pruebas automatizadas de la autenticación y del panel de administración.
- **`draft.yaml`:** definición de los modelos para Laravel Blueprint.
- **`docs`:** colección de Postman y documentación del modelo.

### `pachanga-web` (Angular)

- **`src/app/pages`:** componentes de página (`users-page`, `venues-page`, `games-page`, `posts-page`, `not-found-page`). Cada uno muestra el listado en una tabla y gestiona el CRUD.
- **`src/app/components`:** componentes reutilizables, como la barra de navegación.
- **`src/app/modals`:** modales de añadir y editar (`user-modal`, `venue-modal`, `game-modal`, `post-modal`).
- **`src/app/shared`:** `confirm-delete-modal`, modal de confirmación que devuelve por output si el usuario acepta o cancela el borrado.
- **`src/app/services`:** servicios que se conectarán con la API (por ahora sin métodos, a la espera de los endpoints).
- **`src/app/models`:** interfaces TypeScript de cada entidad.
- **`src/app/app.routes.ts` y `app.config.ts`:** rutas (con la ruta por defecto y la de página no encontrada) y configuración de `HttpClient`.
