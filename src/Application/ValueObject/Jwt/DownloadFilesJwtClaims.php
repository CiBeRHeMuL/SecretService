<?php

namespace App\Application\ValueObject\Jwt;

use App\Domain\ValueObject\Jwt\JwtClaimsInterface;
use DateTimeImmutable;
use RuntimeException;
use SensitiveParameter;

readonly class DownloadFilesJwtClaims implements JwtClaimsInterface
{
    public const string SALT = DownloadFilesJwtClaims::class;

    private(set) string $salt;

    public function __construct(
        private(set) string $archiveName,
        #[SensitiveParameter]
        private(set) string $password,
        private(set) DateTimeImmutable $validUntil,
    ) {
        $this->salt = self::SALT;
    }

    /**
     * @inheritDoc
     */
    public static function createByDecoded(array $decoded, ?string $salt = null): JwtClaimsInterface
    {
        if ($salt !== self::SALT) {
            throw new RuntimeException('Invalid salt provided');
        }
        $hsh = $decoded['hsh'] ?? null;
        $iat = $decoded['iat'] ?? null;
        $jti = $decoded['jti'] ?? null;
        $sub = $decoded['sub'] ?? null;
        $pwd = $decoded['pwd'] ?? null;
        $exp = $decoded['exp'] ?? null;

        if (!isset($hsh, $iat, $jti, $sub, $pwd, $exp)) {
            throw new RuntimeException('Invalid decoded data provided');
        }
        if ($hsh !== $jti || $hsh !== $sub || $jti !== $sub) {
            throw new RuntimeException('Invalid decoded data provided');
        }
        if ($exp < time()) {
            throw new RuntimeException('Invalid decoded data provided');
        }

        return new self($hsh, $pwd, DateTimeImmutable::createFromTimestamp($exp));
    }

    /**
     * @inheritDoc
     */
    public function getClaims(): array
    {
        return [
            'hsh' => $this->archiveName,
            'iat' => time(),
            'jti' => $this->archiveName,
            'sub' => $this->archiveName,
            'pwd' => $this->password,
            'exp' => $this->validUntil->getTimestamp(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getSalt(): string|null
    {
        return $this->salt;
    }
}
