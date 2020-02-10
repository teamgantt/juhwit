<?php

namespace TeamGantt\Juhwit\Models;

class Token
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
    public function __construct(array $claims)
    {
        $this->invariant($claims);
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
     * list of required keys for a JWT from Cognito - including a single custom attribute.
     * Future versions may relax this requirement
     *
     * @param array $claims
     *
     * @throws \DomainException
     *
     * @return void
     */
    private function invariant(array $claims)
    {
        $requiredKeys = [
            'aud',
            'iss',
            'token_use',
            'email',
            'custom:user_id',
        ];

        foreach ($requiredKeys as $requiredKey) {
            if (!isset($claims[$requiredKey])) {
                throw new \DomainException("claim $requiredKey not found");
            }
        }
    }
}
