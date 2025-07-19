<?php

namespace App\Infrastructure\Service\Jwt;

use App\Domain\Service\Jwt\JwtServiceInterface;
use App\Domain\ValueObject\Jwt\JwtClaimsInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;

readonly class JwtService implements JwtServiceInterface
{
    public function __construct(
        private string $key,
    ) {
    }

    public function encode(JwtClaimsInterface $jwt): string
    {
        return JWT::encode($jwt->getClaims(), $this->key . '.' . $jwt->getSalt(), 'HS256');
    }

    public function decode(string $token, string $jwtClassName, string|null $salt = null): JwtClaimsInterface|null
    {
        try {
            $decoded = (array)JWT::decode($token, new Key($this->key . '.' . $salt, 'HS256'));

            /** @var $jwtClassName JwtClaimsInterface */
            return $jwtClassName::createByDecoded($decoded, $salt);
        } catch (Throwable $e) {
            return null;
        }
    }
}
