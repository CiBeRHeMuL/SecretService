<?php

namespace App\Domain\Service\Jwt;

use App\Domain\ValueObject\Jwt\JwtClaimsInterface;

interface JwtServiceInterface
{
    public function encode(JwtClaimsInterface $jwt): string;

    /**
     * @template T
     *
     * @param string $token
     * @param class-string<T> $jwtClassName
     * @param string|null $salt
     *
     * @return T|null
     */
    public function decode(string $token, string $jwtClassName, string|null $salt = null): JwtClaimsInterface|null;
}
