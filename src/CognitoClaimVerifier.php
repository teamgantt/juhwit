<?php

namespace TeamGantt\Juhwit;

use TeamGantt\Juhwit\Contracts\ClaimVerifierInterface;
use TeamGantt\Juhwit\Models\Token;
use TeamGantt\Juhwit\Exceptions\InvalidClaimsException;
use TeamGantt\Juhwit\Models\UserPool;

class CognitoClaimVerifier implements ClaimVerifierInterface
{
    /**
     * @var UserPool
     */
    protected $pool;

    /**
     * CognitoClaimVerifier constructor.
     *
     * @param UserPool $pool
     */
    public function __construct(UserPool $pool)
    {
        $this->pool = $pool;
    }

    /**
     * {@inheritdoc}
     * 
     * @return UserPool 
     */
    public function getUserPool(): UserPool
    {
        return $this->pool;
    }

    /**
     * {@inheritdoc}
     *
     * @param Token $token
     *
     * @throws InvalidClaimsException
     *
     * @return Token
     */
    public function verify(Token $token): Token
    {
        if ($this->pool->hasValidClaims($token)) {
            return $token;
        }

        throw new InvalidClaimsException($this->pool->getClaimsError($token));
    }
}
