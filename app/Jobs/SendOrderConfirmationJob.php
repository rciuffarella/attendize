<?php

namespace App\Jobs;

use App\Mail\SendOrderConfirmationMail;
use App\Models\Order;
use App\Services\Order as OrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Config;
use Mail;

class SendOrderConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $order;
    public $orderService;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order, OrderService $orderService)
    {
        $this->order = $order;
        $this->orderService = $orderService;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            GenerateTicketsJob::dispatchNow($this->order);
            
            // Forza la configurazione email corretta direttamente nel transport
            $this->configureMailTransport();
            
            $mail = new SendOrderConfirmationMail($this->order, $this->orderService);
            Mail::to($this->order->email)
                ->locale(Config::get('app.locale'))
                ->send($mail);
        } catch (\Throwable $e) {
            // Cattura sia Exception che Error per essere sicuri
            \Log::warning('Errore nell\'invio della conferma ordine (job): ' . $e->getMessage());
            // Non rilanciare l'eccezione per non bloccare il processo
            return;
        }
    }
    
    /**
     * Configura il transport di SwiftMailer direttamente per forzare porta 587 e TLS
     */
    private function configureMailTransport()
    {
        // Forza la configurazione nel config
        Config::set('mail.host', 'mail.cercaclick.it');
        Config::set('mail.port', 587);
        Config::set('mail.encryption', 'tls');
        Config::set('mail.username', 'eventi@fondazioneboccadamo.org');
        Config::set('mail.password', 'AAAbbb123@2025');
        Config::set('mail.from.address', 'eventi@fondazioneboccadamo.org');
        Config::set('mail.from.name', 'Fondazione Boccadamo');
        
        // Ottieni il transport di SwiftMailer e forzalo direttamente
        $swift = Mail::getSwiftMailer();
        if ($swift) {
            $transport = $swift->getTransport();
            if ($transport instanceof \Swift_SmtpTransport) {
                $transport->setHost('mail.cercaclick.it');
                $transport->setPort(587);
                $transport->setEncryption('tls');
                $transport->setUsername('eventi@fondazioneboccadamo.org');
                $transport->setPassword('AAAbbb123@2025');
                
                // Disabilita la verifica del certificato SSL per risolvere il problema del CN mismatch
                $options = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ];
                $transport->setStreamOptions($options);
                
                \Log::debug('Mail transport configurato: host=mail.cercaclick.it, port=587, encryption=tls, SSL verification disabled');
            }
        }
    }
}
