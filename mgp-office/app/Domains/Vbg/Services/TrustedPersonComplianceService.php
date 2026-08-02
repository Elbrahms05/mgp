<?php

declare(strict_types=1);

namespace App\Domains\Vbg\Services;

use App\Domains\Vbg\Models\TrustedPersonTraining;
use App\Models\User;
use Carbon\Carbon;

/**
 * Contrôles de conformité des personnes de confiance VBG (update.md 2.5) :
 *  - validité de la formation / recyclage (6 mois) ;
 *  - contrôle automatique de conflit d'intérêts à la saisie d'un signalement.
 */
class TrustedPersonComplianceService
{
    /** Durée de validité d'une formation avant recyclage (mois). */
    public const TRAINING_VALIDITY_MONTHS = 6;

    /**
     * Date jusqu'à laquelle la formation de la personne de confiance est valide.
     */
    public function trainingValidUntil(User $user): ?Carbon
    {
        $latest = TrustedPersonTraining::where('user_id', $user->id)
            ->orderByDesc('valid_until')
            ->first();

        return $latest?->valid_until;
    }

    /**
     * La personne de confiance est-elle à jour de sa formation / recyclage ?
     */
    public function isTrainingValid(User $user): bool
    {
        $validUntil = $this->trainingValidUntil($user);

        return $validUntil !== null && $validUntil->gte(now()->startOfDay());
    }

    /**
     * Enregistre une formation et calcule automatiquement l'échéance de recyclage (+6 mois).
     */
    public function recordTraining(User $user, Carbon $trainedAt, ?string $topic, ?User $recordedBy = null): TrustedPersonTraining
    {
        return TrustedPersonTraining::create([
            'user_id'     => $user->id,
            'trained_at'  => $trainedAt->toDateString(),
            'valid_until' => $trainedAt->copy()->addMonths(self::TRAINING_VALIDITY_MONTHS)->toDateString(),
            'topic'       => $topic,
            'recorded_by' => $recordedBy?->id,
        ]);
    }

    /**
     * Détecte un conflit d'intérêts à la saisie : la personne de confiance ne peut pas
     * traiter un cas la concernant (numéro identique) ni un cas qu'elle déclare la concerner.
     *
     * @param  array<string, mixed>  $data  données du signalement (dont caller_phone, conflict_declared)
     */
    public function detectConflict(array $data, User $trustedPerson): bool
    {
        if (! empty($data['conflict_declared'])) {
            return true;
        }

        $callerPhone = $this->normalizePhone($data['caller_phone'] ?? null);
        $ownPhone    = $this->normalizePhone($trustedPerson->phone ?? null);

        return $callerPhone !== null && $ownPhone !== null && $callerPhone === $ownPhone;
    }

    /**
     * Normalise un numéro sur ses 8 derniers chiffres (numéro national nigérien),
     * pour comparer indépendamment du préfixe pays (+227) et du formatage.
     */
    private function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if ($digits === '') {
            return null;
        }

        return strlen($digits) >= 8 ? substr($digits, -8) : $digits;
    }
}
