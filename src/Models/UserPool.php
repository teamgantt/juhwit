<?php

namespace TeamGantt\Juhwit\Models;

class UserPool
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string[]
     */
    protected $clientIds;

    /**
     * @var string
     */
    protected $region;

    /**
     * @var array
     */
    protected $jwk;

    /**
     * UserPool constructor
     * 
     * @param string $id 
     * @param string[] $clientIds 
     * @param string $region
     * @param array $jwk - a json decoded JSON Web Key for the pool
     */
    public function __construct(
        $id,
        $clientIds,
        $region,
        array $jwk
    ) {
        $this->id = $id;
        $this->clientIds = $clientIds;
        $this->region = $region;
        $this->jwk = $jwk;
    }

    /**
     * @return string
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string[]
     */ 
    public function getClientIds()
    {
        return $this->clientIds;
    }

    /**
     * @return string
     */ 
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @return  array
     */ 
    public function getJwk()
    {
        return $this->jwk;
    }

    /**
     * Get errors associated with a token's claims within the user pool
     * 
     * @param Token $token 
     * @return string|null 
     */
    public function getClaimsError(Token $token)
    {
        if (array_search($token->getClaim('aud'), $this->clientIds) === false) {
            return 'Invalid aud claim';
        }

        if ($token->getClaim('iss') !== "https://cognito-idp.{$this->region}.amazonaws.com/{$this->id}") {
            return 'Invalid iss claim';
        }

        if ($token->getClaim('token_use') !== 'id') {
            return 'Invalid token_use claim';
        }

        return null;
    }

    /**
     * Check if a token has valid claims within the user pool
     * 
     * @param Token $token 
     * @return bool 
     */
    public function hasValidClaims(Token $token)
    {
        return is_null($this->getClaimsError($token));
    }
}
