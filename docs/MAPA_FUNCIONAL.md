# Mapa funcional

Esta tabla permite partir de lo que ve el usuario y llegar al código que lo implementa.

| Función visible | Ruta | Página Livewire | Vista Blade | Lógica principal | Datos principales |
|---|---|---|---|---|---|
| Resumen del negocio | `dashboard` | `Dashboard/DashboardPage.php` | `dashboard/index.blade.php` | Consultas de indicadores | Sale, SaleDetail, Customer, Product, User |
| Catálogo | `catalog` | `Inventory/CatalogPage.php` | `inventory/catalog.blade.php` | Búsqueda y disponibilidad | Product |
| Inventario | `inventory` | `Inventory/InventoryPage.php` | `inventory/index.blade.php` | CRUD de productos e imágenes | Product, Category, Supplier; `Media/ProductImageService` |
| Kardex | `kardex` | `Inventory/KardexPage.php` | `inventory/kardex.blade.php` | Consulta de movimientos | InventoryMovement, Product |
| Nueva venta | `invoicing` | `Sales/InvoicingPage.php` | `sales/invoicing.blade.php` | Carrito, cliente, pago, factura | `Sales/SaleService`, `Sales/InvoicePdfService` |
| Historial de ventas | `salesHistory` | `Sales/SalesHistoryPage.php` | `sales/history.blade.php` | Consulta y anulación | Sale; `Sales/SaleService` |
| Compras | `purchases` | `Purchases/PurchasesPage.php` | `purchases/index.blade.php` | Entrada, costo y saldo | `Purchases/PurchaseService` |
| Caja | `cash` | `Cash/CashPage.php` | `cash/index.blade.php` | Apertura, movimientos y cierre | `Cash/CashService` |
| Clientes | `customers` | `Customers/CustomersPage.php` | `customers/index.blade.php` | CRUD y clasificación | Customer, CustomerType |
| Proveedores | `suppliers` | `Suppliers/SuppliersPage.php` | `suppliers/index.blade.php` | CRUD | Supplier |
| Reportes | `reports` | `Reports/ReportsPage.php` | `reports/index.blade.php` | Descargas PDF/Excel | `Exports/Reports/*Export.php` |
| Usuarios y fotos | `users` | `Administration/UsersPage.php` | `administration/users.blade.php` | CRUD, rol y avatar | User, Role |
| Configuración | `configuration` | `Administration/ConfigurationPage.php` | `administration/configuration.blade.php` | Roles, tipos y respaldos | Role, CustomerType, User, Storage |

Los paths de Página y Vista son relativos a `app/Livewire` y `resources/views/livewire`, respectivamente.

## Permisos

| Perfil | Acceso adicional |
|---|---|
| Administrador | Usuarios, configuración y todos los módulos operativos |
| Contador | Reportes, inventario, compras, ventas históricas, proveedores, Kardex y caja |
| Usuario autenticado | Dashboard, catálogo, clientes y facturación |

La aplicación aplica estas reglas en `routes/web.php` mediante `app/Http/Middleware/RoleMiddleware.php`. La visibilidad de opciones se refleja también en `resources/views/layouts/partials/sidebar.blade.php`.

## Modelo de datos por proceso

### Venta

`Customer → Sale → SaleDetail → Product`

Una venta también se relaciona con `Payment`, `PaymentMethod`, `InventoryMovement`, `CashSession` y `CashMovement`.

### Compra

`Supplier → Purchase → PurchaseDetail → Product`

Cada detalle produce una entrada de `InventoryMovement`; un pago inmediato puede producir un `CashMovement`.

### Inventario

`Product` pertenece a `Category` y `Supplier`. `InventoryMovement` registra entradas, salidas, ajustes y reversiones para no depender únicamente del número actual de stock.

## Dónde modificar la interfaz

| Quiero cambiar... | Archivo o carpeta |
|---|---|
| Colores, tarjetas, botones, tablas, modo oscuro | `resources/css/app.css` |
| Contenedor y estructura general | `resources/views/layouts/app.blade.php` |
| Menú lateral e iconos | `resources/views/layouts/partials/sidebar.blade.php` |
| Barra superior, avatar y tema | `resources/views/layouts/partials/topbar.blade.php` |
| Campos y modales comunes | `resources/views/components/` |
| Logo e identidad estática | `public/images/brand/` |
| Diseño de una pantalla | `resources/views/livewire/<modulo>/` |
| Comportamiento de sus botones | `app/Livewire/<Modulo>/*Page.php` |
| Reglas de venta/compra/caja | `app/Services/<Modulo>/` |
| Factura imprimible | `resources/views/exports/invoice.blade.php` |

## Puntos de estudio recomendados

1. `routes/web.php`: mapa de navegación y permisos.
2. `layouts/partials/sidebar.blade.php`: cómo una ruta se convierte en acceso visible.
3. `Inventory/CatalogPage.php` más `inventory/catalog.blade.php`: ejemplo pequeño de Livewire.
4. `Sales/InvoicingPage.php` más `Sales/SaleService.php`: ejemplo completo de interfaz y reglas transaccionales.
5. Migraciones de `database/migrations`: forma real de las tablas.
6. `tests/Feature`: comportamiento que debe conservarse al modificar el sistema.
