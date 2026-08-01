# UPDATE.md — Mises à jour requises pour la conformité du système PACIPA-MGP

> **Objet** : écarts constatés entre l'état actuel du dépôt `Elbrahms05/mgp` (commit `e522ca4`)
> et le référentiel des 28 exigences (Annexe 3 du mémoire — EF-01…14, ENF-01…07, ER-01…07),
> avec les mises à jour à réaliser pour atteindre la conformité.
>
> **Légende priorités** : 🔴 P0 = bloquant conformité BM / MVP · 🟠 P1 = requis avant généralisation · 🟢 P2 = amélioration
>
> **Statuts** : ✅ Conforme · 🟡 Partiel · ❌ Absent · 📄 Spécifié seulement (claude-*.md à jour, code absent)

---

## 0. Synthèse de l'état du dépôt

| Composant | État constaté | Conformité |
|---|---|---|
| Spécifications `claude*.md` (6 fichiers + complément) | Complètes, alignées sur le manuel MGP mars 2025 | ✅ |
| `mgp-mobile/` — fork ODK Collect + module `pacipa/` | Avancé : SMS chiffré, sync, FormIdGenerator, palette PACIPA | 🟡 |
| `mgp-mobile/forms/` — F01–F07 (CSV → XLSForm → XForm + `build_forms.py`) | Présents, 3 langues (FR/HA/ZR) | 🟡 |
| **`mgp-office/` — back-office Laravel** | **Répertoire vide : aucun code** | ❌ |
| Canaux directs VBG (USSD, SMS 3xxx, Ligne verte, WhatsApp, personnes de confiance) | Spécifiés (`claude-vbg.md`), rien d'implémenté | 📄 |
| `docker/`, `.github/` (CI/CD), tests | Annoncés dans `claude-deploy.md`, absents du dépôt | ❌ |
| `template/` | Gabarit HTML admin générique, non intégré en Blade | 🟡 |
| Médias didactiques formulaires (`forms/media/icons`, `audio/`) | Absents | ❌ |
| `graphify-out/` (référencé par `claude.md`) | Absent | ❌ |
| Formulaires dupliqués à la racine (`pacipa_f01*.xlsx`, 26 lignes, monolingues) | Divergent des versions `forms/xlsform/` | 🟡 |

**Conclusion** : la chaîne de collecte terrain est en bonne voie ; la conformité globale est
bloquée par l'absence totale du back-office, qui porte à lui seul **16 des 28 exigences**.

---

## 1. 🔴 P0 — Back-office Laravel `mgp-office/` (chantier principal)

Le répertoire est vide. Toutes les mises à jour ci-dessous consistent à implémenter
`claude-office.md` §par§ ; chaque item cite les exigences qu'il débloque.

- [ ] **1.1 Socle applicatif** — Laravel 12, PostgreSQL + Redis, Breeze/Fortify + **2FA
  obligatoire** pour `super_admin`, `coordonnateur_ugp`, `specialiste_vbg` ; Sanctum pour
  l'API mobile. Intégrer `template/` en layouts Blade + Alpine.js/Materialize.
  → *ENF-02 (socle), ER-03 (prérequis audit)*
- [ ] **1.2 Domaines métier** — arborescence `app/Domains/` complète (Geography via
  `elbrahms/niger-location`, Site, Committee, Complaint, Vbg, Worker, Mediation, Form,
  OdkBridge, Communication, Evaluation, Documentation, Statistic).
  → *EF-01, EF-02, ER-05, ER-06*
- [ ] **1.3 Workflow des plaintes** — statuts `NOUVELLE → ENREGISTRÉE → EXAMEN → RECEVABLE/
  REJETÉE → MÉDIATION → RÉSOLUE/ESCALADÉE → CLÔTURÉE` ; chaque transition horodatée
  (utilisateur + commentaire) ; PV générés (4 ex. CBGP, 3 ex. CCGP).
  → *EF-02, ER-04, ER-06*
