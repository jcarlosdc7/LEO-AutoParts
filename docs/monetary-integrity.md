# Exact Money Policy

## Representación

- Moneda operativa actual: `NIO`.
- Fuente de verdad: MySQL `DECIMAL`, nunca `FLOAT`/`DOUBLE`.
- Importes liquidados, totales, pagos, caja, impuestos y diferencias: `DECIMAL(19,2)`.
- Precios y costos unitarios: `DECIMAL(19,4)`.
- Escala de cálculo PHP: 6 decimales con BCMath.
- Escala de presentación: 2 decimales.
- Política única de redondeo: `HALF_UP` al convertir un cálculo a un importe liquidado.
- Entradas con notación científica, overflow, signo no autorizado o más escala de la permitida se rechazan.

## Inventario de campos

- Catálogo: `products.price`.
- Ventas: `sales.total`, `sales.amount`, `sales.change`.
- Líneas: `sale_details.price`, `sale_details.total`.
- Pagos: `payments.amount`, `sale_payments.*amount`.
- Devoluciones: `sale_return_items.unit_price`, `refund_amount`, `refunds.amount`.
- Notas de crédito: subtotal, tax, total y precios unitarios.
- Caja: apertura, esperado, contado, diferencia, movimientos, denominaciones y conteos.
- Inventario: `stock_movements.unit_cost` y `total_cost`.

## Reconciliación

`php artisan money:reconcile` verifica esquema, líneas contra cabecera, pagos, movimientos de caja, refunds y notas de crédito. Sólo reporta diferencias; nunca repara historia.

La migración `2026_08_28_000009` toma snapshots `COUNT/SUM` antes y después, amplía precisión, agrega CHECK constraints y conserva evidencia en `monetary_migration_audits`.

El método de reversión existe para ciclos técnicos y entornos de prueba, pero reduce las columnas a su precisión anterior. En producción sólo es seguro si una verificación previa demuestra que ningún valor excede el rango o la escala anterior; la estrategia recomendada sigue siendo restaurar el backup verificado creado antes del despliegue.

## Ensayo del 28 de agosto de 2026

- Backup de base de datos creado correctamente antes de la migración.
- Migración aplicada sobre MySQL local y snapshot before/after marcado verified.
- cash:reconcile: PASS para 1 sesión.
- inventory:reconcile: PASS para 30 productos.
- money:reconcile: FAIL seguro, sin modificar datos. Detectó 18 ventas históricas sin líneas (IDs 13–30) y ventas de efectivo sin movimiento de caja asociado. No existe evidencia suficiente para fabricar líneas o contramovimientos; esos registros deben clasificarse/importarse desde su fuente o aislarse antes de declarar producción.
