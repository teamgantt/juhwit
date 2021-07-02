<?php

use TeamGantt\Juhwit\Exceptions\ExpiredException;
use Firebase\JWT\JWT;
use Kahlan\Plugin\Double;
use TeamGantt\Juhwit\CognitoClaimVerifier;
use TeamGantt\Juhwit\Contracts\ClaimVerifierInterface;
use TeamGantt\Juhwit\Exceptions\InvalidClaimsException;
use TeamGantt\Juhwit\Exceptions\InvalidJwkException;
use TeamGantt\Juhwit\Exceptions\InvalidStructureException;
use TeamGantt\Juhwit\JwtDecoder;
use TeamGantt\Juhwit\Models\UserPool;

describe('JwtDecoder', function () {
    beforeAll(function () {
        $this->jwt = "eyJraWQiOiI4WG9DOUxBOE9uSE1FTG1hcmxHc1BhWWI4WTVDdVYwZ1RMMzJzVkVaRjdnPSIsImFsZyI6IlJTMjU2In0.eyJzdWIiOiI5NmY4YzQ0Mi04MTlkLTQ3NzQtODNlNC04NDAxZDU2ZjYwZWMiLCJhdWQiOiI2dDk4MTNzMGR2bzZwbGo1bmFxdjY5NnE5OSIsImVtYWlsX3ZlcmlmaWVkIjp0cnVlLCJldmVudF9pZCI6ImQzY2M1MDZiLWIzZTctNDk2My04NDYyLTYyZWI5YzFlM2Q3ZiIsInRva2VuX3VzZSI6ImlkIiwiYXV0aF90aW1lIjoxNTc5NjM5NDIzLCJpc3MiOiJodHRwczpcL1wvY29nbml0by1pZHAudXMtZWFzdC0yLmFtYXpvbmF3cy5jb21cL3VzLWVhc3QtMl9kUkNueVZHVUciLCJjdXN0b206dXNlcl9pZCI6IjEyMyIsImNvZ25pdG86dXNlcm5hbWUiOiI5NmY4YzQ0Mi04MTlkLTQ3NzQtODNlNC04NDAxZDU2ZjYwZWMiLCJleHAiOjE1Nzk2NDMwMjMsImlhdCI6MTU3OTYzOTQyMywiZW1haWwiOiJpc2htYWVsQHRlYW1nYW50dC5jb20ifQ.OYUvrp-rKy_-A9eisMahC9s1GSQrx5ElgX36gNGO6RLLYZXe2DOVJTO1UgVupjcKM3bDscpSjUweQiOBupvnkDlN4bHHAfERsRPpCwtRMWQW7MGGB6FIJ5yb3K3ObEZcD-P_ASJ7a7BIvr4tTvnzKqiDh2zXnmeo1Jhe62bxsuu_57Z1lW9ju79SdqLCqZUxw20b7kQTO173NUe0biAKMXjElYv9_zW0nc9a6Yx8LVVHUJT8KN4v0VnGJnNIIpRJHRCHTd4sJpEg3rOgHubIiuuUZhyZS1-qVG3D4OlD2d9MtTgQOrgdaorxg6JAIza3TPmRZ7CoQMndtgRqNq34Aw";
        $jwkPath = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'jwk.valid.json');
        $jwkContents = file_get_contents($jwkPath);
        $this->validJwk = json_decode($jwkContents, true);
        $this->userPool = new UserPool('id', ['clientId'], 'us-east-2', $this->validJwk);
    });

    beforeEach(function () {
        $this->verifier = Double::instance(['implements' => ClaimVerifierInterface::class]);
        
        allow($this->verifier)->toReceive('verify')->andRun(function ($token) {
            return $token;
        });

        allow($this->verifier)->toReceive('getUserPool')->andReturn($this->userPool);

        $this->decoder = new JwtDecoder($this->verifier);

        // Give it a leeway of 100 years to allow testing decoding the token without exception
        JWT::$leeway = 60 * 60 * 24 * 365 * 100;
    });

    afterEach(function () {
        JWT::$leeway = 60;
    });

    describe('->decode()', function () {
        it('should reject invalid structure', function () {
            $sut = function () {
                $invalid = '11111.222222';
                $this->decoder->decode($invalid);
            };
            expect($sut)->toThrow(new InvalidStructureException());
        });

        it('should reject a structure that contains non-base64 encoded items', function () {
            $sut = function () {
                $invalid = 'as%iudfh9w=8uihf.asi%udfh9w=8uihf.as%iudfh9w=8uihf';
                $this->decoder->decode($invalid);
            };
            expect($sut)->toThrow(new InvalidStructureException());
        });

        it('should reject an empty structure', function () {
            $sut = function () {
                $invalid = '..';
                $this->decoder->decode($invalid);
            };
            expect($sut)->toThrow(new InvalidStructureException());
        });

        it('should decode a valid jwt', function () {
            $token = $this->decoder->decode($this->jwt);
            expect($token->getClaim('custom:user_id'))->toBe('123');
            expect($token->getClaim('token_use'))->toBe('id');
        });

        it('should support providing expected values as required claims', function () {
            $token = $this->decoder->decode($this->jwt, ['custom:user_id' => '123']);
        
            $sut = function () {
                $this->decoder->decode($this->jwt, ['custom:user_id' => '456']);
            };

            expect($token->getClaim('custom:user_id'))->toBe('123');
            expect($sut)->toThrow(new InvalidClaimsException('unexpected value for claim custom:user_id'));
        });

        it('should guarantee a jwt has certain claims', function () {
            $sut = function () {
                $this->decoder->decode($this->jwt, ['foo']);
            };
            expect($sut)->toThrow(new InvalidClaimsException("claim foo not found"));
        });

        it('should be able to decode an access token', function () {
            $jwt = "eyJraWQiOiIzVXI5TDd0XC84NW83UlN6aWl4aEliQ2lCbkVDSmpGTFFcL3FhSnJRY0lPZm89IiwiYWxnIjoiUlMyNTYifQ.eyJzdWIiOiIxM2NxMzg1bGJrYjRhcG5sZDZ2bnZoZ2kzNyIsInRva2VuX3VzZSI6ImFjY2VzcyIsInNjb3BlIjoiaHR0cHM6XC9cL2FwaS1sb2NhbC50ZWFtZ2FudHQuY29tXC90YXNrLmVkaXQiLCJhdXRoX3RpbWUiOjE2MjUyMzYyNDUsImlzcyI6Imh0dHBzOlwvXC9jb2duaXRvLWlkcC51cy1lYXN0LTIuYW1hem9uYXdzLmNvbVwvdXMtZWFzdC0yX3JiVUo2Mk1pYiIsImV4cCI6MTYyNTMyMjY0NSwiaWF0IjoxNjI1MjM2MjQ1LCJ2ZXJzaW9uIjoyLCJqdGkiOiI2YTFkZmYyMC1iZTcxLTQxYjUtYjk3MC01Njg3MTAzYmY1M2UiLCJjbGllbnRfaWQiOiIxM2NxMzg1bGJrYjRhcG5sZDZ2bnZoZ2kzNyJ9.InU3IYNFgBlWRezlx2V6LBPTdX0chYYv6jBjTsV47fAma2E83PTw_QRcr-xFNtm7q_kuVri7b7-H3gGSIMoRXvsVqVV9l0AujZmfsZLDJtZNdipY6gxsofjswBVj883faiB17D8FqXosTL41NEfywG-pDb3tKT2q1qXR_Lfy2LokACoIU_vC8POqo9t5Spmb2sKfjDRmb3sMF0OruQBgT8hm9cCDn41F4MLmBB7f24g-1HuGl5LicO5JVGHBDi0_n_N_Q71vpDaPfYSWGcwoQvHot8vunIrfvXPzbWYSFelVm7Y3bsffgUVymbvSdyCjJKiqOUCrH_uFlLbPZdShrg";
            $jwkPath = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'jwk.client-credentials.json');
            $jwkContents = file_get_contents($jwkPath);
            $userPool = new UserPool('us-east-2_rbUJ62Mib', ['13cq385lbkb4apnld6vnvhgi37'], 'us-east-2', json_decode($jwkContents, true));
            $verifier = new CognitoClaimVerifier($userPool);
            $decoder = new JwtDecoder($verifier);

            $token = $decoder->decode($jwt);

            expect($token->getClaim('scope'))->toBe('https://api-local.teamgantt.com/task.edit');
        });

        it('should throw an exception for a missing claim key', function () {
            $decoderWithExtraRequiredKey = new JwtDecoder($this->verifier, ['custom:foo']);
            $sut = function () use ($decoderWithExtraRequiredKey) {
                $decoderWithExtraRequiredKey->decode($this->jwt);
            };
            expect($sut)->toThrow(new InvalidClaimsException("claim custom:foo not found"));
        });

        it('should throw an exception for expired tokens', function () {
            JWT::$leeway = 60;
            $sut = function () {
                $this->decoder->decode($this->jwt);
            };
            expect($sut)->toThrow(new ExpiredException("Expired token"));
        });

        it('should throw an exception for a jwk file missing identified key id', function () {
            $userPool = new UserPool('id', ['client-id'], 'us-east-2', []);
            allow($this->verifier)->toReceive('getUserPool')->andReturn($userPool);
            $sut = function () {
                $this->decoder->decode($this->jwt);
            };
            expect($sut)->toThrow(new InvalidJwkException());
        });
    });
});
