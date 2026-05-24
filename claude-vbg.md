# PACIPA-MGP - Module VBG/EAS/HS (`claude-vbg.md`)

## Sécurité des données VBG

| Mesure | Mise en œuvre |
|---|---|
| Chiffrement au repos | libsodium (XSalsa20-Poly1305) sur chaque champ sensible individuellement |
| Chiffrement en transit | TLS 1.3 + chiffrement applicatif supplémentaire sur payloads VBG |
| Séparation physique | Schéma PostgreSQL séparé `vbg`, accès restreint au niveau SGBD |
| Clé de déchiffrement | HSM / Vault - seuls `specialiste_vbg` et `coordonnateur_ugp` détiennent la clé |
| Pas de données VBG dans les logs | Filtrage automatique des champs sensibles dans audit logs |
| Consentement | Enregistrement horodaté, possibilité de retrait ultérieur |
| Archivage séparé | Partie 1 (identité) et Partie 2 (faits) stockées séparément (Annexe 9) |
| Destruction sécurisée | Purge cryptographique après clôture + délai légal |

**Qui peut déchiffrer les données VBG :** `specialiste_vbg` + `coordonnateur_ugp` UNIQUEMENT. Les administrateurs système n'ont PAS accès.

## Commission d'enquête VBG

- Membres : Spécialistes sauvegardes + VBG UGP + Point focal genre Ministère
- Délai : 30 jours max
- Elle vérifie uniquement le **lien plainte-projet** - JAMAIS la culpabilité (rôle de la justice)
- Workflow : constitution dossier → investigation → rapport vérification → recommandations → notification BM

## Table `vbg_reports` - colonnes spécifiques canaux directs

| Colonne | Type | Description |
|---|---|---|
| `channel` | enum | `ussd`, `sms`, `whatsapp`, `voice_line`, `trusted_person`, `point_focal`, `web` |
| `is_anonymous` | boolean | Si true : aucune donnée identifiante stockée |
| `caller_phone_encrypted` | text nullable | Numéro tél chiffré libsodium, null si anonyme |
| `trusted_person_id` | FK nullable | Lien vers personne de confiance (canal ⑤) |
| `ussd_session_id` | string nullable | ID session USSD |
| `raw_sms_content` | text nullable | Contenu SMS brut chiffré (format libre) |
| `voice_memo_path` | string nullable | Chemin mémo vocal WhatsApp chiffré |
| `site_id` | FK nullable | Rattachement a posteriori par Spécialiste VBG |

---

## 5 Canaux Directs VBG - Signalement sans passer par le comité local

**Pourquoi :** Dans les communautés rurales du Niger, la survivante peut être voisine, parente ou dépendante de membres du comité. L'auteur présumé peut être un notable ou un agent du projet. Le MGP prévoit que les plaignants peuvent saisir directement le niveau qu'ils jugent pertinent.

**Principe :** Chaque canal aboutit **directement** au Spécialiste VBG UGP Niamey, sans transit par aucun CBGP/CCGP.

```
Survivant(e) en zone rurale
  ├── ① USSD *xxx#
  ├── ② SMS short code 3xxx
  ├── ③ Appel Ligne Verte (numéro gratuit)
  ├── ④ WhatsApp Bot (+227 xx xx xx xx)
  └── ⑤ Personne de confiance hors comité
              → Spécialiste VBG UGP (Niamey, 24h max)
```

---

### Canal ① - USSD *xxx# (priorité absolue)

Fonctionne sur **tout téléphone basique**, sans internet, sans crédit (appel inversé gratuit via convention opérateur).

**Menu (5 langues : FR / HA / ZR / TQ / FF) :**
```
*xxx#
├── 1. Choisir la langue
├── 2. Signaler un incident
│   ├── Qu'est-ce qui s'est passé ? (1.Violence physique / 2.Violence sexuelle /
│   │                                 3.Mariage forcé / 4.Harcèlement / 5.Autre /
│   │                                 6.Je préfère ne pas dire)
│   ├── Quand ? (1.Aujourd'hui / 2.Cette semaine / 3.Ce mois / 4.Avant)
│   ├── L'auteur est-il lié au projet PACIPA ? (1.Oui / 2.Non / 3.Je ne sais pas)
│   ├── Souhaitez-vous être rappelé(e) ?
│   │   (1.Oui ce numéro / 2.Oui autre numéro → saisie / 3.Non, rester anonyme)
│   └── CONFIRMATION : "Code VBG-xxxx. Spécialiste vous contacte sous 24h."
├── 3. Suivre mon signalement (code VBG-xxxx → statut)
└── 0. Parler à quelqu'un maintenant → transfert Ligne Verte
```