- [ ] **1.4 Moteur SLA + escalade automatique** — `SlaSettings` paramétrable (3+7j CBGP,
  3+4j CCGP, 7+3j CNGP, 24 h notification VBG, 72 h référencement, 48 h PEES, 30 j
  commission) ; job planifié : alerte à l'approche du terme, **escalade automatique** au
  niveau supérieur en cas de dépassement sans action. Couvrir par `SlaCalculationTest` et
  `ComplaintEscalationTest`.
  → *EF-03 — cœur de l'hypothèse H1 du mémoire*
- [ ] **1.5 Accusé de réception automatique** — génération immédiate à l'enregistrement
  (PDF DomPDF + **SMS auto** via `SmsSettings`), y compris pour la saisie différée.
  → *EF-06*
- [ ] **1.6 Module VBG cloisonné** — schéma PostgreSQL séparé `vbg` ; chiffrement champ
  par champ libsodium (XSalsa20-Poly1305) via `VbgEncryptionService` ; clé hors BDD
  (Vault/HSM), déchiffrement réservé à `specialiste_vbg` + `coordonnateur_ugp` ;
  Annexe 9 **Partie 1 et Partie 2 stockées séparément** ; consentement horodaté et
  révocable (`ConsentService`) bloquant la Partie 2 ; filtrage des champs sensibles dans
  les logs ; purge cryptographique après clôture + délai légal.
  → *EF-04, EF-12, ER-02, ER-07 — cœur de l'hypothèse H2*
- [ ] **1.7 Notifications Banque mondiale** — `WorldBankNotificationService` : 24 h EAS/HS
  et décès (ESIRT), 48 h incidents E&S (PEES), rapport commission ≤ 30 j ; destinataires
  configurés en Paramètres (« points focaux Banque mondiale » — cf. complément métier).
  Couvrir par `NotificationDeadlineTest`.
  → *ER-01*
- [ ] **1.8 Référencement VBG outillé** — minuterie 72 h, prestataires liés par type de
  service (médical, psychosocial, juridique, sécurité), **4 centres holistiques
  pré-configurés** (Maradi, Tahoua, Tillabéri, Zinder). `VbgReferralFlowTest`.
  → *EF-13*
- [ ] **1.9 RBAC 13+ rôles** — `spatie/laravel-permission`, matrice de `claude-office.md`
  (dont `personne_confiance_vbg`, `entreprise_travaux`, `auditeur`) ; double cloisonnement
  territorial (chaque comité ne voit que son ressort) et thématique (module VBG). Le rôle
  membre de comité = champ select conforme aux rôles du manuel. `VbgAccessControlTest`.
  → *ER-02, prérequis de toutes les EF*
- [ ] **1.10 Audit trail** — `spatie/laravel-activitylog` sur tout CRUD, login, export,
  consultation ; journal intangible, consultable `super_admin`/`coordonnateur_ugp`.
  → *ER-03*
- [ ] **1.11 Sessions de comité & médiation** — convocations, présences, décisions,
  PV de conciliation (Annexe 5/F04). **Verrou métier : aucun bouton « Médiation » sur le
  circuit Type 8 (EAS/HS) — jamais de règlement à l'amiable.**
  → *EF-07, EF-04*
- [ ] **1.12 Enquête de satisfaction à la clôture** — questionnaire lié à la plainte
  (processus + résultat), alimentant les indicateurs.
  → *EF-08*
- [ ] **1.13 Saisie différée du canal papier** — écran de reprise des fiches F01 papier
  avec numérisation jointe (`spatie/laravel-medialibrary`).
  → *EF-11, EF-09*
- [ ] **1.14 Plaintes des travailleurs** — deux circuits distincts (travailleurs directs
  UGP : 7 j puis CNGP 2 semaines ; contractuels : chef du personnel 7 j ouvrables puis
  spécialiste MGP/RH 7 j) ; rôle `entreprise_travaux` limité à ce périmètre.
  → *ER-05*
