<?php

namespace TeamGantt\Juhwit\Contracts;

use TeamGantt\Juhwit\Models\Token;
use TeamGantt\Juhwit\Models\UserPool;

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

    /**
     * Get the user pool that tokens are verified against
     * 
     * @return UserPool 
     */
    public function getUserPool(): UserPool;
}
