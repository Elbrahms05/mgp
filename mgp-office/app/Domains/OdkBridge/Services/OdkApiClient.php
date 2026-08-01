<?php

declare(strict_types=1);

namespace App\Domains\OdkBridge\Services;

use App\Settings\OdkSettings;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Client HTTP vers ODK Central / KoboToolbox / ODK Aggregate.
 *
 * Endpoints utilisés :
 *  POST /v1/sessions                                     → authentification ODK Central (email+password → token 24h)
 *  GET  /v1/projects/{id}/forms                          → liste des formulaires
 *  GET  /v1/projects/{id}/forms/{fid}/submissions        → liste soumissions (JSON)
 *  GET  /v1/projects/{id}/forms/{fid}/submissions/{iid}  → une soumission XML
 *  POST /v1/projects/{id}/forms                          → publier un formulaire XForm
 *  POST /v1/projects/{id}/forms/{fid}/submissions        → envoyer une soumission
 *
 * Auth :
 *  - ODK Central    : token de session obtenu via POST /v1/sessions (valid 24h, caché 23h)
 *  - KoboToolbox    : api_token statique (Bearer)
 *  - ODK Aggregate  : api_token statique (Bearer)
 */
class OdkApiClient
{
    /** ODK Central project ID (configurable via OdkSettings ou .env). */
    private readonly int $projectId;

    public function __construct(private readonly OdkSettings $settings)
    {
        $this->projectId = (int) config('services.odk.project_id', 1);
    }

    // ---------------------------------------------------------------
    //  Formulaires
    // ---------------------------------------------------------------

    /**
     * Liste tous les formulaires actifs du projet.
     *
     * @return array<int, array{xmlFormId: string, name: string, version: string, state: string}>
     */
    public function listForms(): array
    {
        $response = $this->client()
            ->get($this->url("projects/{$this->projectId}/forms"));

        $this->assertOk($response, 'listForms');

        return collect($response->json())
            ->filter(fn(array $f) => ($f['state'] ?? '') !== 'closed')
            ->values()
            ->all();
    }

    /**
     * Détails d'un formulaire : version courante, état, nb soumissions totales, sha256.
     *
     * @return array{xmlFormId: string, name: string, version: string, state: string,
     *               publishedAt: string, submissions: int, sha256: string, enketoId: string}
     */
    public function getFormDetails(string $xmlFormId): array
    {
        $response = $this->client()
            ->get($this->url("projects/{$this->projectId}/forms/{$xmlFormId}"));

        $this->assertOk($response, "getFormDetails({$xmlFormId})");

        return $response->json();
    }

    /**
     * Historique des versions publiées d'un formulaire.
     *
     * @return array<int, array{version: string, publishedAt: string, state: string,
     *                          sha256: string, name: string}>
     */
    public function getFormVersions(string $xmlFormId): array
    {
        $response = $this->client()
            ->get($this->url("projects/{$this->projectId}/forms/{$xmlFormId}/versions"));

        $this->assertOk($response, "getFormVersions({$xmlFormId})");

        return $response->json();
    }

    /**
     * Publie un XForm XML sur ODK Central (déploiement depuis le back-office).
     */
    public function publishForm(string $xmlContent, string $formId): array
    {
        $response = $this->client()
            ->withHeaders(['X-XlsFormId' => $formId])
            ->withBody($xmlContent, 'application/xml')
            ->post($this->url("projects/{$this->projectId}/forms"));

        $this->assertOk($response, "publishForm({$formId})");

        return $response->json();
    }

    // ---------------------------------------------------------------
    //  Soumissions
    // ---------------------------------------------------------------

    /**
     * Liste les soumissions d'un formulaire depuis une date donnée.
     *
     * @return array<int, array{instanceId: string, submitterId: int, deviceId: string, createdAt: string}>
     */
    public function listSubmissions(string $formId, ?\DateTimeInterface $since = null): array
    {
        $query = [];
        if ($since !== null) {
            $query['$filter'] = "__system/submissionDate ge " . $since->format('Y-m-d');
        }

        $response = $this->client()
            ->get($this->url("projects/{$this->projectId}/forms/{$formId}/submissions"), $query);

        $this->assertOk($response, "listSubmissions({$formId})");

        return $response->json()['value'] ?? $response->json();
    }

    /**
     * Récupère le XML brut d'une soumission spécifique.
     */
    public function getSubmissionXml(string $xmlFormId, string $instanceId): string
    {
        $response = $this->client()->get(
            "/v1/projects/{$this->projectId}/forms/{$xmlFormId}/submissions/{$instanceId}.xml"
        );

        if (! $response->ok()) {
            throw new \RuntimeException("Erreur récupération XML {$instanceId}");
        }

        return $response->body();
    }

