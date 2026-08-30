# LEO-AutoParts — User Flows

Última actualización: 2026-08-28  
Fuente: rutas, middleware, componentes Livewire y servicios presentes en el baseline `5385878`.

## Roles reales

La aplicación sólo define actualmente:

- `Administrador`
- `Contador`
- `Vendedor`

No existen todavía roles independientes de Inventario, Auditor o sólo lectura. No deben asumirse hasta que la matriz de permisos de Fase 6 los defina.

## Administrador

### Flujo operativo disponible

`login → dashboard → catálogo/productos → clientes → facturación/venta → caja → historial de ventas → devolución/reembolso → inventario/Kardex → proveedores → reportes → usuarios/configuración`

### Capacidades verificadas

- Crear ventas y operar caja.
- Anular ventas y procesar devoluciones.
- Ajustar inventario.
- Consultar historial financiero.
- Administrar usuarios y roles mediante las pantallas existentes.
- Descargar documentos de ventas autorizados.

### Brechas

- Compras, recepción de mercancía, COGS y rentabilidad no existen.
- Gestión de usuarios y sesiones no está cerrada según la política productiva.
- Backups no tienen restore rehearsal.
- Reportes y dashboard no son todavía fuente financiera definitiva.

## Vendedor

### Flujo operativo disponible

`login → dashboard → catálogo → clientes → abrir caja → facturación/venta → movimientos permitidos → cerrar caja → consultar su sesión`

### Restricciones verificadas

- No accede a usuarios, configuración, reportes, inventario administrativo, proveedores ni historial administrativo.
- No puede ajustar inventario, anular ventas ni procesar devoluciones administrativas.
- Sólo puede operar su propia sesión de caja; un administrador puede intervenir según política.
- Las ventas requieren una sesión de caja activa, incluso cuando el método no afecta efectivo.

### Brechas

- Browser E2E del turno completo pendiente.
- No existe flujo de crédito ni pagos divididos.
- La política de aprobación de retiros sensibles es administrativa, no un workflow humano de dos pasos.

## Contador

### Flujo disponible

`login → dashboard → catálogo → historial de ventas → inventario/Kardex en lectura → proveedores → reportes`

### Restricciones verificadas

- No puede facturar, operar caja ni administrar usuarios/configuración.
- Puede consultar inventario, pero los ajustes quedan reservados al Administrador.
- Puede descargar una factura si está autorizado por el controlador.

### Brechas

- No existe un workspace contable completo con cierre, asientos, COGS o rentabilidad.
- No existe rol Auditor/read-only independiente.
- Reportes monetarios quedan bloqueados por Fase 5.

## Flujos empresariales transversales

### Venta en efectivo

`Vendedor/Admin login → abrir caja → seleccionar cliente → seleccionar productos → elegir método CASH → confirmar importe recibido → SaleService recalcula precios/stock → venta + detalle + pago + movimiento de caja + Kardex → factura privada`

Estado: funcional y probado; integridad monetaria exacta pendiente de Fase 5.

### Venta no efectiva

`Vendedor/Admin → caja activa → venta → método que no afecta cajón → venta/pago asociados a sesión → sin movimiento físico de caja`

Estado: funcional y probado parcialmente; no hay pagos divididos.

### Devolución

`Administrador → historial de ventas → seleccionar líneas/cantidades → condición/restock → método de reembolso → ReturnService → devolución + refund + nota de crédito + Kardex/caja cuando corresponde`

Estado: transaccional, idempotente y probado con concurrencia.

### Caja

`Vendedor/Admin → seleccionar caja → conteo por denominaciones → abrir → ventas/movimientos → conteo ciego → conciliación → justificar diferencia → cerrar → consultar historial`

Estado: técnicamente completo; Browser E2E pendiente.

### Inventario

`Administrador → producto → ajuste autorizado → bloqueo de fila → movimiento inmutable → stock materializado → conciliación`

`Contador → consultar inventario/Kardex → exportar según autorización → sin mutación`

Estado: backend, UI y concurrencia probados; E2E pendiente.

## Flujos no implementados

- Compra a proveedor.
- Recepción parcial o total de compra.
- Actualización de costo por recepción.
- Cuentas por pagar.
- COGS y margen.
- Pagos divididos.
- Crédito y cuentas por cobrar.
- Restore de backup.
- Revocación administrativa de sesiones.
- Auditor/read-only dedicado.

