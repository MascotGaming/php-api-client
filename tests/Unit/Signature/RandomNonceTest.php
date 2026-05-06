<?php

namespace Unit\Signature;

use mascotgaming\mascot\api\client\Signature\RandomNonce;
use PHPUnit\Framework\TestCase;
use phpseclib3\Math\BigInteger;

class RandomNonceTest extends TestCase
{
    public function testReturnsNumericString()
    {
        $nonce = new RandomNonce();
        $value = $nonce->next();

        $this->assertIsString($value);
        $this->assertMatchesRegularExpression('/^[0-9]+$/', $value);
    }

    public function testReturnsUint64Value()
    {
        $nonce = new RandomNonce();
        $value = new BigInteger($nonce->next(), 10);

        $maxUint64 = new BigInteger('18446744073709551615', 10);

        $this->assertTrue($value->compare($maxUint64) <= 0);
        $this->assertTrue($value->compare(new BigInteger('0', 10)) >= 0);
    }

    public function testGeneratesDifferentValues()
    {
        $nonce = new RandomNonce();
        $values = array();

        for ($i = 0; $i < 1000; $i++) {
            $values[] = $nonce->next();
        }

        $this->assertCount(count(array_unique($values)), $values);
    }
}