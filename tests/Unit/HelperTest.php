<?php

namespace Unit;

use PHPUnit\Framework\TestCase;
use mascotgaming\mascot\api\client\Helper;
use mascotgaming\mascot\api\client\ParamType;

class HelperTest extends TestCase
{
    public function testRequiredParamPassesForString()
    {
        Helper::requiredParam(array('name' => 'test'), 'name', ParamType::STRING);

        $this->assertTrue(true);
    }

    public function testRequiredParamThrowsWhenMissing()
    {
        $this->expectException(\Exception::class);

        Helper::requiredParam(array(), 'name', ParamType::STRING);
    }

    public function testRequiredParamThrowsOnInvalidString()
    {
        $this->expectException(\Exception::class);

        Helper::requiredParam(array('name' => 123), 'name', ParamType::STRING);
    }

    public function testRequiredParamPassesForInteger()
    {
        Helper::requiredParam(array('id' => 123), 'id', ParamType::INTEGER);

        $this->assertTrue(true);
    }

    public function testRequiredParamThrowsOnInvalidInteger()
    {
        $this->expectException(\Exception::class);

        Helper::requiredParam(array('id' => '123'), 'id', ParamType::INTEGER);
    }

    public function testRequiredParamPassesForArray()
    {
        Helper::requiredParam(array('items' => array(1, 2, 3)), 'items', ParamType::T_ARRAY);

        $this->assertTrue(true);
    }

    public function testRequiredParamThrowsOnInvalidArray()
    {
        $this->expectException(\Exception::class);

        Helper::requiredParam(array('items' => 'test'), 'items', ParamType::T_ARRAY);
    }

    public function testOptionalParamSkipsMissingParam()
    {
        Helper::optionalParam(array(), 'name', ParamType::STRING);

        $this->assertTrue(true);
    }

    public function testOptionalParamValidatesExistingParam()
    {
        $this->expectException(\Exception::class);

        Helper::optionalParam(array('name' => 123), 'name', ParamType::STRING);
    }

    public function testRequiredParamCallsCallback()
    {
        $called = false;

        Helper::requiredParam(
            array('name' => 'test'),
            'name',
            ParamType::STRING,
            function ($params, $key, $type) use (&$called) {
                $called = true;

                $this->assertSame('test', $params[$key]);
                $this->assertSame('name', $key);
                $this->assertSame(ParamType::STRING, $type);
            }
        );

        $this->assertTrue($called);
    }

    public function testOptionalParamCallsCallback()
    {
        $called = false;

        Helper::optionalParam(
            array('name' => 'test'),
            'name',
            ParamType::STRING,
            function () use (&$called) {
                $called = true;
            }
        );

        $this->assertTrue($called);
    }

    public function testStrictValuesPasses()
    {
        Helper::strictValues(array('type' => 'deposit'), 'type', array('deposit', 'withdraw'));

        $this->assertTrue(true);
    }

    public function testStrictValuesThrows()
    {
        $this->expectException(\Exception::class);

        Helper::strictValues(array('type' => 'bonus'), 'type', array('deposit', 'withdraw'));
    }

    public function testRequiredParamPassesForIp()
    {
        Helper::requiredParam(array('ip' => '127.0.0.1'), 'ip', ParamType::IP_ADDRESS);

        $this->assertTrue(true);
    }

    public function testRequiredParamThrowsOnInvalidIp()
    {
        $this->expectException(\Exception::class);

        Helper::requiredParam(array('ip' => 'not-an-ip'), 'ip', ParamType::IP_ADDRESS);
    }
}