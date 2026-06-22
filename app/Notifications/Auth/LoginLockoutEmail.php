<?php

namespace App\Notifications\Auth;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoginLockoutEmail extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected string $ip,
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Security Alert: Multiple Failed Login Attempts')
            ->greeting('Hello!')
            ->line('We detected multiple failed login attempts for your account.')
            ->line('**Email:** ' . $notifiable->email)
            ->line('**IP Address:** ' . $this->ip)
            ->line('---')
            ->line('**Was this you?**')
            ->line('If you forgot your password, you can reset it using the button below.')
            ->action('Reset Password', route('password.request'))
            ->line('---')
            ->line('**Was this NOT you?**')
            ->line('Someone may be attempting to access your account.')
            ->line('If you have any questions, please contact our support team.')
            ->line('Thank you for your attention to this security alert!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
