<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage; // Tambahkan ini

class OrderShipped extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Tentukan channel notifikasi.
     */
    public function via($notifiable)
    {
        return ['webpush'];
    }

    /**
     * Isi notifikasi Web Push.
     */
    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('Pesanan Baru!')
            ->icon('/logo-icon.png')
            ->body('Ada pesanan baru di POS Anda.')
            ->action('Lihat', 'view_order')
            ->data(['url' => '/orders']); // URL yg mau dibuka kalau notifikasi diklik
    }
}
