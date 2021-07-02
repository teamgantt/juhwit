<?php

namespace TeamGantt\Juhwit\Contracts;

use TeamGantt\Juhwit\Models\Token;

interface DecoderInterface
{
    /**
     * Given a JWT string, decode it. An invalid token
     * will result in a null response - otherwise an array
     * with the result will be returned.
     *
     * @param string $token
     * @param array<string> $extraRequiredClaims
     *
     * @return Token
     */
    public function decode(string $token, array $extraRequiredClaims = []): Token;
}
