<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CompanyAdminWelcomeNotification extends Notification
{
    protected $password;

    public function __construct($password)
    {
        $this->password = $password;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Welcome to Your Company Admin Account')
            ->greeting('Congratulations ' . $notifiable->name . '!')
            ->line('You have been appointed as the Company Admin for your organization.')
            ->line('Here are your login credentials:')
            ->line('**Email:** ' . $notifiable->email)
            ->line('**Password:** ' . $this->password)
            ->line('Please log in to your account and change your password immediately for security purposes.')
            ->action('Login to Your Account', route('login'))
            ->line('If you have any questions, please contact the system administrator.')
            ->salutation('Best regards, ' . config('app.name') . ' Team');
    }
}