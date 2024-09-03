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
