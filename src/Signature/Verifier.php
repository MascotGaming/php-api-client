<?php

namespace mascotgaming\mascot\api\client\Signature;

use InvalidArgumentException;

/**
 * Class Verifier
 *
 * Validates request signatures.
 *
 * The Verifier checks:
 *  - that the request `subject` matches the configured subject
 *  - that the timestamp is within the allowed TTL window
 *  - that the provided signature matches the expected signature
 *
 * Configuration array structure:
 *
 * ```php
 * $config = [
 * 'subject' => 'casino:' . $casinoId, // string: required, must match the subject sent by the client
 * 'signature_ttl_minutes' => 5, // int: required, max allowed age of the request in minutes
 * ];
 * ```
 *
 * Usage example:
 *
 * ```php
 * $signer = new Signer($keyId, $keyValue);
 * $verifier = new Verifier($signer, [
 *     'subject' => 'api-client',
 *     'signature_ttl_minutes' => 5,
 * ]);
 *
 * $isValid = $verifier->verify($nonce, $signature, $subject, $timestamp, $body);
 * ```
 *
 * @package mascotgaming\mascot\api\client\Signature
 */
class Verifier
{
    private $signer;
    private $config;

    /**
     * Verifier constructor.
     *
     * @param mixed $signer The signer instance used to generate expected signatures.
     * @param array $config Configuration containing 'subject' and 'signature_ttl_minutes'.
     *
     * @throws InvalidArgumentException If required config values are missing or invalid.
     */
    public function __construct($signer, array $config)
    {
        if (!is_object($signer) || !method_exists($signer, 'sign')) {
            throw new InvalidArgumentException("Signer must be an object with method sign(\$body, \$nonce, \$timestamp).");
        }

        if (!isset($config['subject']) || !is_string($config['subject']) || $config['subject'] === '') {
            throw new InvalidArgumentException("Invalid or missing 'subject' in config.");
        }

        if (!isset($config['signature_ttl_minutes']) || !is_int($config['signature_ttl_minutes']) || $config['signature_ttl_minutes'] <= 0) {
            throw new InvalidArgumentException("Invalid or missing 'signature_ttl_minutes' in config.");
        }

        $this->signer = $signer;
        $this->config = $config;
    }

    /**
     * Verifies the request signature based on headers and payload.
     *
     * @param string $nonce     The unique request identifier.
     * @param string $signature The provided signature.
     * @param string $subject   The request subject.
     * @param string $timestamp The timestamp header of the request.
     * @param string $body      The raw request body.
     *
     * @return bool True if the signature is valid, otherwise false.
     */
    public function verify($nonce, $signature, $subject, $timestamp, $body)
    {
        // Validate subject
        if ($subject !== $this->config['subject']) {
            return false;
        }

        // Validate timestamp within TTL
        $now = time();
        $timestampInt = (int)$timestamp;
        $ttlSeconds = $this->config['signature_ttl_minutes'] * 60;

        if ($timestampInt < ($now - $ttlSeconds)) {
            return false;
        }

        // Generate the expected signature
        $expectedSignature = $this->signer->sign($body, $nonce, $timestampInt);

        // Compare signatures
        return hash_equals($expectedSignature, $signature);
    }
}
