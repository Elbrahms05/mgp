package ne.pacipa.mgp.forms

import java.time.Year
import java.util.concurrent.atomic.AtomicInteger

/**
 * Génère le numéro unique de plainte PACIPA-MGP.
 *
 * Format : REGION-DEPARTEMENT-COMMUNE-LOCALITE-AAAA-NNNN
 * Exemple : NI-NI-NI-OUG-2025-0001
 *
 * Pour VBG : VBG-REGION-AAAA-NNNN (anonymisé - pas de département/commune/localité)
 * Exemple  : VBG-NI-2025-0001
 */
object FormIdGenerator {

    private val counter = AtomicInteger(0)

    /**
     * Génère un ID plainte standard (Types 1-7).
     * @param regionCode  Code région 2 lettres (ex: "NI")
     * @param deptCode    Code département 2 lettres
     * @param communeCode Code commune 3 lettres
     * @param localiteCode Code localité 3 lettres  ("---" si hors-site)
     * @param sequenceNum Numéro d'ordre (fourni par mgp-office au sync)
     */
    fun generateComplaintId(
        regionCode: String,
        deptCode: String,
        communeCode: String,
        localiteCode: String,
        sequenceNum: Int
    ): String {
        val year = Year.now().value
        val seq  = sequenceNum.toString().padStart(4, '0')
        return "${regionCode.uppercase()}-${deptCode.uppercase()}-${communeCode.uppercase()}-${localiteCode.uppercase()}-$year-$seq"
    }

    /**
     * Génère un ID VBG anonymisé (Type 8 - EAS/HS/VCE).
     * NE CONTIENT PAS d'identifiants géographiques fins.
     */
    fun generateVbgId(regionCode: String, sequenceNum: Int): String {
        val year = Year.now().value
        val seq  = sequenceNum.toString().padStart(4, '0')
        return "VBG-${regionCode.uppercase()}-$year-$seq"
    }

    /**
     * Génère un ID temporaire offline (sera remplacé à la synchronisation).
     * Format : TMP-{timestamp}-{random4}
     */
    fun generateOfflineId(): String {
        val ts     = System.currentTimeMillis()
        val random = (1000..9999).random()
        return "TMP-$ts-$random"
    }
}
