<?php

namespace TeamGantt\Juhwit\Traits;

use TeamGantt\Juhwit\Exceptions\InvalidClaimsException;

trait ValidatesClaims
{
    /**
     * Check that required claims are present. Throws an InvalidClaimsException if a required
     * claim is missing or if the given value does not match the expected value.
     *
     * @param array $claims
     * @param array $requiredClaims
     * @return void
     */
    protected function validateClaims(array $claims, array $requiredClaims)
    {
        foreach ($requiredClaims as $requiredKey => $requiredValue) {
            $key = is_numeric($requiredKey) ? $requiredValue : $requiredKey;

            if (!isset($claims[$key])) {
                throw new InvalidClaimsException("claim $key not found");
            }

            if (! is_numeric($key) && $requiredValue !== $claims[$key]) {
                throw new InvalidClaimsException("unexpected value for claim $key");
            }
        }
    }
}
