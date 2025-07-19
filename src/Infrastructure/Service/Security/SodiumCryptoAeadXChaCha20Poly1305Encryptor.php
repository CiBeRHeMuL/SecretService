<?php

namespace App\Infrastructure\Service\Security;

use App\Domain\Service\Security\EncryptorInterface;
use InvalidArgumentException;
use RuntimeException;

readonly class SodiumCryptoAeadXChaCha20Poly1305Encryptor implements EncryptorInterface
{
    public function __construct(
        private string $nonce,
        private string $key,
    ) {
        if (!extension_loaded('sodium')) {
            throw new RuntimeException('Sodium PHP extension is not loaded');
        }

        $nonceLength = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES;
        if (strlen($this->nonce) !== $nonceLength) {
            throw new InvalidArgumentException("Длина nonce должна быть $nonceLength байт");
        }

        $keyLength = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES;
        if (strlen($this->key) !== $keyLength) {
            throw new InvalidArgumentException("Длина key должна быть $keyLength байт");
        }
    }

    public function encrypt(string $data): string
    {
        return sodium_bin2base64(
            sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
                $data,
                '',
                $this->nonce,
                $this->key,
            ),
            SODIUM_BASE64_VARIANT_ORIGINAL_NO_PADDING,
        );
    }

    public function decrypt(string $data): string
    {
        return sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
            sodium_base642bin($data, SODIUM_BASE64_VARIANT_ORIGINAL_NO_PADDING),
            '',
            $this->nonce,
            $this->key,
        );
    }
}
