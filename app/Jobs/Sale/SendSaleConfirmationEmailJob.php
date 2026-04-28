<?php

namespace App\Jobs\Sale;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\Sale\SaleConfirmationMail;
use App\Models\Sale;

class SendSaleConfirmationEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Sale $sale;
    
    // tries 
    // quantidade máxima de tentativas
    public $tries = 3;
    
    // timeout 
    // tempo máximo em segundos, se passar disso, falha;
    public $timeout = 120;

    // backoff
    // espera 10 segundos antes da próxima tentativa
    public $backoff = 10;


    /**
     * Create a new job instance.
     */
    public function __construct(Sale $sale)
    {
        $this->sale = $sale;
    }

    /**
        * Execute the job.
    */
    public function handle(): void
    {
        // throw new \Exception('Teste de falha do job');
        $clientEmail = $this->sale->client->email ?? null;

        if (!$clientEmail) {
            logger('Sale email not sent: client email missing', [
                'sale_id' => $this->sale->id,
            ]);

            return;
        }
        

        try {
            Mail::to($clientEmail)->send(new SaleConfirmationMail($this->sale));
        } catch (\Exception $e) {
            logger('Job sent to failed_jobs table');
            throw $e;
        }

    }
}

// Reprocessar TODOS os jobs falhos
    // php artisan queue:retry all
    // O que acontece quando você usa retry?
    // Quando você roda: php artisan queue:retry 12
    // o Laravel faz isso:
        // failed_jobs
        //    ↓ (remove registro)
        // jobs
        //    ↓ (reinsere job)
        // queue worker
        //    ↓
        // executa novamente handle()
// ================================

// Reprocessar um job específico
    // Primeiro você lista:
    // php artisan queue:failedExemplo:
    // ID: 12 Job: SendSaleConfirmationEmailJob
    // Depois:
    // php artisan queue:retry 12
// =============================

// Remover da lista de falhos (sem reprocessar)
    // php artisan queue:forget 12
// ============================================