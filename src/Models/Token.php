<?php

namespace TeamGantt\Juhwit\Models;

class Token
{
    const BASE_REQUIRED_CLAIMS = [
        'aud',
        'iss',
        'token_use',
        'email',
    ];

    /**
     * @var array
     */
    private $claims;

    /**
     * Token constructor.
     *
     * @param array $claims
     */
    public function __construct(array $claims, $extraRequiredClaims = [])
    {
        $this->invariant($claims, $extraRequiredClaims);
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

    /**
     * Get the user id associated with this token.
     *
     * @return int
     */
    public function getUserId(): int
    {
        return (int) ($this->getClaim('custom:user_id'));
    }

    /**
     * Validate the claims the Token was constructed with. This is a semi opinionated
     * list of required keys for a JWT from Cognito.
     *
     * @param array $claims
     * @param array<string> $claims
     *
     * @throws \DomainException
     *
     * @return void
     */
    private function invariant(array $claims, array $extraRequiredClaims)
    {
        $requiredKeys = array_merge(self::BASE_REQUIRED_CLAIMS, $extraRequiredClaims);

        foreach ($requiredKeys as $requiredKey) {
            if (!isset($claims[$requiredKey])) {
                throw new \DomainException("claim $requiredKey not found");
            }
        }
    }
}
