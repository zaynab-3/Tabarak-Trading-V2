<?php

namespace App\Services\Orders;

use App\Models\Order;
use App\Models\Setting;
use App\Support\CompanyPolicy;
use Barryvdh\DomPDF\Facade\Pdf as PdfFacade;
use Barryvdh\DomPDF\PDF;

class InvoicePdf
{
    public function __construct(private readonly InvoiceImageDataUri $images) {}

    public function make(Order $order): PDF
    {
        $order->loadMissing('items');

        return PdfFacade::loadView('pdf.order-invoice', [
            'order' => $order,
            'logo' => $this->images->logo(),
            'printedAt' => now('America/Detroit'),
            'company' => [
                'name' => Setting::valueFor('company_legal_name', 'Tabarak Trading LLC'),
                'address_line_1' => Setting::valueFor('company_address_line_1', '14301 Ford Rd'),
                'address_line_2' => Setting::valueFor('company_address_line_2', 'Dearborn, MI 48126'),
                'phone' => Setting::valueFor('company_phone', '(248) 938-9999'),
                'email' => Setting::valueFor('company_email', 'tabaraktrading73@gmail.com'),
                'policy' => CompanyPolicy::text(),
            ],
        ])->setPaper('letter', 'portrait');
    }

}