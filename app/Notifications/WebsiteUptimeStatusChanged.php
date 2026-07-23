<?php

namespace App\Notifications;

use App\Models\UptimeCheck;
use App\Models\Website;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WebsiteUptimeStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Website $website,
        public readonly UptimeCheck $check,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isDown = $this->check->status !== 'online';

        $message = (new MailMessage)
            ->subject($isDown
                ? "{$this->website->name} is down"
                : "{$this->website->name} is back online")
            ->line($isDown
                ? "{$this->website->name} ({$this->website->base_url}) is currently {$this->check->status}."
                : "{$this->website->name} ({$this->website->base_url}) is back online.")
            ->line("Checked at: {$this->check->checked_at}");

        if ($this->check->http_code !== null) {
            $message->line("HTTP status: {$this->check->http_code}");
        }

        return $message->action('View Website', url("/websites/{$this->website->id}"));
    }
}
