package ne.pacipa.mgp.receivers

import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.net.ConnectivityManager
import android.net.NetworkCapabilities
import androidx.work.Constraints
import androidx.work.ExistingWorkPolicy
import androidx.work.NetworkType
import androidx.work.OneTimeWorkRequestBuilder
import androidx.work.WorkManager
import ne.pacipa.mgp.sync.FormSyncWorker
import timber.log.Timber

/**
 * Détecte le retour de connexion réseau et déclenche la synchronisation automatique :
 *  - Upload des soumissions en attente (file Queue Room)
 *  - Téléchargement des nouvelles versions de formulaires depuis mgp-office
 *
 * Enregistré dans le Manifest pour être notifié même si l'app est fermée.
 */
class NetworkChangeReceiver : BroadcastReceiver() {

    override fun onReceive(context: Context, intent: Intent) {
        val action = intent.action ?: return

        if (action == Intent.ACTION_BOOT_COMPLETED || isNetworkAvailable(context)) {
            Timber.d("Réseau disponible - déclenchement sync PACIPA")
            scheduleSyncWork(context)
        }
    }

    private fun isNetworkAvailable(context: Context): Boolean {
        val cm = context.getSystemService(ConnectivityManager::class.java) ?: return false
        val network     = cm.activeNetwork ?: return false
        val capabilities = cm.getNetworkCapabilities(network) ?: return false
        return capabilities.hasCapability(NetworkCapabilities.NET_CAPABILITY_INTERNET)
    }

    private fun scheduleSyncWork(context: Context) {
        val constraints = Constraints.Builder()
            .setRequiredNetworkType(NetworkType.CONNECTED)
            .build()

        val syncRequest = OneTimeWorkRequestBuilder<FormSyncWorker>()
            .setConstraints(constraints)
            .build()

        WorkManager.getInstance(context)
            .enqueueUniqueWork(
                FormSyncWorker.WORK_NAME,
                ExistingWorkPolicy.KEEP,   // Ne relance pas si déjà en cours
                syncRequest
            )
    }
}
