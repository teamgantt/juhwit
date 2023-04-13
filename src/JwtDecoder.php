<?php

namespace TeamGantt\Juhwit;

use CoderCat\JWKToPEM\JWKConverter;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use TeamGantt\Juhwit\Contracts\ClaimVerifierInterface;
use TeamGantt\Juhwit\Contracts\DecoderInterface;
use TeamGantt\Juhwit\Contracts\TokenFactoryInterface;
use TeamGantt\Juhwit\Exceptions\ExpiredException as JuhwitExpiredException;
use TeamGantt\Juhwit\Exceptions\InvalidJwkException;
use TeamGantt\Juhwit\Exceptions\InvalidStructureException;
use TeamGantt\Juhwit\Exceptions\UnknownException;
use TeamGantt\Juhwit\Models\TokenInterface;

/**
 * @see https://docs.aws.amazon.com/cognito/latest/developerguide/amazon-cognito-user-pools-using-tokens-verifying-a-jwt.html
 */
class JwtDecoder implements DecoderInterface
{
    /**
     * @var ClaimVerifierInterface
     */
    protected $verifier;

    /**
     * @var array<string>
     */
    protected $requiredClaims;

    /**
     * @var TokenFactoryInterface
     */
    protected $tokenFactory;

    /**
     * JwtDecoder constructor.
     *
     * @param ClaimVerifierInterface $verifier
     * @param TokenFactoryInterface $tokenFactory
     */
    public function __construct(
        ClaimVerifierInterface $verifier,
        TokenFactoryInterface $tokenFactory = null)
    {
        $this->verifier = $verifier;
        $this->tokenFactory = $tokenFactory?? new CognitoTokenFactory();
    }

    /**
     * {@inheritdoc}
     *
     * @param string $token
     * @param array<string> $requiredClaims
     *
     * @throws TeamGantt\Api\Exceptions\Token\InvalidClaimsException
     *
     * @return array
     */
    public function decode(string $token, array $requiredClaims = []): TokenInterface
    {
        list($header) = $this->validateStructure($token);
        $headerData = json_decode($header, true);
        $kid = $headerData['kid'];

        $claims = $this->getVerifiedToken($kid, $token);
        $token = $this->tokenFactory->create($claims, $requiredClaims);

        return $this->verifier->verify($token);
    }

    /**
     * Get the key that was used to sign the token.
     *
     * @param string $keyId
     * @param string $jwkFile
     *
     * @return null|array
     */
    private function getKey(string $keyId)
    {
        // Get the key that was used to sign the token
        $jwk = $this->verifier->getUserPool()->getJwk();
        $keys = $jwk['keys'] ?? [];

        return array_reduce($keys, function ($signingKey, $current) use ($keyId) {
            if ($current['kid'] === $keyId) {
                return $current;
            }

            return $signingKey;
        });
    }

    /**
     * Verify and return token.
     *
     * @param string $keyId
     * @param string $jwkFile
     * @param string $token
     *
     * @throws \RuntimeException
     *
     * @return array
     */
    private function getVerifiedToken(string $keyId, string $token): array
    {
        $key = $this->getKey($keyId);

        if (empty($key)) {
            throw new InvalidJwkException("Could not locate key with ID $keyId");
        }

        // Convert the JWK to a PEM for use with JWT::decode
        $converter = new JWKConverter();
        $pem = new Key($converter->toPEM($key), $key['alg']);

        // Return the decoded token
        try {
            return (array) JWT::decode($token, $pem);
        } catch (ExpiredException $e) {
            throw new JuhwitExpiredException($e->getMessage(), $e->getCode(), $e);
        } catch (\Exception $e) {
            throw new UnknownException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Verify the token has the structure we are expecting.
     *
     * @param string $token
     *
     * @throws InvalidStructureException
     */
    private function validateStructure(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new InvalidStructureException('Token requires 3 parts delimited by periods');
        }

        $decoded = array_map(function ($part) {
            // base64 url decode
            $b64 = strtr($part, '-_', '+/');

            return base64_decode($b64, true);
        }, $parts);

        $i = 0;
        foreach ($decoded as $part) {
            $i++;
            if (empty($part)) {
                throw new InvalidStructureException("Token part $i not Base64url encoded");
            }
        }

        return $decoded;
    }
}
