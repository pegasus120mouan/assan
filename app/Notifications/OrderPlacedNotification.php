<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

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
        $order = $this->order;

        return (new MailMessage)
            ->subject('Commande '.$order->order_number.' confirmée — '.shop_name())
            ->greeting('Bonjour '.$order->customer_name.',')
            ->line('Votre commande a bien été enregistrée.')
            ->line('Numéro : '.$order->order_number)
            ->line('Total : '.format_price($order->total))
            ->line('Paiement : '.$order->payment_method->label())
            ->action('Suivre ma commande', route('checkout.confirmation', $order))
            ->salutation('L’équipe '.shop_name());
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_placed',
            'order_number' => $this->order->order_number,
            'total' => $this->order->total,
            'message' => 'Votre commande '.$this->order->order_number.' a été enregistrée.',
        ];
    }
}
