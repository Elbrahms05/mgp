<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Complaint\Models\Complaint;
use App\Domains\Complaint\Models\ComplaintType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * EF-06 / EF-10 / ENF-01 - Webhook de soumission SMS (option A) : signature HMAC vérifiée,
 * idempotence, création d'une plainte non sensible. Un type sensible n'est jamais
 * matérialisé par ce canal.
 */
class SmsWebhookTest extends TestCase
{
    use RefreshDatabase;

    private string $secret = 'sms-shared-secret-xyz';

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Storage::fake('local');
        config(['webhook-client.configs.0.signing_secret' => $this->secret]);
    }

    /** @param array<string,mixed> $payload */
    private function postSms(array $payload, ?string $signature = null): \Illuminate\Testing\TestResponse
    {
        $body      = json_encode($payload);
        $signature ??= hash_hmac('sha256', $body, $this->secret);

        return $this->call(
            'POST',
            '/webhooks/sms',
            [], [], [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X_SIGNATURE' => $signature],
            $body,
        );
    }

    public function test_une_soumission_signee_cree_une_plainte(): void
    {
        ComplaintType::create(['code' => 't1', 'label' => 'Foncier', 'is_sensitive' => false, 'sort_order' => 1]);

        $this->postSms([
            'message_id'  => 'sms-0001',
            'type_code'   => 't1',
            'description' => 'Problème de bornage signalé par SMS',
            'phone'       => '+22790000000',
        ])->assertOk();

        $this->assertDatabaseHas('complaints', [
            'channel'     => 'sms',
            'description' => 'Problème de bornage signalé par SMS',
        ]);
    }

    public function test_une_signature_invalide_est_rejetee(): void
    {
        ComplaintType::create(['code' => 't1', 'label' => 'Foncier', 'is_sensitive' => false, 'sort_order' => 1]);

        $this->postSms(['message_id' => 'sms-0002', 'type_code' => 't1', 'description' => 'x'], 'mauvaise-signature')
            ->assertStatus(500);

        $this->assertDatabaseCount('complaints', 0);
    }

    public function test_l_idempotence_evite_les_doublons(): void
    {
        ComplaintType::create(['code' => 't1', 'label' => 'Foncier', 'is_sensitive' => false, 'sort_order' => 1]);

        $payload = ['message_id' => 'sms-0003', 'type_code' => 't1', 'description' => 'Doublon test'];

        $this->postSms($payload)->assertOk();
        $this->postSms($payload)->assertOk(); // rejeu passerelle

        $this->assertDatabaseCount('complaints', 1);
    }

    public function test_un_type_sensible_n_est_pas_materialise_par_sms(): void
    {
        ComplaintType::create(['code' => 'type8', 'label' => 'VBG', 'is_sensitive' => true, 'sort_order' => 8]);

        $this->postSms([
            'message_id' => 'sms-0004',
            'type_code'  => 'type8',
            'description' => 'ne doit pas être créé',
        ])->assertOk();

        $this->assertDatabaseCount('complaints', 0);
    }
}
