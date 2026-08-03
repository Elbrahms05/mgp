# UPDATE.md - Mises à jour requises pour la conformité du système PACIPA-MGP (v2)

> **Objet** : revue du dépôt `Elbrahms05/mgp` au commit `cae5a5d` (« Suivre les fichiers de
> mgp-office - correction du gitlink ») contre le référentiel des 28 exigences (Annexe 3 du
> mémoire - EF-01…14, ENF-01…07, ER-01…07). Cette version **remplace** le `update.md` v1
> commis à la racine, établi quand `mgp-office/` était un gitlink vide.
>
> **Priorités** : 🔴 P0 = bloquant pilote/conformité BM · 🟠 P1 = requis avant généralisation · 🟢 P2 = amélioration
> **Statuts** : ✅ Conforme · 🟡 Partiel · ❌ Absent

---

## 0. Écarts v1 corrigés - le back-office existe désormais

La quasi-totalité du chantier n°1 de la v1 est réalisée. Constaté dans le code :

| Item v1 | Constat au commit `cae5a5d` |
|---|---|
| 1.1 Socle | Laravel 12, PostgreSQL/Redis, tous les packages Spatie + DomPDF + Maatwebsite ; vues Blade de **tous** les modules (`resources/views/pages/{complaints,vbg,workers,sites,reports,statistics,…}`) |
| 1.2 Domaines | 14 domaines `app/Domains/` (dont `Company/` ajouté hors spec - à documenter) |
| 1.3 Workflow | `ComplaintWorkflowService` complet : register/accept/reject/startMediation/resolve/close/escalate + `ComplaintHistory` |
| 1.4 SLA/escalade | `SlaMonitorService::computeDeadline()` sur `SlaSettings` ; commande planifiée `complaints:check-sla --escalate` (auto en production) ; équivalent travailleurs `workers:check-sla` |
| 1.6 Module VBG | Schéma PostgreSQL **séparé** `vbg.*` (4 tables), `VbgEncryptionService` en **libsodium réel** (`sodium_crypto_secretbox`), clé hors BDD (`VBG_ENCRYPTION_KEY`), `ConsentService`, `AnonymizationService`, purge planifiée `vbg:purge-stale --hours=72` |
| 1.9 RBAC | `RolesAndPermissionsSeeder` : les 13+ rôles au complet (dont `personne_confiance_vbg`, `entreprise_travaux`, `auditeur`) ; champs 2FA sur `User` |
| 1.10 Audit | `spatie/laravel-activitylog` migré + `AuditController` |
| 1.14 Travailleurs | Domaine `Worker/` complet : workflow, SLA, historique, lien `company_id` |
| 1.15/1.17 | `KpiCalculatorService`, `ChartDataService`, `SiteRiskAnalysisService` + `sites:recalculate-risk` planifié |
| 1.18 Pont ODK | `OdkApiClient/OdkSyncService/SubmissionMapper` + `odk:sync` et `odk:sync --vbg` planifiés + webhooks `/webhooks/odk` |
| 1.19 Aide | `PageGuidesSeeder` + `FieldTooltipsSeeder` |
| 1.20 Paramètres | Les 6 classes Settings (Project/Odk/Sms/Sla/Security/Notification) + seeders |
| 3.x Canaux VBG | Les 4 webhooks (`UssdWebhookController`, `SmsWebhookController`, `WhatsAppWebhookController`, `TrustedPersonController`) + `DirectReportIngestionService` + routes `/webhooks/vbg` |
| 4.4 F01 assaini | La note type 8 redirige vers F03 : « NE PAS continuer ce formulaire » (`relevant ${type}='type8'`) |
| ENF-07 (serveur) | `lang/` couvre FR + HA/ZR/TQ/KR/FF |

**Le reste de ce fichier ne liste que ce qui manque encore.**

---

## 1. 🔴 P0 - Câblages fonctionnels manquants dans mgp-office

- [x] **1.1 Notifications Banque mondiale réellement émises** - ✅ **FAIT**. Mailables
  dé-identifiés `EsirtNotificationMail` (24 h) / `PeesNotificationMail` (48 h) + vues
  `resources/views/emails/vbg/{esirt,pees}.blade.php` ; `WorldBankNotificationService`
  réécrit : envoi réel `Mail::to()` vers les destinataires de
  `NotificationSettings->recipients['worldbank_esirt'|'worldbank_pees']['emails']`,
  traçage dans `notification_logs` via le modèle `App\Models\NotificationLog` (statut
  sent/failed/pending, `sent_at`). Couvert par `NotificationDeadlineTest`.
  → *ER-01 - satisfait*
