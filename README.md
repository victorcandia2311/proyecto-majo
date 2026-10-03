# Proyecto Majo

Aplicacion de gestion para productos, inventario, ventas, proveedores, empleados y asistencia.

## Requisitos

- PHP 8.3 o superior
- Composer
- Node.js y npm
- PostgreSQL

## Instalacion

1. Instala las dependencias de PHP con `composer install`.
2. Copia `.env.example` como `.env` y configura PostgreSQL y el correo.
3. Ejecuta `php artisan key:generate`.
4. Ejecuta las migraciones con `php artisan migrate`.
5. Instala las dependencias del frontend con `npm install` y genera los recursos con `npm run build`.

Para desarrollo, inicia Vite con `npm run dev` y Laravel con `php artisan serve`.

## Pruebas

Ejecuta `php artisan test`.