# LEO AutoParts

Sistema web de inventario, compras, caja, ventas y facturación para una tienda de repuestos automotrices. El proyecto está pensado como una aplicación operativa demostrable y como portafolio técnico ejecutable con Docker.

## Funciones principales

- Catálogo e inventario con imágenes optimizadas, existencias, precios y categorías.
- Facturación a clientes con control transaccional de stock y factura PDF.
- Historial de ventas, pagos y anulaciones con reversión de inventario.
- Compras a proveedores con costo, saldo pendiente y entrada automática al Kardex.
- Apertura, movimientos y cierre de caja.
- Clientes, proveedores, usuarios, roles, configuración y respaldos.
- Reportes PDF/Excel y panel de indicadores.
- Interfaz responsive con tema claro y oscuro.

## Tecnología

- PHP 8.4, Laravel 11 y Eloquent ORM.
- Livewire 3 y Blade para la interfaz reactiva.
- Tailwind CSS 3 y Vite 6.
- MySQL 8.
- Docker Compose / Laravel Sail.
- Pest para pruebas; Laravel Pint para estilo PHP.

## Ejecutar con Docker

```bash
cp .env.example .env
docker compose --env-file .env up -d --build
docker compose --env-file .env exec -u sail laravel.test composer install
docker compose --env-file .env exec -u sail laravel.test php artisan key:generate
docker compose --env-file .env exec -u sail laravel.test php artisan migrate --seed
docker compose --env-file .env exec -u sail laravel.test php artisan storage:link
docker compose --env-file .env exec -u sail laravel.test npm install
docker compose --env-file .env exec -u sail laravel.test npm run build
```

La aplicación queda disponible en `http://localhost` (o en el puerto configurado mediante `APP_PORT`).

## Comandos de calidad

```bash
docker compose --env-file .env exec -u sail laravel.test php artisan test
docker compose --env-file .env exec -u sail laravel.test vendor/bin/pint --test
docker compose --env-file .env exec -u sail laravel.test npm run build
```

## Entender el proyecto

- [Arquitectura y guía de código](docs/ARQUITECTURA.md)
- [Mapa funcional de pantallas y clases](docs/MAPA_FUNCIONAL.md)
- [Estado técnico y trabajo pendiente](docs/ESTADO_TECNICO.md)

El punto de entrada recomendado es `routes/web.php`. Desde allí puede seguirse cada ruta hacia una clase `app/Livewire/<Modulo>/*Page.php`, su vista equivalente en `resources/views/livewire/<modulo>/` y, cuando existe lógica crítica, un servicio en `app/Services/<Modulo>/`.

## Licencia

Este repositorio corresponde al proyecto LEO AutoParts. Revise las licencias de las dependencias declaradas en Composer y npm antes de redistribuirlo.
