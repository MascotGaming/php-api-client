# MascotGaming/php-api-client
Operator API v1 client for PHP.

## Requirements

> - PHP 5.6.3 or higher
> - The **phpseclib2** library.
    >   - (required for secure 64-bit nonce generation used by the signature mechanism)
> - OpenSSL and cURL extensions (required by json-rpc client)

## Example of usage:

```php
<?php
use mascotgaming\mascot\api\client\Client;

require __DIR__.'/vendor/autoload.php';

$client = new Client(array(
        // This is the base URL for the Operator API v1.
        'url' => 'https://api.mascot.games/v1/',

        // This is the file path for the Operator API v1 key.
        'sslKeyPath' => __DIR__.'/ssl/apikey.pem',

        // Sometimes it's useful to enable debug mode.
        // 'debug' => true,
));

// This will list all games.
var_export($client->listGames(array()));
```

## Example of usage with signature authentication method:

> *NOTE*: Please refer to corresponding documentation regarding the **"Signature authentication method"**.

```php
<?php
use mascotgaming\mascot\api\client\Client;

require __DIR__.'/vendor/autoload.php';

$client = new Client(array(
    'url' => 'https://customer.mascot.games/v1/signed/',
    'debug' => true,
    'ssl_verification' => false,
    'signature_verification' => true,
    'signature' => array(
        'key_id' => 'example-key',
        'key_value' => 'ExampleKeyValue',
        'casino_id' => $myCasinoID,
    ),
));

var_export($client->listGames([]));
```
