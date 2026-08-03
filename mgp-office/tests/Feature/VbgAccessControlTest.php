<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * ER-02 - Le déchiffrement des données VBG est réservé aux rôles habilités
 * (spécialiste VBG, coordonnateur UGP, super admin). Un rôle non habilité ne
 * possède jamais la permission `vbg.view_decrypted`.
 */
class VbgAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::create(['name' => 'vbg.view_decrypted']);
        Permission::create(['name' => 'complaints.view_own_region']);

        Role::create(['name' => 'specialiste_vbg'])->givePermissionTo('vbg.view_decrypted');
        Role::create(['name' => 'chargé_enregistrement'])->givePermissionTo('complaints.view_own_region');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_le_specialiste_vbg_peut_dechiffrer(): void
    {
        $user = User::factory()->create();
        $user->assignRole('specialiste_vbg');

        $this->assertTrue($user->can('vbg.view_decrypted'));
    }

    public function test_un_role_non_habilite_ne_peut_pas_dechiffrer(): void
    {
        $user = User::factory()->create();
        $user->assignRole('chargé_enregistrement');

        $this->assertFalse($user->can('vbg.view_decrypted'));
    }

    public function test_la_config_liste_les_trois_roles_habilites(): void
    {
        $this->assertSame(
            ['super_admin', 'coordonnateur_ugp', 'specialiste_vbg'],
            config('vbg.decrypt_roles'),
        );
    }
}
