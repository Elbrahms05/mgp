package ne.pacipa.mgp.sms

import org.json.JSONObject

/**
 * Construit le payload JSON compact pour soumission SMS.
 *
 * Contrainte : la soumission SMS ne doit pas dépasser 160 caractères
 * (après chiffrement + encodage Base64). Le payload JSON est donc minimaliste :
 * seuls les champs obligatoires et l'ID de formulaire sont inclus.
 *
 * Le serveur webhook de mgp-office (spatie/laravel-webhook-client) reçoit le SMS,
 * déchiffre, et crée la plainte avec statut "sms_pending" pour complétion ultérieure.
 */
object SmsPayloadBuilder {

    /**
     * Payload SMS minimal pour un dépôt de plainte (F01).
     * ~90 chars JSON → ~120 chars en Base64 → tient en 1 SMS.
     */
    fun buildComplaintPayload(
        formId: String,       // "pacipa_f01"
        complaintId: String,  // ID plainte (temporaire offline ou définitif)
        type: String,         // "type1".."type8"
        regionCode: String,
        channel: String,
        timestamp: Long = System.currentTimeMillis()
    ): String {
        return JSONObject().apply {
            put("fid", formId)
            put("id",  complaintId)
            put("tp",  type)
            put("rg",  regionCode)
            put("ch",  channel)
            put("ts",  timestamp)
        }.toString()
    }

    /**
     * Payload SMS pour un signalement VBG (F03 - anonymisé).
     * NE CONTIENT AUCUNE donnée identifiant la survivant(e).
     */
    fun buildVbgPayload(
        vbgId: String,     // "VBG-NI-2025-0001"
        ageGroup: String,  // "girl_lt18" | "woman_gte18" | "boy_lt18" | "man_gte18"
        gender: String,    // "F" | "M"
        consentGiven: Boolean,
        timestamp: Long = System.currentTimeMillis()
    ): String {
        return JSONObject().apply {
            put("fid", "pacipa_f03")
            put("id",  vbgId)
            put("ag",  ageGroup)
            put("gn",  gender)
            put("cs",  if (consentGiven) 1 else 0)
            put("ts",  timestamp)
        }.toString()
    }
}
