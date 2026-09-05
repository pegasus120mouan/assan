<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StaffOrderPlacedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;

        return (new MailMessage)
            ->subject('Nouvelle commande '.$order->order_number)
            ->greeting('Nouvelle commande')
            ->line('Une commande vient d’être passée.')
            ->line('Numéro : '.$order->order_number)
            ->line('Client : '.$order->customer_name.' ('.$order->customer_phone.')')
            ->line('Total : '.format_price($order->total))
            ->action('Ouvrir la commande', route('admin.orders.show', $order))
            ->salutation(shop_name());
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'staff_order_placed',
            'order_number' => $this->order->order_number,
            'total' => $this->order->total,
            'message' => 'Nouvelle commande '.$this->order->order_number,
        ];
    }
}
