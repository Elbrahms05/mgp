<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Changement de locale
|--------------------------------------------------------------------------
*/
Route::get('/locale/{locale}', function (string $locale) {
    $supported = ['fr', 'ha', 'zr', 'tq', 'kr', 'ff'];
    if (in_array($locale, $supported, strict: true)) {
        session(['locale' => $locale]);
        if (auth()->check()) {
            auth()->user()->update(['locale' => $locale]);
        }
    }
    return back();
})->name('locale.set')->middleware('web');

/*
|--------------------------------------------------------------------------
| Routes authentifiées
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'enforce.2fa'])->group(function () {

    // Dashboard
    Route::get('/', DashboardController::class)->name('dashboard');

    /*
    |----------------------------------------------------------------------
    | Sites PACIPA
    |----------------------------------------------------------------------
    */
    Route::middleware('permission:sites.view_own_region')->prefix('sites')->name('sites.')->group(function () {
        Route::get('/',         [\App\Http\Controllers\Site\SiteController::class, 'index'])->name('index');
        Route::get('/map',      [\App\Http\Controllers\Site\SiteController::class, 'map'])->name('map');
        Route::get('/map/data', [\App\Http\Controllers\Site\SiteController::class, 'mapData'])->name('map.data');

        // Routes statiques avant les routes paramétrées
        Route::middleware('permission:sites.create')->group(function () {
            Route::get('/create', [\App\Http\Controllers\Site\SiteController::class, 'create'])->name('create');
            Route::post('/',      [\App\Http\Controllers\Site\SiteController::class, 'store'])->name('store');
        });

        Route::get('/{site}', [\App\Http\Controllers\Site\SiteController::class, 'show'])->name('show');

        Route::middleware('permission:sites.update')->group(function () {
            Route::get('/{site}/edit', [\App\Http\Controllers\Site\SiteController::class, 'edit'])->name('edit');
            Route::put('/{site}',      [\App\Http\Controllers\Site\SiteController::class, 'update'])->name('update');
        });

        Route::middleware('permission:sites.delete')->group(function () {
            Route::delete('/{site}', [\App\Http\Controllers\Site\SiteController::class, 'destroy'])->name('destroy');
        });
    });

    /*
    |----------------------------------------------------------------------
    | Plaintes non sensibles
    |----------------------------------------------------------------------
    */
    Route::middleware('permission:complaints.view_own_region')->prefix('complaints')->name('complaints.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Complaint\ComplaintController::class, 'index'])->name('index');

        // Routes statiques avant les routes paramétrées
        // Saisie différée d'une fiche papier F01 (EF-11)
        Route::middleware('permission:complaints.create')->group(function () {
            Route::get('/differe/nouveau',  [\App\Http\Controllers\Complaint\DeferredComplaintController::class, 'create'])->name('deferred.create');
            Route::post('/differe',         [\App\Http\Controllers\Complaint\DeferredComplaintController::class, 'store'])->name('deferred.store');
        });

        Route::middleware('permission:complaints.create')->group(function () {
            Route::get('/create', [\App\Http\Controllers\Complaint\ComplaintController::class, 'create'])->name('create');
            Route::post('/',      [\App\Http\Controllers\Complaint\ComplaintController::class, 'store'])->name('store');
        });

        Route::middleware('permission:complaints.export')->group(function () {
            Route::get('/export', [\App\Http\Controllers\Complaint\ComplaintController::class, 'export'])->name('export');
        });

        Route::get('/{complaint}', [\App\Http\Controllers\Complaint\ComplaintController::class, 'show'])->name('show');

        // Accusé de réception PDF imprimable (EF-06)
        Route::get('/{complaint}/accuse', [\App\Http\Controllers\Complaint\ComplaintController::class, 'acknowledgment'])->name('acknowledgment');

        // Enquête de satisfaction à la clôture (EF-08)
        Route::get('/{complaint}/satisfaction',  [\App\Http\Controllers\Complaint\SatisfactionSurveyController::class, 'create'])->name('satisfaction.create');
        Route::post('/{complaint}/satisfaction', [\App\Http\Controllers\Complaint\SatisfactionSurveyController::class, 'store'])->name('satisfaction.store');

        Route::middleware('permission:complaints.edit')->group(function () {
            Route::get('/{complaint}/edit', [\App\Http\Controllers\Complaint\ComplaintController::class, 'edit'])->name('edit');
            Route::put('/{complaint}',      [\App\Http\Controllers\Complaint\ComplaintController::class, 'update'])->name('update');
        });

        Route::middleware('permission:complaints.escalate')->group(function () {
            Route::get('/{complaint}/escalate',  [\App\Http\Controllers\Complaint\EscalationController::class, 'create'])->name('escalate');
            Route::post('/{complaint}/escalate', [\App\Http\Controllers\Complaint\EscalationController::class, 'store'])->name('escalate.store');
        });

        // Actions workflow (sans permission séparée - contrôlées dans le controller)
        Route::post('/{complaint}/accept',    [\App\Http\Controllers\Complaint\ComplaintController::class, 'accept'])->name('accept');
        Route::post('/{complaint}/reject',    [\App\Http\Controllers\Complaint\ComplaintController::class, 'reject'])->name('reject');
        Route::post('/{complaint}/mediation', [\App\Http\Controllers\Complaint\ComplaintController::class, 'mediation'])->name('mediation');
        Route::post('/{complaint}/resolve',   [\App\Http\Controllers\Complaint\ComplaintController::class, 'resolve'])->name('resolve');
        Route::post('/{complaint}/close',     [\App\Http\Controllers\Complaint\ComplaintController::class, 'close'])->name('close');
    });

    /*
    |----------------------------------------------------------------------
    | VBG / EAS / HS - accès restreint
    |----------------------------------------------------------------------
    */
    Route::middleware(['permission:vbg.receive', 'vbg.access'])->prefix('vbg')->name('vbg.')->group(function () {
        Route::get('/',         [\App\Http\Controllers\Vbg\VbgReportController::class, 'index'])->name('index');
        Route::get('/create',   [\App\Http\Controllers\Vbg\VbgReportController::class, 'create'])->name('create');
        Route::post('/',        [\App\Http\Controllers\Vbg\VbgReportController::class, 'store'])->name('store');
        Route::get('/{report}', [\App\Http\Controllers\Vbg\VbgReportController::class, 'show'])->name('show');

        Route::get('/{report}/referral',  [\App\Http\Controllers\Vbg\ReferralController::class, 'create'])->name('referral');
        Route::post('/{report}/referral', [\App\Http\Controllers\Vbg\ReferralController::class, 'store'])->name('referral.store');

        Route::middleware('permission:vbg.manage_enquiry')->group(function () {
            Route::get('/{report}/enquiry',  [\App\Http\Controllers\Vbg\EnquiryController::class, 'create'])->name('enquiry');
            Route::post('/{report}/enquiry', [\App\Http\Controllers\Vbg\EnquiryController::class, 'store'])->name('enquiry.store');
        });

        // Registre de formation / recyclage des personnes de confiance (2.5)
        Route::get('/personnes-confiance/formations',  [\App\Http\Controllers\Vbg\TrustedPersonTrainingController::class, 'index'])->name('trusted-person.trainings');
        Route::post('/personnes-confiance/formations', [\App\Http\Controllers\Vbg\TrustedPersonTrainingController::class, 'store'])->name('trusted-person.trainings.store');
    });

    /*
    |----------------------------------------------------------------------
    | Comités
    |----------------------------------------------------------------------
    */
    Route::middleware('permission:committees.view')->prefix('committees')->name('committees.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Committee\CommitteeController::class, 'index'])->name('index');

        // Routes statiques avant les routes paramétrées
        Route::middleware('permission:committees.manage')->group(function () {
            Route::get('/create', [\App\Http\Controllers\Committee\CommitteeController::class, 'create'])->name('create');
            Route::post('/',      [\App\Http\Controllers\Committee\CommitteeController::class, 'store'])->name('store');
        });

        Route::get('/{committee}', [\App\Http\Controllers\Committee\CommitteeController::class, 'show'])->name('show');

        Route::middleware('permission:committees.manage')->group(function () {
            Route::get('/{committee}/edit', [\App\Http\Controllers\Committee\CommitteeController::class, 'edit'])->name('edit');
            Route::put('/{committee}',      [\App\Http\Controllers\Committee\CommitteeController::class, 'update'])->name('update');
        });

        Route::middleware('permission:committees.evaluate')->group(function () {
            Route::get('/{committee}/evaluate',  [\App\Http\Controllers\Committee\CommitteeController::class, 'evaluate'])->name('evaluate');
            Route::post('/{committee}/evaluate', [\App\Http\Controllers\Committee\CommitteeController::class, 'storeEvaluation'])->name('evaluate.store');
        });
    });

    /*
    |----------------------------------------------------------------------
    | Plaintes travailleurs
    |----------------------------------------------------------------------
    */
    Route::middleware('permission:workers.view')->prefix('workers')->name('workers.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Worker\WorkerComplaintController::class, 'index'])->name('index');

        // Routes statiques avant les routes paramétrées
        Route::middleware('permission:workers.manage')->group(function () {
            Route::get('/create', [\App\Http\Controllers\Worker\WorkerComplaintController::class, 'create'])->name('create');
            Route::post('/',      [\App\Http\Controllers\Worker\WorkerComplaintController::class, 'store'])->name('store');
        });

        Route::get('/{worker}', [\App\Http\Controllers\Worker\WorkerComplaintController::class, 'show'])->name('show');

        Route::middleware('permission:workers.manage')->group(function () {
            Route::get('/{worker}/edit',      [\App\Http\Controllers\Worker\WorkerComplaintController::class, 'edit'])->name('edit');
            Route::put('/{worker}',           [\App\Http\Controllers\Worker\WorkerComplaintController::class, 'update'])->name('update');
            Route::post('/{worker}/transition', [\App\Http\Controllers\Worker\WorkerComplaintController::class, 'transition'])->name('transition');
        });
    });

    /*
    |----------------------------------------------------------------------
    | Médiation
    |----------------------------------------------------------------------
    */
    Route::middleware('permission:mediation.view')->prefix('mediation')->name('mediation.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Mediation\MediationController::class, 'index'])->name('index');

        // Routes statiques avant les routes paramétrées
        Route::middleware('permission:mediation.create')->group(function () {
            Route::get('/create', [\App\Http\Controllers\Mediation\MediationController::class, 'create'])->name('create');
            Route::post('/',      [\App\Http\Controllers\Mediation\MediationController::class, 'store'])->name('store');
        });

        Route::get('/{mediation}', [\App\Http\Controllers\Mediation\MediationController::class, 'show'])->name('show');
    });

    /*
    |----------------------------------------------------------------------
    | Statistiques
    |----------------------------------------------------------------------
    */
    Route::middleware('permission:statistics.view')->prefix('statistics')->name('statistics.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Statistic\StatisticController::class, 'dashboard'])->name('dashboard');
    });

    /*
    |----------------------------------------------------------------------
    | Rapports
    |----------------------------------------------------------------------
    */
    Route::middleware('permission:reports.generate')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/',          [\App\Http\Controllers\Report\ReportController::class, 'index'])->name('index');
        Route::post('/generate', [\App\Http\Controllers\Report\ReportController::class, 'generate'])->name('generate');

        Route::middleware('permission:reports.export')->group(function () {
            Route::get('/{report}/download', [\App\Http\Controllers\Report\ReportController::class, 'download'])->name('download');
        });
    });

    /*
    |----------------------------------------------------------------------
    | Formulaires ODK
    |----------------------------------------------------------------------
    */
    Route::middleware('permission:forms.view')->prefix('forms')->name('forms.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Form\FormController::class, 'index'])->name('index');

        // Routes statiques avant les routes paramétrées
        Route::middleware('permission:forms.manage')->group(function () {
            Route::get('/create', [\App\Http\Controllers\Form\FormController::class, 'create'])->name('create');
            Route::post('/',      [\App\Http\Controllers\Form\FormController::class, 'store'])->name('store');
        });

        Route::get('/{form}', [\App\Http\Controllers\Form\FormController::class, 'show'])->name('show');

        Route::middleware('permission:forms.manage')->group(function () {
            Route::get('/{form}/edit', [\App\Http\Controllers\Form\FormController::class, 'edit'])->name('edit');
            Route::put('/{form}',      [\App\Http\Controllers\Form\FormController::class, 'update'])->name('update');
        });
    });

    /*
    |----------------------------------------------------------------------
    | Communication
    |----------------------------------------------------------------------
    */
    Route::middleware('permission:communication.view')->prefix('communication')->name('communication.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Communication\CommunicationController::class, 'index'])->name('index');

        // Routes statiques avant les routes paramétrées
        Route::middleware('permission:communication.manage')->group(function () {
            Route::get('/create', [\App\Http\Controllers\Communication\CommunicationController::class, 'create'])->name('create');
            Route::post('/',      [\App\Http\Controllers\Communication\CommunicationController::class, 'store'])->name('store');
        });

        Route::get('/{campaign}', [\App\Http\Controllers\Communication\CommunicationController::class, 'show'])->name('show');

        Route::middleware('permission:communication.manage')->group(function () {
            Route::get('/{campaign}/edit', [\App\Http\Controllers\Communication\CommunicationController::class, 'edit'])->name('edit');
            Route::put('/{campaign}',      [\App\Http\Controllers\Communication\CommunicationController::class, 'update'])->name('update');
        });
    });

    /*
    |----------------------------------------------------------------------
    | Administration - super_admin uniquement
    |----------------------------------------------------------------------
    */
    Route::middleware('role:super_admin')->group(function () {

        // Utilisateurs
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/',              [\App\Http\Controllers\User\UserController::class, 'index'])->name('index');
            Route::get('/create',        [\App\Http\Controllers\User\UserController::class, 'create'])->name('create');
            Route::post('/',             [\App\Http\Controllers\User\UserController::class, 'store'])->name('store');
            Route::get('/{user}',        [\App\Http\Controllers\User\UserController::class, 'show'])->name('show');
            Route::get('/{user}/edit',   [\App\Http\Controllers\User\UserController::class, 'edit'])->name('edit');
            Route::put('/{user}',        [\App\Http\Controllers\User\UserController::class, 'update'])->name('update');
            Route::delete('/{user}',     [\App\Http\Controllers\User\UserController::class, 'destroy'])->name('destroy');
        });

        // Rôles
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [\App\Http\Controllers\User\UserController::class, 'roles'])->name('index');
        });

        // Paramétrage
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/',          [\App\Http\Controllers\Setting\SettingController::class, 'index'])->name('index');
            Route::put('/project',   [\App\Http\Controllers\Setting\SettingController::class, 'updateProject'])->name('project.update');
            Route::put('/sla',       [\App\Http\Controllers\Setting\SettingController::class, 'updateSla'])->name('sla.update');
            Route::put('/sms',       [\App\Http\Controllers\Setting\SettingController::class, 'updateSms'])->name('sms.update');
            Route::put('/odk',       [\App\Http\Controllers\Setting\SettingController::class, 'updateOdk'])->name('odk.update');
            Route::put('/security',  [\App\Http\Controllers\Setting\SettingController::class, 'updateSecurity'])->name('security.update');
        });

        // Synchronisation ODK manuelle (topbar)
        Route::post('/odk/sync', \App\Http\Controllers\OdkBridge\OdkManualSyncController::class)
            ->middleware('permission:forms.manage')
            ->name('odk.manual-sync');

        // Documentation / guides
        Route::prefix('documentation')->name('documentation.')->group(function () {
            Route::get('/',     [\App\Http\Controllers\Documentation\PageGuideController::class, 'index'])->name('index');
            // Routes statiques avant les routes paramétrées
            Route::get('/create', [\App\Http\Controllers\Documentation\PageGuideController::class, 'create'])->name('create');
            Route::post('/',      [\App\Http\Controllers\Documentation\PageGuideController::class, 'store'])->name('store');
            Route::get('/{guide}/edit', [\App\Http\Controllers\Documentation\PageGuideController::class, 'edit'])->name('edit');
            Route::put('/{guide}',      [\App\Http\Controllers\Documentation\PageGuideController::class, 'update'])->name('update');
            Route::delete('/{guide}',   [\App\Http\Controllers\Documentation\PageGuideController::class, 'destroy'])->name('destroy');
        });

        // Entreprises
        Route::middleware('permission:companies.manage')->prefix('companies')->name('companies.')->group(function () {
            Route::get('/',                        [\App\Http\Controllers\Company\CompanyController::class, 'index'])->name('index');
            Route::get('/create',                  [\App\Http\Controllers\Company\CompanyController::class, 'create'])->name('create');
            Route::post('/',                       [\App\Http\Controllers\Company\CompanyController::class, 'store'])->name('store');
            Route::get('/{company}',               [\App\Http\Controllers\Company\CompanyController::class, 'show'])->name('show');
            Route::get('/{company}/edit',          [\App\Http\Controllers\Company\CompanyController::class, 'edit'])->name('edit');
            Route::put('/{company}',               [\App\Http\Controllers\Company\CompanyController::class, 'update'])->name('update');
            Route::delete('/{company}',            [\App\Http\Controllers\Company\CompanyController::class, 'destroy'])->name('destroy');
            // Attach/detach sites
            Route::post('/{company}/sites/attach', [\App\Http\Controllers\Company\CompanyController::class, 'attachSite'])->name('sites.attach');
            Route::delete('/{company}/sites/{site}', [\App\Http\Controllers\Company\CompanyController::class, 'detachSite'])->name('sites.detach');
            Route::put('/{company}/sites/{site}/responsible', [\App\Http\Controllers\Company\CompanyController::class, 'updateResponsible'])->name('sites.responsible.update');
        });
    });

    // Audit
    Route::middleware('permission:audit.view')->prefix('audit')->name('audit.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Audit\AuditController::class, 'index'])->name('index');
    });

    // Géographie
    Route::middleware('permission:settings.view')->prefix('geography')->name('geography.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Setting\SettingController::class, 'geography'])->name('index');
    });

    // Profil Breeze
    Route::get('/profile',    [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Webhooks VBG (sans auth standard - vérification HMAC)
|--------------------------------------------------------------------------
*/
Route::prefix('webhooks/vbg')->name('webhooks.vbg.')->group(function () {
    Route::post('/ussd',     [\App\Http\Controllers\Vbg\DirectReport\UssdWebhookController::class, '__invoke'])->name('ussd');
    Route::post('/sms',      [\App\Http\Controllers\Vbg\DirectReport\SmsWebhookController::class, '__invoke'])->name('sms');
    // WhatsApp : GET pour la vérification Meta, POST pour les messages entrants
    Route::match(['GET', 'POST'], '/whatsapp', [\App\Http\Controllers\Vbg\DirectReport\WhatsAppWebhookController::class, '__invoke'])->name('whatsapp');
});

/*
| Webhook soumissions SMS (passerelle → back-office) — update.md 1.4 (option A).
| Signature HMAC-SHA256 vérifiée par spatie/laravel-webhook-client (config « sms »),
| idempotence et création de plainte gérées par ProcessSmsWebhookJob.
*/
Route::webhooks('webhooks/sms', 'sms');

// Canal personne de confiance (token Sanctum)
Route::middleware(['auth:sanctum', 'permission:vbg.receive_direct'])
    ->prefix('api/vbg/trusted-person')
    ->name('api.vbg.trusted-person.')
    ->group(function () {
        Route::post('/',       [\App\Http\Controllers\Vbg\DirectReport\TrustedPersonController::class, 'store'])->name('store');
        Route::get('/services',[\App\Http\Controllers\Vbg\DirectReport\TrustedPersonController::class, 'services'])->name('services');
    });

/*
|--------------------------------------------------------------------------
| API géographie cascade (interne, auth requise)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('api/geography')->name('api.geography.')->group(function () {
    Route::get('/departments', function () {
        return \App\Domains\Geography\Models\Department::where('region_id', request('region_id'))
            ->orderBy('name')->get(['id', 'name']);
    })->name('departments');

    Route::get('/communes', function () {
        return \App\Domains\Geography\Models\Commune::where('department_id', request('department_id'))
            ->orderBy('name')->get(['id', 'name']);
    })->name('communes');

    Route::get('/localities', function () {
        return \App\Domains\Geography\Models\Locality::where('commune_id', request('commune_id'))
            ->orderBy('name')->get(['id', 'name']);
    })->name('localities');

    Route::get('/sites', function () {
        return \App\Domains\Site\Models\Site::where('locality_id', request('locality_id'))
            ->where('status', '!=', 'closed')
            ->orderBy('name')->get(['id', 'code', 'name']);
    })->name('sites');
});

/*
|--------------------------------------------------------------------------
| Webhook ODK Central (soumissions entrantes depuis l'application mobile)
| Auth : signature HMAC-SHA256 (X-ODK-Signature header)
|--------------------------------------------------------------------------
*/
Route::prefix('webhooks/odk')->name('webhooks.odk.')->group(function () {
    Route::post('/submission', [\App\Http\Controllers\OdkBridge\OdkWebhookController::class, 'submission'])
        ->name('submission');
});
