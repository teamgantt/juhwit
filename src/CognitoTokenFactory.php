<?php

namespace TeamGantt\Juhwit;

use TeamGantt\Juhwit\Contracts\TokenFactoryInterface;
use TeamGantt\Juhwit\Models\TokenInterface;
use TeamGantt\Juhwit\Exceptions\InvalidClaimsException;
use TeamGantt\Juhwit\Models\Token\{IdToken, AccessToken};
use TeamGantt\Juhwit\Traits\ValidatesClaims;

class CognitoTokenFactory implements TokenFactoryInterface
{
    use ValidatesClaims;

    /**
     * {@inheritDoc}
     *
     * @param array $claims
     * @param array $requiredClaims
     * @return TokenInterface
     */
    public function create(array $claims, array $requiredClaims = []): TokenInterface
    {
        $this->validateClaims($claims, $requiredClaims);

        if (! isset($claims['token_use'])) {
            throw new InvalidClaimsException('Missing token_use claim');
        }

        $tokenUse = $claims['token_use'];

        switch ($tokenUse) {
            case 'id':
                return new IdToken($claims);
            case 'access':
                return new AccessToken($claims);
            default:
                throw new InvalidClaimsException('Invalid token_use claim');
        }
    }
}