    /**
     * Marque une soumission comme traitée (custom tag ODK Central).
     * ODK Central ne dispose pas d'une API native de "marquer traité" -
     * on utilise l'endpoint de review status.
     */
    public function markSubmissionReviewed(string $formId, string $instanceId): void
    {
        $this->client()
            ->patch($this->url("projects/{$this->projectId}/forms/{$formId}/submissions/{$instanceId}"), [
                'reviewState' => 'approved',
            ]);
    }

    // ---------------------------------------------------------------
    //  Helpers privés
    // ---------------------------------------------------------------

    private function client(): PendingRequest
    {
        if (empty($this->settings->server_url)) {
            throw new RuntimeException('ODK non configuré (URL manquante).');
        }

        $http = Http::baseUrl($this->settings->server_url)
            ->withToken($this->resolveToken())
            ->timeout($this->settings->timeout_seconds)
            ->retry($this->settings->retry_attempts, 500)
            ->acceptJson();

        if (!config('app.http_verify_ssl')) {
            $http = $http->withoutVerifying();
        }

        return $http;
    }

    /**
     * Résout le Bearer token selon le type de serveur.
     *
     * ODK Central : token de session obtenu via POST /v1/sessions (email+password).
     *               Token valide 24h côté serveur, mis en cache 23h pour éviter
     *               une authentification à chaque requête.
     *
     * KoboToolbox / ODK Aggregate : token API statique stocké dans OdkSettings.
     */
    private function resolveToken(): string
    {
        if ($this->settings->server_type !== 'odk_central') {
            if (empty($this->settings->api_token)) {
                throw new RuntimeException('Token API manquant dans les paramètres ODK.');
            }

            return $this->settings->api_token;
        }

        // ODK Central - session token valide 24h, caché 23h
        return Cache::remember(
            'odk_central_session_token',
            now()->addHours(23),
            function (): string {
                if (empty($this->settings->username) || empty($this->settings->password)) {
                    throw new RuntimeException(
                        'ODK Central : email ou mot de passe non configuré dans le paramétrage.'
                    );
                }

                $http = Http::baseUrl($this->settings->server_url)
                    ->timeout($this->settings->timeout_seconds)
                    ->acceptJson();

                if (!config('app.http_verify_ssl')) {
                    $http = $http->withoutVerifying();
                }

                $response = $http->post('/v1/sessions', [
                    'email'    => $this->settings->username,
                    'password' => $this->settings->password,
                ]);

                if ($response->failed()) {
                    Log::error('OdkApiClient - authentification ODK Central échouée', [
                        'status' => $response->status(),
                        'body'   => $response->body(),
                    ]);
                    throw new RuntimeException(
                        "ODK Central authentification : HTTP {$response->status()} - vérifiez email/mot de passe."
                    );
                }

                $token = $response->json('token');

                if (empty($token)) {
                    throw new RuntimeException('ODK Central : token absent dans la réponse de /v1/sessions.');
                }

                return $token;
            }
        );
    }

    /**
     * Invalide le token de session ODK Central en cache.
     * À appeler après un changement de mot de passe ou en cas d'erreur 401.
     */
    public function clearSessionToken(): void
    {
        Cache::forget('odk_central_session_token');
    }

    private function url(string $path): string
    {
        return rtrim($this->settings->server_url, '/') . '/v1/' . ltrim($path, '/');
    }

    private function assertOk(Response $response, string $context): void
    {
        if ($response->failed()) {
            // 401 sur ODK Central → token expiré - invalider le cache
            if ($response->status() === 401 && $this->settings->server_type === 'odk_central') {
                $this->clearSessionToken();
            }

            Log::error("OdkApiClient - {$context} a échoué", [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new RuntimeException(
                "ODK {$context} : HTTP {$response->status()} - {$response->body()}"
            );
        }
    }

    public function getSubmissionsCount(string $xmlFormId): int
    {
        $page = 0;
        $limit = 100;
        $total = 0;

        do {
            $response = $this->client()->get(
                "/v1/projects/{$this->projectId}/forms/{$xmlFormId}/submissions",
                [
                    '$top' => $limit,
                    '$skip' => $page * $limit,
                ]
            );

            if (! $response->ok()) {
                throw new \RuntimeException("Erreur récupération submissions");
            }

            $data = $response->json();
            $count = count($data);

            $total += $count;
            $page++;
        } while ($count === $limit);

        return $total;
    }
}
