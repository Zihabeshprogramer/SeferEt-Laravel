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

class ServiceRequestRejected implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ServiceRequest $serviceRequest;

    public function __construct(ServiceRequest $serviceRequest)
    {
        $this->serviceRequest = $serviceRequest->load(['package', 'agent', 'provider']);
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            // Broadcast to the specific agent
            new PrivateChannel('user.' . $this->serviceRequest->agent_id),
            
            // Broadcast to package channel for agents working on this package
            new PrivateChannel('package.' . $this->serviceRequest->package_id),
            
            // Broadcast to general service requests channel for admins
            new PrivateChannel('service-requests')
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'service-request.rejected';
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
                'rejected_at' => $this->serviceRequest->rejected_at?->toISOString(),
                'rejection_reason' => $this->serviceRequest->rejection_reason,
                'created_at' => $this->serviceRequest->created_at->toISOString(),
            ],
            'package' => [
                'id' => $this->serviceRequest->package->id,
                'name' => $this->serviceRequest->package->name,
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
                'title' => 'Service Request Rejected',
                'message' => 'Your service request was rejected by ' . $this->serviceRequest->provider->name . 
                           ($this->serviceRequest->rejection_reason ? ': ' . $this->serviceRequest->rejection_reason : ''),
                'type' => 'error',
                'icon' => 'fas fa-times-circle'
            ]
        ];
    }
}