<?php

declare(strict_types=1);

namespace App\Http\Controllers\Form;

use App\Domains\Form\Models\Form;
use App\Domains\OdkBridge\Services\OdkApiClient;
use App\Http\Controllers\Controller;
use App\Http\Requests\Form\StoreFormRequest;
use App\Http\Requests\Form\UpdateFormRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

use function Psy\debug;

class FormController extends Controller
{
    /** Formulaires F01–F07 définis dans le MGP */
    private const FORM_REFERENCES = [
        'F01' => 'F01 - Dépôt de plainte (Annexe 4)',
        'F02' => 'F02 - Plainte non sensible (Annexe 8)',
        'F03' => 'F03 - Rapport VBG/EAS/HS (Annexe 9)',
        'F04' => 'F04 - PV de conciliation (Annexe 5)',
        'F05' => 'F05 - Suivi périodique (Annexe 6)',
        'F06' => 'F06 - PV installation comité (Annexe 3)',
        'F07' => 'F07 - Évaluation trimestrielle (Annexe 10)',
    ];

    public function __construct(
        private readonly OdkApiClient $odk,
    ) {}

    public function index(): View
{
    $forms = Form::with('versions')
        ->orderBy('name')
        ->get();

    $formRefs = self::FORM_REFERENCES;

    $odkForms = [];
    $odkError = null;

    try {
        $odkFormsRaw = collect($this->odk->listForms());

        // 🔥 ENRICHIR CHAQUE FORM
        $odkFormsRaw = $odkFormsRaw->map(function ($odkForm) {

            $xmlFormId = $odkForm['xmlFormId'];

            try {
                $cacheKey = "odk_submissions_count_{$xmlFormId}";

                $odkForm['submissionsCount'] = cache()->remember(
                    $cacheKey,
                    now()->addMinutes(5),
                    function () use ($xmlFormId) {
                        return $this->odk->getSubmissionsCount($xmlFormId);
                    }
                );

            } catch (\Throwable $e) {
                $odkForm['submissionsCount'] = 0;

                Log::warning('ODK count failed', [
                    'xmlFormId' => $xmlFormId,
                    'error' => $e->getMessage(),
                ]);
            }

            return $odkForm;
        });

        // 🔥 INDEXATION PAR xmlFormId (ton code est OK)
        $odkForms = $odkFormsRaw
            ->keyBy('xmlFormId')
            ->toArray();

    } catch (\Throwable $e) {
        $odkError = $e->getMessage();

        Log::warning('ODK Central inaccessible', [
            'error' => $e->getMessage(),
        ]);
    }

    return view('pages.forms.index', compact(
        'forms',
        'formRefs',
        'odkForms',
        'odkError'
    ));
}

    public function create(): View
    {
        $odkForms = [];
        $odkError = null;

        try {
            $odkForms = $this->odk->listForms();
        } catch (\Throwable $e) {
            $odkError = $e->getMessage();
            Log::warning('FormController::create - ODK Central inaccessible', ['error' => $e->getMessage()]);
        }

        // IDs déjà enregistrés pour les exclure de la liste
        $registeredIds = Form::pluck('form_id')->all();

        return view('pages.forms.create', compact('odkForms', 'odkError', 'registeredIds'));
    }

    public function store(StoreFormRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Initialiser la version courante depuis ODK Central (non bloquant)
        $currentVersion = '1.0';
        try {
            $odkDetails     = $this->odk->getFormDetails($data['form_id']);
            $currentVersion = $odkDetails['version'] ?? '1.0';
        } catch (\Throwable) {
            // Non bloquant - valeur par défaut
        }

        $data['current_version'] = $currentVersion;
        $form = Form::create($data);

        return redirect()
            ->route('forms.show', $form)
            ->with('success', "Formulaire \"{$form->name}\" créé.");
    }

    public function show(Form $form): View
    {
        $form->load('versions.createdBy');
        $formRefs = self::FORM_REFERENCES;

        // Détails ODK Central en temps réel (non bloquant)
        $odkDetails  = null;
        $odkVersions = [];
        $odkError    = null;
        $submissionsCount = null;
        $submissionsCountError = null;
        try {
            $odkDetails  = $this->odk->getFormDetails($form->form_id);
            $odkVersions = $this->odk->getFormVersions($form->form_id);
            $submissionsCount = $this->odk->getSubmissionsCount($form->form_id);
        } catch (\Throwable $e) {
            $odkError = $e->getMessage();
            Log::info("FormController::show - ODK Central inaccessible pour {$form->form_id}", [
                'error' => $e->getMessage(),
            ]);
        }

        Log::info('FormController::show - ODK details', [
            'form_id' => $form->form_id,
            'odkDetails' => $odkDetails,
            'odkVersionsCount' => count($odkVersions),
            'odkError' => $odkError,
            'submissionsCount' => $submissionsCount ?? null,
        ]);
        return view('pages.forms.show', compact('form', 'formRefs', 'odkDetails', 'odkVersions', 'odkError','submissionsCount'));
    }

    public function edit(Form $form): View
    {
        $formRefs = self::FORM_REFERENCES;

        return view('pages.forms.edit', compact('form', 'formRefs'));
    }

    public function update(UpdateFormRequest $request, Form $form): RedirectResponse
    {
        $form->update($request->validated());

        return redirect()
            ->route('forms.show', $form)
            ->with('success', "Formulaire \"{$form->name}\" mis à jour.");
    }

}
