# PACIPA-MGP - Fichier de référence principal

## Identité du projet

| Champ | Valeur |
|---|---|
| **Nom** | PACIPA-MGP (Mécanisme de Gestion des Plaintes Sensible aux VBG/EAS/HS) |
| **Projet parent** | PACIPA - P179272 |
| **Bailleur** | Banque mondiale / IDA |
| **Tutelle** | Ministère de l'Agriculture et de l'Élevage - République du Niger (CNSP) |
| **Budget MGP** | 175 000 000 FCFA (dont 25 000 000 FCFA digitalisation) |
| **Zones** | Agadez, Diffa, Dosso, Maradi, Niamey, Tahoua, Tillabéri, Zinder |
| **Durée** | 2025 - 2027+ |

## Index des fichiers de spécification

| Fichier | Contenu |
|---|---|
| `claude.md` | CE FICHIER - identité projet, index, rappels critiques |
| `claude-metier.md` | Règles métier MGP : types plaintes, niveaux, délais SLA, circuit VBG |
| `claude-office.md` | Back-office Laravel : stack, RBAC, domaines, modules |
| `claude-mobile.md` | App mobile Android + formulaires XLSForm (F01–F07) |
| `claude-vbg.md` | Module VBG : chiffrement, canaux directs, sécurité |
| `claude-deploy.md` | Infrastructure, déploiement, formation, plan progressif |

## Architecture technique (résumé)

```
Terrain (Android)     Serveur central        Back-office
┌─────────────┐       ┌──────────────┐       ┌──────────────────┐
│ ODK   │──────▶│ ODK Central  │──────▶│ Dashboard Laravel│
│ Collect     │       │ (CouchDB/PG) │       │ (suivi, stats,   │
│ (XForm MGP) │       │              │       │  reporting, RBAC)│
└─────────────┘       └──────────────┘       └──────────────────┘
     Hors-ligne            TLS 1.3                 2FA obligatoire
     SMS fallback          E2EE VBG                Spatie packages
```

## Rappels critiques (à lire avant chaque sprint)

1. **Confidentialité VBG absolue** : Aucune donnée identifiant une survivante ne doit apparaître en clair dans les logs, rapports, exports ou écrans non habilités. Le code plainte VBG est le SEUL identifiant circulant hors du module VBG.

2. **Jamais de médiation pour EAS/HS** : Le workflow VBG ne propose JAMAIS de bouton "Médiation". Circuit : réception → référencement services → notification UGP → commission d'enquête → notification Banque mondiale.

3. **Offline-first** : L'app mobile fonctionne 100% hors-ligne. La synchronisation est un bonus, pas un prérequis.

4. **Délais SLA non négociables** : 24h notification VBG, 72h référencement, 3+7j CBGP, 3+4j CCGP, 7+3j CNGP, 30j commission. Engagements contractuels Banque mondiale.

5. **Approche centrée survivante** : Consentement éclairé, sécurité, confidentialité, respect des choix/droits/dignité, non-discrimination, intérêt supérieur de l'enfant.

6. **Séparation données VBG** : Schéma PostgreSQL distinct, chiffrement individuel libsodium. Même un DBA ne peut pas lire les données VBG sans la clé.

7. **Canal Direct VBG = priorité** : Les 5 canaux directs (USSD, SMS, Ligne Verte, WhatsApp, Personne de confiance) sont aussi prioritaires que le back-office. Le message de réponse ne mentionne JAMAIS "VBG" ou "violence".

8. **Géographie via package** : Utiliser `elbrahms/niger-location`. Ne jamais recréer le référentiel géographique à la main.

9. **Une plainte = un site** : `site_id` nullable uniquement pour plaintes hors-site (Type 1) et VBG anonymes.

10. **Aide contextuelle obligatoire** : Chaque page back-office a un guide dans `PageGuidesSeeder` avec référence MGP obligatoire.

11. **Budget digitalisation** : 25 000 000 FCFA - prioriser MVP (formulaires + back-office + dashboard + module VBG) puis itérer.

12. **Deux dépôts, un seul claude.md** : Tout changement métier est reflété ici avant d'être codé.
13. **Palette claude-colors.md** : respecter charte graphique

## Décisions d'architecture (mise en œuvre `update.md`)

- **Transport SMS (update.md 1.4) → Option A retenue** : module SMS maison + webhook sécurisé
  côté back-office via `spatie/laravel-webhook-client` (route `POST /webhooks/sms`, signature
  HMAC-SHA256 `X-Signature`, idempotence par `message_id`, `ProcessSmsWebhookJob`). Le fork
  `medic/medic-collect` (option B) n'est pas retenu pour l'instant. Un seul transport SMS maintenu.
  Le déchiffrement compatible mobile `SmsEncryption` reste un point d'intégration à brancher.
- **2FA (update.md 1.9) → Fortify en socle, Breeze conservé** : `laravel/fortify` fournit
  uniquement le 2FA (TOTP + codes de récupération), ses routes sont désactivées
  (`Fortify::$registersRoutes = false`) ; l'authentification reste gérée par Breeze, avec un
  défi TOTP inséré à la connexion. 2FA **imposé** aux rôles `super_admin`/`coordonnateur_ugp`/
  `specialiste_vbg` (`SecuritySettings::two_factor_required_roles`), **non imposé en `APP_ENV=local`**.
- **API mobile (update.md 1.3)** : `laravel/sanctum`, jeton par appareil. Le certificat client
  (mTLS) des appareils terrain reste une couche infrastructure (cf. `claude-deploy.md`).
- **Référentiel géographique (update.md 4.2)** : la règle n° 8 (`elbrahms/niger-location`) n'est
  **pas encore** appliquée - le domaine `Geography/` reste la source côté back-office. Arbitrage
  encore ouvert pour l'office (requérir le package et migrer, ou amender la règle n° 8).
- **Géographie côté formulaires (update.md 3.3) → décision : AUCUNE géographie dans les XForm.**
  Les référentiels géographiques évoluent : on ne les fige plus dans les formulaires. L'agent
  saisit **uniquement l'ID du site PACIPA** (F01) ; le back-office déduit région/localité du
  site (`SubmissionMapper` résout par code/id). Cascade region→localité **supprimée** de F01.
- **Domaine `Company/`** : ajouté hors `claude-office.md` (lien `company_id` sur les travailleurs) -
  à documenter dans `claude-office.md`.
- **CI/CD (update.md 4.1)** : GitHub Actions `.github/workflows/ci.yml` (tests PHP 8.4 sur SQLite
  en mémoire + Pint informatif) ; environnement Docker de dev dans `docker/`.

## graphify

This project has a graphify knowledge graph at graphify-out/.

Rules:
- Before answering architecture or codebase questions, read graphify-out/GRAPH_REPORT.md for god nodes and community structure
- If graphify-out/wiki/index.md exists, navigate it instead of reading raw files
- After modifying code files in this session, run `graphify update .` to keep the graph current (AST-only, no API cost)
