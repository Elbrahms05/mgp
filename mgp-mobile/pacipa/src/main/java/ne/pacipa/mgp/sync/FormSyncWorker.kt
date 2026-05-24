package ne.pacipa.mgp.sync

import android.content.Context
import androidx.work.CoroutineWorker
import androidx.work.WorkerParameters
import okhttp3.OkHttpClient
import okhttp3.Request
import org.json.JSONArray
import timber.log.Timber

/**
 * Worker de synchronisation PACIPA-MGP.
 *
 * Deux opérations séquentielles :
 *   1. Upload des soumissions en attente dans la queue Room
 *   2. Téléchargement des mises à jour de formulaires F01-F07 depuis mgp-office API
 *
 * Déclenché par :
 *   - NetworkChangeReceiver (retour connexion)
 *   - PeriodicWorkRequest (toutes les 6h si réseau disponible)
 *   - Action manuelle utilisateur
 *
 * L'authentification se fait via le token API généré par mgp-office
 * (endpoint POST /api/mobile/auth → token Bearer 24h).
 */
class FormSyncWorker(
    context: Context,
    params: WorkerParameters
) : CoroutineWorker(context, params) {

    private val http = OkHttpClient()

    override suspend fun doWork(): Result {
        val prefs      = applicationContext.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
        val serverUrl  = prefs.getString(KEY_SERVER_URL, null)  ?: return Result.failure()
        val bearerToken = prefs.getString(KEY_TOKEN, null)       ?: return Result.failure()

        return try {
            uploadPendingSubmissions(serverUrl, bearerToken)
            downloadFormUpdates(serverUrl, bearerToken)
            Timber.i("Sync PACIPA terminée avec succès")
            Result.success()
        } catch (e: Exception) {
            Timber.e(e, "Erreur sync PACIPA")
            Result.retry()
        }
    }

    // ── Upload ──────────────────────────────────────────────────────────────

    private fun uploadPendingSubmissions(serverUrl: String, token: String) {
        // TODO: lire la queue Room (SyncQueueDao) et poster chaque soumission
        // POST {serverUrl}/api/mobile/submissions avec le JSON de la soumission
        // En cas de succès : marquer comme syncronisée dans Room
        // En cas d'erreur 4xx : marquer comme erreur (ne pas retenter)
        // En cas d'erreur 5xx/réseau : laisser en queue (retry automatique)
        Timber.d("Upload soumissions en attente...")
    }

    // ── Download formulaires ─────────────────────────────────────────────────

    private fun downloadFormUpdates(serverUrl: String, token: String) {
        val request = Request.Builder()
            .url("$serverUrl/api/mobile/forms")
            .header("Authorization", "Bearer $token")
            .header("Accept", "application/json")
            .build()

        http.newCall(request).execute().use { response ->
            if (!response.isSuccessful) {
                Timber.w("Téléchargement formulaires : HTTP ${response.code}")
                return
            }
            val body = response.body?.string() ?: return
            val forms = JSONArray(body)
            Timber.i("${forms.length()} formulaire(s) disponibles sur mgp-office")
            // TODO: pour chaque formulaire dont la version diffère du cache local,
            //       télécharger le XForm XML et l'enregistrer dans le stockage ODK Collect.
        }
    }

    companion object {
        const val WORK_NAME    = "pacipa_form_sync"
        const val PREFS_NAME   = "pacipa_settings"
        const val KEY_SERVER_URL = "server_url"
        const val KEY_TOKEN    = "bearer_token"
    }
}
