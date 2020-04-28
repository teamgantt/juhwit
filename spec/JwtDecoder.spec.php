<?php

use TeamGantt\Juhwit\Exceptions\ExpiredException;
use Firebase\JWT\JWT;
use Kahlan\Plugin\Double;
use TeamGantt\Juhwit\Contracts\ClaimVerifierInterface;
use TeamGantt\Juhwit\Exceptions\InvalidJwkException;
use TeamGantt\Juhwit\Exceptions\InvalidStructureException;
use TeamGantt\Juhwit\JwtDecoder;

describe('JwtDecoder', function () {
    beforeEach(function () {
        $this->jwt = "eyJraWQiOiI4WG9DOUxBOE9uSE1FTG1hcmxHc1BhWWI4WTVDdVYwZ1RMMzJzVkVaRjdnPSIsImFsZyI6IlJTMjU2In0.eyJzdWIiOiI5NmY4YzQ0Mi04MTlkLTQ3NzQtODNlNC04NDAxZDU2ZjYwZWMiLCJhdWQiOiI2dDk4MTNzMGR2bzZwbGo1bmFxdjY5NnE5OSIsImVtYWlsX3ZlcmlmaWVkIjp0cnVlLCJldmVudF9pZCI6ImQzY2M1MDZiLWIzZTctNDk2My04NDYyLTYyZWI5YzFlM2Q3ZiIsInRva2VuX3VzZSI6ImlkIiwiYXV0aF90aW1lIjoxNTc5NjM5NDIzLCJpc3MiOiJodHRwczpcL1wvY29nbml0by1pZHAudXMtZWFzdC0yLmFtYXpvbmF3cy5jb21cL3VzLWVhc3QtMl9kUkNueVZHVUciLCJjdXN0b206dXNlcl9pZCI6IjEyMyIsImNvZ25pdG86dXNlcm5hbWUiOiI5NmY4YzQ0Mi04MTlkLTQ3NzQtODNlNC04NDAxZDU2ZjYwZWMiLCJleHAiOjE1Nzk2NDMwMjMsImlhdCI6MTU3OTYzOTQyMywiZW1haWwiOiJpc2htYWVsQHRlYW1nYW50dC5jb20ifQ.OYUvrp-rKy_-A9eisMahC9s1GSQrx5ElgX36gNGO6RLLYZXe2DOVJTO1UgVupjcKM3bDscpSjUweQiOBupvnkDlN4bHHAfERsRPpCwtRMWQW7MGGB6FIJ5yb3K3ObEZcD-P_ASJ7a7BIvr4tTvnzKqiDh2zXnmeo1Jhe62bxsuu_57Z1lW9ju79SdqLCqZUxw20b7kQTO173NUe0biAKMXjElYv9_zW0nc9a6Yx8LVVHUJT8KN4v0VnGJnNIIpRJHRCHTd4sJpEg3rOgHubIiuuUZhyZS1-qVG3D4OlD2d9MtTgQOrgdaorxg6JAIza3TPmRZ7CoQMndtgRqNq34Aw";
        $this->validJwk = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'jwk.valid.json');
        $this->verifier = Double::instance(['implements' => ClaimVerifierInterface::class]);
        allow($this->verifier)->toReceive('verify')->andRun(function ($token) {
            return $token;
        });
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
                $this->decoder->decode($invalid, $this->validJwk);
            };
            expect($sut)->toThrow(new InvalidStructureException());
        });

        it('should reject a structure that contains non-base64 encoded items', function () {
            $sut = function () {
                $invalid = 'as%iudfh9w=8uihf.asi%udfh9w=8uihf.as%iudfh9w=8uihf';
                $this->decoder->decode($invalid, $this->validJwk);
            };
            expect($sut)->toThrow(new InvalidStructureException());
        });

        it('should decode a valid jwt', function () {
            $token = $this->decoder->decode($this->jwt, $this->validJwk);
            expect($token->getClaim('custom:user_id'))->toBe('123');
            expect($token->getClaim('token_use'))->toBe('id');
        });

        it('should throw an exception for a missing claim key', function () {
            $decoderWithExtraRequiredKey = new JwtDecoder($this->verifier, ['custom:foo']);
            $sut = function () use ($decoderWithExtraRequiredKey) {
                $decoderWithExtraRequiredKey->decode($this->jwt, $this->validJwk);
            };
            expect($sut)->toThrow(new DomainException("claim custom:foo not found"));
        });

        it('should throw an exception for expired tokens', function () {
            JWT::$leeway = 60;
            $sut = function () {
                $this->decoder->decode($this->jwt, $this->validJwk);
            };
            expect($sut)->toThrow(new ExpiredException("Expired token"));
        });

        it('should throw an exception for a missing jwk file', function () {
            $jwkFile = '/path/to/nowhere';
            $sut = function () use ($jwkFile) {
                $this->decoder->decode($this->jwt, $jwkFile);
            };
            expect($sut)->toThrow(new RuntimeException());
        });

        it('should throw an exception for a jwk file missing identified key id', function () {
            $jwkFile = __DIR__ . '/jwk.error.json';
            $sut = function () use ($jwkFile) {
                $this->decoder->decode($this->jwt, $jwkFile);
            };
            expect($sut)->toThrow(new InvalidJwkException());
        });
    });
});
