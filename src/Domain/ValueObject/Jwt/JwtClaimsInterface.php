<?php

namespace App\Domain\ValueObject\Jwt;

interface JwtClaimsInterface
{
    /**
     * Create claims from decoded jwt token
     *
     * @param array $decoded
     * @param string|null $salt
     *
     * @return static
     */
    public static function createByDecoded(array $decoded, string|null $salt = null): JwtClaimsInterface;

    /**
     * All registered and custom claims
     *
     * @see https://auth0.com/docs/secure/tokens/json-web-tokens/json-web-token-claims#registered-claims
     * @see https://auth0.com/docs/secure/tokens/json-web-tokens/json-web-token-claims#custom-claims
     *
     * @return array
     */
    public function getClaims(): array;

    /**
     * Additional salt for jwt key
     * @return string|null
     */
    public function getSalt(): string|null;
}
