<?php

declare(strict_types=1);

namespace App\Domains\OdkBridge\Services;

use App\Domains\Complaint\Enums\ComplaintChannel;
use App\Domains\Complaint\Enums\ComplaintStatus;
use App\Domains\Complaint\Models\Complaint;
use App\Domains\Complaint\Models\ComplaintType;
use App\Domains\Committee\Models\Committee;
use App\Domains\Evaluation\Models\CommitteeEvaluation;
use App\Domains\Mediation\Models\Mediation;
use App\Domains\Site\Models\Site;
use App\Domains\Vbg\Enums\GbvimsClassification;
use App\Domains\Vbg\Enums\VbgAuthorLink;
use App\Domains\Vbg\Enums\VbgChannel;
use App\Domains\Vbg\Models\VbgReport;
use App\Domains\Vbg\Services\DirectReportIngestionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

/**
 * Transforme un payload de soumission ODK (XML parsé) en modèle Eloquent.
 *
 * Routing par form_id :
 *   pacipa_f01              → Complaint::create (nouvelle plainte)
 *   pacipa_f02              → Complaint update  (instruction)
 *   pacipa_f03_vbg          → VbgReport via DirectReportIngestionService
 *   pacipa_f04              → Mediation::create (PV conciliation)
 *   pacipa_f05              → Complaint history update (suivi)
 *   pacipa_f06              → Committee::updateOrCreate (installation)
 *   pacipa_f07              → CommitteeEvaluation::create (évaluation)
 */
class SubmissionMapper
{
    /** Map des canaux XForm → ComplaintChannel enum */
    private const CHANNEL_MAP = [
        'en_personne'      => ComplaintChannel::IN_PERSON,
        'telephone'        => ComplaintChannel::PHONE,
        'sms'              => ComplaintChannel::SMS,
        'whatsapp'         => ComplaintChannel::WHATSAPP,
        'courrier'         => ComplaintChannel::LETTER,
        'email'            => ComplaintChannel::EMAIL,
        'boite_reclamation' => ComplaintChannel::COMPLAINT_BOX,
        'ligne_verte'      => ComplaintChannel::HOTLINE,
        'auto_saisine'     => ComplaintChannel::AUTO,
    ];

    /** Map des formes VBG XForm → GbvimsClassification enum */
    private const GBVIMS_MAP = [
        'viol'                  => GbvimsClassification::RAPE,
        'agression_sexuelle'    => GbvimsClassification::SEXUAL_ASSAULT,
        'agression_physique'    => GbvimsClassification::PHYSICAL_ASSAULT,
        'violence_psychologique' => GbvimsClassification::PSYCHOLOGICAL_EMOTIONAL,
        'mariage_force'         => GbvimsClassification::FORCED_MARRIAGE,
        'deni_services'         => GbvimsClassification::DENIAL_OF_RESOURCES,
        'aucun_incident_verifie' => GbvimsClassification::NO_INCIDENT,
    ];

    public function __construct(
        private readonly DirectReportIngestionService $ingestionService,
    ) {}

    /**
     * Porte d'entrée principale : parse le XML et délègue selon le form_type MGP.
     *
     * @param  string $xmlFormId  xmlFormId ODK Central (pour les logs)
     * @param  string $formType   Type MGP enregistré dans la table forms (F01–F07)
     * @param  string $xmlBody    XML brut de la soumission
     * @return Complaint|VbgReport|Mediation|CommitteeEvaluation|Committee|null
     */
    public function map(string $xmlFormId, string $formType, string $xmlBody): mixed
{
    try {
        libxml_use_internal_errors(true);

        $xmlBody = trim($xmlBody);

        if (empty($xmlBody)) {
            Log::error("XML vide pour {$xmlFormId}");
            return null;
        }

        // 🔴 Détection JSON (cas ODK submissions list)
        if (str_starts_with($xmlBody, '{') || str_starts_with($xmlBody, '[')) {
            Log::warning("Payload JSON reçu au lieu de XML pour {$xmlFormId}", [
                'body' => $xmlBody
            ]);
            return null; // ⛔ skip proprement
        }

        // 🔴 Vérification rapide XML
        if (!str_starts_with($xmlBody, '<')) {
            Log::error("Contenu non XML pour {$xmlFormId}", [
                'body' => $xmlBody
            ]);
            return null;
        }

        $xml = simplexml_load_string($xmlBody);

        if ($xml === false) {
            Log::error("XML invalide pour {$xmlFormId}", [
                'errors' => libxml_get_errors(),
                'body' => $xmlBody
            ]);
            return null;
        }

    } catch (\Exception $e) {
        Log::error("SubmissionMapper - exception XML {$xmlFormId}", [
            'error' => $e->getMessage()
        ]);
        return null;
    }

    return match ($formType) {
        'F01'   => $this->mapF01($xml),
        'F02'   => $this->mapF02($xml),
        'F03'   => $this->mapF03($xml),
        'F04'   => $this->mapF04($xml),
        'F05'   => $this->mapF05($xml),
        'F06'   => $this->mapF06($xml),
        'F07'   => $this->mapF07($xml),
        default => $this->logUnknown($xmlFormId, $formType),
    };
}

