<?php

namespace App\Events\Sale;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Sale;

class SaleCreated
{
    use Dispatchable, SerializesModels;

    public Sale $sale;

    /**
     * Create a new event instance.
     */
    public function __construct(Sale $sale)
    {
        $this->sale = $sale;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}

// flow
    // Sale criada
    //    ↓
    // Dispara Event (SaleCreated)
    //    ↓
    // Listener escuta esse Event
    //    ↓
    // Listener despacha Job (SendSaleConfirmationEmailJob)
    //    ↓
    // Queue processa o Job
    //    ↓
    // Email enviado
// flow

// O que acontece?

    //  Quando dispararmos:     - SaleCreated::dispatch($sale);
    //                            ou
    //                          - event(new SaleCreated($sale));
    //  ... o Laravel entende:  - uma venda foi criada 
    //                          - outras partes do sistema podem reagir a isso
    //  
    //  Nao ha acoplamento com controller. Isso é arquitetura limpa.
    // -------------------------------------------------------------
    //  Onde disparar esse Event?
    //  No SaleService.php, depois de salvar a venda
    //    exemplo:
    //      $sale = Sale::create([ dados da venda ]);
    //      event(new SaleCreated($sale));
    //          ou
    //      SaleCreated::dispatch($sale);
    //  No arquivo onde for disparar: use App\Events\Sale\SaleCreated;
    //  Resumo do caminho Controller → Service → Event

// ===============

// listener
    //  php artisan make:listener Sale/SendSaleEmailListener --event=Sale/SaleCreated
    //      Ja cria olistener vinculado ao event
    //
// ========