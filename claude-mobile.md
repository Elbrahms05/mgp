# PACIPA-MGP - Application Mobile (`mgp-mobile/`)

## Principes UX terrain (CRITIQUE)

Les chargés d'enregistrement ont un **faible niveau de maîtrise digitale**. Les formulaires doivent être extrêmement didactiques :

- **Un écran = une question** (jamais de scroll long)
- **Boutons gros** (48dp minimum) et colorés
- **Hint/aide** sur chaque question en langage simple
- **Icônes illustratives** + **notes audio** pré-enregistrées par section et par langue
- **Validation temps réel** : champs obligatoires, format téléphone Niger +227XXXXXXXX, dates cohérentes
- **Mode hors-ligne** : fonctionnement 100% sans connexion, envoi prioritaire avec SMS, sync auto dès retour WiFi/réseau
- **SMS fallback** : zones sans data - formulaires critiques transitent par SMS structuré
- **Résumé visuel** avant soumission : icônes vert/rouge par section remplie/manquante
- **Formulaires** chargement : Pour une premiere installation ou en cas de mise à jour des formulaires requerir la connexion internet   pour charger les formulaires (stockés sur ODK Central Serveur accessible via API mgp-office)


## Personnalisation PACIPA

- Logo PACIPA (République du Niger + Banque mondiale) sur splash screen et en-tête
- Palette : vert agriculture + bleu IDA
- Splash screen : *"PACIPA - Mécanisme de Gestion des Plaintes"*

## Architecture Android

- **Java 11+** (ou Kotlin)
- **MVVM** : Room (SQLite local) + ViewModel + LiveData
- **Moteur XForm** : ODK Collect engine (JavaRosa)
- **Offline-first** : Room → file d'attente sync
- **sms** : Envoi des soumissions via SMS chiffrés(pas plus de 160 caractères; creer une webhook pour recevoir et traiter les données au niveau mgp-office; cette fois sans passer par odk central.En effet lorsque le serveur sms recevra il enverra directement les données au lien de la webhook obtenu de mgp-office (spatie/laravel-webhook-client). ce webhook doit etre securisé.)
- `NetworkChangeReceiver` : détecte retour réseau → auto-sync

## Formulaires XLSForm (F01–F07)

Nommage : `pacipa_mgp_f01_depot_plainte_v1.xlsx`
Form ID : `pacipa_f01`, `pacipa_f03_vbg`, etc.
Colonnes : `label::French`, `label::Haoussa`, `label::Zarma`, `hint::French`...
Groupes : `begin_group / end_group` avec `appearance: field-list`

### F01 - Dépôt de plainte (Annexe 4)

```
1. Identification
   - N° auto-généré : REGION-DEPARTMENT-COMMUNE-LOCALITE-NUMORDRE
   - Cascade géographique : Région → Département → Commune → Localité → Site PACIPA
     └── Option "Plainte hors site" (Type 1)
     └── Option "Site non listé" (texte libre, rattachement UGP a posteriori)

2. Identité du plaignant
   - Nom/prénom, âge, sexe, lieu résidence, contact (+227XXXXXXXX), volonté anonymat

3. Objet de la plainte
   - Type (select_one : 8 types), personne/structure visée
   - Description détaillée (texte long), date/heure des faits
   - Témoins (repeat group : nom + contact)

4. Canal de réception
   - select_one : En personne / Téléphone / SMS / WhatsApp / Courrier / Email /
                  Boîte réclamation / Ligne verte / Auto-saisine

5. Pièces jointes (optionnel) : photos, scans

6. Confirmation
   - Résumé visuel, signature numérique chargé, date/heure auto, GPS position auto
```

### F02 - Plainte NON SENSIBLE (Annexe 8)

Reprend F01 + : nature bien affecté, observations autorité, réponse plaignant, résolution, signatures.

### F03 - Rapport VBG/EAS/HS (Annexe 9) - SÉCURITÉ MAXIMALE

Chiffrement E2EE. Accès : points focaux VBG + UGP uniquement.