    // ---------------------------------------------------------------
    //  F01 - Dépôt de plainte → Complaint
    // ---------------------------------------------------------------

    private function mapF01(SimpleXMLElement $xml): ?Complaint
    {
        $localRef = $this->field($xml, 'id_plainte');

        // Éviter les doublons (soumission déjà importée)
        if ($localRef !== '' && Complaint::where('registration_number', $localRef)->exists()) {
            Log::info("SubmissionMapper F01 - doublon ignoré : {$localRef}");
            return null;
        }

        // La localisation est déduite du SEUL identifiant de site saisi (les référentiels
        // géographiques peuvent évoluer : ne rien figer côté formulaire).
        $horsSite = $this->field($xml, 'plainte_hors_site') === 'oui';
        $siteId   = $this->field($xml, 'site_id');
        $site     = (! $horsSite && $siteId !== '')
            ? Site::where('code', $siteId)
                ->orWhere('id', ctype_digit($siteId) ? (int) $siteId : 0)
                ->first()
            : null;

        $isAnonymous = $this->field($xml, 'anonymat') === 'oui';

        // Le formulaire code les types « type1 »…« type8 » ; le référentiel back-office
        // utilise « type_1 »…« type_8 ».
        $typeCode = preg_replace('/^type(\d)$/', 'type_$1', $this->field($xml, 'type'));
        $type     = $typeCode ? ComplaintType::where('code', $typeCode)->first() : null;

        $channel = self::CHANNEL_MAP[$this->field($xml, 'canal')] ?? ComplaintChannel::IN_PERSON;

        return Complaint::create([
            'registration_number' => $localRef ?: 'ODK-' . now()->format('YmdHis'),
            'site_id'             => $site?->id,
            'complainant_name'    => $isAnonymous ? null : ($this->field($xml, 'nom_prenom') ?: null),
            'complainant_age'     => $this->field($xml, 'age') ?: null,
            'complainant_gender'  => $this->field($xml, 'genre') ?: null,
            'complainant_locality' => ($this->field($xml, 'lieu_residence')
                ?: ($horsSite ? $this->field($xml, 'site_libre') : '')) ?: null,
            'complainant_phone'   => $this->field($xml, 'contact') ?: null,
            'is_anonymous'        => $isAnonymous,
            'complaint_type_id'   => $type?->id,
            'accused_party'       => $this->field($xml, 'personne_visee') ?: null,
            'description'         => $this->field($xml, 'description') ?: 'Plainte importée (ODK)',
            'incident_date'       => $this->parseDate($this->field($xml, 'date_faits')),
            'channel'             => $channel,
            'status'              => ComplaintStatus::REGISTERED,
            'source'              => 'odk_mobile',
            'registered_by'       => 3,
        ]);
    }

    /**
     * Lit la valeur d'un champ par son nom, où qu'il se trouve dans l'arbre de la
     * soumission (robuste au regroupement en sections field-list du XForm).
     */
    private function field(SimpleXMLElement $xml, string $name): string
    {
        $nodes = $xml->xpath("//{$name}");

        return $nodes ? trim((string) $nodes[0]) : '';
    }

    // ---------------------------------------------------------------
    //  F02 - Instruction plainte non sensible → Complaint update
    // ---------------------------------------------------------------

