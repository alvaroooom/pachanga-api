# 3.7. ARQUITECTURA Y STACK TECNOLÓGICO

Se plantea una arquitectura cliente-servidor en la que los clientes (aplicación móvil/web y panel de administración) consumen una API REST común. En esta primera versión se implementa el **backend y la API REST**; los clientes se desarrollarán en fases posteriores.

La tecnología utilizada es la siguiente:

- **Backend y API REST:** PHP 8.4 con el framework **Laravel 13**, siguiendo una arquitectura por capas (rutas, controladores, modelos Eloquent y migraciones).
- **Base de datos relacional:** **MySQL**, gestionada mediante migraciones de Laravel y modelada con el ORM Eloquent. La integridad referencial se garantiza con claves foráneas y borrado en cascada.
- **Autenticación:** **Laravel Sanctum**, que emite tokens de acceso personales para autenticar las peticiones a la API. Las contraseñas se almacenan con hash seguro (bcrypt) y la comunicación se realizará mediante HTTPS.
- **Generación de código:** **Laravel Blueprint**, que permite definir los modelos en un fichero `draft.yaml` y generar a partir de él modelos, migraciones y factories.
- **Pruebas y documentación de la API:** PHPUnit para las pruebas automatizadas y **Postman** para probar y documentar los endpoints.
- **Frontend móvil y web:** pendiente de definir en una fase posterior (Flutter o React Native para la aplicación móvil, y un framework web como React o Vue para el cliente web y el panel de administración). Todos consumirán la misma API REST.
- **Mejoras futuras previstas:** servicios en tiempo real para el chat (por ejemplo, Redis o Firebase), notificaciones push con Firebase Cloud Messaging y despliegue en contenedores Docker en la nube.

## Cambios derivados en otros apartados

- **RNF-03 (Seguridad):** donde dice "autenticación basada en JSON Web Tokens (JWT)", cambiar por "autenticación basada en tokens de acceso personales mediante Laravel Sanctum".
- **3.1, componente C4:** donde dice "soporte para consultas geográficas", añadir "(ampliación futura)", ya que en esta versión los campos solo guardan la ciudad y la dirección.
- **3.1, componente C5 (tiempo real):** marcarlo también como ampliación futura, ya que el chat y las notificaciones push no forman parte de la versión actual.
