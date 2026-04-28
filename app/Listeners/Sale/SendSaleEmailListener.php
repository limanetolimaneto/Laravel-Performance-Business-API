<?php

namespace App\Listeners\Sale;

use App\Events\Sale\SaleCreated;
use App\Jobs\Sale\SendSaleConfirmationEmailJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSaleEmailListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SaleCreated $event): void
    {
        logger('Listener funcionando');
        SendSaleConfirmationEmailJob::dispatch($event->sale);
    }
}

// SaleCreated
//    ↓
// Listener escuta
//    ↓
// Job é enviado para fila
// Isso desacopla completamente o envio de email da criação da venda.
// Excelente arquitetura.