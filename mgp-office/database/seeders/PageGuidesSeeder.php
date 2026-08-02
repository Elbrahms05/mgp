<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Documentation\Models\PageGuide;
use Illuminate\Database\Seeder;

class PageGuidesSeeder extends Seeder
{
    public function run(): void
    {
        $guides = [
            [
                'route_name'    => 'dashboard',
                'page_title'    => 'Tableau de bord',
                'mgp_reference' => '§5.2, Tableau 7',
                'summary'       => 'Vue d\'ensemble de l\'état du MGP : plaintes reçues, en cours, résolues, indicateurs clés. Les compteurs reflètent les indicateurs du Tableau 7 du MGP.',
                'warnings'      => [['type' => 'info', 'text' => 'Les plaintes VBG n\'apparaissent qu\'en données agrégées anonymisées.']],
            ],
            [
                'route_name'    => 'complaints.index',
                'page_title'    => 'Liste des plaintes',
                'mgp_reference' => '§4.2.1, Tableau 4',
                'summary'       => 'Toutes les plaintes non sensibles (types 1 à 7) enregistrées dans le système. Filtrez par statut, type, région, niveau de traitement.',
            ],
            [
                'route_name'    => 'complaints.create',
                'page_title'    => 'Nouvelle plainte',
                'mgp_reference' => '§4.3.3, Annexe 4, Annexe 8',
                'summary'       => 'Enregistrement d\'une plainte selon le formulaire Annexe 4/8 du MGP. Chaque plainte reçoit un numéro unique et un accusé de réception automatique.',
                'warnings'      => [
                    ['type' => 'danger', 'text' => 'Si le plaignant signale une violence sexuelle ou physique (Type 8), utilisez le formulaire VBG - pas ce formulaire.'],
                ],
            ],
            [
                'route_name'    => 'complaints.show',
                'page_title'    => 'Détail plainte',
                'mgp_reference' => '§4.3.3, Tableau 6',
                'summary'       => 'Suivi complet d\'une plainte : statut actuel, historique des actions, délais restants, PV associés. Circuit : Enregistrée → Examen → Recevable/Rejetée → Médiation → Résolue/Escaladée → Clôturée.',
            ],
            [
                'route_name'    => 'complaints.escalate',
                'page_title'    => 'Escalade plainte',
                'mgp_reference' => '§4.3.3.1 à §4.3.3.3',
                'summary'       => 'Transfert d\'une plainte non résolue au niveau supérieur (CBGP→CCGP ou CCGP→CNGP). Un PV de non-conciliation est généré.',
                'warnings'      => [
                    ['type' => 'warning', 'text' => 'L\'escalade doit intervenir dans les 72h après la session de médiation infructueuse.'],
                ],
            ],
            [
                'route_name'    => 'vbg.index',
                'page_title'    => 'Plaintes VBG/EAS/HS',
                'mgp_reference' => '§4.3.3.5, Annexe 9',
                'summary'       => 'Liste des plaintes sensibles (type 8). Les données sont chiffrées. Aucune médiation n\'est autorisée.',
                'warnings'      => [
                    ['type' => 'danger', 'text' => 'Accès RESTREINT. Toute consultation est tracée dans le journal d\'audit.'],
                    ['type' => 'danger', 'text' => 'JAMAIS de règlement à l\'amiable pour les plaintes EAS/HS.'],
                ],
            ],
            [
                'route_name'    => 'vbg.create',
                'page_title'    => 'Réception plainte VBG',
                'mgp_reference' => '§4.3.3.5, Annexe 9',
                'summary'       => 'Formulaire en 2 parties (Annexe 9). Partie 1 : identité + consentement. Partie 2 : description des faits (uniquement si consentement obtenu). Les données sont chiffrées avant stockage.',
                'warnings'      => [
                    ['type' => 'danger', 'text' => 'Le consentement éclairé est OBLIGATOIRE avant toute saisie de la Partie 2.'],
                ],
            ],
            [
                'route_name'    => 'vbg.referral',
                'page_title'    => 'Référencement VBG',
                'mgp_reference' => '§4.3.3.5',
                'summary'       => 'Transfert vers un prestataire de services VBG (médical, psychosocial, juridique, sécurité). Délai max : 72 heures.',
                'warnings'      => [
                    ['type' => 'warning', 'text' => 'Délai maximum : 72h après réception du signalement VBG.'],
                ],
            ],
            [
                'route_name'    => 'vbg.enquiry',
                'page_title'    => 'Commission d\'enquête',
                'mgp_reference' => '§4.3.3.5',
                'summary'       => 'La commission vérifie le lien plainte-projet (30j max). Elle ne statue JAMAIS sur la culpabilité.',
                'warnings'      => [
                    ['type' => 'warning', 'text' => 'Délai maximum : 30 jours. La commission vérifie UNIQUEMENT le lien avec le projet.'],
                ],
            ],
            [
                'route_name'    => 'committees.index',
                'page_title'    => 'Liste des comités',
                'mgp_reference' => '§4.2.2, Tableau 5',
                'summary'       => 'Tous les comités MGP installés : CBGP (base), CCGP (communal), CNGP (national).',
            ],
            [
                'route_name'    => 'committees.create',
                'page_title'    => 'Installation comité',
                'mgp_reference' => '§4.2.2.2, Annexe 3',
                'summary'       => 'Création d\'un comité selon le PV d\'installation (Annexe 3). Chaque CBGP et CCGP doit avoir 2 points focaux VBG (1H + 1F).',
                'warnings'      => [
                    ['type' => 'warning', 'text' => '2 points focaux VBG obligatoires (1 homme + 1 femme) pour CBGP et CCGP.'],
                ],
            ],
            [
                'route_name'    => 'committees.evaluate',
                'page_title'    => 'Évaluation comité',
                'mgp_reference' => 'Annexe 10',
                'summary'       => 'Fiche d\'évaluation trimestrielle : membres opérationnels, plaintes traitées, difficultés, feedbacks communauté.',
            ],
            [
                'route_name'    => 'mediation.create',
                'page_title'    => 'PV de conciliation',
                'mgp_reference' => '§4.3.4, Annexe 5',
                'summary'       => 'Rédaction du PV de conciliation (Annexe 5). Uniquement pour les plaintes NON sensibles.',
                'warnings'      => [
                    ['type' => 'danger', 'text' => 'JAMAIS utilisable pour les plaintes VBG/EAS/HS. La médiation est INTERDITE pour les cas EAS/HS.'],
                ],
            ],
            [
                'route_name'    => 'workers.index',
                'page_title'    => 'Plaintes travailleurs',
                'mgp_reference' => '§4.3.3.4',
                'summary'       => 'Plaintes des travailleurs directs (UGP) et contractuels (entreprises). Circuit spécifique en 2 niveaux avec délais de 7 jours par niveau.',
            ],
            [
                'route_name'    => 'tracking.index',
                'page_title'    => 'Suivi global',
                'mgp_reference' => 'Tableau 6',
                'summary'       => 'Vue consolidée de toutes les plaintes avec alertes SLA. Délais : CBGP 3+7j, CCGP 3+4j, CNGP 7+3j, notification VBG 24h, référencement 72h.',
                'warnings'      => [
                    ['type' => 'warning', 'text' => 'Les plaintes en rouge ont dépassé leur délai SLA. Action immédiate requise.'],
                ],
            ],
            [
                'route_name'    => 'statistics.dashboard',
                'page_title'    => 'Statistiques',
                'mgp_reference' => '§5.2, Tableau 7',
                'summary'       => 'Indicateurs avancés : taux résolution, délais moyens, répartition géographique, canaux utilisés. Données VBG uniquement en agrégé anonymisé.',
            ],
            [
                'route_name'    => 'reports.index',
                'page_title'    => 'Rapports',
                'mgp_reference' => '§5.2',
                'summary'       => 'Génération des rapports périodiques (mensuel, trimestriel, semestriel, annuel) pour l\'UGP, les URC, le COPIL et la Banque mondiale.',
            ],
            [
                'route_name'    => 'forms.index',
                'page_title'    => 'Gestion formulaires',
                'mgp_reference' => '§4.3, Annexe 4',
                'summary'       => 'Administration des formulaires XForm déployés sur les tablettes terrain. Création, versioning, déploiement vers ODK/KoBoToolbox.',
            ],
            [
                'route_name'    => 'geography.index',
                'page_title'    => 'Zones d\'intervention',
                'mgp_reference' => '§1.4',
                'summary'       => 'Configuration des 8 régions, départements, communes et localités du PACIPA. Base de la cascade géographique dans les formulaires.',
            ],
            [
                'route_name'    => 'settings.index',
                'page_title'    => 'Paramétrage',
                'mgp_reference' => '§5.4',
                'summary'       => 'Configuration globale : projet, connexion ODK, SMS gateway, délais SLA, sécurité, notifications.',
            ],
            [
                'route_name'    => 'users.index',
                'page_title'    => 'Utilisateurs',
                'mgp_reference' => '§4.2.2.2, Tableau 5',
                'summary'       => 'Gestion des comptes. Chaque utilisateur est rattaché à un rôle et à une zone géographique.',
            ],
            [
                'route_name'    => 'roles.index',
                'page_title'    => 'Rôles & Permissions',
                'mgp_reference' => '§4.2.2.2',
                'summary'       => '16 rôles alignés sur l\'organigramme du MGP. Le Spécialiste VBG et le Coordonnateur UGP sont les seuls à pouvoir déchiffrer les données VBG.',
            ],
            [
                'route_name'    => 'communication.index',
                'page_title'    => 'Communication',
                'mgp_reference' => '§5.1',
                'summary'       => 'Suivi des campagnes de sensibilisation, émissions radio, supports diffusés. Le MGP doit être vulgarisé en français et en langues locales.',
            ],
            [
                'route_name'    => 'audit.index',
                'page_title'    => 'Journal d\'audit',
                'mgp_reference' => '§5.5, ESIRT',
                'summary'       => 'Historique complet de toutes les actions utilisateur. Toute consultation de données VBG est tracée.',
                'warnings'      => [
                    ['type' => 'info', 'text' => 'Ce journal est conservé conformément aux exigences ESIRT de la Banque mondiale.'],
                ],
            ],
            [
                'route_name'    => 'sites.index',
                'page_title'    => 'Liste des sites',
                'mgp_reference' => '§1.2, Composantes 1-5',
                'summary'       => 'Tous les sites physiques du PACIPA. Les plaintes sont rattachées à un site précis. Le niveau de risque est recalculé chaque jour.',
            ],
            [
                'route_name'    => 'sites.create',
                'page_title'    => 'Nouveau site',
                'mgp_reference' => '§4.2.2',
                'summary'       => 'Création d\'un site rattaché à une localité du Niger. Le CBGP de la localité devient automatiquement le comité référent.',
            ],
            [
                'route_name'    => 'sites.show',
                'page_title'    => 'Détail du site',
                'mgp_reference' => '§4.3.3, §4.3.3.5',
                'summary'       => 'Vue 360° d\'un site : infos générales, plaintes ouvertes/résolues, entreprises intervenantes, indicateurs de performance E&S.',
                'warnings'      => [
                    ['type' => 'danger', 'text' => 'Les sites en niveau critique font l\'objet d\'une supervision renforcée du Spécialiste sauvegarde sociale.'],
                ],
            ],
            [
                'route_name'    => 'mediation.index',
                'page_title'    => 'Sessions de médiation',
                'mgp_reference' => '§4.3.4, Annexe 5',
                'summary'       => 'Liste de toutes les sessions de médiation enregistrées. La médiation est UNIQUEMENT applicable aux plaintes non sensibles (types 1 à 7). Jamais pour les plaintes EAS/HS.',
                'warnings'      => [
                    ['type' => 'danger', 'text' => 'La médiation est INTERDITE pour les plaintes VBG/EAS/HS (Type 8). Tout cas de médiation proposée sur une plainte VBG est une violation du MGP.'],
                ],
            ],
            [
                'route_name'    => 'mediation.show',
                'page_title'    => 'Détail médiation',
                'mgp_reference' => '§4.3.4, Annexe 5',
                'summary'       => 'PV de conciliation (Annexe 5). Si accord : la plainte passe automatiquement en statut Résolue. Si désaccord : la plainte peut être escaladée.',
            ],
            [
                'route_name'    => 'workers.create',
                'page_title'    => 'Nouvelle plainte travailleur',
                'mgp_reference' => '§4.3.3.4',
                'summary'       => 'Enregistrement d\'une plainte travailleur. Préciser si le travailleur est directement employé par l\'UGP ou par une entreprise contractuelle (délais différents).',
                'warnings'      => [
                    ['type' => 'warning', 'text' => 'Délais : Travailleurs directs N1=7j, N2=14j. Contractuels N1=7j ouvrables, N2=7j.'],
                ],
            ],
            [
                'route_name'    => 'workers.show',
                'page_title'    => 'Détail plainte travailleur',
                'mgp_reference' => '§4.3.3.4',
                'summary'       => 'Suivi d\'une plainte travailleur avec escalade possible au niveau 2. La résolution doit être tracée avec date et commentaire.',
            ],
            [
                'route_name'    => 'documentation.index',
                'page_title'    => 'Guides contextuels',
                'mgp_reference' => '§5.6',
                'summary'       => 'Administration des guides d\'aide contextuels affichés sur chaque page du back-office. Chaque guide référence le chapitre MGP correspondant.',
                'warnings'      => [
                    ['type' => 'info', 'text' => 'Les guides sont affichés via le badge ? flottant sur chaque page. Ils apparaissent selon le mode : sidebar, modal ou tooltip.'],
                ],
            ],
        ];

        foreach ($guides as $guide) {
            PageGuide::updateOrCreate(
                ['route_name' => $guide['route_name']],
                array_merge($guide, ['is_active' => true, 'display_mode' => 'sidebar'])
            );
        }
    }
}
