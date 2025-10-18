<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceRequestRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected ServiceRequest $serviceRequest;

    public function __construct(ServiceRequest $serviceRequest)
    {
        $this->serviceRequest = $serviceRequest;
    }

    public function via($notifiable)
    {
        $channels = ['database'];
        
        // Add mail if user has email notifications enabled
        if ($notifiable->settings['email_notifications'] ?? true) {
            $channels[] = 'mail';
        }
        
        return $channels;
    }

    public function toMail($notifiable)
    {
        $packageName = $this->serviceRequest->package->name ?? "Package #{$this->serviceRequest->package_id}";
        $providerName = $this->serviceRequest->provider->name ?? 'Provider';
        
        $mailMessage = (new MailMessage)
                    ->subject('Service Request Rejected - ' . $packageName)
                    ->greeting('Hello ' . $notifiable->name . '!')
                    ->line('Unfortunately, your service request has been rejected.')
                    ->line('**Package:** ' . $packageName)
                    ->line('**Provider:** ' . $providerName)
                    ->line('**Service Type:** ' . ucfirst($this->serviceRequest->provider_type))
                    ->line('**Dates:** ' . $this->serviceRequest->start_date . ' to ' . $this->serviceRequest->end_date);

        if ($this->serviceRequest->rejection_reason) {
            $mailMessage->line('**Rejection Reason:** ' . $this->serviceRequest->rejection_reason);
        }

        // Add alternative dates if provided
        if ($this->serviceRequest->alternative_dates && is_array($this->serviceRequest->alternative_dates)) {
            $mailMessage->line('**Alternative Dates Suggested:**');
            foreach ($this->serviceRequest->alternative_dates as $altDate) {
                $mailMessage->line('- ' . $altDate['start_date'] . ' to ' . $altDate['end_date']);
            }
        }

        return $mailMessage
                    ->line('**Rejected:** ' . $this->serviceRequest->rejected_at?->format('M j, Y g:i A'))
                    ->action('View Package', url('/b2b/packages/' . $this->serviceRequest->package_id))
                    ->line('You can create a new request or try alternative providers.')
                    ->error();
    }

    public function toDatabase($notifiable)
    {
        return [
            'service_request_id' => $this->serviceRequest->id,
            'type' => 'service_request_rejected',
            'title' => 'Service Request Rejected',
            'message' => 'Your service request has been rejected by ' . ($this->serviceRequest->provider->name ?? 'the provider'),
            'data' => [
                'package_id' => $this->serviceRequest->package_id,
                'package_name' => $this->serviceRequest->package->name ?? null,
                'provider_name' => $this->serviceRequest->provider->name ?? null,
                'provider_type' => $this->serviceRequest->provider_type,
                'rejected_at' => $this->serviceRequest->rejected_at?->toISOString(),
                'rejection_reason' => $this->serviceRequest->rejection_reason,
                'alternative_dates' => $this->serviceRequest->alternative_dates,
                'requested_quantity' => $this->serviceRequest->requested_quantity,
                'start_date' => $this->serviceRequest->start_date,
                'end_date' => $this->serviceRequest->end_date
            ],
            'action_url' => '/b2b/packages/' . $this->serviceRequest->package_id,
            'icon' => 'fas fa-times-circle',
            'color' => 'danger'
        ];
    }

    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}