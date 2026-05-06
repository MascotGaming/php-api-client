<?php

namespace Unit\Signature;

use mascotgaming\mascot\api\client\Signature\SequentialNonce;
use PHPUnit\Framework\TestCase;

class SequentialNonceTest extends TestCase
{
	public function testIncrementsFromGivenIntStart()
	{
		$gen = new SequentialNonce(100);
		$this->assertSame('101', $gen->next());
		$this->assertSame('102', $gen->next());
		$this->assertSame('103', $gen->next());
	}

	public function testWorksWithStringStartAndLargeValues()
	{
		$gen = new SequentialNonce('9223372036854775806'); // 2^63-2
		$this->assertSame('9223372036854775807', $gen->next());
		$this->assertSame('9223372036854775808', $gen->next()); // beyond signed 64-bit
		$this->assertSame('9223372036854775809', $gen->next());
	}

	public function testMonotonicityOverManySteps()
	{
		$gen = new SequentialNonce(0);
		$prev = $gen->next();
		for ($i = 0; $i < 1000; $i++) {
			$cur = $gen->next();
			$this->assertGreaterThan($prev, $cur);
			$prev = $cur;
		}
	}
}
