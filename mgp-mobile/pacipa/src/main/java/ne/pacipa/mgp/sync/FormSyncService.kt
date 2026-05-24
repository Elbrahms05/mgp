package ne.pacipa.mgp.sync

import android.app.IntentService
import android.content.Context
import android.content.Intent
import androidx.work.Constraints
import androidx.work.ExistingPeriodicWorkPolicy
import androidx.work.NetworkType
import androidx.work.PeriodicWorkRequestBuilder
import androidx.work.WorkManager
import timber.log.Timber
import java.util.concurrent.TimeUnit

/**
 * Planificateur de synchronisation périodique (toutes les 6h si réseau disponible).
 *
 * À appeler au démarrage de l'application et après configuration du serveur.
 * Complète le déclenchement par NetworkChangeReceiver (événementiel).
 */
class FormSyncService : IntentService("FormSyncService") {

    @Deprecated("Use schedulePeriodicSync instead")
    override fun onHandleIntent(intent: Intent?) {
        Timber.d("FormSyncService démarré")
    }

    companion object {

        fun schedulePeriodicSync(context: Context) {
            val constraints = Constraints.Builder()
                .setRequiredNetworkType(NetworkType.CONNECTED)
                .build()

            val periodicRequest = PeriodicWorkRequestBuilder<FormSyncWorker>(
                repeatInterval = 6,
                repeatIntervalTimeUnit = TimeUnit.HOURS
            )
                .setConstraints(constraints)
                .build()

            WorkManager.getInstance(context).enqueueUniquePeriodicWork(
                "${FormSyncWorker.WORK_NAME}_periodic",
                ExistingPeriodicWorkPolicy.KEEP,
                periodicRequest
            )
            Timber.i("Sync périodique PACIPA planifiée (toutes les 6h)")
        }

        fun cancelPeriodicSync(context: Context) {
            WorkManager.getInstance(context)
                .cancelUniqueWork("${FormSyncWorker.WORK_NAME}_periodic")
        }
    }
}
