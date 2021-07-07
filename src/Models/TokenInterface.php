<?php

namespace TeamGantt\Juhwit\Models;

use TeamGantt\Juhwit\Models\UserPool;

interface TokenInterface
{

    /**
     * Get a claim value for the token.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getClaim($name);


    /**
     * Get any claim errors relevant to the given UserPool.
     *
     * @param UserPool $userPool
     * @return string | null
     */
    public function getClaimsError(UserPool $userPool);
}
