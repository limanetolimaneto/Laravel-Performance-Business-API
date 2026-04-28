<?php

namespace App\Mail\Sale;

use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SaleConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Sale $sale;

    public function __construct(Sale $sale)
    {
        $this->sale = $sale;
    }

    public function build()
    {
        return $this->subject('Confirmação da sua compra')
            ->view('emails.sale.confirmation')
            ->with([
                'sale' => $this->sale,
            ]);
    }
}