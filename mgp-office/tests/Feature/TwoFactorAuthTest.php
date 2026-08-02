<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * ER-02 / ENF-02 — Le 2FA est imposé aux rôles sensibles et réellement vérifié à la
 * connexion (défi TOTP).
 */
class TwoFactorAuthTest extends TestCase
{
    use RefreshDatabase;

    private function makeRole(string $name): Role
    {
        $role = Role::create(['name' => $name]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $role;
    }

    /** Active un 2FA confirmé avec un secret connu ; renvoie [user, secret]. */
    private function enroll(User $user): array
    {
        $secret = app(Google2FA::class)->generateSecretKey();

        $user->forceFill([
            'two_factor_secret'         => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode(['AAAA-BBBB', 'CCCC-DDDD'])),
            'two_factor_confirmed_at'   => now(),
            'two_factor_enabled'        => true,
        ])->save();

        return [$user, $secret];
    }

    public function test_un_role_sensible_sans_2fa_est_redirige_vers_la_configuration(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->makeRole('specialiste_vbg')->name);

        $this->actingAs($user)->get('/')->assertRedirect(route('two-factor.setup'));
    }

    public function test_la_page_de_configuration_2fa_est_accessible(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->makeRole('specialiste_vbg')->name);

        $this->actingAs($user)->get(route('two-factor.setup'))->assertOk();
    }

    public function test_la_connexion_d_un_utilisateur_2fa_declenche_le_defi(): void
    {
        [$user] = $this->enroll(User::factory()->create(['email' => 'agent@pacipa.ne']));

        $response = $this->post('/login', [
            'email'    => 'agent@pacipa.ne',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('two-factor.challenge'));
        $this->assertGuest(); // pas encore connecté : le mot de passe seul ne suffit pas
    }

    public function test_un_code_totp_valide_finalise_la_connexion(): void
    {
        [$user, $secret] = $this->enroll(User::factory()->create(['email' => 'agent@pacipa.ne']));

        // Étape 1 : mot de passe → défi
        $this->post('/login', ['email' => 'agent@pacipa.ne', 'password' => 'password'])
            ->assertRedirect(route('two-factor.challenge'));

        // Étape 2 : code TOTP valide → session ouverte
        $code = app(Google2FA::class)->getCurrentOtp($secret);

        $this->post(route('two-factor.challenge.store'), ['code' => $code])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_un_code_invalide_est_refuse(): void
    {
        $this->enroll(User::factory()->create(['email' => 'agent@pacipa.ne']));

        $this->post('/login', ['email' => 'agent@pacipa.ne', 'password' => 'password']);

        $this->post(route('two-factor.challenge.store'), ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertGuest();
    }
}
