<?php

namespace App\Notifications;

use App\Models\Ad;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdSubmittedNotification extends Notification implements ShouldQueue
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
        $ownerName = $this->ad->owner->name ?? 'Unknown Owner';
        
        return (new MailMessage)
                    ->subject('New Advertisement Submission - ' . $this->ad->title)
                    ->greeting('Hello ' . $notifiable->name . '!')
                    ->line('A new advertisement has been submitted for approval.')
                    ->line('**Title:** ' . $this->ad->title)
                    ->line('**Owner:** ' . $ownerName)
                    ->line('**Placement:** ' . ucfirst(str_replace('_', ' ', $this->ad->placement ?? 'Not specified')))
                    ->line('**Device Type:** ' . ucfirst($this->ad->device_type ?? 'all'))
                    ->when($this->ad->product, function ($message) {
                        return $message->line('**Promoting:** ' . ($this->ad->product_type ?? 'N/A'));
                    })
                    ->action('Review Advertisement', url('/admin/ads/pending'))
                    ->line('Please review and take action on this advertisement submission.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'ad_id' => $this->ad->id,
            'type' => 'ad_submitted',
            'title' => 'New Advertisement Submission',
            'message' => 'A new advertisement "' . $this->ad->title . '" has been submitted for approval',
            'data' => [
                'ad_title' => $this->ad->title,
                'owner_name' => $this->ad->owner->name ?? null,
                'placement' => $this->ad->placement,
                'device_type' => $this->ad->device_type,
                'product_type' => $this->ad->product_type,
            ],
            'action_url' => '/admin/ads/' . $this->ad->id,
            'icon' => 'fas fa-ad',
            'color' => 'warning'
        ];
    }

    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}