- [x] **1.2 Accusé de réception effectif** - ✅ **FAIT (PDF + SMS)**. Nouveau
  `ComplaintAcknowledgmentService` (DomPDF → `resources/views/pdf/complaint-acknowledgment.blade.php`,
  stocké en collection média privée `acknowledgment`, tracé dans `notification_logs`).
  Génération **centralisée dans `ComplaintObserver::created()`** → couvre **toutes** les
  voies de création (saisie back-office, import ODK `SubmissionMapper::mapF01`, futur
  webhook SMS). L'accusé **SMS** au plaignant part déjà via
  `ComplaintRegisteredNotification`. Route imprimable `complaints.acknowledgment`
  (`GET /complaints/{complaint}/accuse`). Couvert par `ComplaintWorkflowTest`.
  **Bug corrigé au passage** : `ComplaintObserver` appelait
  `Notification::route('database', null)` (interdit → toute création de plainte levait une
  exception) ; désormais la notif tableau de bord vise l'agent enregistreur.
  ⚠️ Reste l'émetteur SMS réel (passerelle) - voir 1.4 / 2.1.
  → *EF-06*
- [x] **1.3 API mobile : dépendance manquante** - ✅ **FAIT**. `laravel/sanctum` installé,
  `HasApiTokens` sur `User`. Endpoints `/api/mobile/*` (`AuthController` login/me/logout par
  jeton d'appareil ; `FormController` index/show distribuant F01–F07 depuis
  `mgp-office/forms/` avec `version`=hash pour le `FormSyncService` ; `ComplaintStatusController`
  remontée d'état). **F03/VBG cloisonné** : distribué et téléchargeable uniquement avec
  `vbg.receive`. Couvert par `MobileApiTest` (8 tests). ⚠️ Reste le **certificat client
  (mTLS)** des appareils = infra (documenté en tête de `routes/api.php`, cf. 4.3).
  → *ENF-01, EF-01*
- [ ] **1.4 Webhook de soumission SMS (mobile → office, sans ODK Central)** - côté mobile,
  `pacipa/sms/` (SmsSubmissionService/PayloadBuilder/Encryption) est prêt ; côté serveur,
  **rien** : `spatie/laravel-webhook-client` absent, aucune route de réception. Décision
  d'architecture à trancher et à consigner dans `claude.md` (règle n° 12) :
  **option A** conserver le module SMS maison et créer le webhook sécurisé (signature,
  idempotence, déchiffrement compatible `SmsEncryption`, segmentation ≤160 car.) ;
  **option B (envisagée)** basculer la couche transport sur le fork `medic/medic-collect`
  (ODK Collect modifié par Medic - envoi des formulaires par SMS vers numéro court/normal,
  relais vers le backend), téléphones + SIM fournis aux acteurs. Dans les deux cas, un
  seul transport SMS maintenu, avec accusé SMS automatique (cf. 1.2).
  → *ENF-01, ENF-04, EF-06, EF-10*
  → ✅ **FAIT (option A)** : `spatie/laravel-webhook-client` installé et configuré (config
  « sms »). Route `POST /webhooks/sms` avec **signature HMAC-SHA256** (`X-Signature`),
  **idempotence** par `message_id`, et `ProcessSmsWebhookJob` qui crée une plainte non
  sensible via `ComplaintWorkflowService` (accusé SMS + PDF automatiques). Les types
  sensibles (VBG) ne sont **jamais** matérialisés par ce canal. Exclusion CSRF `webhooks/*`
  ajoutée dans `bootstrap/app.php` (corrige aussi les webhooks VBG/ODK existants). Couvert
  par `SmsWebhookTest` (4 tests). ⚠️ Le déchiffrement compatible mobile `SmsEncryption`
  reste un point d'intégration documenté (en tête de `ProcessSmsWebhookJob`). Arbitrage
  option A retenu - à consigner dans `claude.md` règle n°12.
- [x] **1.5 Enquête de satisfaction à la clôture** - ✅ **FAIT**. Table
  `satisfaction_surveys` + modèle `SatisfactionSurvey` (relation `Complaint::satisfactionSurvey`),
  `SatisfactionSurveyController` (create/store) + vue `pages/complaints/satisfaction.blade.php`
  (notes 1–5 globale/délais/accueil/résolution, recommandation, canal, commentaire).
  Indicateurs `KpiCalculatorService::getSatisfactionStats()` (moyenne, % satisfaits, %
  recommandation). Couvert par `SatisfactionSurveyTest` (2 tests).
  → *EF-08*
- [x] **1.6 Saisie différée du canal papier** - ✅ **FAIT**. Colonne `received_at` sur
  `complaints` (date de réception papier ≠ date de saisie) ;
  `SlaMonitorService::computeDeadline()` accepte une base `$from` et
  `ComplaintWorkflowService::register()` fonde le SLA sur `received_at`.
  `DeferredComplaintController` (create/store) + vue `pages/complaints/deferred.blade.php`
  avec **fiche numérisée jointe** (medialibrary, EF-09). Couvert par `DeferredComplaintTest`
  (SLA calculé depuis la réception + pièce jointe).
  → *EF-11, EF-09*
- [x] **1.7 Suite de tests critiques** - ✅ **FAIT**. Les 9 tests sont écrits et **verts**
  (`php artisan test` : 46 passés) : `SlaCalculationTest`, `ComplaintEscalationTest`,
  `VbgEncryptionTest`, `VbgReferralFlowTest`, `VbgAccessControlTest`,
  `NotificationDeadlineTest`, `ComplaintWorkflowTest`, `PageGuideDisplayTest`,
  `SpatieBackupTest`. Migration VBG rendue **compatible SQLite** (ATTACH DATABASE pour les
  tests, `CREATE SCHEMA` conservé pour PostgreSQL). Deux écarts réels trouvés et corrigés
  par les tests : le bug `ComplaintObserver` (cf. 1.2) et 4 guides sans `mgp_reference`
  (règle n°10) dans `PageGuidesSeeder` (`forms.index`, `settings.index`, `audit.index`,
  `documentation.index`).
  → *transverse, critère de sortie de tous les items P0*
- [x] **1.8 Rapports : génération et envoi automatique** - ✅ **FAIT (états clés + envoi)**.
  `ReportPdfService` (DomPDF) : tableau de bord mensuel, VBG agrégé trimestriel
  **anonymisé** (indicateurs `getVbgKpis`, aucune donnée nominative), rapport annuel MGP -
  vues `pdf/reports/*`. Export **Excel** `ComplaintsExport` (Maatwebsite, plaintes non
  sensibles). Formats `pdf`/`xlsx` branchés dans `ReportController::generate()` +
  `GenerateReportRequest` + sélecteur UI. **Envoi automatique périodique** : commande
  `reports:send-periodic --period=monthly|quarterly|annual` (`PeriodicReportMail` avec PDF
  joint), destinataires depuis `NotificationSettings::report_recipients` (rôles/emails,
  flag `enabled`), traçage `notification_logs` ; planifié dans `routes/console.php`
  (mensuel/trimestriel/annuel). Couvert par `ReportGenerationTest` (3) +
  `PeriodicReportSendTest` (2). ⚠️ Restent en P2 : fiche Annexe 6, PV Annexe 5 et rapport
  de commission **chiffré** (gabarits spécifiques - mêmes briques PDF).
  → *EF-05, EF-14, ER-04*
- [x] **1.9 2FA : contrainte effective** - ✅ **FAIT**. `laravel/fortify` installé, trait
  `TwoFactorAuthenticatable` + colonne `two_factor_recovery_codes` sur `User`. Flux réel :
  activation/confirmation/désactivation + codes de récupération (`TwoFactorController` +
  `security/two-factor` view via actions Fortify) ; **défi TOTP à la connexion** intégré à
  Breeze (`AuthenticatedSessionController` suspend la session → `TwoFactorChallengeController`
  vérifie code TOTP ou code de récupération). Middleware `Enforce2FA` réécrit :
  **blocage** des rôles `super_admin`/`coordonnateur_ugp`/`specialiste_vbg` (liste
  `SecuritySettings::two_factor_required_roles`) tant que le 2FA n'est pas confirmé. Routes
  Fortify désactivées (`Fortify::$registersRoutes = false`) pour ne pas entrer en conflit
  avec Breeze. Couvert par `TwoFactorAuthTest` (5 tests : enforcement + défi TOTP complet).
  → *ER-02, ENF-02*
- [x] **1.10 Sauvegardes planifiées + restauration** - ✅ **FAIT (planification)**.
  `routes/console.php` planifie `backup:clean` (01:30), `backup:run` (02:30) et
  `backup:monitor` (08:00), sorties dans `storage/logs/backup.log`. Rétention portée à
  **90 j** (`config/backup.php` → `keep_daily_backups_for_days`) ; chiffrement d'archive
  déjà actif (`encryption = default`, mot de passe `BACKUP_ARCHIVE_PASSWORD`). Couvert par
  `SpatieBackupTest`. ⚠️ Reste à **documenter/exécuter le test de restauration mensuel**
  (procédure d'exploitation, hors code).
  → *ENF-02*

---

## 2. 🟠 P1 - Canaux directs VBG : du code aux opérateurs

Les contrôleurs et l'ingestion existent ; il reste l'intégration réelle et les garanties.

- [~] **2.1 Passerelles à intégrer** - **partie code déjà en place** : `UssdWebhookController` /
  `SmsWebhookController` / `WhatsAppWebhookController` vérifient déjà la **signature HMAC-SHA256**
  et renvoient des **réponses neutres** (jamais « VBG »/« violence »), code `VBG-xxxx` seul
  identifiant (cf. `UssdChannel::response()`). **Reste (ops)** : brancher une passerelle réelle
  (Africa's Talking / Twilio / partenaire local) et fournir le secret `VBG_WEBHOOK_SECRET`.
  → *EF-04, ER-02*
- [ ] **2.2 Conventions opérateurs** - négociation Orange/Airtel/Moov : short code USSD
  `*xxx#` gratuit (appel inversé) et SMS `3xxx`. Action UGP à tracer (préalable à toute
  communication radio).
  → *EF-04, ENF-07*
- [ ] **2.3 Menus 5 langues** - vérifier que l'arbre USSD/WhatsApp sert FR/HA/ZR/TQ/FF,
  avec l'option « Je préfère ne pas dire » non contournable.
  → *ENF-07*
- [ ] **2.4 Ligne verte** - parcours opératrice ONG : saisie Annexe 9 Partie 1 en direct
  dans le back-office, bascule hors-heures vers la spécialiste VBG, aucun enregistrement
  d'appel sans consentement.
  → *EF-04, EF-12*
- [x] **2.5 Personnes de confiance** - ✅ **FAIT**. Table `vbg_trusted_person_trainings` +
  modèle `TrustedPersonTraining` (registre formation/recyclage) ;
  `TrustedPersonComplianceService` : validité **6 mois** (`recordTraining` calcule
  `valid_until`), `isTrainingValid()`, et **contrôle automatique de conflit d'intérêts**
  (`detectConflict` : numéro identique sur les 8 derniers chiffres, ou conflit déclaré).
  `TrustedPersonController::store()` **bloque** la saisie si formation périmée (403) ou
  conflit (422). Registre géré via `TrustedPersonTrainingController` (index/store) +
  vue `pages/vbg/trusted-person-trainings`. Couvert par `TrustedPersonComplianceTest`
  (6 tests). ⚠️ La formation initiale/recyclage effective des acteurs reste une action UGP.
  → *EF-04, ER-02*
- [ ] **2.6 Recette sécurité du module VBG** - parcours de bout en bout validé par la
  spécialiste VBG de l'UGP (données fictives) avant toute mise en service : cloisonnement,
  déchiffrement limité aux 2 rôles, absence de fuite dans logs/exports, purge 72 h.
  → *ER-02, ER-07 - jalon obligatoire du Lot B*

---

## 3. 🟠 P1 - Formulaires : une seule source, médias, langues

- [~] **3.1 Monosource** - **avancé**. `mgp-mobile/forms/xlsform/` est la source ; le
  pipeline `build_forms.py` **compile et valide** désormais F01 (ODK Validate OK) -
  **bug Windows corrigé** (sortie console UTF-8, le script plantait sur le caractère ►).
  Reste : **publier** les XForm générés vers `mgp-office/forms/` (étape de build) et
  archiver/supprimer les `.xlsx` racine obsolètes.
  → *ENF-05*
- [x] **3.2 Verrouiller la fin de parcours F01 type 8** - ✅ **FAIT + validé ODK**. Les
  sections postérieures au type (grp_plaignant/plainte(reste)/canal/pièces jointes/
  confirmation) portent `relevant = ${type} != 'type8'` : pour un type 8, seule la note de
  redirection vers F03 s'affiche, aucun champ ultérieur n'est saisissable.
  → *EF-04, ER-02*
- [x] **3.3 Identification par ID de site (décision révisée) + validé ODK** — ✅ **FAIT**.
  **Décision** : le formulaire ne transporte **plus aucune géographie** (les référentiels géo
  évoluent — ne rien figer côté XForm). L'agent saisit **uniquement l'ID du site PACIPA** ;
  le back-office **déduit tout** de cet identifiant. F01 : cascade region/departement/commune/
  localite **supprimée**, remplacée par un champ `site_id` (+ bascule « hors-site » avec lieu
  libre). Côté office, `SubmissionMapper::mapF01` **réaligné** : lecture des champs robuste au
  regroupement (`xpath //name`), résolution du site par code/id, mapping `type1→type_1`, code
  `id_plainte` = `site-année-uuid`. XForm réduit de 1,7 Mo à **26 Ko**. Couvert par
  `OdkF01MappingTest` (2 tests). Le générateur géo `build_geo_choices.py` (approche cascade)
  est **abandonné** et retiré.
  → *EF-01, EF-10*
- [x] **3.4 Compléments F01 vs spec** - ✅ **FAIT**. Groupe répétable témoins (déjà présent),
  GPS auto (`geopoint placement-map`), signature du chargé (`image` + `appearance=signature`
  - l'ancien type `draw` était invalide et empêchait toute compilation), résumé visuel
  affichant les valeurs saisies (`${type}`, `${nom_prenom}`, `${age}`, `${date_faits}`,
  `${site_id}`).
  → *EF-01, EF-09*
- [ ] **3.5 Langues des formulaires** - les XLSForms restent FR/HA/ZR alors que le serveur
  couvre déjà 6 langues : ajouter `label::Tamasheq/Kanouri/Fulfulde` au moins sur F01 et
  F03.
  → *ENF-07*
- [ ] **3.6 Médias didactiques** - `forms/media/icons/` (ic_type1…8, canaux) et
  `forms/media/audio/{fr,ha,zr}/` toujours absents ; condition d'adoption des comités
  faiblement alphabétisés relevée au pilote.
  → *ENF-03*

---

## 4. 🟠 P1 - Infrastructure, exploitation, référentiels

- [x] **4.1 `docker/` et `.github/`** - ✅ **FAIT**. CI GitHub Actions
  `.github/workflows/ci.yml` : job **tests** (PHP 8.4, extensions dont `sodium`, `composer
  install`, clé VBG générée, `php artisan test`) qui **protège désormais les 77 tests**, +
  job **lint** Pint (informatif). Environnement Docker de dev `docker/` : `docker-compose.yml`
  (PostgreSQL 16, Redis 7, app PHP-FPM 8.4, Nginx), `Dockerfile`, `nginx.conf`, `README.md`.
  ODK Central hors périmètre (stack dédiée, cf. `claude-deploy.md`).
  → *ENF-05*
- [ ] **4.2 Référentiel géographique : trancher la contradiction** - `claude.md` règle n° 8
  impose `elbrahms/niger-location`, mais le package est **absent de composer.json** et le
  domaine `Geography/` réimplémente les modèles avec ses propres seeders
  (`localites.sql`). Soit requérir le package et migrer, soit amender la règle n° 8 -
  puis une seule source géographique pour l'office **et** les choices des formulaires
  (cf. 3.3).
  → *ENF-05, EF-01*
- [ ] **4.3 Durcissement** - TLS 1.3 de bout en bout, LUKS serveur, rotation des clés
  (`SecuritySettings`), certificat client mobile (avec 1.3).
  → *ER-02, ENF-02*
- [ ] **4.4 Test de charge 8 régions** - avant Phase 4 (exigence « à confirmer » de la
  matrice).
  → *ENF-06*
- [ ] **4.5 Énergie terrain** - kits solaires zones instables, Starlink URC isolées (plan
  d'équipement budgété).
  → *ENF-01, ENF-03*

---

## 5. 🟢 P2 - Hygiène du dépôt

- [ ] **5.1** Remplacer le `update.md` v1 de la racine par la présente v2 et cocher au fil
  de l'eau (miroir de la colonne Statut de la matrice Excel - Annexe 3).
- [x] **5.2** ✅ **FAIT (partiel)** - `claude.md` porte désormais une section « Décisions
  d'architecture » consignant les arbitrages SMS (1.4, option A), 2FA (1.9), API mobile (1.3),
  géographie (4.2, non résolu), le domaine `Company/` et la CI/Docker (4.1). Reste à étoffer
  la documentation du domaine `Company/` dans `claude-office.md`.
- [ ] **5.3** Générer `graphify-out/` ou retirer la section graphify de `claude.md`.
- [~] **5.4** **Git Flow + Conventional Commits adoptés** (branche `feat/mgp-conformite-p0-p1`
  + commit conventionnel). Reste le nettoyage de `template/` (pages démo).
- [ ] **5.5** Verser dans `docs/` : manuel MGP PDF, BPMN (annexe 5) et matrice des 28
  exigences.

---

## 6. Tableau de conformité récapitulatif (commit `cae5a5d`)

| Exigence | Intitulé court | État | Reste à faire |
|---|---|---|---|
| EF-01 | Enregistrement code/type/canal/localisation/PJ | 🟡 API mobile OK (1.3) | 3.3, 3.4 |
| EF-02 | Workflow 3 niveaux + PV | ✅ | tests (1.7) |
| EF-03 | Alertes + escalade automatique | ✅ | `SlaCalculationTest`/`ComplaintEscalationTest` (1.7) |
| EF-04 | Circuit sensible cloisonné | 🟡 HMAC+neutralité (2.1 code) + personnes confiance (2.5) | passerelle réelle 2.1, 2.2-2.4, 2.6, 3.2 |
| EF-05 | Indicateurs automatiques | ✅ KPI + états PDF/Excel + envoi auto (1.8) | tests ✅ |
| EF-06 | Accusé de réception immédiat | 🟡 accusé PDF+SMS + webhook SMS (1.2, 1.4) | passerelle réelle (2.1) |
| EF-07 | Sessions de comité / médiation | ✅ (Mediation + vues) | vérifier absence de médiation Type 8 en recette (2.6) |
| EF-08 | Enquête de satisfaction | ✅ entité + indicateurs (1.5) | tests ✅ |
| EF-09 | Pièces jointes/preuves | 🟡 medialibrary + fiche papier jointe (1.6) | 3.4 |
| EF-10 | Tous canaux de dépôt | 🟡 webhook SMS (1.4) | 2.1, 2.2 |
| EF-11 | Saisie différée papier | ✅ écran + SLA sur date réception (1.6) | tests ✅ |
| EF-12 | Consentement bloquant | 🟡 ConsentService OK | recette 2.6 ; ligne verte 2.4 |
| EF-13 | Référencement 72 h outillé | 🟡 Referral + enums OK | centres holistiques seedés + minuterie en recette |
| EF-14 | Tableau de bord multi-axes | 🟡 états PDF/Excel + envoi auto (1.8) | axes additionnels (Annexe 5/6) |
| ENF-01 | Hors-ligne + sync | ✅ ODK + API mobile (1.3) + webhook SMS (1.4) | énergie terrain 4.5 (infra) |
| ENF-02 | Disponibilité + sauvegardes | 🟡 backup 90 j (1.10) + 2FA imposé (1.9) | 4.3, test restauration |
| ENF-03 | Utilisabilité faible littératie | 🟡 PageGuides OK | 3.6 |
| ENF-04 | Faible débit | 🟡 | mesure au pilote ; contrat SMS (1.4) |
| ENF-05 | Maintenabilité locale | 🟡 CI + Docker dev (4.1) | 3.1, 4.2 |
| ENF-06 | Montée en charge 8 régions | ❌ | 4.4 |
| ENF-07 | Multilinguisme | 🟡 serveur 6 langues | 3.5, 2.3 |
| ER-01 | Notifications UGP/BM 24 h-48 h | ✅ envois réels + tracés (1.1) | tests (1.7) ✅ |
| ER-02 | Confidentialité VBG | 🟡 schéma+libsodium+RBAC+2FA imposé (1.9) | 2.6, 4.3 |
| ER-03 | Journalisation intangible | ✅ | vérifier filtrage champs VBG en recette (2.6) |
| ER-04 | Archivage opposable | 🟡 rapports PDF/Excel horodatés + tracés (1.8) | rapport commission chiffré (P2) |
| ER-05 | Circuit travailleurs | ✅ | tests (1.7) |
| ER-06 | Traçabilité recours PAP | ✅ | - |
| ER-07 | Minimisation + agrégation | 🟡 AnonymizationService OK | recette 2.6 |

**Bilan** : 11 ✅ · 16 🟡 · 1 ❌ (après Lots A + B ; contre 7 ✅ · 18 🟡 · 3 ❌ après Lot A,
6 ✅ · 18 🟡 · 4 ❌ avant, et 0 ✅ · 12 🟡 · 16 ❌ en v1). Le seul ❌ restant est **ENF-06**
(test de charge 8 régions, 4.4) - une recette d'infra, plus aucun chantier de code.

> **Lot A (P0 « prouver ») réalisé** - 1.1 (notifications BM), 1.2 (accusé PDF+SMS),
> 1.7 (9 tests) et 1.10 (sauvegardes planifiées 90 j). Deux bugs réels corrigés (voir 1.2, 1.7).
>
> **Lot B (P0 « compléter ») réalisé en entier** - dépendances Composer installées
> (Sanctum, Fortify, webhook-client). Faits : **1.9** (2FA imposé + défi TOTP réel), **1.3**
> (API mobile Sanctum + distribution formulaires F03 cloisonné), **1.5** (enquête de
> satisfaction), **1.4** (webhook SMS signé + idempotent, option A), **1.6** (saisie différée
> papier, SLA sur date de réception), **1.8** (états PDF/Excel + envoi périodique auto).
> Suite de tests : **71 passés**.
>
> **Lot C (P1 VBG opérationnel) - partie code faite** : **2.5** (personnes de confiance :
> registre formation 6 mois + contrôle automatique de conflit d'intérêts) livré et testé
> (6 tests) ; **2.1** confirmé côté code (HMAC + réponses neutres déjà en place). Le reste
> de Lot C (2.2 conventions opérateurs, 2.3 langues gateway, 2.4 ligne verte, 2.6 recette
> sécurité) relève de l'intégration passerelle et de la recette, pas du code. Suite de
> tests : **77 passés**.
>
> **Tous les P0 de code (Lots A + B) sont livrés et testés.** Reste, hors code : ENF-06
> (test de charge, 4.4), le certificat client mTLS (4.3), et les gabarits P2 (Annexe 5/6,
> rapport commission chiffré). Prochaine étape logique : reste de **Lot C** (ops) puis
> **Lot D** (formulaires + infra, sections 3 et 4).
>
> **Lot D - démarré** : **4.1** (CI GitHub Actions + Docker dev) livré - la CI exécute et
> **protège les 77 tests** ; **5.2** (arbitrages consignés dans `claude.md`) et **5.4**
> (Git Flow + Conventional Commits) faits. Reste, côté code, essentiellement du `mgp-mobile`
> (formulaires XLSForm 3.2→3.6) et l'arbitrage géographique 4.2 ; le reste de la section 4
> est infra/recette.

---

## 7. Ordre de réalisation recommandé

1. **Lot A (P0 « prouver »)** : 1.7 (tests) + 1.1, 1.2, 1.10 - les exigences déjà codées
   deviennent démontrées et les notifications/accusés deviennent réels.
2. **Lot B (P0 « compléter »)** : 1.3 → 1.6, 1.8, 1.9 - API mobile, SMS, satisfaction,
   saisie différée, rapports, 2FA.
3. **Lot C (P1 VBG opérationnel)** : 2.1 → 2.6 - passerelles, conventions opérateurs,
   recette sécurité (jalon spécialiste VBG obligatoire).
4. **Lot D (P1 formulaires + infra)** : 3.1 → 3.6, 4.1 → 4.5.
5. **Lot E (P2)** : section 5.

> **Traçabilité mémoire** : chaque case cochée met à jour la colonne « Statut » de la
> matrice Excel (Annexe 3) et, pour les ✅ nouveaux, la section 3.7.3 du mémoire. Le
> tableau 6 (évaluation pilote) se nourrit des Lots A-B : sans accusé réel (EF-06) ni
> satisfaction (EF-08), deux lignes de la grille resteront vides.