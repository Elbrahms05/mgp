# PACIPA-MGP - Règles métier MGP

## Les 8 types de plaintes (Tableau 4)

| # | Type | Exemples |
|---|---|---|
| 1 | Inaccessibilité aux informations | Manque d'info sur les opportunités projet |
| 2 | Exécution des marchés | Non-respect clauses, corruption, retard décaissement |
| 3 | Travaux de réhabilitation | Nuisances, pollution, accidents, non-paiement |
| 4 | Conditions de travail | Recrutement, travail enfants, accidents travail |
| 5 | Pertes/affectation biens physiques | Perte cultures, désaccords limites parcelles |
| 6 | Octroi d'équipements | Mauvais ciblage, corruption, discrimination genre |
| 7 | Formations | Favoritisme, non-versement perdiems |
| **8** | **VBG/EAS/HS et VCE** | **Agressions, harcèlement, viols, exploitation mineurs** |

> **Type 8 = plainte SENSIBLE** : circuit séparé, chiffrement E2EE, JAMAIS de médiation.

## Les 3 niveaux de résolution

| Niveau | Organe | Délai max |
|---|---|---|
| **1** | CBGP - Comité de Base (localité) | 13 jours (3j examen + 7j investigation + 3j notification) |
| **2** | CCGP - Comité Communal | 10 jours (3j + 4j + 3j) |
| **3** | CNGP - Comité National / UGP | 10 jours (7j + 3j) |

Composition clé de chaque comité : Président, Secrétaire, Représentants bénéficiaires/PAP/femmes/ONG, **2 Points focaux VBG (1H + 1F)**.

## Workflow plainte non sensible

```
NOUVELLE → ENREGISTRÉE (accusé SMS auto)
         → EXAMEN (3-7j)
         → RECEVABLE ou REJETÉE
         → MÉDIATION
         → RÉSOLUE (PV accord) / ESCALADÉE / NON RÉSOLUE
         → CLÔTURÉE
```

Chaque transition : horodatage + utilisateur + commentaire.
Escalade automatique si délai dépassé sans action.

## Circuit VBG/EAS/HS

```
Survivant(e) → Point focal VBG (CBGP/CCGP)
                  ├── Référencement prestataire VBG local (72h max)
                  └── Notification UGP (24h max)
                            ├── UGP → Banque mondiale (24h ESIRT / 48h PEES)
                            └── Comité National VBG
                                    └── Commission d'enquête (30j max)
                                        (vérifie lien projet, JAMAIS la culpabilité)
```

**Principes VBG :**
- Consentement éclairé systématique
- Confidentialité absolue
- Sécurité survivant(e) en tout temps
- Approche centrée sur la survivante
- Intérêt supérieur de l'enfant (VCE)
- **JAMAIS de règlement à l'amiable pour EAS/HS**

## Délais SLA (configurables via SlaSettings)

| Événement | Délai |
|---|---|
| Accusé réception | Immédiat |
| Examen CBGP | 3 + 7 jours |
| Examen CCGP | 3 + 4 jours |
| Examen CNGP | 7 + 3 jours |
| Notification VBG → UGP | 24h |
| Référencement VBG → services | 72h |
| Notification UGP → Banque mondiale (EAS/HS) | 24h (ESIRT) |
| Notification UGP → Banque mondiale (incidents E&S) | 48h (PEES) |
| Commission d'enquête | 30 jours max |

## Notifications obligatoires Banque mondiale

| Événement | Délai |
|---|---|
| Cas EAS/HS ou décès | 24h (ESIRT) |
| Tout incident E&S | 48h (PEES) |
| Résultat commission d'enquête | 30 jours max |

## Traitement plaintes travailleurs

**Travailleurs directs UGP :**
- N1 : Responsable admin UGP → Coordonnateur (7j max)
- N2 : CNGP (2 semaines max)

**Travailleurs contractuels :**
- N1 : Chef personnel entreprise → Directeur travaux (7j ouvrables)
- N2 : Spécialiste MGP UGP → RH (7j max)

## Voies de réception des plaintes

En personne / Téléphone / SMS / WhatsApp / Courrier / Email / Boîte réclamation / Ligne verte / Auto-saisine (presse, rapports)

## Formulaires de référence (Annexes MGP)

| Formulaire | Annexe | Usage |
|---|---|---|
| F01 - Dépôt de plainte | 4 | Enregistrement initial toute plainte |
| F02 - Plainte non sensible | 8 | Détail plainte standard |
| F03 - Rapport VBG/EAS/HS | 9 | Partie 1 (identité+consentement) + Partie 2 (faits si consentement) |
| F04 - PV de conciliation | 5 | Résultat médiation |
| F05 - Suivi périodique | 6 | Tableau de bord traitement |
| F06 - PV installation comité | 3 | Mise en place CBGP/CCGP |
| F07 - Évaluation trimestrielle | 10 | Bilan comité |

> **F03 Annexe 9** : Archivé SÉPARÉMENT. Partie 1 (identité) et Partie 2 (faits) stockées séparément. NE PAS PARTAGER.

## Indicateurs de suivi clés (Tableau 7)

**Plaintes non sensibles (mensuel/trimestriel) :**
- Nombre reçues / traitées / recevables / rejetées
- Nombre séances médiation par comité
- Délais de réponse (jours)
- Nombre par canal (tel, SMS, personne, courriel, vocal)
- Nombre activités dissémination

**Plaintes VBG/EAS/HS (mensuel/trimestriel) :**
- Nombre cas signalés
- % déposées directement par survivantes
- % par personne interposée / autres moyens
- Taux référencement services (médical, juridique, psychosocial, sécurité)

## Budget MGP

| Rubrique | FCFA |
|---|---|
| Diffusion MGP | 20 000 000 |
| **Digitalisation** | **25 000 000** |
| Communication/sensibilisation | 50 000 000 |
| Renforcement capacités | 50 000 000 |
| Appui opérationnalisation | 20 000 000 |
| Suivi-évaluation | 10 000 000 |
| **TOTAL** | **175 000 000** |

## Glossaire

| Sigle | Signification |
|---|---|
| CBGP | Comité de Base de Gestion des Plaintes |
| CCGP | Comité Communal de Gestion de Plaintes |
| CNGP | Comité National de Gestion des Plaintes |
| UGP | Unité de Gestion du Projet |
| URC | Unité Régionale de Coordination |
| VBG | Violence Basée sur le Genre |
| EAS | Exploitation et Abus Sexuel |
| HS | Harcèlement Sexuel |
| VCE | Violence Contre les Enfants |
| PAP | Personne Affectée par le Projet |
| PEES | Plan d'Engagement Environnemental et Social |
| ESIRT | Environmental and Social Incident Response Toolkit |
| GBVIMS | Gender-Based Violence Information Management System |
| NES | Norme Environnementale et Sociale (Banque mondiale) |
| COPIL | Comité de Pilotage |
| MGP | Mécanisme de Gestion des Plaintes |
| MAG/ELEL | Ministère de l'Agriculture et de l'Élevage |
