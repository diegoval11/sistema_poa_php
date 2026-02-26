# Stack Tecnológico

## Dependencias de Producción

| Capa | Tecnología | Versión | Uso |
|---|---|---|---|
| **Backend** | PHP + Laravel | ^12 / PHP ^8.2 | Framework principal |
| **Frontend** | Blade + Vite | — | Plantillas y bundler de assets |
| **Base de Datos** | MySQL / SQLite | — | Persistencia de datos |
| **Autenticación** | Laravel Breeze | ^2.3 | Login, sesiones, cambio de clave |
| **Exportación Excel** | Maatwebsite/Excel | ^3.1 | Generación de reportes `.xlsx` |
| **Exportación PDF** | barryvdh/laravel-dompdf | ^3.1 | Generación de reportes `.pdf` |

## Dependencias de Desarrollo

| Herramienta | Versión | Para qué |
|---|---|---|
| PestPHP | ^4.3 | Testing |
| Laravel Pint | ^1.24 | Formato de código (PSR-12) |
| Laravel Sail | ^1.41 | Entorno Docker opcional |
| Faker | ^1.23 | Seeders y factories |

## Frontend

El frontend usa **Blade** (motor de plantillas de Laravel) con assets gestionados por **Vite**. No se usa ningún framework JS mayor (React/Vue). La interactividad se implementa con JavaScript vanilla y Alpine.js donde se necesita.

::: info
Todos los assets de frontend se compilan con `npm run dev` (development) o `npm run build` (producción).
:::