- [ ] **1.15 Tableau de bord & indicateurs (Tableau 7)** — `KpiCalculatorService` :
  compteurs temps réel, carte Niger (heatmap régions + sites par risque), répartition
  8 types / canaux / régions, délais moyens par niveau, liste rouge SLA dépassés ;
  **indicateurs VBG exclusivement agrégés et anonymisés** (`AnonymizationService`),
  visibles `specialiste_vbg` + `coordonnateur_ugp` uniquement.
  → *EF-05, EF-14, ER-07 — cœur de l'hypothèse H3*
- [ ] **1.16 Rapports & états** — DomPDF + Maatwebsite Excel : tableau de bord mensuel,
  évaluation trimestrielle comités (F07), rapport MGP semestriel/annuel, rapport VBG
  agrégé trimestriel, fiche de suivi par plainte (Annexe 6/F05), PV conciliation, rapport
  de commission **chiffré** ; **envoi automatique des rapports périodiques** aux personnes
  habilitées définies en Paramètres (cf. complément métier).
  → *EF-05, EF-14, ER-04*
- [ ] **1.17 Module Sites (§4.12)** — table `sites` (code `PACIPA-REGION-NNN`, type,
  statut, `risk_level`), `SiteRiskAnalysisService` quotidien (VBG confirmé → critical ;
  >20 % SLA dépassés → +1 ; accident/décès → critical) avec notification auto ;
  `site_id` nullable uniquement Type 1 et VBG anonymes.
  → *EF-01, EF-14*
- [ ] **1.18 Pont ODK** — `OdkApiClient` + `OdkSyncService` + `SubmissionMapper`
  (`spatie/laravel-data`) : intégration des soumissions ODK Central → `Complaint` /
  `VbgReport`, exposition des formulaires à l'app mobile via API.
  → *EF-01, ENF-01*
- [ ] **1.19 Aide contextuelle** — `page_guides` + `PageGuidesSeeder` : **chaque page**
  avec objectif, référence MGP (§/tableau/annexe), guide pas-à-pas, alertes.
  `PageGuideDisplayTest`.
  → *ENF-03*
- [ ] **1.20 Paramétrage générique** — classes Spatie Settings (Project/Odk/Sms/Sla/
  Security/Notification) ; l'application doit être **entièrement paramétrable pour être
  réutilisée sur d'autres projets Banque mondiale** (cf. complément métier ; perspective
  « socle réutilisable » du mémoire).
  → *ENF-05, ENF-06*
- [ ] **1.21 Gestion des personnes ressources & prestataires** — CRUD personnes
  ressources ; structures/prestataires de référencement liés par type de prestation.
  → *EF-13*

---

## 2. 🔴 P0 — Canal SMS de soumission (mobile → back-office)

Le module mobile `pacipa/sms/` (SmsSubmissionService, SmsPayloadBuilder, SmsEncryption)
existe ; **le côté serveur est absent** (mgp-office vide).

- [ ] **2.1 Webhook SMS sécurisé** — endpoint `spatie/laravel-webhook-client` recevant les
  soumissions SMS **sans transiter par ODK Central** (passerelle SMS → webhook mgp-office),
  vérification de signature, idempotence, remontée en `Complaint`/`VbgReport`.
  → *ENF-01, EF-01*
- [ ] **2.2 Contrat de payload ≤ 160 caractères** — figer le format entre
  `SmsPayloadBuilder` (mobile) et le parseur serveur : segmentation multi-SMS, numéro de
  séquence, checksum, chiffrement compatible `SmsEncryption` ; documenter dans
  `claude-mobile.md`.
  → *ENF-01, ENF-04*
- [ ] **2.3 Option retenue à instruire : fork `medic/medic-collect`** — évaluer la reprise
  du transport SMS de medic-collect (ODK Collect modifié par Medic) plutôt que le module
  maison : envoi des formulaires par SMS vers numéro court ou normal, relais vers le
  webhook backend ; téléphones + SIM fournis aux acteurs. Trancher, puis aligner
  `claude-mobile.md` et le code sur l'option choisie (ne pas maintenir les deux).
  → *ENF-01, ENF-05*
- [ ] **2.4 Accusé SMS automatique** en réponse à toute soumission SMS (texte neutre).
  → *EF-06*

