# LEO-AutoParts — Production Readiness Matrix

Última actualización: 2026-08-28  
Baseline evaluado: `5385878 fix(cash): harden mass assignment and modal access`  
Rama: `codex/financial-production-hardening`

## Criterio

Estados permitidos: `PASS`, `PARTIAL`, `FAIL`, `NOT IMPLEMENTED`, `NOT APPLICABLE`.

`PASS` exige evidencia funcional, autorización server-side, integridad, pruebas y operación coherente. La ausencia actual de Browser E2E impide marcar la columna E2E como PASS.

| Module | Functional | Security | Authorization | Data Integrity | Tests | UI | E2E | Production |
|---|---|---|---|---|---|---|---|---|
| Authentication | PASS | PARTIAL | PASS | PASS | PASS | PASS | NOT IMPLEMENTED | PARTIAL |
| Users | PARTIAL | PARTIAL | PARTIAL | PARTIAL | PARTIAL | PARTIAL | NOT IMPLEMENTED | FAIL |
| Roles | PARTIAL | PARTIAL | PARTIAL | PARTIAL | PARTIAL | PARTIAL | NOT IMPLEMENTED | FAIL |
| Products | PARTIAL | PARTIAL | PARTIAL | PASS | PASS | PASS | NOT IMPLEMENTED | PARTIAL |
| Categories | PARTIAL | PARTIAL | PARTIAL | PARTIAL | PARTIAL | PASS | NOT IMPLEMENTED | FAIL |
| Suppliers | PARTIAL | PARTIAL | PARTIAL | PARTIAL | PARTIAL | PASS | NOT IMPLEMENTED | FAIL |
| Inventory | PASS | PASS | PASS | PASS | PASS | PASS | NOT IMPLEMENTED | PARTIAL |
| Kardex | PASS | PASS | PASS | PASS | PASS | PASS | NOT IMPLEMENTED | PARTIAL |
| Sales | PASS | PASS | PASS | PARTIAL | PASS | PASS | NOT IMPLEMENTED | PARTIAL |
| Sale details | PASS | PASS | PASS | PARTIAL | PASS | PASS | NOT IMPLEMENTED | PARTIAL |
| Payment methods | PARTIAL | PARTIAL | PARTIAL | PARTIAL | PARTIAL | PASS | NOT IMPLEMENTED | FAIL |
| Sale payments | PARTIAL | PASS | PASS | PARTIAL | PASS | PASS | NOT IMPLEMENTED | PARTIAL |
| Invoices | PARTIAL | PASS | PASS | PARTIAL | PARTIAL | PASS | NOT IMPLEMENTED | PARTIAL |
| Returns | PASS | PASS | PASS | PASS | PASS | PASS | NOT IMPLEMENTED | PARTIAL |
| Refunds | PASS | PASS | PASS | PASS | PASS | PASS | NOT IMPLEMENTED | PARTIAL |
| Credit notes | PASS | PASS | PASS | PASS | PASS | PARTIAL | NOT IMPLEMENTED | PARTIAL |
| Cash registers | PASS | PASS | PASS | PASS | PASS | PASS | NOT IMPLEMENTED | PARTIAL |
| Cash counts | PASS | PASS | PASS | PASS | PASS | PASS | NOT IMPLEMENTED | PARTIAL |
| Customers | PARTIAL | PARTIAL | PARTIAL | PARTIAL | PARTIAL | PASS | NOT IMPLEMENTED | FAIL |
| Purchases | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | FAIL |
| Purchase receiving | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | FAIL |
| Costs / COGS | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | FAIL |
| Profitability | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | FAIL |
| Reports | PARTIAL | PARTIAL | PARTIAL | FAIL | PARTIAL | PASS | NOT IMPLEMENTED | FAIL |
| Dashboard | PARTIAL | PARTIAL | PARTIAL | FAIL | PARTIAL | PASS | NOT IMPLEMENTED | FAIL |
| Settings | PARTIAL | PARTIAL | PARTIAL | PARTIAL | PARTIAL | PASS | NOT IMPLEMENTED | FAIL |
| Backups | PARTIAL | PARTIAL | PASS | FAIL | PARTIAL | PARTIAL | NOT IMPLEMENTED | FAIL |
| Restore | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | FAIL |
| Audit logs | PARTIAL | PARTIAL | PARTIAL | PARTIAL | PASS | PARTIAL | NOT IMPLEMENTED | FAIL |
| Exports | PARTIAL | PARTIAL | PARTIAL | PARTIAL | PARTIAL | PASS | NOT IMPLEMENTED | FAIL |
| Documents / PDFs | PARTIAL | PASS | PASS | PARTIAL | PARTIAL | PASS | NOT IMPLEMENTED | PARTIAL |
| Warehouses | PARTIAL | PARTIAL | PARTIAL | PARTIAL | PARTIAL | PARTIAL | NOT IMPLEMENTED | FAIL |
| Jobs / queues | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT IMPLEMENTED | FAIL |
| Observability | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT APPLICABLE | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT APPLICABLE | NOT IMPLEMENTED | FAIL |
| Deployment / rollback | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT APPLICABLE | NOT IMPLEMENTED | NOT IMPLEMENTED | NOT APPLICABLE | NOT IMPLEMENTED | FAIL |

## Evidencia y bloqueadores principales

- Authentication: registro público deshabilitado, login/logout/reset cubiertos; faltan revisión completa de rate limiting, revocación y políticas de sesión.
- Users/Roles: existen pantallas administrativas y protección parcial del último administrador; la autorización depende de nombres de rol y hay IDs de rol por defecto en Livewire.
- Inventory/Kardex: servicios transaccionales, ledger inmutable, reconciliación y concurrencia MySQL pasan. Falta Browser E2E.
- Sales/Returns/Cash: servicios transaccionales y pruebas de manipulación/concurrencia pasan. Fase 5 eliminó aritmética binaria en rutas monetarias, pero la base local conserva ventas históricas incompletas detectadas por conciliación.
- Reports/Dashboard: cálculos y presentación monetaria usan la política decimal central. Siguen siendo reportes operativos; no reemplazan contabilidad general, COGS ni rentabilidad.
- Customers/Suppliers/Categories: CRUD existente con autorización de ruta, pero faltan pruebas específicas, auditoría, IDOR y controles de historia.
- Purchases/COGS/Profitability: no existe un flujo empresarial implementado.
- Backups: existe una superficie administrativa sobre almacenamiento local, pero no hay backup automatizado, cifrado, destino off-host ni restore rehearsal.
- Browser E2E: no disponible en el runtime actual; permanece deuda transversal.

## Condiciones NO-GO abiertas

- HIGH: registros históricos locales sin detalle y ventas de efectivo sin contramovimiento asociado; conciliación monetaria falla de forma segura.
- HIGH: backup/restore de producción sin rehearsal.
- HIGH: módulos de compras, costos y rentabilidad no implementados.
- MEDIUM: RBAC basado en nombres de rol y controles de usuario incompletos.
- MEDIUM: Browser E2E, responsive y accesibilidad sin validación interactiva.
