<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Trace intangible de chaque notification émise (SMS, email, database).
 *
 * Sert de preuve de conformité aux délais contractuels Banque mondiale
 * (ESIRT 24h, PEES 48h - cf. ER-01) et de piste d'audit des accusés de réception.
 *
 * ⚠️  Ne jamais journaliser de donnée identifiant une survivante VBG dans `body`
 *     ou `subject` : seul le code VBG-xxxx circule hors du module VBG (règle n°1).
 */
class NotificationLog extends Model
{
    protected $fillable = [
        'event',
        'channel',
        'recipient',
        'subject',
        'body',
        'status',
        'error_message',
        'notifiable_type',
        'notifiable_id',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT    = 'sent';
    public const STATUS_FAILED  = 'failed';

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }
}