---

## 3. 🔴 P0 — Canaux directs VBG (spécifiés, non implémentés)

`claude-vbg.md` décrit 5 canaux aboutissant directement au Spécialiste VBG UGP ; aucun
contrôleur/webhook n'existe. **Rappel transverse : les messages de réponse ne contiennent
JAMAIS « VBG » ou « violence »** ; anonymat possible partout ; le code `VBG-xxxx` est le
seul identifiant circulant.

- [ ] **3.1 Ingestion unifiée** — `DirectReportIngestionService` + colonnes canaux de
  `vbg_reports` (`channel`, `is_anonymous`, `caller_phone_encrypted`, `ussd_session_id`,
  `raw_sms_content`, `voice_memo_path`, `trusted_person_id`) ; chiffrement **à la
  réception du webhook**, avant tout stockage ; purge des logs webhooks à 72 h.
  → *EF-04, ER-02, ER-07*
- [ ] **3.2 Canal USSD `*xxx#`** — `UssdWebhookController` + menu 5 langues (avec
  « 6. Je préfère ne pas dire ») ; conventions opérateurs (Orange/Airtel/Moov, appel
  inversé gratuit) à négocier — action UGP, à tracer.
  → *EF-04, ENF-07*
- [ ] **3.3 Canal SMS 3xxx** — `SmsWebhookController` : format libre et structuré,
  mot-clé ANONYME, chiffrement immédiat du numéro, réponse neutre avec code VBG-xxxx.
  → *EF-04, EF-06*
- [ ] **3.4 Ligne verte** — saisie Annexe 9 Partie 1 en direct par l'opératrice ONG dans
  le back-office ; appel jamais enregistré sauf consentement ; bascule 24h/24.
  → *EF-04, EF-12*
- [ ] **3.5 WhatsApp Bot** — même arbre que l'USSD ; vocaux transcrits par un opérateur
  humain (jamais par IA) ; photos avec consentement ; instruction de suppression de la
  conversation.
  → *EF-04, EF-09*
- [ ] **3.6 Personnes de confiance hors comité** — rôle `personne_confiance_vbg` limité à
  la Partie 1 + transmission directe UGP ; **contrôle automatique de conflit d'intérêts à
  la saisie** ; registre de formation/recyclage 6 mois.
  → *EF-04, EF-12, ER-02*

---

## 4. 🟠 P1 — Formulaires F01–F07 (écarts vs `claude-mobile.md` et le manuel)

- [ ] **4.1 Supprimer la double source** — les `pacipa_f01.xlsx` / `pacipa_f01_depot.xlsx`
  à la racine (26 questions, **monolingues**, `localite_id`/`site_id` en texte libre)
  divergent des versions `mgp-mobile/forms/xlsform/`. Conserver une seule source (les CSV
  + `build_forms.py`), déplacer les fichiers racine vers `docs/archive/` ou les supprimer.
  → *ENF-05*
- [ ] **4.2 Cascade géographique réelle dans F01** — remplacer les champs texte libre par
  des `select_one` en cascade Région → Département → Commune → Localité → Site (choices
  générés depuis `elbrahms/niger-location` + référentiel sites), avec options « Plainte
  hors site » (Type 1) et « Site non listé ».
  → *EF-01, EF-10*
- [ ] **4.3 Numérotation conforme** — `FormIdGenerator` : code plainte
  `REGION-DEPARTEMENT-COMMUNE-LOCALITE-N°ORDRE` calculé dans F01 (champ `calculation`),
  identique côté back-office.
  → *EF-01*
- [ ] **4.4 Retirer tout contenu sensible de F01** — F01 détecte la sensibilité
  (`sensibilite`) puis **arrête le parcours et oriente vers le circuit F03/point focal** ;
  aucun détail VBG ne doit être saisi dans F01 (actuellement `note_vbg` + consentement y
  figurent). La description des faits n'existe que dans F03 Partie 2, sous consentement.
  → *EF-04, ER-02, ER-07*
