<?php

namespace TeamGantt\Juhwit\Models\Token;

use TeamGantt\Juhwit\Models\Token;
use TeamGantt\Juhwit\Models\UserPool;

class IdToken extends Token
{
    public function getClaimsError(UserPool $userPool)
    {
        if (array_search($this->getClaim('aud'), $userPool->getClientIds()) === false) {
            return 'Invalid aud claim';
        }

        if ($this->getClaim('iss') !== "https://cognito-idp.{$userPool->getRegion()}.amazonaws.com/{$userPool->getId()}") {
            return 'Invalid iss claim';
        }

        if ($this->getClaim('token_use') !== 'id') {
            return 'Invalid token_use claim';
        }

        return null;
    }
}
