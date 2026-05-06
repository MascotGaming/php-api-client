<?php

namespace Unit;

use PHPUnit\Framework\TestCase;
use mascotgaming\mascot\api\client\Client;
use mascotgaming\mascot\api\client\Exception;

class ClientTest extends TestCase
{
    private function client()
    {
        $client = new Client(array('url' => 'https://example.com'));

        $fake = new FakeJsonRpcClient();

        $property = new \ReflectionProperty($client, '_client');
        $property->setAccessible(true);
        $property->setValue($client, $fake);

        return array($client, $fake);
    }

    public function testConstructorThrowsWithoutUrl()
    {
        $this->expectException(Exception::class);

        new Client(array());
    }

    public function testListGames()
    {
        list($client, $fake) = $this->client();

        $result = $client->listGames(array('BankGroupId' => 'main'));

        $this->assertSame('Game.List', $fake->method);
        $this->assertSame(array('BankGroupId' => 'main'), $fake->params);
        $this->assertSame(array('ok' => true), $result);
    }

    public function testSetBankGroup()
    {
        list($client, $fake) = $this->client();

        $params = array(
            'Id' => 'bg1',
            'Currency' => 'EUR',
            'SettingsPatch' => 10,
        );

        $client->setBankGroup($params);

        $this->assertSame('BankGroup.Set', $fake->method);
        $this->assertSame($params, $fake->params);
    }

    public function testSetBankGroupThrowsWithoutId()
    {
        list($client) = $this->client();

        $this->expectException(Exception::class);

        $client->setBankGroup(array('Currency' => 'EUR'));
    }

    public function testSetPlayer()
    {
        list($client, $fake) = $this->client();

        $params = array(
            'Id' => 'player1',
            'BankGroupId' => 'bg1',
            'Nick' => 'test',
        );

        $client->setPlayer($params);

        $this->assertSame('Player.Set', $fake->method);
        $this->assertSame($params, $fake->params);
    }

    public function testSetBonus()
    {
        list($client, $fake) = $this->client();

        $params = array('Id' => 'bonus1');

        $client->setBonus($params);

        $this->assertSame('Bonus.Set', $fake->method);
        $this->assertSame($params, $fake->params);
    }

    public function testGetPendingBonusTransactions()
    {
        list($client, $fake) = $this->client();

        $client->getPendingBonusTransactions();

        $this->assertSame('Bonus.GetPendingBonusTransactions', $fake->method);
        $this->assertSame(array(), $fake->params);
    }

    public function testCreateSession()
    {
        list($client, $fake) = $this->client();

        $params = array(
            'PlayerId' => 'player1',
            'GameId' => 'game1',
            'RestorePolicy' => 'Restore',
            'BaseHost' => '127.0.0.1',
        );

        $client->createSession($params);

        $this->assertSame('Session.Create', $fake->method);
        $this->assertSame($params, $fake->params);
    }

    public function testCreateSessionThrowsOnInvalidRestorePolicy()
    {
        list($client) = $this->client();

        $this->expectException(Exception::class);

        $client->createSession(array(
            'PlayerId' => 'player1',
            'GameId' => 'game1',
            'RestorePolicy' => 'Bad',
        ));
    }

    public function testCreateSessionPassesWithValidPlayerIp()
    {
        list($client, $fake) = $this->client();

        $params = array(
            'PlayerId' => 'player1',
            'GameId' => 'game1',
            'PlayerIp' => '192.168.1.1',
        );

        $client->createSession($params);

        $this->assertSame('Session.Create', $fake->method);
    }

    public function testCreateSessionThrowsOnInvalidPlayerIp()
    {
        list($client) = $this->client();

        $this->expectException(Exception::class);

        $client->createSession(array(
            'PlayerId' => 'player1',
            'GameId' => 'game1',
            'PlayerIp' => 'not-an-ip',
        ));
    }

    public function testCreateDemoSession()
    {
        list($client, $fake) = $this->client();

        $params = array(
            'GameId' => 'game1',
            'BankGroupId' => 'bg1',
            'StartBalance' => 1000,
        );

        $client->createDemoSession($params);

        $this->assertSame('Session.CreateDemo', $fake->method);
        $this->assertSame($params, $fake->params);
    }

    public function testCloseSession()
    {
        list($client, $fake) = $this->client();

        $params = array('SessionId' => 'session1');

        $client->closeSession($params);

        $this->assertSame('Session.Close', $fake->method);
        $this->assertSame($params, $fake->params);
    }

    public function testGetHistoryToken()
    {
        list($client, $fake) = $this->client();

        $params = array(
            'SessionId' => 'session1',
            'ExpiryInSeconds' => 3600,
        );

        $client->getHistoryToken($params);

        $this->assertSame('History.GetToken', $fake->method);
        $this->assertSame($params, $fake->params);
    }
}

class FakeJsonRpcClient
{
    public $method;
    public $params;

    public function execute($method, $params = array())
    {
        $this->method = $method;
        $this->params = $params;

        return array('ok' => true);
    }
}