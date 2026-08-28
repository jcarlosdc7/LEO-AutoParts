# Inventory ledger decisions

products.stock remains the materialized current balance for fast reads. The immutable
stock_movements ledger is the auditable source used to reconstruct and reconcile that
balance. InventoryService updates both in the same database transaction.

## Lock order

Business flows lock their reference record when it already exists, then sale details,
products in ascending product ID, payment/cash records, and dependent records. The central
inventory operation locks one product at a time. Multi-product callers therefore sort and
pre-lock product IDs before invoking InventoryService. Database transactions retry deadlocks
up to three times.

## Warehouse baseline

Phase 4 introduces the stable MAIN warehouse and assigns every legacy movement to it.
The product balance is still global while only one operational warehouse exists. A future
multi-warehouse phase can introduce per-warehouse materialized balances without changing
movement history or type codes.

## Historical backfill

For each existing product, the migration calculates:

opening balance = stored stock - sum(existing movements)

When the result is non-zero it creates one idempotent initial_balance movement immediately
before the product's earliest existing movement. This is an adoption baseline, not an
invented purchase. Existing movements are preserved and receive stable legacy operation keys.

## Idempotency and references

Every new movement has a unique operation key. Sale, void, and customer-return keys derive
from the originating model, document ID, product, and movement type. Administrative
adjustments use an immutable entity with a unique client UUID. Polymorphic references retain
the exact business document that caused the movement.

## Costs and transfers

Nullable cost columns and stable purchase, supplier-return, damage, loss, and transfer codes
prepare the schema for later phases. Phase 4 does not invent historical costs or implement a
multi-warehouse transfer workflow.
