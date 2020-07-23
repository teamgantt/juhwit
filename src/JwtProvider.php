<?php

namespace TeamGantt\Juhwit;

use Illuminate\Support\ServiceProvider;
use TeamGantt\Juhwit\Contracts\ClaimVerifierInterface;
use TeamGantt\Juhwit\Contracts\DecoderInterface;
use TeamGantt\Juhwit\CognitoClaimVerifier;
use TeamGantt\Juhwit\JwtDecoder;

class JwtProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->bind(ClaimVerifierInterface::class, function () {
            $clientIds = config('cognito.clientIds', []);
            $poolId = config('cognito.poolId');
            $region = config('cognito.region');

            return new CognitoClaimVerifier($clientIds, $poolId, $region);
        });

        $this->app->bind(DecoderInterface::class, function ($app) {
            $verifier = $app->make(ClaimVerifierInterface::class);
            return new JwtDecoder($verifier, config('cognito.extraRequiredClaims', []));
        });
    }
}
