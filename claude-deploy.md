# PACIPA-MGP - Déploiement et infrastructure (`claude-deploy.md`)

## Infrastructure Niger

| Contrainte | Solution |
|---|---|
| Connectivité limitée | App mobile 100% offline. Sync opportuniste. |
| Énergie instable | Solutions solaires pour tablettes/smartphones terrain. |
| Zones sans data | SMS structuré comme fallback pour formulaires critiques. |
| Sites isolés | Starlink comme backbone (déjà en usage au PACIPA). |

## Sécurité infrastructure

| Mesure | Détail |
|---|---|
| En transit | TLS 1.3 obligatoire (HTTPS) |
| Au repos | AES-256 BD + chiffrement disque LUKS serveur |
| Applicatif VBG | libsodium (XSalsa20-Poly1305) en plus du chiffrement BD |
| 2FA | Obligatoire pour `coordonnateur_ugp`, `specialiste_vbg`, `super_admin` |
| RBAC | Spatie - principe du moindre privilège |
| Audit trail | Spatie Activitylog - tout CRUD, login, export, consultation |
| Sauvegarde | Backup chiffré quotidien, rétention 90 jours, test restauration mensuel |
| Auth mobile | Certificat client pour les appareils terrain |
| Sessions USSD VBG | Logs webhooks purgés après 72h |

## Hébergement ODK Central

- Serveur dédié UGP Niamey **ou** cloud certifié (Banque mondiale compliance)
- PostgreSQL (préféré) ou CouchDB
- TLS 1.3 + authentification certificat client
- AES-256 au repos, E2EE pour formulaires VBG
- Sync bidirectionnelle chiffrée avec back-office Laravel (webhook ou pull cron)

## Conformité réglementaire

- **NES 10 (BM)** : Mobilisation parties prenantes et MGP
- **NES 2** : Main-d'œuvre et conditions de travail
- **NES 5** : Acquisition terres, réinstallation involontaire
- **Bonnes pratiques BM** : Lutter contre EAS/HS dans projets FPI (3e édition)
- **ESIRT** : Notification 24h cas EAS/HS
- **PEES PACIPA** : Notification 48h incidents E&S, 24h EAS/HS et décès
- **Législation nigérienne** : Code du Travail (Loi N°2012-45), Code Pénal infractions VBG

## Plan de déploiement progressif

| Phase | Période | Contenu |
|---|---|---|
| Phase 1 | T2 2025 | Développement back-office + formulaires XForm |
| Phase 2 | T3 2025 | Pilote 2 régions : Dosso + Maradi (forte expérience MGP) |
| Phase 3 | T4 2025 | Formation comités pilotes + ajustements UX terrain |
| Phase 4 | T1 2026 | Extension aux 6 régions restantes |
| Phase 5 | 2026+ | Suivi, évaluation, itérations continues |

## Formation des utilisateurs

| Public | Contenu | Durée |
|---|---|---|
| Chargés d'enregistrement CBGP | App mobile, formulaires, sync | 2 jours |
| Points focaux VBG | Formulaire Annexe 9, consentement, confidentialité, référencement | 3 jours |
| Présidents CBGP/CCGP | Interface web suivi plaintes, PV, médiation | 2 jours |
| Équipe UGP (sauvegardes + VBG) | Back-office complet, dashboard, rapports, commission enquête | 3 jours |
| Administrateur système | Installation, configuration, maintenance, sauvegarde | 2 jours |
| Personnes de confiance VBG | Approche centrée survivante, consentement, formulaire simplifié | Formation spécifique + recyclage 6 mois |

## Structure monorepo

```
pacipa-mgp/
├── mgp-office/       Back-office Laravel
├── mgp-mobile/       Application mobile Android
├── docs/             MGP PDF, schémas, specs
├── docker/           Docker Compose dev local (PG, Redis, ODK Central)
├── .github/          CI/CD workflows
├── claude.md         Index + rappels critiques
├── claude-metier.md  Règles métier MGP
├── claude-office.md  Back-office Laravel
├── claude-mobile.md  App mobile + formulaires
├── claude-vbg.md     Module VBG sécurité + canaux directs
└── claude-deploy.md  CE FICHIER - infrastructure + déploiement
```

## Git

- **Branching** : Git Flow (main / develop / feature / hotfix)
- **Commits** : Conventional Commits en français (`feat:`, `fix:`, `docs:`, `chore:`)
- **Scope** : `feat(mgp-office): ...` ou `feat(mobile): ...`
- **CI/CD** : GitHub Actions ou GitLab CI - lint, tests, déploiement staging

## Tests critiques à couvrir

```
Unit:
  VbgEncryptionTest.php
  SlaCalculationTest.php
  ComplaintWorkflowTest.php

Feature:
  ComplaintEscalationTest.php
  VbgReferralFlowTest.php
  VbgAccessControlTest.php
  NotificationDeadlineTest.php
  PageGuideDisplayTest.php
  SpatieBackupTest.php
```

## Risques identifiés et mesures d'atténuation

| Risque | Mesure |
|---|---|
| Faible appropriation du MGP | Diffusion à tous les acteurs, sensibilisation continue |
| Absence spécialistes EAS/HS | Cartographie structures, formation points focaux |
| Conflits mise en place comités | Harmonisation partenaires, transparence désignation |
| Corruption gestion plaintes | Interdiction formelle + sanctions, dénonciation UGP |
| Insuffisance budget MGP | Budget annuel dédié sur toute la durée |
| Insécurité zones intervention | Respect Plan de Gestion de Sécurité |
| Discrimination | Équité dans la prise en charge de toutes les parties prenantes |
