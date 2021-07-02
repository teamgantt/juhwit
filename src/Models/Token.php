<?php

namespace TeamGantt\Juhwit\Models;

use TeamGantt\Juhwit\Exceptions\InvalidClaimsException;
use TeamGantt\Juhwit\Models\Token\AccessToken;
use TeamGantt\Juhwit\Models\Token\IdToken;

abstract class Token implements TokenInterface
{
    /**
     * @var array
     */
    private $claims;

    /**
     * Token constructor.
     *
     * @param array $claims
     */
    public function __construct(array $claims, array $requiredClaims = [])
    {
        $this->invariant($claims, $requiredClaims);
        $this->claims = $claims;
    }

    /**
     * Get a claim value for the token.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getClaim($name)
    {
        if (isset($this->claims[$name])) {
            return $this->claims[$name];
        }
    }

    public static function create(array $claims, array $requiredClaims = [])
    {
        if (! isset($claims['token_use'])) {
            throw new InvalidClaimsException('Missing token_use claim');
        }

        $tokenUse = $claims['token_use'];

        switch ($tokenUse) {
            case 'id':
                return new IdToken($claims, $requiredClaims);
            case 'access':
                return new AccessToken($claims, $requiredClaims);
            default:
                throw new InvalidClaimsException('Invalid token_use claim');
        }
    }

    /**
     * Validate the claims the Token was constructed with. This is a semi opinionated
     * list of required keys for a JWT from Cognito.
     *
     * @param array $claims
     * @param array<string> $claims
     *
     * @throws InvalidClaimsException
     *
     * @return void
     */
    private function invariant(array $claims, array $requiredClaims)
    {
        foreach ($requiredClaims as $requiredKey => $requiredValue) {
            $key = is_numeric($requiredKey) ? $requiredValue : $requiredKey;

            if (!isset($claims[$key])) {
                throw new InvalidClaimsException("claim $key not found");
            }

            if (! is_numeric($key) && $requiredValue !== $claims[$key]) {
                throw new InvalidClaimsException("unexpected value for claim $key");
            }
        }
    }
}
