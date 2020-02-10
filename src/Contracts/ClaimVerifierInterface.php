<?php

namespace TeamGantt\Juhwit\Contracts;

// use TeamGantt\Api\Exceptions\Token\InvalidClaimsException;
use TeamGantt\Juhwit\Models\Token;

interface ClaimVerifierInterface
{
    /**
     * Verify the claims contained in a token.
     *
     * @param Token $token
     *
     * @throws InvalidClaimsException
     *
     * @return Token
     */
    public function verify(Token $token): Token;
}
