<?php

namespace TeamGantt\Juhwit;

use DomainException;
use Exception;
use TeamGantt\Juhwit\Contracts\ClaimVerifierInterface;
use TeamGantt\Juhwit\Contracts\DecoderInterface;
use TeamGantt\Juhwit\Exceptions\UnknownException;
use TeamGantt\Juhwit\Models\Token;

class MultiPoolJwtDecoder implements DecoderInterface
{
    /**
     * @var ClaimVerifierInterface[]
     */
    protected $verifiers;

    /**
     * @var string[]
     */
    protected $extraRequiredClaims;

    /**
     * 
     * @param ClaimVerifierInterface[] $verifiers 
     * @param string[] $extraRequiredClaims
     * @return void 
     */
    public function __construct(array $verifiers, array $extraRequiredClaims = [])
    {
        foreach ($verifiers as $verifier) {
            if (! $verifier instanceof ClaimVerifierInterface) {
                throw new DomainException("All verifiers must implement the ClaimVerifierInterface interface.");
            }
        }
        $this->verifiers = $verifiers;
        $this->extraRequiredClaims = $extraRequiredClaims;
    }

    /**
     * {@inheritdoc}
     * 
     * @param string $token 
     * @return Token 
     * @throws Exception 
     */
    public function decode(string $token): Token
    {
        $lastError = new UnknownException("An unknown error has occurred.");
        foreach ($this->verifiers as $verifier) {
            $decoder = new JwtDecoder($verifier, $this->extraRequiredClaims);
            try {
                return $decoder->decode($token);
            } catch (Exception $e) {
                $lastError = $e;
            }
        }
        throw $lastError;
    }
}