    private function mapF02(SimpleXMLElement $xml): ?Complaint
    {
        $ref = (string) $xml->ref_f01;
        $complaint = Complaint::where('registration_number', $ref)->first();

        if (! $complaint) {
            Log::warning("SubmissionMapper F02 - plainte introuvable : {$ref}");
            return null;
        }

        $complaint->update([
            'status' => ComplaintStatus::UNDER_REVIEW,
        ]);

        // Historique
        $complaint->history()->create([
            'action'  => 'odk_f02_instruction',
            'details' => [
                'nature_bien'       => (string) $xml->nature_bien_affecte,
                'observations'      => (string) $xml->observations_autorite,
                'reponse_proposee'  => (string) $xml->reponse_proposee,
                'plaignant_accepte' => (string) $xml->plaignant_accepte,
                'statut_resolution' => (string) $xml->statut_resolution,
            ],
        ]);

        return $complaint;
    }

    // ---------------------------------------------------------------
    //  F03 - Rapport VBG/EAS/HS → VbgReport (via ingestion service)
    // ---------------------------------------------------------------

    private function mapF03(SimpleXMLElement $xml): ?VbgReport
    {
        // Extraire les formes VBG (select_multiple = valeurs espace-séparées)
        $formeVbgRaw   = (string) $xml->forme_vbg;
        $classification = $this->mapFirstGbvims($formeVbgRaw);

        $payload = [
            'channel'           => VbgChannel::POINT_FOCAL->value,
            'is_anonymous'      => true,          // F03 est toujours anonyme côté MGP
            'caller_phone'      => null,
            'survivor_age_group' => (string) $xml->survivant_groupe_age ?: null,
            'incident_date'     => $this->parseDate((string) $xml->date_incident),
            'incident_zone'     => (string) $xml->lieu_incident_type ?: null,
            'author_link'       => $this->mapAuthorLink((string) $xml->auteur_categorie),
            'classification'    => $classification?->value,
            'consent_given'     => ((string) $xml->consentement_referral) === 'oui',
            'services_received' => $this->parseSpaceSeparated((string) $xml->services_recus_avant),
            'raw_content'       => $formeVbgRaw,
            // Partie 2 (si consentement)
            'author_name'       => (string) $xml->auteur_nom_surnom ?: null,
            'incident_details'  => (string) $xml->compte_rendu_faits ?: null,
        ];

        return $this->ingestionService->ingest($payload);
    }

    // ---------------------------------------------------------------
    //  F04 - PV de conciliation → Mediation
    // ---------------------------------------------------------------

    private function mapF04(SimpleXMLElement $xml): ?Mediation
    {
        $ref       = (string) $xml->ref_plainte;
        $complaint = Complaint::where('registration_number', $ref)->first();

        if (! $complaint) {
            Log::warning("SubmissionMapper F04 - plainte introuvable : {$ref}");
            return null;
        }

        return Mediation::create([
            'complaint_id'   => $complaint->id,
            'session_date'   => $this->parseDate((string) $xml->date_seance),
            'location'       => (string) $xml->lieu_seance,
            'president'      => (string) $xml->president_seance,
            'outcome'        => (string) $xml->resultat_conciliation,
            'summary'        => (string) $xml->description_accord,
            'commitments'    => (string) $xml->engagements ?: null,
            'deadline'       => $this->parseDate((string) $xml->delai_execution),
            'status'         => match ((string) $xml->resultat_conciliation) {
                'accord'         => 'agreed',
                'accord_partiel' => 'partially_agreed',
                default          => 'failed',
            },
            'source'         => 'odk_mobile',
        ]);
    }

    // ---------------------------------------------------------------
    //  F05 - Suivi périodique → Complaint history entry
    // ---------------------------------------------------------------

    private function mapF05(SimpleXMLElement $xml): ?Complaint
    {
        $ref       = (string) $xml->ref_plainte;
        $complaint = Complaint::where('registration_number', $ref)->first();

        if (! $complaint) {
            Log::warning("SubmissionMapper F05 - plainte introuvable : {$ref}");
            return null;
        }

        $newStatus = match ((string) $xml->statut_actuel) {
            'resolu'                 => ComplaintStatus::RESOLVED,
            'transmis_niveau_superieur' => ComplaintStatus::ESCALATED,
            'classe'                 => ComplaintStatus::CLOSED,
            default                  => $complaint->status,
        };

        $complaint->update(['status' => $newStatus]);

        $complaint->history()->create([
            'action'  => 'odk_f05_suivi',
            'details' => [
                'service_transmis'   => (string) $xml->service_transmis,
                'accuse_reception'   => (string) $xml->accuse_reception,
                'statut'             => (string) $xml->statut_actuel,
                'plaignant_informe'  => (string) $xml->plaignant_informe,
                'reaction_plaignant' => (string) $xml->reaction_plaignant,
            ],
        ]);

        return $complaint;
    }

