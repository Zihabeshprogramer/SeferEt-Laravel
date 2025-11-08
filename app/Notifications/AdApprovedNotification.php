<?php

namespace App\Notifications;

use App\Models\Ad;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdApprovedNotification extends Notification implements ShouldQueue
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
        $approverName = $this->ad->approver->name ?? 'Administrator';
        
        return (new MailMessage)
                    ->subject('Advertisement Approved - ' . $this->ad->title)
                    ->greeting('Hello ' . $notifiable->name . '!')
                    ->line('Great news! Your advertisement has been approved.')
                    ->line('**Title:** ' . $this->ad->title)
                    ->line('**Approved by:** ' . $approverName)
                    ->line('**Approved at:** ' . $this->ad->approved_at->format('M j, Y g:i A'))
                    ->when($this->ad->start_at, function ($message) {
                        return $message->line('**Start Date:** ' . $this->ad->start_at->format('M j, Y'));
                    })
                    ->when($this->ad->end_at, function ($message) {
                        return $message->line('**End Date:** ' . $this->ad->end_at->format('M j, Y'));
                    })
                    ->action('View Advertisement', url('/b2b/ads/' . $this->ad->id))
                    ->line('Your advertisement will start appearing according to its schedule.')
                    ->line('Thank you for using our advertising platform!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'ad_id' => $this->ad->id,
            'type' => 'ad_approved',
            'title' => 'Advertisement Approved',
            'message' => 'Your advertisement "' . $this->ad->title . '" has been approved',
            'data' => [
                'ad_title' => $this->ad->title,
                'approver_name' => $this->ad->approver->name ?? null,
                'approved_at' => $this->ad->approved_at?->toISOString(),
                'start_at' => $this->ad->start_at?->toISOString(),
                'end_at' => $this->ad->end_at?->toISOString(),
            ],
            'action_url' => '/b2b/ads/' . $this->ad->id,
            'icon' => 'fas fa-check-circle',
            'color' => 'success'
        ];
    }

    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}
