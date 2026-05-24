package ne.pacipa.mgp.sms

import android.app.Service
import android.content.Context
import android.content.Intent
import android.os.IBinder
import android.telephony.SmsManager
import timber.log.Timber

/**
 * Service de soumission SMS pour les zones sans connexion data.
 *
 * Flux :
 *   1. L'app crée un payload JSON (SmsPayloadBuilder)
 *   2. Le payload est chiffré (SmsEncryption - AES-256-GCM)
 *   3. Le SMS chiffré est envoyé au numéro de la ligne de réception MGP
 *   4. Le serveur SMS reçoit et transfère à mgp-office via webhook sécurisé
 *      (spatie/laravel-webhook-client)
 *   5. mgp-office crée la plainte avec statut "sms_pending"
 *   6. À la prochaine synchronisation, l'app complète la soumission
 *
 * IMPORTANT : Les données VBG dans le payload SMS sont anonymisées
 * (pas d'identifiant survivant(e), pas de description des faits).
 */
class SmsSubmissionService : Service() {

    override fun onBind(intent: Intent?): IBinder? = null

    override fun onStartCommand(intent: Intent?, flags: Int, startId: Int): Int {
        val payload        = intent?.getStringExtra(EXTRA_PAYLOAD) ?: return START_NOT_STICKY
        val encryptionKey  = intent.getByteArrayExtra(EXTRA_KEY)   ?: return START_NOT_STICKY
        val destinationNum = intent.getStringExtra(EXTRA_DEST)     ?: return START_NOT_STICKY

        try {
            val encrypted = SmsEncryption.encrypt(payload, encryptionKey)
            sendSms(destinationNum, encrypted)
            Timber.i("SMS soumission envoyée vers $destinationNum (${encrypted.length} chars)")
        } catch (e: Exception) {
            Timber.e(e, "Erreur soumission SMS")
        }
        stopSelf(startId)
        return START_NOT_STICKY
    }

    private fun sendSms(destination: String, message: String) {
        val smsManager = getSystemService(SmsManager::class.java)
        // Découper si > 160 caractères (multi-SMS)
        if (message.length <= 160) {
            smsManager.sendTextMessage(destination, null, message, null, null)
        } else {
            val parts = smsManager.divideMessage(message)
            smsManager.sendMultipartTextMessage(destination, null, parts, null, null)
        }
    }

    companion object {
        const val EXTRA_PAYLOAD = "payload"
        const val EXTRA_KEY     = "key"
        const val EXTRA_DEST    = "destination"

        fun start(context: Context, payload: String, keyBytes: ByteArray, destination: String) {
            val intent = Intent(context, SmsSubmissionService::class.java).apply {
                putExtra(EXTRA_PAYLOAD, payload)
                putExtra(EXTRA_KEY,     keyBytes)
                putExtra(EXTRA_DEST,    destination)
            }
            context.startService(intent)
        }
    }
}
