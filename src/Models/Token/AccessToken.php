<?php

namespace TeamGantt\Juhwit\Models\Token;

use TeamGantt\Juhwit\Models\Token;
use TeamGantt\Juhwit\Models\UserPool;

class AccessToken extends Token
{
    public function getClaimsError(UserPool $userPool)
    {
        if ($this->getClaim('iss') !== "https://cognito-idp.{$userPool->getRegion()}.amazonaws.com/{$userPool->getId()}") {
            return 'Invalid iss claim';
        }

        if ($this->getClaim('token_use') !== 'access') {
            return 'Invalid token_use claim';
        }

        return null;
    }
}
