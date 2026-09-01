<?php

namespace App\Services\Orders;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf as PdfFacade;
use Barryvdh\DomPDF\PDF;

class InvoicePdf
{
    public function __construct(private readonly InvoiceImageDataUri $images) {}

    public function make(Order $order): PDF
    {
        $order->loadMissing('items');
        $itemImages = $order->items->mapWithKeys(
            fn ($item) => [$item->id => $this->images->forItem($item)],
        );

        return PdfFacade::loadView('pdf.order-invoice', [
            'order' => $order,
            'itemImages' => $itemImages,
            'logo' => $this->images->logo(),
        ])->setPaper('a4');
    }
}
