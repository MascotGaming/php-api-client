<?php

namespace Unit\Signature;

use JsonRPC\Client;
use JsonRPC\HttpClient;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use mascotgaming\mascot\api\client\Signature\Middleware;
use mascotgaming\mascot\api\client\Signature\Signer;
use mascotgaming\mascot\api\client\Signature\Nonce;

class MiddlewareTest extends TestCase
{
    private $keyId = 'testKey';

    private function headersToMap(array $headers)
    {
        $map = array();

        foreach ($headers as $h) {
            if (!is_string($h)) {
                continue;
            }

            $parts = explode(':', $h, 2);
            $name  = trim($parts[0]);
            $value = isset($parts[1]) ? trim($parts[1]) : '';

            $map[$name] = $value;
        }

        return $map;
    }

    public function testAttachRegistersCallbackAndAddsHeaders()
    {
        $signer = $this->getMockBuilder(Signer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['sign'])
            ->getMock();

        $nonce = $this->getMockBuilder(Nonce::class)
            ->onlyMethods(['next'])
            ->getMock();

        $httpClient = $this->getMockBuilder(HttpClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['withHeaders', 'withBeforeRequestCallback'])
            ->getMock();

        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHttpClient'])
            ->getMock();

        $client->expects($this->once())
            ->method('getHttpClient')
            ->willReturn($httpClient);

        $nonce->expects($this->once())
            ->method('next')
            ->willReturn('123');

        $payload = '{"jsonrpc":"2.0","method":"testMethod","params":{"callerId":1},"id":0}';

        $signer->expects($this->once())
            ->method('sign')
            ->with($payload, '123', $this->isType('int'))
            ->willReturn($this->keyId . '=deadbeef');

        $capturedCallback = null;

        $httpClient->expects($this->once())
            ->method('withBeforeRequestCallback')
            ->willReturnCallback(function ($cb) use (&$capturedCallback) {
                $capturedCallback = $cb;
            });

        /** @noinspection PhpParamsInspection */
        $httpClient->expects($this->once())
            ->method('withHeaders')
            ->with($this->callback(function (array $headers) {
                $map = $this->headersToMap($headers);

                $this->assertSame('application/json', $map['Content-Type']);
                $this->assertSame('casino:1', $map['X-Subject']);
                $this->assertSame('123', $map['X-Nonce']);
                $this->assertArrayHasKey('X-Timestamp', $map);
                $this->assertGreaterThan(0, (int)$map['X-Timestamp']);
                $this->assertSame($this->keyId . '=deadbeef', $map['X-Signature']);

                return true;
            }));

        Middleware::attach($client, $signer, $nonce, 'casino:1');

        $this->assertNotNull($capturedCallback);

        call_user_func($capturedCallback, $httpClient, $payload);
    }

    public function testAttachDoesNotOverwriteExistingHeaders()
    {
        $signer = $this->getMockBuilder(Signer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['sign'])
            ->getMock();

        $nonce = $this->getMockBuilder(Nonce::class)
            ->onlyMethods(['next'])
            ->getMock();

        $httpClient = $this->getMockBuilder(HttpClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['withHeaders', 'withBeforeRequestCallback'])
            ->getMock();

        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHttpClient'])
            ->getMock();

        $client->expects($this->once())
            ->method('getHttpClient')
            ->willReturn($httpClient);

        $nonce->expects($this->once())
            ->method('next')
            ->willReturn('123');

        $payload = '{"jsonrpc":"2.0","method":"testMethod","params":{"callerId":1},"id":0}';

        $signer->expects($this->once())
            ->method('sign')
            ->with($payload, '123', $this->isType('int'))
            ->willReturn($this->keyId . '=deadbeef');

        $capturedCallback = null;
        $existingHeaders = ['X-Custom' => 'foo'];

        $httpClient->expects($this->once())
            ->method('withBeforeRequestCallback')
            ->willReturnCallback(function ($cb) use (&$capturedCallback) {
                $capturedCallback = $cb;
            });

        $capturedHeaders = [];

        $httpClient->expects($this->once())
            ->method('withHeaders')
            ->willReturnCallback(function (array $headers) use (&$capturedHeaders) {
                $capturedHeaders = $headers;
            });

        Middleware::attach($client, $signer, $nonce, 'casino:1');

        $this->assertNotNull($capturedCallback);

        call_user_func($capturedCallback, $httpClient, $payload);

        $map = $this->headersToMap($capturedHeaders);
        $map = array_merge($existingHeaders, $map);

        $this->assertArrayHasKey('X-Custom', $map);
        $this->assertSame('foo', $map['X-Custom']);

        $this->assertSame('application/json', $map['Content-Type']);
        $this->assertSame('casino:1', $map['X-Subject']);
        $this->assertSame('123', $map['X-Nonce']);
        $this->assertArrayHasKey('X-Timestamp', $map);
        $this->assertGreaterThan(0, (int)$map['X-Timestamp']);
        $this->assertSame($this->keyId . '=deadbeef', $map['X-Signature']);
    }

    public function testAttachThrowsOnEmptySubject()
    {
        $this->expectException(RuntimeException::class);

        $signer = $this->getMockBuilder(Signer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $nonce = $this->getMockBuilder(Nonce::class)
            ->getMock();

        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        Middleware::attach($client, $signer, $nonce, '');
    }
}
