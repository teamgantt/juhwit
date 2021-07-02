# Juhwit

Verify JWT's from AWS Cognito

## Usage

Juhwit ships with a handful of interfaces and their default implementations.

The main service provided by Juhwit is the `JwtDecoder` which is composed with the complimentary `CognitoClaimVerifier`.

```php
<?php

use TeamGantt\Juhwit\JwtDecoder;
use TeamGantt\Juhwit\Models\UserPool;
use TeamGantt\Juhwit\CognitoClaimVerifier;

// Create a UserPool to pass to the CognitoClaimVerifier
$poolId = 'some pool id from cognito';
$clientIds = ['some client id from cognito'];
$region = 'us-east-2';

// we need some public keys in the form of a jwk (accessible via cognito)
$jwk = json_decode(file_get_contents('path/to/jwk.json'), true);

$pool = new UserPool($poolId, $clientIds, $region, $jwk);
$verifier = new CognitoClaimVerifier($pool);
$decoder = new JwtDecoder($verifier);

// If all is valid we will get a token back - otherwise a TokenException is thrown
$token = $decoder->decode($someTokenFromARequest);
```

### Requiring extra claims

A token may be required to have certain claims.

If you want to require claims, such as `custom:foo` or `custom:user`, you can require those by providing a second argument to the `JwtDecoder` instance.

```php
<?php

use TeamGantt\Juhwit\JwtDecoder;

$decoder = new JwtDecoder($verifier, ['custom:user', 'custom:foo']);
```

It is also possible to require claim values to be a specific value.

```php
use TeamGantt\Juhwit\JwtDecoder;

$decoder = new JwtDecoder($verifier, ['custom:user', 'token_use' => 'id']);
```

Keep in mind that instances of `Token` will perform their own checks against required claims. See TeamGantt\Juhwit\Models\Token::getClaimsErrors() for more information.

## Leveraging docker

Juhwit is tested and developed against PHP 7.4.11. This project uses a combination of docker and [direnv](https://direnv.net/)
to keep a consistent environment. To leverage direnv, `cd` into the juhwit project directory and run the following:

```
$ docker build -t juhwit:dev .
$ direnv allow
```

This will put your current terminal into an environment that uses the dockerized php and composer binaries. You can use them like you normally would
i.e:

```
$ php -v
$ composer list
```


## Running Tests

```
$ composer test
```
