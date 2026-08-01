<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\AnonymousNotifiable as BaseAnonymousNotifiable;

/**
 * Notifiable anonyme pour envoyer des notifications à un numéro de téléphone
 * sans utilisateur Eloquent (plaignant externe au système).
 * 
 * ⚠️  Les notifications doivent vérifier le type de notifiable et ne retourner que 'sms'
 * via leur méthode via() pour éviter les erreurs avec des canaux non-supportés.
 */
class AnonymousNotifiable extends BaseAnonymousNotifiable
{
    public function __construct(private readonly string $phone)
    {
        $this->route('sms', $phone);
    }

    public function routeNotificationFor($driver)
    {
        if ($driver === 'sms') {
            return $this->phone;
        }

        return parent::routeNotificationFor($driver);
    }
}
