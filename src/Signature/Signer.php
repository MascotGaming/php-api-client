<?php

namespace mascotgaming\mascot\api\client\Signature;

use phpseclib3\Math\BigInteger;

/**
 * Class Signer
 *
 * This class is responsible for generating signatures for authentication purposes.
 * It implements HMAC-SHA256-based signing to ensure message integrity and security.
 *
 *  **Requirements**
 *  - PHP ≥ 5.6
 *
 * Usage:
 * ```php
 * $signer = new Signer($keyId, $keyValue);
 * $signature = $signer->sign($data, $nonce, $timestamp);
 * ```
 */
class Signer
{
    /**
     * @var string $keyId Identifier for the key used in signature generation.
     */
    private $keyId;

    /**
     * @var string $keyValue Secret key value used for generating HMAC signatures.
     */
    private $keyValue;

    /**
     * Signer constructor.
     *
     * @param string $keyId Key ID provided to you by the API provider.
     * @param string $keyValue Key Value provided to you by the API provider.
     */
    public function __construct($keyId, $keyValue)
    {
        $this->keyId = $keyId;
        $this->keyValue = $keyValue;
    }

    /**
     * Generates an HMAC-SHA256 signature for the provided data.
     *
     * @param string $data The data that needs to be signed.
     * @param int $nonce A unique nonce (64-bit) to prevent replay attacks.
     * @param int $timestamp The timestamp (32-bit) when the request is made.
     *
     * @return string The generated signature in the format `keyId=signature`.
     */
    public function sign($data, $nonce, $timestamp)
    {
        $big = new BigInteger((string)$nonce, 10);
        $nonceBin = $big->toBytes();
        $nonceBin = str_pad($nonceBin, 8, "\x00", STR_PAD_LEFT);
        $timestampBin = pack('N', $timestamp);

        $buffer = $nonceBin . $timestampBin;
        $hmac = hash_hmac('sha256', $buffer . $data, $this->keyValue, true);

        // Returns the generated signature in the format: keyId=hex(signature)
        return "{$this->keyId}=" . bin2hex($hmac);
    }

    /**
     * @return string
     */
    public function getKeyId()
    {
        return $this->keyId;
    }
}
