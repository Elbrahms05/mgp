<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Documentation\Models\FieldTooltip;
use Illuminate\Database\Seeder;

class FieldTooltipsSeeder extends Seeder
{
    public function run(): void
    {
        $tooltips = [
            [
                'route_name'    => 'complaints.create',
                'field_name'    => 'complaint_type_id',
                'tooltip_text'  => 'Les 8 types de plaintes sont définis dans le Tableau 4 du MGP. Le type 8 (VBG/EAS/HS) est une plainte SENSIBLE qui suit un circuit séparé - utilisez le formulaire VBG dédié.',
                'mgp_reference' => 'Tableau 4',
            ],
            [
                'route_name'    => 'complaints.create',
                'field_name'    => 'channel',
                'tooltip_text'  => 'Voies de transmission des plaintes définies au §4.3.2 du MGP : en personne, téléphone, SMS, WhatsApp, courrier, email, boîte réclamation, ligne verte, auto-saisine.',
                'mgp_reference' => '§4.3.2',
            ],
            [
                'route_name'    => 'complaints.show',
                'field_name'    => 'status',
                'tooltip_text'  => 'Circuit de traitement (Tableau 6) : Enregistrée → En examen → Recevable/Rejetée → Médiation → Résolue/Escaladée → Clôturée. Chaque transition est horodatée.',
                'mgp_reference' => 'Tableau 6',
            ],
            [
                'route_name'    => 'vbg.create',
                'field_name'    => 'consent_given',
                'tooltip_text'  => 'Le consentement éclairé est OBLIGATOIRE avant toute collecte de données personnelles. Sans consentement, seules les données anonymisées minimales sont enregistrées (§4.3.3.5).',
                'mgp_reference' => '§4.3.3.5',
            ],
            [
                'route_name'    => 'vbg.create',
                'field_name'    => 'classification',
                'tooltip_text'  => 'Classification GBVIMS (Genre-Based Violence Information Management System) définie par l\'ONU. Utilisée dans les statistiques mondiales de suivi des VBG.',
                'mgp_reference' => 'Annexe 9, Classification GBVIMS',
            ],
            [
                'route_name'    => 'complaints.escalate',
                'field_name'    => 'current_level',
                'tooltip_text'  => 'Niveau d\'escalade : CBGP (base) → CCGP (communal) → CNGP (national). Délais : CBGP 3+7j, CCGP 3+4j, CNGP 7+3j (§4.2.2.1).',
                'mgp_reference' => '§4.2.2.1',
            ],
            [
                'route_name'    => 'vbg.referral',
                'field_name'    => 'service_type',
                'tooltip_text'  => '4 types de services de référencement : médical, psychosocial, juridique, sécurité. Les 4 centres holistiques (Maradi, Tahoua, Tillabéri, Zinder) sont disponibles.',
                'mgp_reference' => '§4.3.3.5, §6.4',
            ],
            [
                'route_name'    => 'sites.create',
                'field_name'    => 'category',
                'tooltip_text'  => 'Composante PACIPA du site : C1 (production agricole/animale), C2 (marchés), C3 (financement), C4 (coordination/formation), C5 (gestion de projet).',
                'mgp_reference' => '§1.2',
            ],
            [
                'route_name'    => 'users.create',
                'field_name'    => 'role',
                'tooltip_text'  => '16 rôles prédéfinis alignés sur l\'organigramme MGP (§4.2.2.2). Le rôle détermine les permissions d\'accès et le périmètre géographique de l\'utilisateur.',
                'mgp_reference' => '§4.2.2.2, Tableau 5',
            ],
        ];

        foreach ($tooltips as $tooltip) {
            FieldTooltip::updateOrCreate(
                [
                    'route_name' => $tooltip['route_name'],
                    'field_name' => $tooltip['field_name'],
                ],
                array_merge($tooltip, ['is_active' => true])
            );
        }
    }
}
