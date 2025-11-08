<?php

namespace App\Notifications;

use App\Models\Ad;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Ad $ad;

    public function __construct(Ad $ad)
    {
        $this->ad = $ad;
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
        $rejectorName = $this->ad->approver->name ?? 'Administrator';
        
        return (new MailMessage)
                    ->subject('Advertisement Rejected - ' . $this->ad->title)
                    ->greeting('Hello ' . $notifiable->name . '!')
                    ->line('We regret to inform you that your advertisement has been rejected.')
                    ->line('**Title:** ' . $this->ad->title)
                    ->line('**Reviewed by:** ' . $rejectorName)
                    ->when($this->ad->rejection_reason, function ($message) {
                        return $message->line('**Reason:** ' . $this->ad->rejection_reason);
                    })
                    ->action('View Advertisement', url('/b2b/ads/' . $this->ad->id))
                    ->line('You can edit your advertisement and resubmit it for approval.')
                    ->line('If you have any questions, please contact our support team.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'ad_id' => $this->ad->id,
            'type' => 'ad_rejected',
            'title' => 'Advertisement Rejected',
            'message' => 'Your advertisement "' . $this->ad->title . '" has been rejected',
            'data' => [
                'ad_title' => $this->ad->title,
                'rejector_name' => $this->ad->approver->name ?? null,
                'rejection_reason' => $this->ad->rejection_reason,
                'rejected_at' => $this->ad->approved_at?->toISOString(),
            ],
            'action_url' => '/b2b/ads/' . $this->ad->id,
            'icon' => 'fas fa-times-circle',
            'color' => 'danger'
        ];
    }

    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}
