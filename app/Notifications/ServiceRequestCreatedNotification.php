<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class ServiceRequestCreatedNotification extends Notification implements ShouldQueue
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
        $agentName = $this->serviceRequest->agent->name ?? 'Unknown Agent';
        
        return (new MailMessage)
                    ->subject('New Service Request - ' . $packageName)
                    ->greeting('Hello ' . $notifiable->name . '!')
                    ->line('You have received a new service request from ' . $agentName . '.')
                    ->line('**Package:** ' . $packageName)
                    ->line('**Service Type:** ' . ucfirst($this->serviceRequest->provider_type))
                    ->line('**Quantity:** ' . $this->serviceRequest->requested_quantity)
                    ->line('**Dates:** ' . $this->serviceRequest->start_date . ' to ' . $this->serviceRequest->end_date)
                    ->line('**Priority:** ' . ucfirst($this->serviceRequest->priority))
                    ->when($this->serviceRequest->special_requirements, function ($message) {
                        return $message->line('**Special Requirements:** ' . $this->serviceRequest->special_requirements);
                    })
                    ->line('**Expires:** ' . $this->serviceRequest->expires_at?->format('M j, Y g:i A'))
                    ->action('Review Request', url('/b2b/service-requests/' . $this->serviceRequest->id))
                    ->line('Please review and respond to this request before it expires.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'service_request_id' => $this->serviceRequest->id,
            'type' => 'service_request_created',
            'title' => 'New Service Request',
            'message' => 'You have a new service request from ' . ($this->serviceRequest->agent->name ?? 'an agent'),
            'data' => [
                'package_id' => $this->serviceRequest->package_id,
                'package_name' => $this->serviceRequest->package->name ?? null,
                'agent_name' => $this->serviceRequest->agent->name ?? null,
                'provider_type' => $this->serviceRequest->provider_type,
                'priority' => $this->serviceRequest->priority,
                'expires_at' => $this->serviceRequest->expires_at?->toISOString(),
                'requested_quantity' => $this->serviceRequest->requested_quantity,
                'start_date' => $this->serviceRequest->start_date,
                'end_date' => $this->serviceRequest->end_date
            ],
            'action_url' => '/b2b/service-requests/' . $this->serviceRequest->id,
            'icon' => 'fas fa-handshake',
            'color' => 'info'
        ];
    }

    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}