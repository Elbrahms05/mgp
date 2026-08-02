<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Distribution des formulaires XForm (F01–F07) à l'app mobile (EF-01 / ENF-01).
 *
 * Les XForms servis sont ceux de `mgp-office/forms/`. Le mobile (FormSyncService) compare
 * le `version` (hash) pour ne télécharger que les formulaires nouveaux ou modifiés.
 * Le formulaire VBG (F03) n'est distribué qu'aux agents habilités (`vbg.receive`) — le
 * cloisonnement du circuit sensible commence dès la distribution (ER-02).
 */
class FormController extends Controller
{
    /**
     * Catalogue des formulaires : code → [fichier, titre, sensible].
     *
     * @var array<string, array{file: string, title: string, sensitive: bool}>
     */
    private const FORMS = [
        'F01' => ['file' => 'pacipa_f01.xml',     'title' => 'Dépôt de plainte',              'sensitive' => false],
        'F02' => ['file' => 'pacipa_f02.xml',     'title' => 'Instruction plainte non sensible', 'sensitive' => false],
        'F03' => ['file' => 'pacipa_f03_vbg.xml', 'title' => 'Signalement VBG',                'sensitive' => true],
        'F04' => ['file' => 'pacipa_f04.xml',     'title' => 'Plainte travailleur',            'sensitive' => false],
        'F05' => ['file' => 'pacipa_f05.xml',     'title' => 'Procès-verbal de médiation',     'sensitive' => false],
        'F06' => ['file' => 'pacipa_f06.xml',     'title' => 'Enquête de satisfaction',        'sensitive' => false],
        'F07' => ['file' => 'pacipa_f07.xml',     'title' => 'Suivi / clôture',                'sensitive' => false],
    ];

    /**
     * Liste les formulaires accessibles à l'agent, avec version (hash) et URL de téléchargement.
     */
    public function index(Request $request): JsonResponse
    {
        $canVbg = $request->user()->can('vbg.receive');

        $forms = [];

        foreach (self::FORMS as $code => $meta) {
            if ($meta['sensitive'] && ! $canVbg) {
                continue;
            }

            $path = $this->path($meta['file']);

            if (! is_file($path)) {
                continue;
            }

            $forms[] = [
                'code'         => $code,
                'title'        => $meta['title'],
                'sensitive'    => $meta['sensitive'],
                'version'      => md5_file($path),
                'size'         => filesize($path),
                'download_url' => route('api.mobile.forms.show', ['code' => $code]),
            ];
        }

        return response()->json(['forms' => $forms]);
    }

    /**
     * Télécharge le XML d'un formulaire donné.
     */
    public function show(Request $request, string $code): BinaryFileResponse|JsonResponse
    {
        $code = strtoupper($code);
        $meta = self::FORMS[$code] ?? null;

        if ($meta === null) {
            return response()->json(['message' => 'Formulaire inconnu.'], 404);
        }

        if ($meta['sensitive'] && ! $request->user()->can('vbg.receive')) {
            return response()->json(['message' => 'Accès non autorisé à ce formulaire.'], 403);
        }

        $path = $this->path($meta['file']);

        if (! is_file($path)) {
            return response()->json(['message' => 'Formulaire indisponible.'], 404);
        }

        return response()->download($path, $meta['file'], ['Content-Type' => 'application/xml']);
    }

    private function path(string $file): string
    {
        return base_path('forms/' . $file);
    }
}
