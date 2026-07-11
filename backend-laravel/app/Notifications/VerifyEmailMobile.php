<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailMobile extends Notification
{
    use Queueable;
    public function __construct(private readonly string $url) {}
    public function via(object $notifiable): array { return ['mail']; }
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('تأكيد بريدك في Warqna')
            ->greeting('مرحبًا '.$notifiable->username)
            ->line('اضغط الزر لتأكيد البريد الإلكتروني المرتبط بحساب Warqna.')
            ->action('تأكيد البريد الإلكتروني', $this->url)
            ->line('تنتهي صلاحية الرابط تلقائيًا. تجاهل الرسالة إذا لم تطلبها.');
    }
}
