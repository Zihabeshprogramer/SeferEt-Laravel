<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceRequestApprovedNotification extends Notification implements ShouldQueue
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
        
        return (new MailMessage)
                    ->subject('Service Request Approved - ' . $packageName)
                    ->greeting('Hello ' . $notifiable->name . '!')
                    ->line('Great news! Your service request has been approved.')
                    ->line('**Package:** ' . $packageName)
                    ->line('**Provider:** ' . $providerName)
                    ->line('**Service Type:** ' . ucfirst($this->serviceRequest->provider_type))
                    ->line('**Quantity:** ' . $this->serviceRequest->requested_quantity)
                    ->line('**Dates:** ' . $this->serviceRequest->start_date . ' to ' . $this->serviceRequest->end_date)
                    ->when($this->serviceRequest->approval_notes, function ($message) {
                        return $message->line('**Provider Notes:** ' . $this->serviceRequest->approval_notes);
                    })
                    ->line('**Approved:** ' . $this->serviceRequest->approved_at?->format('M j, Y g:i A'))
                    ->action('View Package', url('/b2b/packages/' . $this->serviceRequest->package_id))
                    ->line('You can now proceed with your travel package!')
                    ->success();
    }

    public function toDatabase($notifiable)
    {
        return [
            'service_request_id' => $this->serviceRequest->id,
            'type' => 'service_request_approved',
            'title' => 'Service Request Approved',
            'message' => 'Your service request has been approved by ' . ($this->serviceRequest->provider->name ?? 'the provider'),
            'data' => [
                'package_id' => $this->serviceRequest->package_id,
                'package_name' => $this->serviceRequest->package->name ?? null,
                'provider_name' => $this->serviceRequest->provider->name ?? null,
                'provider_type' => $this->serviceRequest->provider_type,
                'approved_at' => $this->serviceRequest->approved_at?->toISOString(),
                'approval_notes' => $this->serviceRequest->approval_notes,
                'requested_quantity' => $this->serviceRequest->requested_quantity,
                'start_date' => $this->serviceRequest->start_date,
                'end_date' => $this->serviceRequest->end_date
            ],
            'action_url' => '/b2b/packages/' . $this->serviceRequest->package_id,
            'icon' => 'fas fa-check-circle',
            'color' => 'success'
        ];
    }

    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}