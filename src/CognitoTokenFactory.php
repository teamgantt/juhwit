<?php

namespace TeamGantt\Juhwit;

use TeamGantt\Juhwit\Contracts\TokenFactoryInterface;
use TeamGantt\Juhwit\Models\TokenInterface;
use TeamGantt\Juhwit\Exceptions\InvalidClaimsException;
use TeamGantt\Juhwit\Models\Token\{IdToken, AccessToken};

class CognitoTokenFactory implements TokenFactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @param array $claims
     * @param array $requiredClaims
     * @return TokenInterface
     */
    public function create(array $claims, array $requiredClaims = []): TokenInterface
    {
        if (! isset($claims['token_use'])) {
            throw new InvalidClaimsException('Missing token_use claim');
        }

        $tokenUse = $claims['token_use'];

        switch ($tokenUse) {
            case 'id':
                return new IdToken($claims, $requiredClaims);
            case 'access':
                return new AccessToken($claims, $requiredClaims);
            default:
                throw new InvalidClaimsException('Invalid token_use claim');
        }
    }
}
