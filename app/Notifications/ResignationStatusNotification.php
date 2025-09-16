<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\EmployeeResignation;

class ResignationStatusNotification extends Notification
{
    protected $resignation;
    protected $action;

    public function __construct(EmployeeResignation $resignation, $action)
    {
        $this->resignation = $resignation;
        $this->action = $action;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $subject = $this->getSubject();
        $greeting = "Hello {$notifiable->name},";

        return (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line($this->getMessage())
            ->action('View Resignation', route('employee.resignations.show', $this->resignation))
            ->line('Thank you for using our application!');
    }

    public function toArray($notifiable)
    {
        return [
            'resignation_id' => $this->resignation->id,
            'action' => $this->action,
            'message' => $this->getMessage(),
            'employee_name' => $this->resignation->employee->name,
        ];
    }

    private function getSubject()
    {
        switch ($this->action) {
            case 'submitted':
                return 'Resignation Request Submitted';
            case 'approved':
                return 'Resignation Request Approved';
            case 'rejected':
                return 'Resignation Request Rejected';
            case 'withdrawn':
                return 'Resignation Request Withdrawn';
            default:
                return 'Resignation Status Update';
        }
    }

    private function getMessage()
    {
        switch ($this->action) {
            case 'submitted':
                return 'Your resignation request has been submitted and is pending approval.';
            case 'approved':
                return 'Your resignation request has been approved. Your last working day is ' . $this->resignation->last_working_date->format('M d, Y') . '.';
            case 'rejected':
                return 'Your resignation request has been rejected. Please contact HR for more details.';
            case 'withdrawn':
                return 'Your resignation request has been withdrawn successfully.';
            default:
                return 'Your resignation status has been updated.';
        }
    }
}