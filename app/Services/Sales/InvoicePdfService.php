<?php

namespace App\Services\Sales;

use App\Models\Sale;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;

class InvoicePdfService
{
    private const HALF_LETTER_PORTRAIT = [0, 0, 396, 612];

    public function generate(Sale $sale): string
    {
        $sale->loadMissing(['customer', 'user', 'paymentMethod', 'saleDetails.product']);

        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', false);
        $options->set('isRemoteEnabled', false);
        $options->set('isFontSubsettingEnabled', true);

        $document = new Dompdf($options);
        $document->loadHtml(view('exports.invoice', [
            'sale' => $sale,
            'logoDataUri' => $this->logoDataUri(),
        ])->render());
        $document->setPaper(self::HALF_LETTER_PORTRAIT);
        $document->render();

        $filename = 'LEO AutoParts - '.$sale->invoice_number.'.pdf';
        Storage::disk('public')->put('facturas/'.$filename, $document->output());

        return asset('storage/facturas/'.$filename);
    }

    private function logoDataUri(): string
    {
        $path = public_path('images/brand/wordmark-dark.png');

        return 'data:image/png;base64,'.base64_encode(file_get_contents($path));
    }
}
