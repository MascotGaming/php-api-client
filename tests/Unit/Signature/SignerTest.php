<?php

namespace Unit\Signature;

use mascotgaming\mascot\api\client\Signature\Signer;
use PHPUnit\Framework\TestCase;

class SignerTest extends TestCase
{
	public function testSignGeneratesExpectedSignature()
	{
		$key = 'my_test_key_value!@#$%^&*(';
		$service = new Signer("secret-key", $key);
		$data = 'test payload';
		$nonce = '1580145857615089920';
		$timestamp = 1762935805;

		$signature = $service->sign($data, $nonce, $timestamp);
		$this->assertEquals("secret-key=9de6aa92b03efd6a96dd3d0c68e56c33a674d339462066467658ba9e68f38934", $signature);
	}
}
