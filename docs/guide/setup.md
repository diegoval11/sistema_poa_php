# Cómo Levantar el Proyecto

## Requisitos previos

- PHP >= 8.2
- Composer
- Node.js >= 18
- MySQL (o SQLite para desarrollo local)

## Instalación rápida

```bash
# 1. Clonar el repositorio
git clone https://github.com/diegoval11/sistema_poa_php.git
cd sistema_poa_php

# 2. Instalar dependencias PHP
composer install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Configurar base de datos en .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_DATABASE=sistema_poa
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Ejecutar migraciones y seeders
php artisan migrate --seed

# 6. Instalar dependencias JS
npm install

# 7. Levantar servidores en paralelo
composer run dev
```

::: tip Atajo con `composer run dev`
El comando `composer run dev` lanza en paralelo:
- `php artisan serve` — Servidor PHP en `http://localhost:8000`
- `npm run dev` — Vite en modo HMR
- `php artisan queue:listen` — Cola de trabajos
- `php artisan pail` — Logs en tiempo real
:::

## Configuración de Storage

Las evidencias subidas se guardan en `storage/app/public`. Para que sean accesibles:

```bash
php artisan storage:link
```

## Credenciales por defecto (Seeder)

| Rol | Email | Contraseña |
|---|---|---|
| Admin | `admin@alcaldia.gob.sv` | (ver seeder) |
| Unidad | `unidad@alcaldia.gob.sv` | (ver seeder) |

::: warning
Al primer login, el sistema fuerza el cambio de contraseña si `debe_cambiar_clave = true`.
:::

## Levantar la Documentación (este sitio)

```bash
cd docs
npm install
npm run docs:dev
# → http://localhost:5173
```
