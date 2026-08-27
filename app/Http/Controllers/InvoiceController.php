<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    public function download(Sale $sale)
    {
        $user = request()->user();
        abort_unless($user && ($user->id === $sale->user_id || $user->hasAnyRole(['Administrador', 'Contador'])), 403);

        $path = "invoices/invoice-{$sale->id}.pdf";
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, "LEO-AutoParts-Factura-{$sale->id}.pdf", [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
