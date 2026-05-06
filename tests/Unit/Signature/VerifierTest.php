<?php

namespace Unit\Signature;

use mascotgaming\mascot\api\client\Signature\Signer;
use mascotgaming\mascot\api\client\Signature\Verifier;
use PHPUnit\Framework\TestCase;

class VerifierTest extends TestCase
{
    private $keyId = 'testKey';
    private $keyValue = 'SomeSecretKey==';

    public function testValidSignatureReturnsTrue()
    {
        $signer = new Signer($this->keyId, $this->keyValue);

        $nonce = '12454753469419480291';
        $timestamp = time();
        $body = '{"jsonrpc":"2.0","method":"testMethod","params":{"callerId":1122},"id":0}';

        $signature = $signer->sign($body, $nonce, $timestamp);

        $config = [
            'subject' => 'casino=1122',
            'signature_ttl_minutes' => 5,
        ];

        $verifier = new Verifier($signer, $config);

        $this->assertTrue($verifier->verify($nonce, $signature, 'casino=1122', $timestamp, $body));
    }

    public function testSignatureMismatchReturnsFalse()
    {
        $signer = new Signer($this->keyId, $this->keyValue);

        $nonce = '123456789';
        $timestamp = time();
        $body = '{"example":"data"}';

        $wrongSignature = 'testKey=deadbeef'; // invalid signature

        $config = [
            'subject' => 'api-client',
            'signature_ttl_minutes' => 5,
        ];

        $verifier = new Verifier($signer, $config);

        $this->assertFalse($verifier->verify($nonce, $wrongSignature, 'api-client', $timestamp, $body));
    }

    public function testExpiredTimestampReturnsFalse()
    {
        $signer = new Signer($this->keyId, $this->keyValue);

        $nonce = '123456789';
        $timestamp = time() - (100 * 60);
        $body = '{"example":"data"}';

        $signature = $signer->sign($body, $nonce, $timestamp);

        $config = [
            'subject' => 'api-client',
            'signature_ttl_minutes' => 5,
        ];

        $verifier = new Verifier($signer, $config);

        $this->assertFalse($verifier->verify($nonce, $signature, 'api-client', $timestamp, $body));
    }
}
