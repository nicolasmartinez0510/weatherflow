# WeatherFlow API

API REST para gestion de usuarios, estaciones meteorologicas y mediciones, implementada con Laravel 13 y arquitectura hexagonal (dominio y casos de uso en `src/`, adaptadores HTTP e infraestructura en Laravel).

## Requisitos

- PHP 8.3 o superior
- Composer 2
- MongoDB 7 (local o en Docker)
- Extension `mongodb` de PHP habilitada
- Node.js 20+ y npm (solo si queres compilar assets frontend)
- Docker Desktop (opcional, para correr con Sail)

## Clonar e instalar

```bash
git clone <repo-url>
cd weatherflow
composer install
cp .env.example .env
php artisan key:generate
```

## Configuracion de entorno (`.env`)

Variables importantes:

```dotenv
APP_URL=http://localhost:8100
MONGODB_URI=mongodb://mongodb:27017
MONGODB_DATABASE=weatherflow
L5_SWAGGER_BASE_PATH="${APP_URL}"
```

Notas:

- Con Sail, el host de Mongo suele ser `mongodb`.
- Si corres todo en tu host (sin Docker), usa por ejemplo `MONGODB_URI=mongodb://127.0.0.1:27017`.
- La API de Laravel usa el prefijo `/api` (ejemplo: `http://localhost:8100/api/users`).

## Levantar el proyecto

### Opcion A: Laravel Sail (recomendada)

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan config:clear
```

Servicios relevantes en este proyecto:

- App: `http://localhost:8100`
- MongoDB: `mongodb://127.0.0.1:27017` (desde tu host)

### Opcion B: PHP local (sin Sail)

Asegurate de tener Mongo corriendo en la URI configurada y luego:

```bash
php artisan config:clear
php artisan serve
```

Por defecto Laravel queda en `http://127.0.0.1:8000`.

## Base de datos y migraciones

El core de WeatherFlow persiste en Mongo (`users`, `stations`, `measurements`).

Si ademas queres inicializar tablas SQL de soporte de Laravel (segun tu `DB_CONNECTION`), ejecuta:

```bash
php artisan migrate
```

Con Sail:

```bash
./vendor/bin/sail artisan migrate
```

## OpenAPI / Swagger

Generar documentacion:

```bash
php artisan l5-swagger:generate
```

o usando el script de Composer:

```bash
composer docs:openapi
```

Con Sail:

```bash
./vendor/bin/sail artisan l5-swagger:generate
```

Swagger UI:

- `GET /api/documentation`
- Ejemplo con Sail: `http://localhost:8100/api/documentation`

## Correr tests

La suite incluye `Unit` y `Feature`. Para tests de integracion con Mongo, revisar `.env.testing` (`WEATHERFLOW_TEST_USE_MONGO=true` y `MONGODB_DATABASE=weatherflow-tests`).

```bash
php artisan test
```

o:

```bash
composer test
```

Con Sail:

```bash
./vendor/bin/sail artisan test
```

## Postman

Hay una coleccion lista para importar:

- `docs/postman/WeatherFlow.postman_collection.json`

Configura la variable `baseUrl` segun tu entorno (`http://localhost:8100/api` con Sail o `http://127.0.0.1:8000/api` local).

## Estructura del proyecto (hexagonal)

El codigo de negocio vive bajo `src/` (`WeatherFlow\`):

- `src/Domain`: entidades, value objects, servicios de dominio, puertos de repositorio.
- `src/Application`: casos de uso.
- `src/Infrastructure`: adaptadores de persistencia y otros detalles tecnicos.
- `app/Http`: controladores y requests HTTP delgados.

Los bindings de puertos a adaptadores se registran en `app/Providers/DomainServiceProvider.php`.
