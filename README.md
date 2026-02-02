# Sistema de POA | Alcaldía Municipal de Santa Ana

![PHP-8.2](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Laravel-11](https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)

## Descripción del Proyecto

El **Sistema de POA** es una plataforma integral desarrollada para la **Alcaldía Municipal de Santa Ana**, diseñada para optimizar y centralizar la gestión del Plan Operativo Anual. Esta herramienta permite a las unidades organizativas registrar, planificar y dar seguimiento a sus metas, actividades y presupuestos, mientras que proporciona a los administradores una visión global del desempeño institucional a través de reportes avanzados y tableros de control.

## Requisitos del Servidor

Para ejecutar este proyecto, asegúrate de que tu servidor cumpla con los siguientes requisitos:

-   **PHP**: Versión 8.2 o superior.
-   **Composer**: Para la gestión de dependencias de PHP.
-   **Node.js y NPM**: Para la compilación de assets de frontend.
-   **Base de Datos**: MySQL 8.0, MariaDB o equivalente.

## Instalación Local (Paso a Paso)

Sigue estos pasos para levantar el entorno de desarrollo en tu máquina local:

1.  **Clonar el repositorio**
    
    ```bash
    git clone [https://github.com/tu-usuario/sistema-poa.git](https://github.com/tu-usuario/sistema-poa.git)
    cd sistema-poa
    ```
    

2.  **Instalar dependencias de Backend**
    
    Descarga e instala las librerías necesarias de Laravel utilizando Composer.
    
    ```bash
    composer install
    ```
    

3.  **Configurar Variables de Entorno y Clave de Aplicación**
    
    Copia el archivo de configuración de ejemplo y genera una nueva clave de encriptación.
    
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    

4.  **Configurar Base de Datos**
    
    Abre el archivo `.env` y configura tus credenciales de base de datos (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`). Luego, ejecuta las migraciones y los seeders para crear las tablas y usuarios base:
    
    ```bash
    php artisan migrate --seed
    ```
    

5.  **Instalar y Compilar Frontend**
    
    Este proyecto utiliza Tailwind CSS, por lo que es obligatorio instalar y compilar los assets.
    
    ```bash
    npm install
    npm run build
    ```
    

6.  **Vincular el Almacenamiento (Storage)**
    
    Este comando es **vital** para que la carga de archivos (evidencias, documentos) funcione correctamente.
    
    ```bash
    php artisan storage:link
    ```
    

7.  **Iniciar Servidor Local**
    
    ```bash
    php artisan serve
    ```
    

## Guía de Despliegue en Producción (Deployment)

Para desplegar la aplicación en un entorno productivo, sigue estas recomendaciones adicionales para garantizar rendimiento y seguridad:

### 1. Optimización de Carga

Ejecuta los siguientes comandos para cachear la configuración, rutas y vistas. Esto mejora significativamente la velocidad de respuesta.

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

2. Compilación de Assets para Producción
Asegúrate de compilar los estilos y scripts minificados.


```
bash
npm run build
```
3. Permisos de Carpetas
Asegúrate de que el servidor web (Nginx/Apache) tenga permisos de escritura sobre las carpetas de almacenamiento y caché.

```
Bash

chmod -R 775 storage bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
```
4. Configuración del Servidor Web
Es crucial que tu servidor web apunte directamente a la carpeta /public del proyecto como su raíz (Document Root). Esto asegura que los archivos del sistema no sean accesibles públicamente.

👤 Usuarios por Defecto
Al ejecutar el comando de migración con seed (migrate --seed), se crearán las siguientes credenciales de acceso:

Administrador: admin@alcaldia.gob.sv / password

Unidad de Prueba: unidad@alcaldia.gob.sv / password
