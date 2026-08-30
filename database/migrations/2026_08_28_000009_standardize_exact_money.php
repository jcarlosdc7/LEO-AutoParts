<?php

use App\Support\Decimal;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $columns = [
        'products' => ['price'],
        'sales' => ['total', 'amount', 'change'],
        'sale_details' => ['price', 'total'],
        'payments' => ['amount'],
        'sale_payments' => ['amount', 'received_amount', 'change_amount'],
        'sale_return_items' => ['unit_price', 'refund_amount'],
        'refunds' => ['amount'],
        'credit_notes' => ['subtotal', 'tax', 'total'],
        'credit_note_items' => ['unit_price', 'subtotal', 'tax', 'total'],
        'cash_sessions' => ['opening_amount', 'expected_amount', 'closing_amount', 'difference'],
        'cash_movements' => ['amount'],
        'cash_denominations' => ['value'],
        'cash_counts' => ['total', 'expected_amount', 'difference'],
        'cash_count_lines' => ['subtotal'],
        'stock_movements' => ['unit_cost', 'total_cost'],
    ];

    public function up(): void
    {
        Schema::create('monetary_migration_audits', function (Blueprint $table): void {
            $table->id();
            $table->string('migration_key')->unique();
            $table->json('before_snapshot');
            $table->json('after_snapshot');
            $table->string('status');
            $table->timestamp('verified_at');
        });

        $before = $this->snapshot();

        foreach ($this->statements() as $statement) {
            DB::statement($statement);
        }
        foreach ($this->checks() as $statement) {
            DB::statement($statement);
        }

        $after = $this->snapshot();
        foreach ($before as $key => $value) {
            if ($value['rows'] !== $after[$key]['rows'] || Decimal::compare($value['sum'], $after[$key]['sum'], 4) !== 0) {
                throw new RuntimeException("Monetary migration reconciliation failed for {$key}.");
            }
        }

        DB::table('monetary_migration_audits')->insert([
            'migration_key' => '2026_08_28_000009_exact_money',
            'before_snapshot' => json_encode($before, JSON_THROW_ON_ERROR),
            'after_snapshot' => json_encode($after, JSON_THROW_ON_ERROR),
            'status' => 'verified',
            'verified_at' => now(),
        ]);
    }

    public function down(): void
    {
        foreach (array_reverse($this->constraintNames()) as $constraint) {
            DB::statement("ALTER TABLE {$constraint[0]} DROP CHECK {$constraint[1]}");
        }

        foreach ($this->rollbackStatements() as $statement) {
            DB::statement($statement);
        }

        Schema::dropIfExists('monetary_migration_audits');
    }

    private function snapshot(): array
    {
        $snapshot = [];
        foreach ($this->columns as $table => $columns) {
            foreach ($columns as $column) {
                $result = DB::table($table)
                    ->selectRaw("COUNT(*) AS row_count, COALESCE(SUM(`{$column}`), 0) AS aggregate")
                    ->first();
                $snapshot["{$table}.{$column}"] = [
                    'rows' => (int) $result->row_count,
                    'sum' => (string) $result->aggregate,
                ];
            }
        }

        return $snapshot;
    }

    private function statements(): array
    {
        return [
            'ALTER TABLE products MODIFY price DECIMAL(19,4) NOT NULL',
            'ALTER TABLE sales MODIFY total DECIMAL(19,2) NOT NULL, MODIFY amount DECIMAL(19,2) NULL, MODIFY `change` DECIMAL(19,2) NULL',
            'ALTER TABLE sale_details MODIFY price DECIMAL(19,4) NOT NULL, MODIFY total DECIMAL(19,2) NOT NULL',
            'ALTER TABLE payments MODIFY amount DECIMAL(19,2) NOT NULL',
            'ALTER TABLE sale_payments MODIFY amount DECIMAL(19,2) NOT NULL, MODIFY received_amount DECIMAL(19,2) NULL, MODIFY change_amount DECIMAL(19,2) NOT NULL DEFAULT 0',
            'ALTER TABLE sale_return_items MODIFY unit_price DECIMAL(19,4) NOT NULL, MODIFY refund_amount DECIMAL(19,2) NOT NULL',
            'ALTER TABLE refunds MODIFY amount DECIMAL(19,2) NOT NULL',
            'ALTER TABLE credit_notes MODIFY subtotal DECIMAL(19,2) NOT NULL, MODIFY tax DECIMAL(19,2) NOT NULL DEFAULT 0, MODIFY total DECIMAL(19,2) NOT NULL',
            'ALTER TABLE credit_note_items MODIFY unit_price DECIMAL(19,4) NOT NULL, MODIFY subtotal DECIMAL(19,2) NOT NULL, MODIFY tax DECIMAL(19,2) NOT NULL DEFAULT 0, MODIFY total DECIMAL(19,2) NOT NULL',
            'ALTER TABLE cash_sessions MODIFY opening_amount DECIMAL(19,2) NOT NULL DEFAULT 0, MODIFY expected_amount DECIMAL(19,2) NULL, MODIFY closing_amount DECIMAL(19,2) NULL, MODIFY difference DECIMAL(19,2) NULL',
            'ALTER TABLE cash_movements MODIFY amount DECIMAL(19,2) NOT NULL',
            'ALTER TABLE cash_denominations MODIFY value DECIMAL(19,2) NOT NULL',
            'ALTER TABLE cash_counts MODIFY total DECIMAL(19,2) NOT NULL, MODIFY expected_amount DECIMAL(19,2) NULL, MODIFY difference DECIMAL(19,2) NULL',
            'ALTER TABLE cash_count_lines MODIFY subtotal DECIMAL(19,2) NOT NULL',
            'ALTER TABLE stock_movements MODIFY unit_cost DECIMAL(19,4) NULL, MODIFY total_cost DECIMAL(19,4) NULL',
        ];
    }

    private function checks(): array
    {
        return [
            'ALTER TABLE products ADD CONSTRAINT products_price_nonnegative CHECK (price >= 0)',
            'ALTER TABLE sales ADD CONSTRAINT sales_money_nonnegative CHECK (total >= 0 AND (amount IS NULL OR amount >= 0) AND (`change` IS NULL OR `change` >= 0))',
            'ALTER TABLE sale_details ADD CONSTRAINT sale_details_money_nonnegative CHECK (price >= 0 AND total >= 0)',
            'ALTER TABLE payments ADD CONSTRAINT payments_amount_nonnegative CHECK (amount >= 0)',
            'ALTER TABLE sale_payments ADD CONSTRAINT sale_payments_money_nonnegative CHECK (amount >= 0 AND (received_amount IS NULL OR received_amount >= 0) AND change_amount >= 0)',
            'ALTER TABLE sale_return_items ADD CONSTRAINT sale_return_items_money_nonnegative CHECK (unit_price >= 0 AND refund_amount >= 0)',
            'ALTER TABLE refunds ADD CONSTRAINT refunds_amount_nonnegative CHECK (amount >= 0)',
            'ALTER TABLE credit_notes ADD CONSTRAINT credit_notes_money_nonnegative CHECK (subtotal >= 0 AND tax >= 0 AND total >= 0)',
            'ALTER TABLE credit_note_items ADD CONSTRAINT credit_note_items_money_nonnegative CHECK (unit_price >= 0 AND subtotal >= 0 AND tax >= 0 AND total >= 0)',
            'ALTER TABLE cash_sessions ADD CONSTRAINT cash_sessions_amounts_nonnegative CHECK (opening_amount >= 0 AND (expected_amount IS NULL OR expected_amount >= 0) AND (closing_amount IS NULL OR closing_amount >= 0))',
            'ALTER TABLE cash_movements ADD CONSTRAINT cash_movements_amount_positive CHECK (amount > 0)',
            'ALTER TABLE cash_denominations ADD CONSTRAINT cash_denominations_value_positive CHECK (value > 0)',
            'ALTER TABLE cash_counts ADD CONSTRAINT cash_counts_amounts_nonnegative CHECK (total >= 0 AND (expected_amount IS NULL OR expected_amount >= 0))',
            'ALTER TABLE cash_count_lines ADD CONSTRAINT cash_count_lines_subtotal_nonnegative CHECK (subtotal >= 0)',
            'ALTER TABLE stock_movements ADD CONSTRAINT stock_movements_costs_nonnegative CHECK ((unit_cost IS NULL OR unit_cost >= 0) AND (total_cost IS NULL OR total_cost >= 0))',
        ];
    }

    private function constraintNames(): array
    {
        return [
            ['products', 'products_price_nonnegative'],
            ['sales', 'sales_money_nonnegative'],
            ['sale_details', 'sale_details_money_nonnegative'],
            ['payments', 'payments_amount_nonnegative'],
            ['sale_payments', 'sale_payments_money_nonnegative'],
            ['sale_return_items', 'sale_return_items_money_nonnegative'],
            ['refunds', 'refunds_amount_nonnegative'],
            ['credit_notes', 'credit_notes_money_nonnegative'],
            ['credit_note_items', 'credit_note_items_money_nonnegative'],
            ['cash_sessions', 'cash_sessions_amounts_nonnegative'],
            ['cash_movements', 'cash_movements_amount_positive'],
            ['cash_denominations', 'cash_denominations_value_positive'],
            ['cash_counts', 'cash_counts_amounts_nonnegative'],
            ['cash_count_lines', 'cash_count_lines_subtotal_nonnegative'],
            ['stock_movements', 'stock_movements_costs_nonnegative'],
        ];
    }

    private function rollbackStatements(): array
    {
        return [
            'ALTER TABLE products MODIFY price DECIMAL(10,2) NOT NULL',
            'ALTER TABLE sales MODIFY total DECIMAL(10,2) NOT NULL, MODIFY amount DECIMAL(10,2) NULL, MODIFY `change` DECIMAL(10,2) NULL',
            'ALTER TABLE sale_details MODIFY price DECIMAL(10,2) NOT NULL, MODIFY total DECIMAL(10,2) NOT NULL',
            'ALTER TABLE payments MODIFY amount DECIMAL(10,2) NOT NULL',
            'ALTER TABLE sale_payments MODIFY amount DECIMAL(12,2) NOT NULL, MODIFY received_amount DECIMAL(12,2) NULL, MODIFY change_amount DECIMAL(12,2) NOT NULL DEFAULT 0',
            'ALTER TABLE sale_return_items MODIFY unit_price DECIMAL(12,2) NOT NULL, MODIFY refund_amount DECIMAL(12,2) NOT NULL',
            'ALTER TABLE refunds MODIFY amount DECIMAL(12,2) NOT NULL',
            'ALTER TABLE credit_notes MODIFY subtotal DECIMAL(12,2) NOT NULL, MODIFY tax DECIMAL(12,2) NOT NULL DEFAULT 0, MODIFY total DECIMAL(12,2) NOT NULL',
            'ALTER TABLE credit_note_items MODIFY unit_price DECIMAL(12,2) NOT NULL, MODIFY subtotal DECIMAL(12,2) NOT NULL, MODIFY tax DECIMAL(12,2) NOT NULL DEFAULT 0, MODIFY total DECIMAL(12,2) NOT NULL',
            'ALTER TABLE cash_sessions MODIFY opening_amount DECIMAL(12,2) NOT NULL DEFAULT 0, MODIFY expected_amount DECIMAL(12,2) NULL, MODIFY closing_amount DECIMAL(12,2) NULL, MODIFY difference DECIMAL(12,2) NULL',
            'ALTER TABLE cash_movements MODIFY amount DECIMAL(12,2) NOT NULL',
            'ALTER TABLE cash_denominations MODIFY value DECIMAL(12,2) NOT NULL',
            'ALTER TABLE cash_counts MODIFY total DECIMAL(12,2) NOT NULL, MODIFY expected_amount DECIMAL(12,2) NULL, MODIFY difference DECIMAL(12,2) NULL',
            'ALTER TABLE cash_count_lines MODIFY subtotal DECIMAL(12,2) NOT NULL',
            'ALTER TABLE stock_movements MODIFY unit_cost DECIMAL(12,2) NULL, MODIFY total_cost DECIMAL(12,2) NULL',
        ];
    }
};
