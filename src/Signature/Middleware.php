<?php

namespace mascotgaming\mascot\api\client\Signature;

use JsonRPC\Client;
use JsonRPC\HttpClient;
use RuntimeException;

class Middleware
{
	/**
	 * Attachable signing middleware for the JsonRPC client.
	 *
	 * @param Client $client  client to attach middleware to.
	 * @param Signer $signer  Signer to sign requests.
	 * @param Nonce  $nonce   Nonce generator/source.
	 * @param string $subject Subject identifier.
	 *
	 * @throws RuntimeException
	 */
	public static function attach(Client $client, Signer $signer, Nonce $nonce, $subject)
	{
        if (!is_string($subject) || $subject === '') {
            throw new RuntimeException('Subject must be a non-empty string.');
        }

		$http = $client->getHttpClient();

		$callback = function (HttpClient $httpClient, $payload) use ($signer, $subject, $nonce) {
			$n = $nonce->next();
			$timestamp = time();
			$signature = $signer->sign($payload, $n, $timestamp);

            $headers = array(
                'Content-Type: application/json',
                'X-Subject: ' . $subject,
                'X-Nonce: ' . $n,
                'X-Timestamp: ' . $timestamp,
                'X-Signature: ' . $signature,
            );

            $httpClient->withHeaders($headers);
		};

        $http->withBeforeRequestCallback($callback);
	}
}
