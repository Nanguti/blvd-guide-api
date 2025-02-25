<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\VerifyEmail;

class CustomVerifyEmail extends Notification
{
    use Queueable;

    /**
     * The frontend URL.
     */
    protected string $frontendUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        $this->frontendUrl = config('app.frontend_url', 'https://blvdguide.vercel.app');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify Email Address')
            ->line('Please click the button below to verify your email address.')
            ->action('Verify Email Address', $verificationUrl)
            ->line('If you did not create an account, no further action is required.')
            ->line('Regards, BLVD GUIDE');
    }


    /**
     * Get the verification URL for the given notifiable.
     */
    protected function verificationUrl(object $notifiable): string
    {
        $apiUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        // Parse the API URL to extract components
        $urlParts = parse_url($apiUrl);

        // Make sure query exists before parsing
        $queryString = $urlParts['query'] ?? '';
        parse_str($queryString, $queryParams);

        // Get the path parts to extract ID and hash
        $path = $urlParts['path'] ?? '';
        $pathParts = explode('/', trim($path, '/'));

        // Safely get the ID and hash
        $pathCount = count($pathParts);

        // Only try to get these if we have enough parts
        if ($pathCount >= 2) {
            $id = $pathParts[$pathCount - 2]; // Second-to-last element
            $hash = $pathParts[$pathCount - 1]; // Last element

            // Build frontend URL with all necessary parameters
            return $this->frontendUrl . '/verify-email/' . $id . '/' . $hash .
                '?expires=' . ($queryParams['expires'] ?? '') .
                '&signature=' . ($queryParams['signature'] ?? '');
        }

        // Fallback in case path doesn't have expected format
        return $this->frontendUrl . '/verify-email';
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
