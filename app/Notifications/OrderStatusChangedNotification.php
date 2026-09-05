<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChangedNotification extends Notification implements ShouldQueue
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
            ->subject('Commande '.$order->order_number.' — '.$order->status->label())
            ->greeting('Bonjour '.$order->customer_name.',')
            ->line('Le statut de votre commande a été mis à jour.')
            ->line('Numéro : '.$order->order_number)
            ->line('Nouveau statut : '.$order->status->label())
            ->action('Voir la commande', $order->user_id
                ? route('account.orders.show', $order)
                : route('checkout.confirmation', $order))
            ->salutation('L’équipe '.shop_name());
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_status_changed',
            'order_number' => $this->order->order_number,
            'status' => $this->order->status->value,
            'message' => 'Commande '.$this->order->order_number.' : '.$this->order->status->label(),
        ];
    }
}
