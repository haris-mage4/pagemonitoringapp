<?php

namespace App\Notifications;

use App\Models\Page;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class NewPageErrorsDetected extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  Collection<int, \App\Models\PageError>  $errors
     */
    public function __construct(
        public readonly Page $page,
        public readonly Collection $errors,
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
        $count = $this->errors->count();

        $message = (new MailMessage)
            ->subject("{$count} new JS console ".($count === 1 ? 'error' : 'errors')." on {$this->page->url}")
            ->line('New JavaScript console '.($count === 1 ? 'error was' : 'errors were').' detected on:')
            ->line($this->page->url);

        foreach ($this->errors as $error) {
            $message->line("- {$error->message}");
        }

        return $message->action('View Page', url("/pages/{$this->page->id}"));
    }
}