```
PARTIE 1 - Enregistrement initial (par prestataire VBG)
  - Code auto-généré anonymisé : VBG-REGION-AAAA-NNNN
  - Âge/sexe survivant(e) : Fille <18 / Femme ≥18 / Garçon <18 / Homme ≥18
  - Date incident, lieu incident
  - Identité auteur présumé : Personnel projet / FDS / Inconnu / Sans lien projet
  - Forme VBG (GBVIMS, select_multiple) :
    □ Viol  □ Agression sexuelle  □ Agression physique
    □ Violence psychologique  □ Mariage forcé/précoce
    □ Déni de services/ressources  □ Aucun incident VBG vérifié
  - Services reçus ? (oui/non)
  - Consentement renvoi MGP PACIPA ? (oui/non)
  ⚠️ Archivé SÉPARÉMENT. NE PAS PARTAGER.

PARTIE 2 - Description des faits (UNIQUEMENT si consentement = OUI)
  - Consentement confirmé (skip logic si non)
  - Auteur présumé : nom/surnom, lien projet, fonction, nationalité, description physique
  - Témoins, compte rendu précis des faits
  - Classification GBVIMS, services reçus (select_multiple)
```

### F04 - PV de conciliation (Annexe 5)

Date, identité plaignant (lié F01), sujet réclamation, présidence séance, résultat (texte long), signatures.

### F05 - Suivi périodique (Annexe 6)

N° plainte (lié F01), date dépôt, transmission service, accusé réception, résolution, retour info réclamant.

### F06 - PV installation comité (Annexe 3)

Localisation, nombre participants AG (total/H/F), date, membres élus (repeat group : poste, profil, candidat élu, mode élection).

### F07 - Évaluation trimestrielle CGP (Annexe 10)

Localité/commune/région, membres opérationnels, plaintes enregistrées/traitées/réglées/transmises, difficultés, solutions, feedbacks communauté, suggestions.

## Ressources embarquées dans les formulaires

**Icônes didactiques (`forms/media/icons/`) :**
- `ic_type1_info.png` à `ic_type8_vbg.png` (cadenas pour type 8)
- `ic_canal_telephone.png`, `ic_canal_sms.png`, `ic_canal_personne.png`...

**Notes audio d'aide (`forms/media/audio/{locale}/`) :**
- Disponibles en FR, HA, ZR pour chaque section de chaque formulaire
- Ex : `f01_section1_identification.mp3`, `f03_partie1_consentement.mp3`

## Structure dossiers `mgp-mobile/`

```
mgp-mobile/
├── app/src/main/java/ne/pacipa/mgp/
│   ├── activities/       SplashActivity, LoginActivity, MainActivity,
│   │                     FormListActivity, FormFillActivity, DraftListActivity,
│   │                     SentListActivity, SyncActivity, SettingsActivity
│   ├── services/         OdkSyncService, SmsSubmissionService,
│   │                     EncryptionService, GpsLocationService, AudioGuideService
│   ├── database/         Room DB (DraftDao, SyncQueueDao)
│   ├── receivers/        NetworkChangeReceiver, SmsReceiver
│   └── utils/            LocaleHelper, FormIdGenerator, PhoneValidator
├── forms/
│   ├── xlsform/          Fichiers source .xlsx (F01–F07)
│   ├── xform/            XML compilés (auto-générés)
│   └── media/            icons/ + audio/
├── branding/             Logo PACIPA SVG, palette couleurs, splash design
└── docs/                 Guide utilisateur terrain (FR + HA), guide installation
```

## Conventions

- Taille tactile minimale : 48dp tous éléments interactifs
- Activities : PascalCase (`FormFillActivity`)
- Layouts : snake_case (`activity_form_fill.xml`)
- Multi-langues : `values/`, `values-ha/`, `values-zr/`, `values-tq/`, `values-kr/`, `values-ff/`

## Déploiement terrain

- Énergie : prévoir solutions solaires pour zones sans électricité fiable
- Starlink : backbone pour sites URC isolés (déjà en usage au PACIPA)
- Test offline : tout formulaire doit être remplissable et sauvegardable sans réseau
