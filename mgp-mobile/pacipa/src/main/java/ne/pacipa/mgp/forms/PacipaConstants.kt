package ne.pacipa.mgp.forms

/**
 * Constantes PACIPA-MGP - ne pas modifier sans mise à jour de claude-metier.md
 */
object PacipaConstants {

    // Régions Niger (8)
    val REGIONS = listOf(
        "Agadez", "Diffa", "Dosso", "Maradi",
        "Niamey", "Tahoua", "Tillabéri", "Zinder"
    )

    // Codes régions pour numérotation plainte
    val REGION_CODES = mapOf(
        "Agadez"    to "AG",
        "Diffa"     to "DI",
        "Dosso"     to "DO",
        "Maradi"    to "MA",
        "Niamey"    to "NI",
        "Tahoua"    to "TA",
        "Tillabéri" to "TI",
        "Zinder"    to "ZI"
    )

    // Types de plaintes (Tableau 4 MGP)
    val COMPLAINT_TYPES = mapOf(
        "type1" to "Inaccessibilité aux informations",
        "type2" to "Exécution des marchés",
        "type3" to "Travaux de réhabilitation",
        "type4" to "Conditions de travail",
        "type5" to "Pertes/affectation biens physiques",
        "type6" to "Octroi d'équipements",
        "type7" to "Formations",
        "type8" to "VBG/EAS/HS et VCE"  // SENSIBLE - circuit séparé
    )

    // Canaux de réception
    val CHANNELS = listOf(
        "en_personne", "telephone", "sms", "whatsapp",
        "courrier", "email", "boite_reclamation", "ligne_verte", "auto_saisine"
    )

    // Langues disponibles
    val LANGUAGES = listOf("fr", "ha", "zr")  // Français, Haoussa, Zarma

    // SLA (heures)
    const val SLA_VBG_NOTIFICATION_H   = 24
    const val SLA_VBG_REFERRAL_H       = 72
    const val SLA_CBGP_EXAM_J          = 3
    const val SLA_CBGP_INVEST_J        = 7
    const val SLA_CCGP_EXAM_J          = 3
    const val SLA_CCGP_INVEST_J        = 4
    const val SLA_CNGP_EXAM_J          = 7
    const val SLA_CNGP_INVEST_J        = 3
    const val SLA_COMMISSION_J         = 30

    // mgp-office API endpoints
    const val API_BASE_PATH            = "/api/mobile/"
    const val API_FORMS_LIST           = "${API_BASE_PATH}forms"
    const val API_FORM_DOWNLOAD        = "${API_BASE_PATH}forms/{id}/download"
    const val API_SUBMISSION           = "${API_BASE_PATH}submissions"
    const val API_SMS_WEBHOOK          = "/webhooks/sms"
}