**Points clés :**
- Aucune donnée ne reste sur le téléphone (pas de trace dans l'historique)
- "6. Je préfère ne pas dire" est essentiel - jamais forcé de classifier
- Convention opérateur à négocier : Orange Niger, Airtel Niger, Moov (short code gratuit)

---

### Canal ② - SMS short code 3xxx

```
Format libre : "PACIPA [message libre]" → 3xxx
Format structuré : "VBG [type:1-6] [quand:1-4] [rappel:OUI/NON]" → 3xxx

Réponse auto (neutre) : "Merci pour votre message au service PACIPA.
                          Code: VBG-xxxx. En cas d'urgence appelez le [ligne verte]."
```

Traitement backend :
- Gateway SMS (Africa's Talking, Twilio, ou partenaire local) → webhook Laravel
- Format structuré → parsing auto → création `VbgReport`
- Format libre → stocké brut → traitement manuel UGP sous 24h
- Numéro expéditeur chiffré **immédiatement** (libsodium) avant stockage
- Mot-clé "ANONYME" = numéro non enregistré

---

### Canal ③ - Ligne Verte (appel vocal gratuit)

- Prévu dans le MGP (§4.3.3.5) via ONG VBG recrutée
- L'opératrice remplit le formulaire Annexe 9 Partie 1 directement dans le back-office pendant l'appel
- Appel **jamais enregistré** sauf consentement explicite
- Disponible 24h/24 (transfert vers Spécialiste VBG UGP hors heures ONG)
- Même numéro que le WhatsApp Bot (double usage)

---

### Canal ④ - WhatsApp Bot (zones avec data mobile)

- Même arbre de décision que le menu USSD
- Possibilité d'envoyer un **message vocal** (transcrit par opérateur VBG, jamais par IA)
- Possibilité d'envoyer une **photo** (certificat médical, blessure - avec consentement)
- E2EE WhatsApp natif + chiffrement applicatif côté serveur
- Le bot indique comment **supprimer la conversation** du téléphone

---

### Canal ⑤ - Réseau de personnes de confiance HORS comité

**Qui :** Agents de santé communautaire / sages-femmes des CSI, enseignant(e)s, leaders religieux formés, animatrices ONG VBG, relais communautaires centres holistiques.

**Fonctionnement :**
1. Formation spécifique (distincte des comités) sur approche centrée survivante, consentement, formulaire simplifié
2. Accès limité dans l'app mobile (rôle `personne_confiance_vbg`) :
   - Saisie Partie 1 (Annexe 9) avec consentement survivante
   - Transmission **directe à l'UGP** (pas au CBGP/CCGP)
   - Consultation liste services VBG locaux
3. Sans smartphone : SMS structuré (canal ②) ou appel ligne verte (canal ③)
4. Ne sont PAS membres du comité de gestion des plaintes (séparation essentielle)

**Critères sélection :**
- Identifiées par les femmes des communautés elles-mêmes (consultations en petits groupes séparés)
- Engagement de confidentialité signé
- Formation recyclée tous les 6 mois
- Le système vérifie automatiquement les conflits d'intérêts lors de la saisie

---

## Garanties de sécurité des canaux directs

| Garantie | Mise en œuvre |
|---|---|
| Aucune trace sur le téléphone | USSD = rien. SMS = réponse neutre. WhatsApp = instruction suppression. |
| Anonymat complet | Disponible sur tous les canaux. Code VBG-xxxx = seul identifiant. |
| Pas de transit comité local | Données → serveur central → Spécialiste VBG UGP. CBGP/CCGP ne voient rien. |
| Chiffrement immédiat | Avant stockage en base, à la réception du webhook. |
| Numéro rappel protégé | Chiffré libsodium. Rappel depuis numéro masqué. |
| Pas de géolocalisation auto | Zone déclarée volontairement, pas de GPS automatique. |
| Message réponse neutre | JAMAIS "VBG", "violence", "plainte". Toujours : "Service d'écoute PACIPA". |
| Logs webhooks VBG purgés | Après 72h. Seul le signalement chiffré en base est conservé. |

---

## Intégration technique backend

```
app/Domains/Vbg/
├── Channels/
│   ├── UssdChannel.php
│   ├── SmsChannel.php
│   ├── WhatsAppChannel.php
│   ├── VoiceLineChannel.php
│   └── TrustedPersonChannel.php
├── Services/
│   ├── VbgEncryptionService.php          # libsodium E2EE
│   ├── ConsentService.php
│   ├── WorldBankNotificationService.php
│   ├── DirectReportIngestionService.php  # Unifie tous canaux → VbgReport
│   └── AnonymizationService.php

app/Http/Controllers/Vbg/DirectReport/
├── UssdWebhookController.php
├── SmsWebhookController.php
├── WhatsAppWebhookController.php
└── TrustedPersonController.php
```

## Sensibilisation terrain (budget communication 50M FCFA)

- **Radio communautaire** (langues locales) : spots neutres, jamais le mot "VBG" - *"Si vous vivez une situation difficile liée au projet PACIPA, composez le *xxx# ou appelez le 3xxx. Confidentiel."*
- **Affichage discret** dans CSI, pharmacies, écoles, mosquées : petit autocollant "Écoute confidentielle + numéro"
- **Sages-femmes** : sensibilisation lors des consultations prénatales (moment privé)
- **Formation agents de santé communautaire** : orientation vers canaux directs
- **Pas d'affichage massif en village** (contre-productif, risque stigmatisation)

## 4 Centres holistiques VBG

Localisés à **Maradi, Tahoua, Tillabéri, Zinder** - pré-configurés comme prestataires dans le système avec workflow de référencement dédié.
