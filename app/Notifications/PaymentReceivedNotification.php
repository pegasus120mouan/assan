<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order, public Payment $payment) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return $notifiable instanceof User
            ? ['mail', 'database']
            : ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Paiement reçu — commande '.$this->order->order_number)
            ->greeting('Bonjour '.$this->order->customer_name.',')
            ->line('Nous avons bien reçu votre paiement.')
            ->line('Commande : '.$this->order->order_number)
            ->line('Montant : '.format_price($this->payment->amount))
            ->action('Voir la commande', $this->order->user_id
                ? route('account.orders.show', $this->order)
                : route('checkout.confirmation', $this->order))
            ->salutation('L’équipe '.shop_name());
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_received',
            'order_number' => $this->order->order_number,
            'amount' => $this->payment->amount,
            'message' => 'Paiement reçu pour la commande '.$this->order->order_number,
        ];
    }
}
