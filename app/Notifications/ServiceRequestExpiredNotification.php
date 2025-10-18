<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceRequestExpiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected ServiceRequest $serviceRequest;
    protected string $recipientType;

    public function __construct(ServiceRequest $serviceRequest, string $recipientType = 'agent')
    {
        $this->serviceRequest = $serviceRequest;
        $this->recipientType = $recipientType; // 'agent' or 'provider'
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
        
        if ($this->recipientType === 'agent') {
            $providerName = $this->serviceRequest->provider->name ?? 'Provider';
            
            return (new MailMessage)
                        ->subject('Service Request Expired - ' . $packageName)
                        ->greeting('Hello ' . $notifiable->name . '!')
                        ->line('Your service request has expired without a response.')
                        ->line('**Package:** ' . $packageName)
                        ->line('**Provider:** ' . $providerName)
                        ->line('**Service Type:** ' . ucfirst($this->serviceRequest->provider_type))
                        ->line('**Dates:** ' . $this->serviceRequest->start_date . ' to ' . $this->serviceRequest->end_date)
                        ->line('**Expired:** ' . $this->serviceRequest->expires_at?->format('M j, Y g:i A'))
                        ->action('Create New Request', url('/b2b/packages/' . $this->serviceRequest->package_id))
                        ->line('You can create a new request or contact the provider directly.')
                        ->error();
        } else {
            $agentName = $this->serviceRequest->agent->name ?? 'Agent';
            
            return (new MailMessage)
                        ->subject('Service Request Expired - ' . $packageName)
                        ->greeting('Hello ' . $notifiable->name . '!')
                        ->line('A service request from ' . $agentName . ' has expired.')
                        ->line('**Package:** ' . $packageName)
                        ->line('**Agent:** ' . $agentName)
                        ->line('**Service Type:** ' . ucfirst($this->serviceRequest->provider_type))
                        ->line('**Dates:** ' . $this->serviceRequest->start_date . ' to ' . $this->serviceRequest->end_date)
                        ->line('**Expired:** ' . $this->serviceRequest->expires_at?->format('M j, Y g:i A'))
                        ->line('This request is no longer available for response.')
                        ->line('Consider reaching out to the agent if you are still interested in providing this service.');
        }
    }

    public function toDatabase($notifiable)
    {
        if ($this->recipientType === 'agent') {
            return [
                'service_request_id' => $this->serviceRequest->id,
                'type' => 'service_request_expired_agent',
                'title' => 'Service Request Expired',
                'message' => 'Your service request has expired without response from ' . ($this->serviceRequest->provider->name ?? 'the provider'),
                'data' => [
                    'package_id' => $this->serviceRequest->package_id,
                    'package_name' => $this->serviceRequest->package->name ?? null,
                    'provider_name' => $this->serviceRequest->provider->name ?? null,
                    'provider_type' => $this->serviceRequest->provider_type,
                    'expired_at' => $this->serviceRequest->expires_at?->toISOString(),
                    'requested_quantity' => $this->serviceRequest->requested_quantity,
                    'start_date' => $this->serviceRequest->start_date,
                    'end_date' => $this->serviceRequest->end_date
                ],
                'action_url' => '/b2b/packages/' . $this->serviceRequest->package_id,
                'icon' => 'fas fa-clock',
                'color' => 'secondary'
            ];
        } else {
            return [
                'service_request_id' => $this->serviceRequest->id,
                'type' => 'service_request_expired_provider',
                'title' => 'Service Request Expired',
                'message' => 'A service request from ' . ($this->serviceRequest->agent->name ?? 'an agent') . ' has expired',
                'data' => [
                    'package_id' => $this->serviceRequest->package_id,
                    'package_name' => $this->serviceRequest->package->name ?? null,
                    'agent_name' => $this->serviceRequest->agent->name ?? null,
                    'provider_type' => $this->serviceRequest->provider_type,
                    'expired_at' => $this->serviceRequest->expires_at?->toISOString(),
                    'requested_quantity' => $this->serviceRequest->requested_quantity,
                    'start_date' => $this->serviceRequest->start_date,
                    'end_date' => $this->serviceRequest->end_date
                ],
                'action_url' => '/b2b/service-requests/' . $this->serviceRequest->id,
                'icon' => 'fas fa-clock',
                'color' => 'warning'
            ];
        }
    }

    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}