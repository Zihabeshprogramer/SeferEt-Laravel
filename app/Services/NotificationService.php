<?php

namespace App\Services;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\ServiceRequestCreatedNotification;
use App\Notifications\ServiceRequestApprovedNotification;
use App\Notifications\ServiceRequestRejectedNotification;
use App\Notifications\ServiceRequestExpiredNotification;
use App\Events\ServiceRequestCreated;
use App\Events\ServiceRequestApproved;
use App\Events\ServiceRequestRejected;
use App\Events\ServiceRequestExpired;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Notify when a service request is created
     */
    public function notifyServiceRequestCreated(ServiceRequest $serviceRequest): void
    {
        try {
            // Don't notify for own service auto-approvals
            if ($serviceRequest->own_service) {
                Log::info('Skipping notification for own service auto-approval', [
                    'service_request_id' => $serviceRequest->id
                ]);
                return;
            }

            // Notify the provider
            $provider = $serviceRequest->provider;
            if ($provider) {
                $provider->notify(new ServiceRequestCreatedNotification($serviceRequest));
            }

            // Fire real-time event
            broadcast(new ServiceRequestCreated($serviceRequest))->toOthers();

            // Log the notification
            Log::info('Service request created notification sent', [
                'service_request_id' => $serviceRequest->id,
                'provider_id' => $serviceRequest->provider_id,
                'agent_id' => $serviceRequest->agent_id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send service request created notification', [
                'service_request_id' => $serviceRequest->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify when a service request is approved
     */
    public function notifyServiceRequestApproved(ServiceRequest $serviceRequest): void
    {
        try {
            // Notify the agent
            $agent = $serviceRequest->agent;
            if ($agent) {
                $agent->notify(new ServiceRequestApprovedNotification($serviceRequest));
            }

            // Fire real-time event
            broadcast(new ServiceRequestApproved($serviceRequest))->toOthers();

            // Send email notification if configured
            $this->sendEmailNotificationIfEnabled($agent, 'request_approved', [
                'service_request' => $serviceRequest,
                'package' => $serviceRequest->package,
                'provider' => $serviceRequest->provider
            ]);

            Log::info('Service request approved notification sent', [
                'service_request_id' => $serviceRequest->id,
                'agent_id' => $serviceRequest->agent_id,
                'provider_id' => $serviceRequest->provider_id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send service request approved notification', [
                'service_request_id' => $serviceRequest->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify when a service request is rejected
     */
    public function notifyServiceRequestRejected(ServiceRequest $serviceRequest): void
    {
        try {
            // Notify the agent
            $agent = $serviceRequest->agent;
            if ($agent) {
                $agent->notify(new ServiceRequestRejectedNotification($serviceRequest));
            }

            // Fire real-time event
            broadcast(new ServiceRequestRejected($serviceRequest))->toOthers();

            // Send email notification for rejection (important)
            $this->sendEmailNotificationIfEnabled($agent, 'request_rejected', [
                'service_request' => $serviceRequest,
                'package' => $serviceRequest->package,
                'provider' => $serviceRequest->provider,
                'rejection_reason' => $serviceRequest->rejection_reason,
                'alternative_dates' => $serviceRequest->alternative_dates
            ]);

            Log::info('Service request rejected notification sent', [
                'service_request_id' => $serviceRequest->id,
                'agent_id' => $serviceRequest->agent_id,
                'provider_id' => $serviceRequest->provider_id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send service request rejected notification', [
                'service_request_id' => $serviceRequest->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify when a service request expires
     */
    public function notifyServiceRequestExpired(ServiceRequest $serviceRequest): void
    {
        try {
            // Notify both agent and provider
            $agent = $serviceRequest->agent;
            $provider = $serviceRequest->provider;

            if ($agent) {
                $agent->notify(new ServiceRequestExpiredNotification($serviceRequest, 'agent'));
            }

            if ($provider) {
                $provider->notify(new ServiceRequestExpiredNotification($serviceRequest, 'provider'));
            }

            // Fire real-time event
            broadcast(new ServiceRequestExpired($serviceRequest))->toOthers();

            Log::info('Service request expired notifications sent', [
                'service_request_id' => $serviceRequest->id,
                'agent_id' => $serviceRequest->agent_id,
                'provider_id' => $serviceRequest->provider_id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send service request expired notifications', [
                'service_request_id' => $serviceRequest->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send reminder notifications for expiring requests
     */
    public function sendExpirationReminders(): void
    {
        try {
            // Get requests expiring in the next 4 hours
            $expiringRequests = ServiceRequest::with(['agent', 'provider', 'package'])
                ->where('status', ServiceRequest::STATUS_PENDING)
                ->where('expires_at', '>', now())
                ->where('expires_at', '<=', now()->addHours(4))
                ->where('reminder_sent', false)
                ->get();

            foreach ($expiringRequests as $request) {
                $this->sendExpirationReminder($request);
                
                // Mark reminder as sent
                $request->update(['reminder_sent' => true]);
            }

            if ($expiringRequests->count() > 0) {
                Log::info('Expiration reminders sent', [
                    'count' => $expiringRequests->count(),
                    'request_ids' => $expiringRequests->pluck('id')->toArray()
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to send expiration reminders', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send individual expiration reminder
     */
    private function sendExpirationReminder(ServiceRequest $serviceRequest): void
    {
        try {
            $provider = $serviceRequest->provider;
            
            if ($provider) {
                // Send in-app notification
                $provider->notify(new \App\Notifications\ServiceRequestExpirationReminderNotification($serviceRequest));

                // Send email reminder
                $this->sendEmailNotificationIfEnabled($provider, 'request_expiring', [
                    'service_request' => $serviceRequest,
                    'package' => $serviceRequest->package,
                    'agent' => $serviceRequest->agent,
                    'hours_remaining' => now()->diffInHours($serviceRequest->expires_at)
                ]);

                // Fire real-time event for immediate UI update
                broadcast(new \App\Events\ServiceRequestExpirationReminder($serviceRequest));
            }

        } catch (\Exception $e) {
            Log::error('Failed to send expiration reminder', [
                'service_request_id' => $serviceRequest->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify about package approval status changes
     */
    public function notifyPackageApprovalStatusChange(int $packageId): void
    {
        try {
            $package = \App\Models\Package::with(['agent', 'serviceRequests'])->find($packageId);
            
            if (!$package) {
                return;
            }

            $agent = $package->agent;
            if (!$agent) {
                return;
            }

            $serviceRequests = $package->serviceRequests;
            
            $approvalSummary = [
                'package_id' => $packageId,
                'total_requests' => $serviceRequests->count(),
                'approved' => $serviceRequests->where('status', ServiceRequest::STATUS_APPROVED)->count(),
                'pending' => $serviceRequests->where('status', ServiceRequest::STATUS_PENDING)->count(),
                'rejected' => $serviceRequests->where('status', ServiceRequest::STATUS_REJECTED)->count(),
                'can_proceed' => $serviceRequests->whereNotIn('status', [
                    ServiceRequest::STATUS_APPROVED,
                    ServiceRequest::STATUS_CANCELLED
                ])->count() === 0
            ];

            // Send in-app notification
            $agent->notify(new \App\Notifications\PackageApprovalStatusNotification($package, $approvalSummary));

            // Fire real-time event
            broadcast(new \App\Events\PackageApprovalStatusChanged($package, $approvalSummary))->toOthers();

            Log::info('Package approval status notification sent', [
                'package_id' => $packageId,
                'agent_id' => $agent->id,
                'approval_summary' => $approvalSummary
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send package approval status notification', [
                'package_id' => $packageId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send bulk notifications to multiple users
     */
    public function sendBulkNotifications(array $userIds, string $notificationClass, array $data): array
    {
        $results = [
            'success_count' => 0,
            'failure_count' => 0,
            'errors' => []
        ];

        try {
            $users = User::whereIn('id', $userIds)->get();

            foreach ($users as $user) {
                try {
                    $notification = new $notificationClass(...$data);
                    $user->notify($notification);
                    $results['success_count']++;
                    
                } catch (\Exception $e) {
                    $results['failure_count']++;
                    $results['errors'][] = [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ];
                }
            }

            Log::info('Bulk notifications sent', [
                'notification_class' => $notificationClass,
                'total_users' => count($userIds),
                'success_count' => $results['success_count'],
                'failure_count' => $results['failure_count']
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send bulk notifications', [
                'notification_class' => $notificationClass,
                'error' => $e->getMessage()
            ]);
            
            $results['failure_count'] = count($userIds);
        }

        return $results;
    }

    /**
     * Get notification preferences for a user
     */
    public function getUserNotificationPreferences(User $user): array
    {
        return [
            'email_notifications' => $user->settings['email_notifications'] ?? true,
            'push_notifications' => $user->settings['push_notifications'] ?? true,
            'sms_notifications' => $user->settings['sms_notifications'] ?? false,
            'notification_types' => [
                'request_created' => $user->settings['notify_request_created'] ?? true,
                'request_approved' => $user->settings['notify_request_approved'] ?? true,
                'request_rejected' => $user->settings['notify_request_rejected'] ?? true,
                'request_expired' => $user->settings['notify_request_expired'] ?? true,
                'request_expiring' => $user->settings['notify_request_expiring'] ?? true,
                'package_status_change' => $user->settings['notify_package_status'] ?? true
            ]
        ];
    }

    /**
     * Update notification preferences for a user
     */
    public function updateNotificationPreferences(User $user, array $preferences): void
    {
        $settings = $user->settings ?? [];
        
        $settings = array_merge($settings, [
            'email_notifications' => $preferences['email_notifications'] ?? true,
            'push_notifications' => $preferences['push_notifications'] ?? true,
            'sms_notifications' => $preferences['sms_notifications'] ?? false,
            'notify_request_created' => $preferences['notification_types']['request_created'] ?? true,
            'notify_request_approved' => $preferences['notification_types']['request_approved'] ?? true,
            'notify_request_rejected' => $preferences['notification_types']['request_rejected'] ?? true,
            'notify_request_expired' => $preferences['notification_types']['request_expired'] ?? true,
            'notify_request_expiring' => $preferences['notification_types']['request_expiring'] ?? true,
            'notify_package_status' => $preferences['notification_types']['package_status_change'] ?? true
        ]);

        $user->update(['settings' => $settings]);

        Log::info('Notification preferences updated', [
            'user_id' => $user->id,
            'preferences' => $preferences
        ]);
    }

    /**
     * Send email notification if enabled for user
     */
    private function sendEmailNotificationIfEnabled(User $user, string $type, array $data): void
    {
        try {
            $preferences = $this->getUserNotificationPreferences($user);
            
            // Check if email notifications are enabled globally and for this type
            if (!$preferences['email_notifications'] || !($preferences['notification_types'][$type] ?? true)) {
                return;
            }

            // Determine email template and send email
            $emailView = $this->getEmailTemplate($type);
            
            if ($emailView) {
                Mail::send($emailView, $data, function ($message) use ($user, $type, $data) {
                    $message->to($user->email, $user->name)
                           ->subject($this->getEmailSubject($type, $data));
                });

                Log::info('Email notification sent', [
                    'user_id' => $user->id,
                    'type' => $type,
                    'email' => $user->email
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to send email notification', [
                'user_id' => $user->id,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get email template for notification type
     */
    private function getEmailTemplate(string $type): ?string
    {
        $templates = [
            'request_created' => 'emails.service_request_created',
            'request_approved' => 'emails.service_request_approved',
            'request_rejected' => 'emails.service_request_rejected',
            'request_expired' => 'emails.service_request_expired',
            'request_expiring' => 'emails.service_request_expiring',
            'package_status_change' => 'emails.package_approval_status'
        ];

        return $templates[$type] ?? null;
    }

    /**
     * Get email subject for notification type
     */
    private function getEmailSubject(string $type, array $data): string
    {
        $serviceRequest = $data['service_request'] ?? null;
        $package = $data['package'] ?? null;
        
        $id = isset($package->id) ? $package->id : 'N/A';

        switch ($type) {
            case 'request_created':
                return "New Service Request - Package #{$id}";
            case 'request_approved':
                return "Service Request Approved - Package #{$id}";
            case 'request_rejected':
                return "Service Request Rejected - Package #{$id}";
            case 'request_expired':
                return "Service Request Expired - Package #{$id}";
            case 'request_expiring':
                $hours = $data['hours_remaining'] ?? 'few';
                return "Service Request Expiring Soon ({$hours} hours) - Package #{$id}";
            case 'package_status_change':
                return "Package Approval Status Update - Package #{$id}";
            default:
                return "SeferEt Notification";
        }
    }

    /**
     * Clear old notifications
     */
    public function cleanupOldNotifications(int $daysToKeep = 30): int
    {
        try {
            $cutoffDate = now()->subDays($daysToKeep);
            
            $deletedCount = \DB::table('notifications')
                ->where('created_at', '<', $cutoffDate)
                ->delete();

            Log::info('Old notifications cleaned up', [
                'deleted_count' => $deletedCount,
                'cutoff_date' => $cutoffDate->format('Y-m-d H:i:s')
            ]);

            return $deletedCount;

        } catch (\Exception $e) {
            Log::error('Failed to cleanup old notifications', [
                'error' => $e->getMessage()
            ]);
            
            return 0;
        }
    }
}