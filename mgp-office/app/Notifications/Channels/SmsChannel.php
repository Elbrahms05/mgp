<?php

declare(strict_types=1);

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Custom SMS Notification Channel
 * 
 * Envoie des notifications SMS via le service configuré (Africa's Talking, Twilio, etc.)
 * Pour le développement, les SMS sont loggés au lieu d'être envoyés.
 */
class SmsChannel
{
    public function send(mixed $notifiable, Notification $notification): void
    {
        // Récupérer le numéro de téléphone
        $phone = $notifiable->routeNotificationFor('sms');

        if (! $phone) {
            Log::warning('SMS notification skipped: no phone number', [
                'notifiable' => $notifiable::class,
                'notification' => $notification::class,
            ]);
            return;
        }

        // Récupérer le message SMS
        $message = $notification->toSms($notifiable);

        if (! $message) {
            return;
        }

        // Log the SMS (en production, l'envoyer via un provider)
        Log::channel('sms')->info('SMS sent', [
            'phone'    => $phone,
            'message'  => $message,
            'provider' => config('sms.provider'),
        ]);

        // TODO: Intégrer un vrai provider SMS (Africa's Talking, Twilio, Orange Niger, etc.)
        // Pour l'instant, on log simplement le message SMS
    }
}
