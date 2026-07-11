<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordMobile extends Notification
{
    use Queueable;
    public function __construct(private readonly string $url) {}
    public function via(object $notifiable): array { return ['mail']; }
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('إعادة تعيين كلمة مرور Warqna')
            ->greeting('مرحبًا '.$notifiable->username)
            ->line('وصلنا طلب لإعادة تعيين كلمة مرور حسابك.')
            ->action('إعادة تعيين كلمة المرور', $this->url)
            ->line('إذا لم تطلب ذلك، لا تتخذ أي إجراء.');
    }
}