    // ---------------------------------------------------------------
    //  F06 - Installation comité CGP → Committee
    // ---------------------------------------------------------------

    private function mapF06(SimpleXMLElement $xml): ?Committee
    {
        $sitePacipa = (string) $xml->site_pacipa;
        $site       = Site::where('code', 'like', "%{$sitePacipa}%")->first();

        return Committee::updateOrCreate(
            ['site_id' => $site?->id, 'level' => 'cbgp'],
            [
                'region_code'         => (string) $xml->region,
                'commune'             => (string) $xml->commune,
                'locality'            => (string) $xml->localite,
                'installation_date'   => $this->parseDate((string) $xml->date_installation),
                'meeting_location'    => (string) $xml->lieu_reunion,
                'total_participants'  => (int) (string) $xml->nb_participants_total,
                'male_participants'   => (int) (string) $xml->nb_participants_hommes,
                'female_participants' => (int) (string) $xml->nb_participants_femmes,
                'ugp_representative'  => (string) $xml->representant_ugp ?: null,
                'status'              => 'active',
                'source'              => 'odk_mobile',
            ]
        );
    }

    // ---------------------------------------------------------------
    //  F07 - Évaluation trimestrielle CGP → CommitteeEvaluation
    // ---------------------------------------------------------------

    private function mapF07(SimpleXMLElement $xml): ?CommitteeEvaluation
    {
        return CommitteeEvaluation::create([
            'region_code'           => (string) $xml->region,
            'commune'               => (string) $xml->commune,
            'locality'              => (string) $xml->localite,
            'quarter'               => (string) $xml->trimestre,
            'year'                  => (int) (string) $xml->annee,
            'evaluation_date'       => $this->parseDate((string) $xml->date_evaluation),
            'total_members'         => (int) (string) $xml->nb_membres_total,
            'active_members'        => (int) (string) $xml->nb_membres_actifs,
            'meetings_held'         => (int) (string) $xml->nb_reunions_tenues,
            'complaints_registered' => (int) (string) $xml->plaintes_enregistrees,
            'complaints_processed'  => (int) (string) $xml->plaintes_traitees,
            'complaints_resolved'   => (int) (string) $xml->plaintes_reglees,
            'complaints_escalated'  => (int) (string) $xml->plaintes_transmises,
            'resolved_within_sla'   => (int) (string) $xml->plaintes_dans_delai,
            'resolved_outside_sla'  => (int) (string) $xml->plaintes_hors_delai,
            'difficulties'          => (string) $xml->difficultes ?: null,
            'solutions'             => (string) $xml->solutions ?: null,
            'community_satisfaction' => (string) $xml->satisfaction_communaute ?: null,
            'community_feedback'    => (string) $xml->feedbacks_recus ?: null,
            'suggestions'           => (string) $xml->suggestions ?: null,
            'source'                => 'odk_mobile',
        ]);
    }

    // ---------------------------------------------------------------
    //  Helpers
    // ---------------------------------------------------------------

    private function parseDate(string $value): ?Carbon
    {
        if (empty($value)) return null;
        try {
            return Carbon::parse($value);
        } catch (\Exception) {
            return null;
        }
    }

    private function parseSpaceSeparated(string $value): array
    {
        return array_filter(explode(' ', trim($value)));
    }

    private function mapFirstGbvims(string $formeVbgRaw): ?GbvimsClassification
    {
        foreach ($this->parseSpaceSeparated($formeVbgRaw) as $code) {
            if (isset(self::GBVIMS_MAP[$code])) {
                return self::GBVIMS_MAP[$code];
            }
        }
        return null;
    }

    private function mapAuthorLink(string $value): ?string
    {
        return match ($value) {
            'personnel_projet'   => VbgAuthorLink::PROJECT_STAFF->value,
            'fds'                => VbgAuthorLink::SECURITY_FORCES->value,
            'sans_lien_projet'   => VbgAuthorLink::NO_PROJECT_LINK->value,
            default              => VbgAuthorLink::UNKNOWN->value,
        };
    }

    private function logUnknown(string $xmlFormId, string $formType): null
    {
        Log::warning("SubmissionMapper - form_type inconnu : {$formType}", ['form_id' => $xmlFormId]);
        return null;
    }
}
