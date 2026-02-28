<?php

namespace App\Notifications;

use App\Models\ClientBundle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BundleLowBalanceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly ClientBundle $bundle) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $client    = $this->bundle->client;
        $tier      = $this->bundle->bundleTier;
        $remaining = $this->bundle->quantity_purchased - $this->bundle->quantity_consumed;
        $pct       = round(($this->bundle->quantity_consumed / $this->bundle->quantity_purchased) * 100);

        return (new MailMessage)
            ->subject("⚠️ Bolsa con saldo crítico — {$client->business_name}")
            ->greeting('Atención')
            ->line("La bolsa **{$tier->name}** del cliente **{$client->business_name}** tiene saldo crítico.")
            ->line("- **Consumido:** {$this->bundle->quantity_consumed} unidades ({$pct}%)")
            ->line("- **Disponibles:** {$remaining} unidades")
            ->action('Ver bolsa', url("/client-bundles/{$this->bundle->id}"))
            ->line('Considera renovar la bolsa para evitar interrupciones en el servicio.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'bundle_id'   => $this->bundle->id,
            'client_name' => $this->bundle->client->business_name,
            'tier_name'   => $this->bundle->bundleTier->name,
            'remaining'   => $this->bundle->quantity_purchased - $this->bundle->quantity_consumed,
        ];
    }
}
