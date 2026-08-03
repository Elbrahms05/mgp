<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domains\Vbg\Services\VbgEncryptionService;
use RuntimeException;
use Tests\TestCase;

/**
 * ER-02 / §6 - Le chiffrement libsodium des données VBG est réversible pour le
 * détenteur de la clé et infalsifiable (toute altération est détectée).
 */
class VbgEncryptionTest extends TestCase
{
    private function service(): VbgEncryptionService
    {
        config(['vbg.encryption_key' => base64_encode(random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES))]);

        return new VbgEncryptionService();
    }

    public function test_le_telephone_chiffre_puis_dechiffre_est_identique(): void
    {
        $service = $this->service();
        $phone   = '+22790123456';

        $encrypted = $service->encryptPhone($phone);

        $this->assertNotSame($phone, $encrypted);
        $this->assertSame($phone, $service->decryptPhone($encrypted));
    }

    public function test_deux_chiffrements_du_meme_texte_different_grace_au_nonce(): void
    {
        $service = $this->service();

        $this->assertNotSame(
            $service->encryptPhone('secret'),
            $service->encryptPhone('secret'),
        );
    }

    public function test_une_alteration_du_chiffre_est_detectee(): void
    {
        $service   = $this->service();
        $encrypted = $service->encryptPhone('+22790123456');

        // Altère un octet au milieu du message chiffré
        $raw          = base64_decode($encrypted, strict: true);
        $raw[strlen($raw) - 1] = $raw[strlen($raw) - 1] === "\x00" ? "\x01" : "\x00";
        $tampered     = base64_encode($raw);

        $this->expectException(RuntimeException::class);
        $service->decryptPhone($tampered);
    }

    public function test_une_cle_invalide_est_rejetee(): void
    {
        config(['vbg.encryption_key' => base64_encode('trop-court')]);

        $this->expectException(RuntimeException::class);
        new VbgEncryptionService();
    }
}
