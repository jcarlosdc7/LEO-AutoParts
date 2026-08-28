<?php

namespace App\Console\Commands;

use App\Models\CashSession;
use App\Services\CashService;
use Illuminate\Console\Command;

class ReconcileCash extends Command
{
    protected $signature = 'cash:reconcile {--session= : Reconcile only one cash session ID}';

    protected $description = 'Detect structural drift between cash sessions, counts and movements without repairing data';

    public function handle(CashService $cash): int
    {
        $query = CashSession::query()
            ->with(['openingCount', 'closingCount'])
            ->when($this->option('session'), fn ($builder) => $builder->whereKey($this->option('session')))
            ->orderBy('id');

        $checked = 0;
        $drifts = [];
        $query->chunkById(200, function ($sessions) use ($cash, &$checked, &$drifts): void {
            foreach ($sessions as $session) {
                $checked++;
                if ($session->openingCount && bccomp((string) $session->opening_amount, (string) $session->openingCount->total, 2) !== 0) {
                    $drifts[] = [$session->id, 'OPENING_COUNT', $session->opening_amount, $session->openingCount->total];
                }

                $reconstructed = $cash->expectedCash($session);
                if ($session->status === 'closed') {
                    if (bccomp((string) $session->expected_amount, $reconstructed, 2) !== 0) {
                        $drifts[] = [$session->id, 'EXPECTED_CASH', $session->expected_amount, $reconstructed];
                    }
                    if ($session->closingCount && bccomp((string) $session->closing_amount, (string) $session->closingCount->total, 2) !== 0) {
                        $drifts[] = [$session->id, 'CLOSING_COUNT', $session->closing_amount, $session->closingCount->total];
                    }
                    $difference = bcsub((string) $session->closing_amount, (string) $session->expected_amount, 2);
                    if (bccomp((string) $session->difference, $difference, 2) !== 0) {
                        $drifts[] = [$session->id, 'DIFFERENCE', $session->difference, $difference];
                    }
                }
            }
        });

        if ($drifts !== []) {
            $this->error('Cash reconciliation detected structural drift. No records were modified.');
            $this->table(['Session', 'Check', 'Stored', 'Reconstructed'], $drifts);

            return self::FAILURE;
        }

        $this->info("Cash reconciliation passed for {$checked} session(s).");

        return self::SUCCESS;
    }
}
