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
12. **Palette claude-colors.md** : respecter charte graphique

## graphify

This project has a graphify knowledge graph at graphify-out/.

Rules:
- Before answering architecture or codebase questions, read graphify-out/GRAPH_REPORT.md for god nodes and community structure
- If graphify-out/wiki/index.md exists, navigate it instead of reading raw files
- After modifying code files in this session, run `graphify update .` to keep the graph current (AST-only, no API cost)