- [ ] **4.5 Compléter F01 vs spec** — groupe répétable témoins (nom + contact), GPS auto,
  signature numérique du chargé, résumé visuel avant soumission.
  → *EF-01, EF-09*
- [ ] **4.6 Langues** — les XLSForms couvrent FR/HA/ZR ; ajouter les colonnes
  `label::Tamasheq`, `label::Kanouri`, `label::Fulfulde` (et `values-tq/kr/ff` côté app)
  au moins pour F01 et F03, conformément à `claude-office.md`/`claude-mobile.md`.
  → *ENF-07 (exigence actuellement « partiellement couverte » dans la matrice)*
- [ ] **4.7 Médias didactiques** — créer `forms/media/icons/` (ic_type1…8, canaux) et
  `forms/media/audio/{fr,ha,zr}/` (aides par section), référencés dans les XLSForms ;
  publics faiblement alphabétisés = condition d'adoption relevée au pilote.
  → *ENF-03*
- [ ] **4.8 Validations** — format téléphone `+227XXXXXXXX`, dates cohérentes, champs
  obligatoires ; un écran = une question (`field-list` à revoir écran par écran).
  → *ENF-03*

---

## 5. 🟠 P1 — Infrastructure, sécurité, exploitation

- [ ] **5.1 `docker/`** — Docker Compose dev (PostgreSQL, Redis, ODK Central) annoncé par
  `claude-deploy.md`, absent. → *ENF-05*
- [ ] **5.2 CI/CD `.github/`** — lint + tests + déploiement staging. → *ENF-05*
- [ ] **5.3 Sauvegardes** — `spatie/laravel-backup` chiffré quotidien, rétention 90 j,
  **test de restauration mensuel documenté**. `SpatieBackupTest`. → *ENF-02*
- [ ] **5.4 Durcissement** — TLS 1.3, AES-256 au repos + LUKS, certificat client pour les
  appareils terrain, rotation des clés (`SecuritySettings`). → *ER-02, ENF-02*
