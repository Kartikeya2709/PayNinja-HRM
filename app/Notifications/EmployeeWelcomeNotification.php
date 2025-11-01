<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EmployeeWelcomeNotification extends Notification
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
            ->subject('Welcome to ' . $notifiable->employee->company->name . '!')
            ->greeting('Welcome ' . $notifiable->name . '!')
            ->line('Congratulations! You have been successfully onboarded as an employee at ' . $notifiable->employee->company->name . '.')
            ->line('Here are your login credentials:')
            ->line('**Email:** ' . $notifiable->email)
            ->line('**Password:** ' . $this->password)
            ->line('**Employee Code:** ' . $notifiable->employee->employee_code)
            ->line('Please log in to your account and change your password immediately for security purposes.')
            ->action('Login to Your Account', route('login'))
            ->line('We are excited to have you on board! If you have any questions, please contact your reporting manager or HR department.')
            ->salutation('Best regards, ' . $notifiable->employee->company->name . ' Team');
    }
}