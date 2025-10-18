<?php

namespace App\Events;

use App\Models\ServiceRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceRequestCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ServiceRequest $serviceRequest;

    public function __construct(ServiceRequest $serviceRequest)
    {
        // Load relationships conditionally based on whether package exists
        $relationships = ['agent', 'provider'];
        
        // Only load package relationship if it's not a draft package
        if ($serviceRequest->package_id && !$serviceRequest->isDraftPackage()) {
            $relationships[] = 'package';
        }
        
        $this->serviceRequest = $serviceRequest->load($relationships);
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $channels = [
            // Broadcast to the specific provider
            new PrivateChannel('user.' . $this->serviceRequest->provider_id),
            
            // Broadcast to general service requests channel for admins
            new PrivateChannel('service-requests')
        ];
        
        // Add package channel only if not a draft package
        if ($this->serviceRequest->package_id) {
            $channels[] = new PrivateChannel('package.' . $this->serviceRequest->package_id);
        } else if ($this->serviceRequest->isDraftPackage()) {
            // For draft packages, broadcast to agent channel
            $channels[] = new PrivateChannel('user.' . $this->serviceRequest->agent_id);
        }
        
        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'service-request.created';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'service_request' => [
                'id' => $this->serviceRequest->id,
                'uuid' => $this->serviceRequest->uuid,
                'status' => $this->serviceRequest->status,
                'priority' => $this->serviceRequest->priority,
                'provider_type' => $this->serviceRequest->provider_type,
                'requested_quantity' => $this->serviceRequest->requested_quantity,
                'start_date' => $this->serviceRequest->start_date,
                'end_date' => $this->serviceRequest->end_date,
                'expires_at' => $this->serviceRequest->expires_at?->toISOString(),
                'special_requirements' => $this->serviceRequest->special_requirements,
                'created_at' => $this->serviceRequest->created_at->toISOString(),
            ],
            'package' => $this->serviceRequest->package ? [
                'id' => $this->serviceRequest->package->id,
                'name' => $this->serviceRequest->package->name,
            ] : [
                'id' => $this->serviceRequest->package_id,
                'name' => 'Draft Package',
            ],
            'agent' => [
                'id' => $this->serviceRequest->agent->id,
                'name' => $this->serviceRequest->agent->name,
                'email' => $this->serviceRequest->agent->email,
            ],
            'provider' => [
                'id' => $this->serviceRequest->provider->id,
                'name' => $this->serviceRequest->provider->name,
                'email' => $this->serviceRequest->provider->email,
            ],
            'notification' => [
                'title' => 'New Service Request',
                'message' => 'You have a new service request from ' . ($this->serviceRequest->agent ? $this->serviceRequest->agent->name : 'Unknown Agent'),
                'type' => 'info',
                'icon' => 'fas fa-handshake'
            ]
        ];
    }
}