# Juhwit

Verify JWT's from AWS Cognito

## Usage

Juhwit ships with a handful of interfaces and their default implementations.

The main service provided by Juhwit is the `JwtDecoder` which is composed with the complimentary `CognitoClaimVerifier`.

```php
<?php

use TeamGantt\Juhwit\JwtDecoder;
use TeamGantt\Juhwit\CognitoClaimVerifier;

// Create a CognitoClaimVerifier with information about the AWS user pool
$clientId = 'some client id from cognito';
$poolId = 'some pool id from cognito';
$region = 'us-east-2';

$verifier = new CognitoClaimVerifier($clientId, $poolId, $region);
$decoder = new JwtDecoder($verifier);

// we need some public keys in the form of a jwk (accessible via cognito)
$pathToJwk = '/some/path/to/jwk.json';

// If all is valid we will get a token back - otherwise a TokenException is thrown
$token = $decoder->decode($someTokenFromARequest, $pathToJwk);
```

### Laravel Provider

A `JwtProvider` is included for ease of use in Laravel/Lumen. 

The following environment variables are required to ensure the `CognitoClaimVerifier` is constructed properly:

* `COGNITO_CLIENT_ID`
* `COGNITO_POOL_ID`
* `COGNITO_REGION`

### Note

We are fairly opinionated on the structure of the JWT for now. See the `invariant()` method of the `Token` model.

Future versions may relax things a bit.

We currently rely on a `custom:user_id` attribute being present in the token. This attribute should be configured as a read only attribute in the Cognito user pool.

## Running Tests

```
$ vendor/bin/kahlan
```
