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

class ServiceRequestApproved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ServiceRequest $serviceRequest;

    public function __construct(ServiceRequest $serviceRequest)
    {
        $this->serviceRequest = $serviceRequest->load(['package', 'agent', 'provider', 'allocations']);
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
        return 'service-request.approved';
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
                'approved_at' => $this->serviceRequest->approved_at?->toISOString(),
                'approval_notes' => $this->serviceRequest->approval_notes,
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
            'allocations' => $this->serviceRequest->allocations->map(function ($allocation) {
                return [
                    'id' => $allocation->id,
                    'quantity' => $allocation->quantity,
                    'total_price' => $allocation->total_price,
                    'status' => $allocation->status,
                ];
            }),
            'notification' => [
                'title' => 'Service Request Approved',
                'message' => 'Your service request has been approved by ' . $this->serviceRequest->provider->name,
                'type' => 'success',
                'icon' => 'fas fa-check-circle'
            ]
        ];
    }
}