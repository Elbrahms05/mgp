# PACIPA-MGP - Back-office Laravel (`mgp-office/`)

## Stack technique

| Composant | Technologie |
|---|---|
| Framework | Laravel 12 |
| Templating | Blade Components + Layouts |
| Frontend | Alpine.js + Materialize CSS |
| Assets | Vite (plugin Laravel officiel) |
| Base de données | PostgreSQL + Redis (cache/sessions/queues) |
| Auth | Laravel Breeze/Fortify + 2FA obligatoire (rôles sensibles) |
| API | Laravel Sanctum |
| PDF | DomPDF / Snappy |
| Excel | Maatwebsite Excel |
| Géographie Niger | `elbrahms/niger-location` (composer require) |
| Chiffrement VBG | libsodium (XSalsa20-Poly1305) |

**Packages Spatie :**
- `spatie/laravel-permission` - RBAC (13 rôles)
- `spatie/laravel-activitylog` - audit trail complet
- `spatie/laravel-backup` - sauvegardes chiffrées automatiques
- `spatie/laravel-medialibrary` - pièces jointes plaintes
- `spatie/laravel-settings` - paramétrage persistant en BDD
- `spatie/laravel-tags` - classification libre plaintes
- `spatie/laravel-translatable` - champs multilingues (FR/HA/ZR)
- `spatie/laravel-query-builder` - filtres/tris dynamiques
- `spatie/laravel-data` - DTOs typés ODK ↔ Laravel
- `spatie/simple-excel` - exports CSV/Excel

## Architecture domaines (`app/Domains/`)

```
app/Domains/
├── Geography/       Models: Region, Department, Commune, Locality (elbrahms/niger-location)
├── Site/            Models: Site, SiteType, SiteContractor | Enums: SiteStatus, SiteCategory
├── Committee/       Models: Committee, CommitteeMember | Enums: CommitteeLevel
├── Complaint/       Models: Complaint, ComplaintType, ComplaintHistory | Enums: ComplaintStatus, ComplaintChannel
├── Vbg/             Models: VbgReport, VbgReferral, VbgEnquiry, VbgConsent
├── Worker/          Models: WorkerComplaint
├── Mediation/       Models: Mediation
├── Form/            Models: Form, FormVersion
├── OdkBridge/       Services: OdkApiClient, OdkSyncService, SubmissionMapper
├── Communication/   Models: CommunicationCampaign
├── Evaluation/      Models: CommitteeEvaluation
├── Documentation/   Models: PageGuide, FieldTooltip
└── Statistic/       Services: KpiCalculatorService, ChartDataService
```

Chaque domaine contient : `Models/`, `Enums/`, `Services/`, `Actions/`

## RBAC - 13 rôles (spatie/laravel-permission)

| Rôle | Scope | Accès VBG déchiffré |
|---|---|---|
| `super_admin` | Global | ✓ |
| `coordonnateur_ugp` | National | ✓ |
| `specialiste_vbg` | National | ✓ (exclusif) |
| `specialiste_sauvegarde_env` | National | - |
| `specialiste_sauvegarde_sociale` | National | - |
| `responsable_se` | National | - |
| `responsable_communication` | National | - |
| `chef_urc` | Régional | - |
| `president_ccgp` | Communal | - |
| `president_cbgp` | Base | - |
| `point_focal_vbg` | Base/Communal | Réception uniquement |
| `charge_enregistrement` | Base | - |
| `prestataire_ong_vbg` | National/Régional | Formulaire Annexe 9 |
| `personne_confiance_vbg` | Localité | Saisie Partie 1 uniquement |
| `entreprise_travaux` | Entreprise | Plaintes travailleurs uniquement |
| `auditeur` | Lecture seule | - |

## Permissions clés

```
complaints.create          → super_admin, coord, spec_sauv, president_ccgp, president_cbgp, charge_enreg
complaints.view_all        → super_admin, coord, spec_sauv
complaints.resolve         → super_admin, coord, spec_sauv, presidents
vbg.receive                → super_admin, coord, spec_vbg, point_focal_vbg
vbg.view_decrypted         → super_admin, coord, spec_vbg UNIQUEMENT
vbg.notify_worldbank       → super_admin, coord, spec_vbg
sites.view_own_region      → tous les rôles de terrain
sites.create/update        → super_admin, coord, spec_sauv
reports.generate           → super_admin, coord, spec_vbg, spec_sauv, president_ccgp
audit.view                 → super_admin, coord
settings.manage            → super_admin uniquement
```

## Module Sites (§4.12)

**Pourquoi :** Quasi toutes les plaintes concernent un site physique précis. Sans ce rattachement : impossible d'identifier les sites à risque, évaluer les entreprises, cibler les supervisions, produire les rapports BM.

