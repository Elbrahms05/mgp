<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * La racine (tableau de bord) est protégée : un visiteur non authentifié est
     * redirigé vers la page de connexion (2FA/auth obligatoire côté back-office).
     */
    public function test_la_racine_redirige_les_visiteurs_non_authentifies(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }
}
