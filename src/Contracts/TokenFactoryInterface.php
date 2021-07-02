<?php

namespace TeamGantt\Juhwit\Contracts;

use TeamGantt\Juhwit\Models\TokenInterface;
use TeamGantt\Juhwit\Exceptions\TokenException;

interface TokenFactoryInterface
{
    /**
     * Create an instance of TokenInterface.
     *
     * @param array $claims
     * @param array $requiredClaims
     * @throws TokenException
     * @return TokenInterface
     */
    public function create(array $claims, array $requiredClaims = []): TokenInterface;
}
