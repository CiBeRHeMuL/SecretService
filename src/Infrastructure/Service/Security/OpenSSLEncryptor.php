<?php

namespace App\Infrastructure\Service\Security;

use App\Domain\Service\Security\EncryptorInterface;
use InvalidArgumentException;
use RuntimeException;

readonly class OpenSSLEncryptor implements EncryptorInterface
{
    public function __construct(
        private string $cipherMethod,
        private string $passphrase,
        private string $iv,
    ) {
        if (!extension_loaded('openssl')) {
            throw new RuntimeException('Openssl PHP extension is not loaded');
        }

        if (!in_array(strtolower($cipherMethod), openssl_get_cipher_methods())) {
            throw new InvalidArgumentException(
                "Выбранный метод шифрования ($this->cipherMethod) недоступен. "
                . 'Список доступных методов можно получить с помощью функции openssl_get_cipher_methods()',
            );
        }

        $ivLength = openssl_cipher_iv_length($this->cipherMethod);
        $keyLength = openssl_cipher_key_length($this->cipherMethod);
        if (strlen($this->iv) !== $ivLength) {
            throw new InvalidArgumentException("Длина IV вектора должна быть $ivLength байт");
        }
        if (strlen($this->passphrase) !== $keyLength) {
            throw new InvalidArgumentException("Длина кодовой фразы должна быть $keyLength байт");
        }
    }

    public function encrypt(string $data): string
    {
        return openssl_encrypt(
            $data,
            $this->cipherMethod,
            $this->passphrase,
            iv: $this->iv,
        );
    }

    public function decrypt(string $data): string
    {
        return openssl_decrypt(
            $data,
            $this->cipherMethod,
            $this->passphrase,
            iv: $this->iv,
        );
    }
}
