<?php

declare(strict_types=1);

namespace App\Domains\Vbg\Services;

use RuntimeException;

class VbgEncryptionService
{
    private string $key;

    public function __construct()
    {
        $rawKey = config('vbg.encryption_key') ?? env('VBG_ENCRYPTION_KEY');

        if (! $rawKey) {
            // En développement : clé aléatoire en mémoire (non persistante)
            // En production : la clé DOIT être définie dans .env
            $rawKey = base64_encode(random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES));
        }

        $decoded = base64_decode($rawKey, strict: true);

        if ($decoded === false || strlen($decoded) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            throw new RuntimeException('VBG_ENCRYPTION_KEY invalide : doit être une clé base64 de 32 octets.');
        }

        $this->key = $decoded;
    }

    public function encryptPhone(string $phone): string
    {
        $nonce      = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = sodium_crypto_secretbox($phone, $nonce, $this->key);

        return base64_encode($nonce . $ciphertext);
    }

    public function decryptPhone(string $encrypted): string
    {
        $decoded = base64_decode($encrypted, strict: true);

        if ($decoded === false) {
            throw new RuntimeException('Données VBG corrompues : décodage base64 échoué.');
        }

        $nonce      = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, $this->key);

        if ($plaintext === false) {
            throw new RuntimeException('Données VBG corrompues : déchiffrement échoué.');
        }

        return $plaintext;
    }
}
