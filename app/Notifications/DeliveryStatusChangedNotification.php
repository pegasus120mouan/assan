<?php

namespace App\Notifications;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeliveryStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order, public Delivery $delivery) {}

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
            ->subject('Livraison '.$this->order->order_number.' — '.$this->delivery->status->label())
            ->greeting('Bonjour '.$this->order->customer_name.',')
            ->line('Mise à jour de votre livraison.')
            ->line('Commande : '.$this->order->order_number)
            ->line('Suivi : '.$this->delivery->tracking_number)
            ->line('Statut : '.$this->delivery->status->label())
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
            'type' => 'delivery_status_changed',
            'order_number' => $this->order->order_number,
            'status' => $this->delivery->status->value,
            'tracking_number' => $this->delivery->tracking_number,
            'message' => 'Livraison '.$this->order->order_number.' : '.$this->delivery->status->label(),
        ];
    }
}
