# Arquitectura de LEO AutoParts

## 1. Enfoque

La aplicación usa una arquitectura modular pragmática sobre las convenciones de Laravel. Las carpetas se agrupan por función del negocio cuando eso facilita encontrar el código; las piezas centrales de Laravel —modelos, migraciones, rutas y componentes Blade reutilizables— conservan sus ubicaciones estándar.

El objetivo no es crear capas por ceremonia, sino que cada archivo tenga una responsabilidad reconocible.

```text
Navegador
   │
   ▼
routes/web.php ── permisos ──► Livewire/*/*Page.php
                                      │
                    ┌─────────────────┼─────────────────┐
                    ▼                 ▼                 ▼
              Vista Blade        Servicio          Modelo Eloquent
              (presentación)  (reglas/transacción) (datos/relaciones)
                    │                 │                 │
                    └─────────────────┴────────► MySQL / Storage
```

## 2. Árbol que debe aprenderse

```text
app/
├── Exports/Reports/          Exportadores PDF y Excel
├── Http/Middleware/          Control de acceso por rol
├── Livewire/                 Pantallas y estado interactivo
│   ├── Administration/
│   ├── Cash/
│   ├── Customers/
│   ├── Dashboard/
│   ├── Inventory/
│   ├── Purchases/
│   ├── Reports/
│   ├── Sales/
│   └── Suppliers/
├── Models/                   Tablas, relaciones y casts
└── Services/                 Operaciones críticas reutilizables
    ├── Cash/
    ├── Media/
    ├── Purchases/
    └── Sales/

resources/
├── css/app.css               Sistema visual y variantes claro/oscuro
├── js/app.js                 Inicialización frontend
└── views/
    ├── components/           Botones, campos, modales y piezas reutilizables
    ├── exports/              Plantillas de documentos (factura)
    ├── layouts/              Marco de la aplicación
    │   └── partials/         Sidebar y barra superior
    └── livewire/             HTML de cada módulo

public/images/brand/          Logo, iconos y wordmarks de la marca
storage/app/public/           Archivos generados y cargados en ejecución
database/migrations/          Evolución verificable del esquema
tests/                        Pruebas unitarias y de integración
```

## 3. Responsabilidad de cada capa

### Rutas

`routes/web.php` define URL, nombre de ruta, pantalla Livewire y permisos. Es el índice principal para comprender qué puede abrir el usuario.

### Páginas Livewire

Las clases `*Page.php` mantienen el estado visible, validan la entrada inmediata, consultan datos para la pantalla y traducen acciones de botones a llamadas de aplicación. No deben contener transacciones contables complejas ni generación de documentos.

Ejemplo: `Sales/InvoicingPage.php` administra la selección de cliente y los renglones de la factura, pero delega la venta a `SaleService` y el documento a `InvoicePdfService`.

### Servicios

Los servicios concentran reglas que deben cumplirse aunque cambie la interfaz:

- `Sales/SaleService.php`: crea y anula ventas dentro de transacciones.
- `Sales/InvoicePdfService.php`: genera y almacena la factura PDF.
- `Purchases/PurchaseService.php`: registra compras, saldos y entradas de inventario.
- `Cash/CashService.php`: abre/cierra caja y registra movimientos.
- `Media/ProductImageService.php`: normaliza imágenes y fondos optimizados.

Una operación que modifica varias tablas debe vivir aquí y usar `DB::transaction`.

### Modelos

`app/Models` representa los conceptos persistentes: venta, detalle, producto, pago, cliente, compra, caja, movimiento, etc. Aquí se encuentran relaciones, atributos asignables, casts y borrado lógico. Permanecen juntos porque es la convención universal de Laravel y permite localizarlos rápidamente.

### Vistas y componentes gráficos

La vista de una pantalla está en la misma área funcional que su clase, pero bajo `resources/views/livewire`. El diseño global no debe repetirse manualmente:

- `resources/css/app.css`: tokens y clases `leo-*` del sistema visual.
- `resources/views/components`: controles y modales reutilizables.
- `resources/views/layouts/app.blade.php`: estructura general.
- `resources/views/layouts/partials/sidebar.blade.php`: opciones, iconos y permisos del menú.
- `resources/views/layouts/partials/topbar.blade.php`: marca, tema y usuario.
- `public/images/brand`: archivos gráficos estáticos.

Las imágenes de productos no pertenecen al repositorio de interfaz: se guardan en `storage/app/public/productImages` y se publican mediante el enlace `public/storage`.

## 4. Recorrido de una venta

1. La ruta `invoicing` abre `Livewire/Sales/InvoicingPage.php`.
2. Esa clase renderiza `views/livewire/sales/invoicing.blade.php`.
3. Los botones `rowSelect`, `addToInvoice`, `selectCustomer` y `saveInvoice` actualizan el estado Livewire.
4. `saveInvoice` llama a `Services/Sales/SaleService.php`.
5. El servicio valida cliente, método, stock, caja y montos; luego escribe venta, detalles, pago, Kardex y movimiento de caja dentro de una transacción.
6. `InvoicePdfService` renderiza `views/exports/invoice.blade.php` y guarda el PDF.
7. La página entrega la URL del documento al navegador.

Este recorrido es un buen ejercicio para aprender la aplicación de extremo a extremo.

## 5. Convenciones para código nuevo

- Una pantalla nueva se llama `<Funcion>Page` y vive dentro de su módulo.
- Su vista usa minúsculas y nombres descriptivos, por ejemplo `livewire.sales.history`.
- El componente coordina; el servicio protege reglas del negocio.
- No se hacen cambios de stock, saldos o caja fuera de una transacción.
- Se usa inyección de dependencias en acciones, no `new Servicio` ni localizadores globales.
- Las consultas cargan relaciones con `with()` cuando la vista las utilizará.
- Los nombres de archivos y recursos no contienen espacios.
- Antes de integrar: Pint, compilación frontend y pruebas.

## 6. Cómo añadir una función

1. Definir la regla y los datos que necesita.
2. Crear o modificar una migración si cambia el esquema.
3. Añadir relaciones/casts al modelo.
4. Implementar la operación crítica en un servicio de dominio.
5. Crear la página Livewire y su vista.
6. Registrar la ruta y el permiso.
7. Añadir el acceso visible al sidebar o a la pantalla relacionada.
8. Cubrir la regla con pruebas y ejecutar los comandos de calidad del README.

## 7. Decisiones de compatibilidad

- Se conservaron los nombres públicos de rutas para no romper enlaces existentes; la URL del historial se normalizó a `/sales-history`.
- `resources/views/livewire/layout/navigation.blade.php` permanece porque el flujo de autenticación de Breeze/Volt y su prueba lo utilizan.
- Los modelos siguen en `app/Models`; moverlos por módulo produciría más imports y se apartaría de la convención Laravel sin aportar claridad en este tamaño de proyecto.
