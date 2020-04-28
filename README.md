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

### Requiring extra claims

A token is required to have the following claims:

* aud
* iss
* token_use
* email

If you want to require extra claims, such as `custom:foo` or `custom:user`, you can require those by providing a second argument
to the `JwtDecoder` instance.

```php
<?php

use TeamGantt\Juhwit\JwtDecoder;

$decoder = new JwtDecoder($verifier, ['custom:user', 'custom:foo']);
```

### Laravel Provider

A `JwtProvider` is included for ease of use in Laravel/Lumen. 

The following environment variables are required to ensure the `CognitoClaimVerifier` is constructed properly:

* `COGNITO_CLIENT_ID`
* `COGNITO_POOL_ID`
* `COGNITO_REGION`

To provide extra claims, create a config file in your laravel or lumen app called `cognito.php` and provide an `extraRequiredClaims` key

```php
// config/cognito.php
<?php

return [
    'extraRequiredClaims' => ['custom:user', 'custom:foo']
];
```

If this config is not provided, only the default claims will be required.

## Running Tests

```
$ vendor/bin/kahlan
```
