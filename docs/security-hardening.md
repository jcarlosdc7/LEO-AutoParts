# Correcciones de seguridad y control de caja

Fecha de aplicación: 31 de julio de 2026.

Este documento registra cada paso aplicado después de la auditoría. Las migraciones `2026_07_31_000001` a `000003` ya fueron ejecutadas sobre la base local.

## 1. Protección del proceso de venta

**Debilidad:** la interfaz enviaba precio, subtotal y total al servidor; una petición manipulada podía cobrar otro precio. La venta, sus detalles y la reducción de inventario tampoco estaban dentro de una sola transacción.

**Corrección:** se creó `SaleService`. El servidor recibe únicamente identificador y cantidad, vuelve a consultar los productos activos, bloquea sus filas con `lockForUpdate`, toma el precio desde MySQL y recalcula todos los importes. Venta, detalle, pago, movimiento de caja y movimiento de inventario se guardan en una transacción con reintentos. Ante stock insuficiente o cualquier error se revierte todo. También se corrigió el subtotal acumulado del carrito.

**Verificación:** las pruebas alteran deliberadamente el precio del carrito, comprueban el precio real, el nuevo stock y los movimientos; además verifican rollback cuando no hay existencias.

## 2. Sistema de caja

**Debilidad:** se podían registrar ventas sin apertura ni cierre de caja y no existía trazabilidad del efectivo.

**Corrección:** se agregaron cajas, sesiones y movimientos. Administradores y vendedores pueden abrir caja con un fondo inicial, registrar ingreso/gasto/retiro y cerrarla indicando el efectivo contado. El sistema calcula efectivo esperado y diferencia. Una venta exige una sesión abierta del usuario y registra automáticamente el movimiento cuando el pago es efectivo.

## 3. Roles, sesiones y usuarios

**Debilidad:** el registro público creaba cuentas operativas, algunos métodos Livewire dependían solo de la pantalla, usuarios inactivos podían seguir autenticados y el mantenimiento de usuarios tenía validación incompleta.

**Corrección:** se retiraron la ruta y el enlace de registro público. `RoleMiddleware` valida rol y estado activo con comparación estricta; `EnsureActiveUser` cierra inmediatamente la sesión de una cuenta desactivada. Ambos se conservan en peticiones Livewire. Las acciones sensibles comprueban nuevamente que el usuario sea administrador. El formulario de usuarios valida correo único, rol existente, contraseña confirmada y estado. Se impide desactivar la propia cuenta o al último administrador activo. Todo cambio relevante genera auditoría.

## 4. Conservación del historial financiero

**Debilidad:** varias claves foráneas usaban eliminación en cascada, por lo que borrar un usuario, cliente, proveedor o producto podía destruir ventas históricas.

**Corrección:** usuarios, clientes, proveedores y productos ahora se desactivan en vez de eliminarse. Las relaciones financieras críticas usan `restrictOnDelete` o `nullOnDelete` según corresponda. La autoeliminación de perfil también se convirtió en desactivación. Las consultas operativas muestran solo registros activos.

## 5. Facturas y archivos

**Debilidad:** los PDF quedaban en almacenamiento público y Dompdf permitía ejecutar PHP.

**Corrección:** las facturas se guardan en el disco privado local. La descarga pasa por un controlador autenticado que solo autoriza al vendedor propietario, administrador o contador, y responde con cabeceras `no-store` y `nosniff`. Dompdf tiene PHP y recursos remotos deshabilitados y limita su acceso al directorio público.

## 6. Respaldos

**Debilidad:** el respaldo usaba ejecución de comandos del sistema y la “restauración” extraía ZIP sin completar una restauración real ni ofrecer rollback.

**Corrección:** la creación utiliza `Artisan::call`, sin construir comandos de shell. La restauración incompleta y destructiva quedó explícitamente deshabilitada; solo acepta un nombre presente en el disco y avisa que no está disponible. No debe reactivarse hasta implementar confirmación reforzada, validación de entradas ZIP, respaldo previo y rollback probado.

## 7. Contraseñas iniciales

**Debilidad:** los seeders contenían la contraseña conocida `password` y duplicaban registros al repetirse.

**Corrección:** roles y usuarios se crean idempotentemente. Fuera de pruebas, `UserSeeder` exige `SEED_DEFAULT_PASSWORD`; no existe una contraseña operativa codificada en el repositorio. `.env.example` incluye la variable vacía y valores de base de datos que obligan a definir un secreto.

**Acción operativa:** la base local conserva las contraseñas de sus usuarios actuales para no bloquear el acceso. Deben cambiarse desde Usuarios/Perfil antes de usar datos reales. En producción se debe crear un usuario MySQL dedicado y nunca usar `root`.

## 8. Docker y configuración

**Debilidad:** HTTP, Vite y MySQL se publicaban en todas las interfaces; MySQL permitía contraseña vacía.

**Corrección:** los puertos se enlazan por defecto a `127.0.0.1`, se eliminó `MYSQL_ALLOW_EMPTY_PASSWORD` y se añadió `no-new-privileges` a ambos servicios. La plantilla usa `APP_DEBUG=false`, español, zona `America/Managua`, host Docker `mysql` y usuario dedicado. `docker compose config --quiet` confirmó que la definición es válida.

Para que Docker adopte `security_opt` y los nuevos enlaces de puertos, recrear los contenedores cuando se haya definido una contraseña no vacía:

```bash
docker compose up -d --force-recreate
```

## 9. Dependencias

**Debilidad:** Composer reportaba 50 avisos en 16 paquetes y NPM una vulnerabilidad alta heredada de C3/D3.

**Corrección:** se actualizaron Laravel y paquetes compatibles (Laravel quedó en `12.64.0`) y se retiró C3, que no era necesario para los gráficos actuales. Resultado final: `composer audit --locked` sin avisos y `npm audit --omit=dev` con cero vulnerabilidades.

## 10. Pruebas y verificación final

Se agregaron pruebas específicas para registro cerrado, permisos por rol, bloqueo de usuarios inactivos, exigencia de caja abierta, precio calculado en servidor, reducción de stock y rollback. También se ajustaron las pruebas heredadas al comportamiento seguro.

Resultados:

- Laravel: **31 pruebas aprobadas, 91 afirmaciones**.
- Composer: **0 avisos de seguridad**.
- NPM producción: **0 vulnerabilidades**.
- Vite: compilación de producción exitosa; se generó `public/build/manifest.json`.
- Docker Compose: configuración válida.
- Rutas: una sola ruta de caja y descarga de factura únicamente dentro del grupo autenticado.

## Uso del nuevo flujo

1. Un administrador crea o activa al vendedor y asigna su rol.
2. El vendedor entra a **Caja**, abre una sesión e indica el fondo inicial.
3. Registra ventas; los totales y el stock se calculan en el servidor.
4. Registra gastos, retiros o ingresos extraordinarios con motivo.
5. Al finalizar, cierra la caja con el efectivo contado y revisa la diferencia.
6. Los registros históricos se desactivan; no se eliminan físicamente.