- [ ] **5.5 Suite de tests critiques** — implémenter les 9 tests listés dans
  `claude-deploy.md` (aucun n'existe) ; en faire le critère de sortie de chaque lot P0.
  → *transverse — c'est aussi la preuve de couverture exigence→composant du mémoire*
- [ ] **5.6 Dimensionnement 8 régions** — test de charge sur volume cible avant Phase 4.
  → *ENF-06 (« à confirmer au pilote » dans la matrice)*
- [ ] **5.7 Énergie terrain** — kits solaires pour zones instables ; Starlink URC isolées
  (plan d'équipement à budgéter, difficulté remontée au pilote). → *ENF-01, ENF-03*

---

## 6. 🟢 P2 — Hygiène du dépôt et documentation

- [ ] **6.1 Générer `graphify-out/`** (référencé par `claude.md`, absent) ou retirer la
  section graphify.
- [ ] **6.2 Historique Git** — un seul commit « first commit » : adopter le Git Flow +
  Conventional Commits FR annoncés (`feat(mgp-office): …`).
- [ ] **6.3 Nettoyer `template/`** après intégration Blade (pages démo inutiles).
- [ ] **6.4 Mettre à jour `claude.md`** à chaque arbitrage (règle n° 12 du fichier) —
  notamment l'issue 2.3 (medic-collect vs module SMS maison).
- [ ] **6.5 `docs/`** — ajouter le manuel MGP PDF, les BPMN (annexe 5) et la matrice des
  28 exigences pour que le dépôt soit auto-porteur.

---

## 7. Tableau de conformité récapitulatif (état actuel du dépôt)

| Exigence | Intitulé court | État | Débloquée par |
|---|---|---|---|
| EF-01 | Enregistrement code unique/type/canal/localisation/PJ | 🟡 formulaires OK, intégration serveur absente | 1.2, 1.18, 4.2, 4.3 |
| EF-02 | Workflow 3 niveaux + PV | ❌ | 1.3 |
| EF-03 | Alertes + escalade automatique | ❌ | 1.4 |
| EF-04 | Circuit sensible cloisonné | 🟡 F03 séparé côté mobile ; serveur + canaux absents | 1.6, 3.x, 4.4 |
| EF-05 | Indicateurs automatiques | ❌ | 1.15, 1.16 |
| EF-06 | Accusé de réception immédiat | 🟡 note de fin F01 ; génération réelle absente | 1.5, 2.4 |
| EF-07 | Sessions de comité | ❌ | 1.11 |
| EF-08 | Enquête de satisfaction | ❌ | 1.12 |
| EF-09 | Pièces jointes/preuves | 🟡 image F01 ; medialibrary absente | 1.13, 4.5 |
| EF-10 | Tous canaux de dépôt | 🟡 champ canal présent ; canaux réels non branchés | 2.x, 3.x |
| EF-11 | Saisie différée papier | ❌ | 1.13 |
| EF-12 | Consentement bloquant | 🟡 présent F01/F03 ; ConsentService absent, à retirer de F01 | 1.6, 4.4 |
| EF-13 | Référencement 72 h outillé | ❌ | 1.8, 1.21 |
| EF-14 | Tableau de bord multi-axes | ❌ | 1.15, 1.16 |
| ENF-01 | Hors-ligne + sync | 🟡 fork ODK OK ; pont serveur + SMS serveur absents | 1.18, 2.x |
| ENF-02 | Disponibilité + sauvegardes | ❌ | 5.3, 5.4 |
| ENF-03 | Utilisabilité faible littératie | 🟡 principes UX spécifiés ; médias/aide absents | 4.7, 4.8, 1.19 |
| ENF-04 | Faible débit | 🟡 par conception ; à mesurer | 2.2 |
| ENF-05 | Maintenabilité locale | 🟡 stack OK ; docker/CI/tests/monosource absents | 5.1, 5.2, 4.1 |
| ENF-06 | Montée en charge 8 régions | ❌ (à confirmer) | 5.6 |
| ENF-07 | Multilinguisme | 🟡 FR/HA/ZR ; TQ/KR/FF manquants | 4.6 |
| ER-01 | Notification UGP 24 h / BM | ❌ | 1.7 |
| ER-02 | Confidentialité VBG (chiffrement, cloisonnement) | 📄 spécifié précisément ; rien d'exécutable | 1.6, 1.9, 3.1, 5.4 |
| ER-03 | Journalisation intangible | ❌ | 1.10 |
| ER-04 | Archivage opposable | ❌ | 1.3, 1.16 |
| ER-05 | Circuit travailleurs | ❌ | 1.14 |
| ER-06 | Traçabilité recours PAP | ❌ | 1.2, 1.3 |
| ER-07 | Minimisation + restitution agrégée | 🟡 principes dans F03 ; AnonymizationService absent | 1.6, 1.15, 3.1 |

---

## 8. Ordre de réalisation recommandé (aligné sur le plan de déploiement)

1. **Lot A (P0, MVP pilote)** : 1.1 → 1.5, 1.9, 1.10, 1.18, 4.1 → 4.4 — une plainte non
   sensible traverse le workflow complet avec SLA, accusé et audit.
2. **Lot B (P0, VBG)** : 1.6, 1.7, 1.8, 3.1, 3.3, 4.4 — le circuit sensible est cloisonné,
   chiffré et notifié dans les délais (24 h / 72 h). *Validation obligatoire par la
   spécialiste VBG de l'UGP avant mise en service.*
3. **Lot C (P0/P1)** : 1.11 → 1.17, 1.19 → 1.21, 2.x, 5.3, 5.5 — pilotage, rapports,
   travailleurs, SMS, sauvegardes, tests.
4. **Lot D (P1, avant Phase 4)** : 3.2, 3.4 → 3.6, 4.5 → 4.8, 5.1, 5.2, 5.4, 5.6, 5.7.
5. **Lot E (P2)** : section 6.

> **Traçabilité mémoire** : chaque case cochée met à jour la colonne « Statut » de la
> matrice Excel des 28 exigences (Annexe 3) — c'est elle qui fait foi pour la
> démonstration de conformité au chapitre 3.7.3.
