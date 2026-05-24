package ne.pacipa.mgp.sms

import android.util.Base64
import javax.crypto.Cipher
import javax.crypto.SecretKey
import javax.crypto.spec.GCMParameterSpec
import javax.crypto.spec.SecretKeySpec
import java.security.SecureRandom

/**
 * Chiffrement AES-256-GCM pour soumissions SMS.
 *
 * La clé partagée est dérivée du token API mgp-office configuré dans les paramètres.
 * Elle est transmise hors-bande (configuration initiale par le responsable MGP).
 *
 * Format SMS chiffré : BASE64(IV[12] || TAG[16] || CIPHERTEXT)
 * Longueur max 160 caractères (SMS) → payload JSON compressé avant chiffrement.
 */
object SmsEncryption {

    private const val ALGORITHM      = "AES/GCM/NoPadding"
    private const val KEY_ALGORITHM  = "AES"
    private const val IV_LENGTH      = 12   // 96 bits GCM standard
    private const val TAG_LENGTH     = 128  // bits

    /**
     * Chiffre un payload JSON et retourne le SMS encodé en Base64.
     * @param plaintext  JSON à chiffrer (< ~90 chars pour tenir en 160 chars Base64)
     * @param keyBytes   Clé AES-256 (32 bytes) dérivée du token API
     */
    fun encrypt(plaintext: String, keyBytes: ByteArray): String {
        require(keyBytes.size == 32) { "La clé doit être 32 bytes (AES-256)" }
        val iv     = ByteArray(IV_LENGTH).also { SecureRandom().nextBytes(it) }
        val key    = SecretKeySpec(keyBytes, KEY_ALGORITHM)
        val cipher = Cipher.getInstance(ALGORITHM).apply {
            init(Cipher.ENCRYPT_MODE, key, GCMParameterSpec(TAG_LENGTH, iv))
        }
        val ciphertext = cipher.doFinal(plaintext.toByteArray(Charsets.UTF_8))
        val combined   = iv + ciphertext   // IV || TAG est intégré dans ciphertext par AES-GCM
        return Base64.encodeToString(combined, Base64.NO_WRAP)
    }

    /**
     * Déchiffre un SMS reçu (utilisé côté webhook mgp-office, fourni ici pour tests).
     */
    fun decrypt(encoded: String, keyBytes: ByteArray): String {
        val combined   = Base64.decode(encoded, Base64.NO_WRAP)
        val iv         = combined.copyOfRange(0, IV_LENGTH)
        val ciphertext = combined.copyOfRange(IV_LENGTH, combined.size)
        val key        = SecretKeySpec(keyBytes, KEY_ALGORITHM)
        val cipher     = Cipher.getInstance(ALGORITHM).apply {
            init(Cipher.DECRYPT_MODE, key, GCMParameterSpec(TAG_LENGTH, iv))
        }
        return String(cipher.doFinal(ciphertext), Charsets.UTF_8)
    }
}