**Table `sites` - colonnes clés :**
- `code` : `PACIPA-MARADI-001`
- `site_type_id` : FK vers référentiel types
- `locality_id` : FK vers `elbrahms/niger-location`
- `category` : enum (composantes PACIPA 1-5)
- `status` : `planned | in_progress | suspended | completed | closed`
- `risk_level` : `low | medium | high | critical` (calculé automatiquement chaque jour)
- `committee_id` : CBGP de la localité (auto-rattaché)

**Types de sites (référentiel seedé) :**
`irrigated_perimeter`, `ces_drs_works`, `nursery`, `water_point`, `livestock_shelter`, `vaccination_park`, `storage_warehouse`, `collection_center`, `rural_market`, `processing_unit`, `financial_office`, `training_center`

**Calcul `risk_level` (SiteRiskAnalysisService, quotidien) :**
- >5 plaintes ouvertes → +1 niveau
- >0 VBG/EAS/HS confirmés → critical
- >20% plaintes dépassant SLA → +1 niveau
- >3 plaintes du même type en 30j → +1 niveau
- Accident/décès → critical

Sites `critical` → notification auto Coordonnateur + Spécialiste sauvegarde sociale.

**Lien plainte ↔ site :** `complaints.site_id` nullable (hors-site = Type 1 uniquement). `vbg_reports.site_id` nullable (VBG anonymes - rattachement a posteriori par Spécialiste VBG).

## Module Documentation - Aide contextuelle (§4.11)

Chaque page back-office affiche un panneau d'aide (badge `?` flottant → sidebar) avec :
- Objectif de la page
- Référence MGP (chapitre/tableau/annexe)
- Guide pas-à-pas
- Alertes (délais SLA, interdictions, sécurité)

**Table `page_guides` :** `route_name (unique)`, `page_title`, `mgp_reference`, `summary`, `detailed_guide (markdown)`, `warnings (json)`, `display_mode (sidebar|modal|tooltip)`, `is_active`

**Implémentation :** `HelpPanelComposer` injecte le guide via `ViewServiceProvider`. `PageGuidesSeeder` pré-remplit tous les guides au déploiement.

**Mapping route → MGP (sélection) :**

| Route | Réf. MGP |
|---|---|
| `dashboard` | §5.2, Tableau 7 |
| `complaints.index` | §4.2.1, Tableau 4 |
| `complaints.create` | §4.3.3, Annexe 4, 8 |
| `vbg.index` | §4.3.3.5, Annexe 9 |
| `vbg.create` | §4.3.3.5, Annexe 9 |
| `committees.index` | §4.2.2, Tableau 5 |
| `sites.index` | §1.2, Composantes 1-5 |
| `tracking.index` | Tableau 6 |
| `statistics.dashboard` | §5.2, Tableau 7 |

## Module Settings (Spatie Settings classes)

`ProjectSettings` - Nom, zones, dates, logos
`OdkSettings` - URL serveur, token, fréquence sync
`SmsSettings` - Provider, API key, templates
`SlaSettings` - Délais par niveau (tous configurables)
`SecuritySettings` - MDP, 2FA, rotation clés
`NotificationSettings` - Canaux par événement, destinataires

## Module Statistics - Dashboard

- Compteurs temps réel : reçues / en cours / résolues / escaladées / rejetées
- Carte Niger interactive (heatmap par région + marqueurs sites par niveau de risque)
- Répartition par type (8 types), par canal, par région
- Taux résolution + délai moyen par niveau (CBGP/CCGP/CNGP)
- Plaintes SLA dépassé (liste rouge)
- Indicateurs VBG agrégés anonymisés (accès `specialiste_vbg` + `coordonnateur_ugp` uniquement)

## Module Reports

| Rapport | Fréquence | Format |
|---|---|---|
| Tableau de bord mensuel | Mensuel | PDF + Excel |
| Évaluation comités | Trimestriel | PDF |
| Rapport MGP | Semestriel + Annuel | PDF + Excel |
| Rapport VBG agrégé anonymisé | Trimestriel | PDF |
| Fiche suivi (Annexe 6) | Continue | PDF par plainte |
| PV conciliation (Annexe 5) | Par événement | PDF |
| Rapport commission enquête | Par événement | PDF chiffré |

## Conventions de code

- PSR-12, PHP
- Models PascalCase singulier, tables snake_case pluriel
- Logique métier dans `app/Domains/`, Controllers dans `app/Http/Controllers/`
- Migrations préfixées (`0007_`, `0020_`...), avec rollback fonctionnel
- Validation dans FormRequest, jamais dans le contrôleur
- Chaque modèle a sa Policy (Spatie Permission)
- Interface en français, code/variables/commentaires en anglais
- Traductions dans `resources/lang/` (fr/ + ha.json, zr.json, tq.json, kr.json, ff.json)

## Localisation

Langues supportées : Français (défaut), Haoussa, Zarma/Djerma, Tamasheq, Kanouri, Fulfulde/Peul
Middleware `SetLocale` → détecte préférence utilisateur (session) → `App::setLocale()`
