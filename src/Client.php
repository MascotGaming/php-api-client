<?php

namespace mascotgaming\mascot\api\client;

use JsonRPC\HttpClient;

class Client
{
    /**
     * @var \JsonRPC\Client
     */
    private $_client;

    /**
     * Client constructor.
     *
     * Config options:
     *  - url (string, required)
     *      Base URL of the JSON-RPC 2.0 server.
     *
     *  - debug (bool, optional, default: false)
     *      Enables HTTP debug output to STDERR.
     *
     *  - ssl_verification (bool, optional, default: true)
     *      Enables/disables TLS certificate verification.
     *      If set to false, HTTP client will skip SSL verification.
     *
     *  - sslKeyPath (string, optional)
     *      Path to local client certificate (PEM) used for TLS authentication.
     *
     *  - signature_verification (bool, optional, default: false)
     *      Enables request signing and response verification middleware.
     *      When true, the "signature" section must be provided.
     *
     *  - signature (array, required when signature_verification = true)
     *      - key_id (string, required)
     *      - key_value (string, required)
     *      - casino_id (int|string, required)
     *      - nonce_start (int|string, optional)
     *          Enables sequential nonce generation starting from the specified value.
     *          If omitted, cryptographically secure random uint64 nonces are used.
     *
     * @param array $config
     *
     * @throws Exception If the required configuration is missing or invalid.
     */
    public function __construct($config)
    {
        if (!array_key_exists('url', $config)) {
            throw new Exception('You must specify url for API');
        }

        $http = new HttpClient($config['url']);

        if (array_key_exists('debug', $config) && $config['debug'] === true) {
            $http->withDebug();
        }

        if (array_key_exists('ssl_verification', $config) && $config['ssl_verification'] === false) {
            $http->withoutSslVerification();
        }

        if (array_key_exists('sslKeyPath', $config)) {
            $http->withSslLocalCert($config['sslKeyPath']);
        }

        $this->_client = new \JsonRPC\Client(null, false, $http);

        if (array_key_exists('signature_verification', $config) && $config['signature_verification'] === true) {
            if (!array_key_exists('signature', $config)) {
                throw new Exception('signature_verification is on, but "signature" section is missing');
            }

            $signatureConfig = $config['signature'];

            if (!array_key_exists('key_id', $signatureConfig) ||
                !array_key_exists('key_value', $signatureConfig) ||
                !array_key_exists('casino_id', $signatureConfig)) {
                throw new Exception('signature.key_id, signature.key_value and signature.casino_id must be specified');
            }

            $keyId = $signatureConfig['key_id'];
            $keyValue = $signatureConfig['key_value'];
            $casinoId = $signatureConfig['casino_id'];

            $start = array_key_exists('nonce_start', $signatureConfig) ? $signatureConfig['nonce_start'] : '0';

            if ($start == 0) {
                $nonce = new Signature\RandomNonce();
            } else {
                $nonce = new Signature\SequentialNonce($start);
            }
            $signer = new Signature\Signer($keyId, $keyValue);

            Signature\Middleware::attach(
                $this->_client,
                $signer,
                $nonce,
                'casino:' . $casinoId
            );
        }
    }

    /**
     * @return \JsonRPC\Client
     */
    private function getClient()
    {
        return $this->_client;
    }

    /**
     * @param string $method
     * @param array  $params
     *
     * @return array
     */
    private function execute($method, $params = array())
    {
        return $this->getClient()->execute($method, $params);
    }

    /**
     * Returns a list of available games.
     *
     * Available params:
     *  - BankGroupId (string, optional)
     *
     * Example:
     * <code>
     * $client->listGames(array(
     *     'BankGroupId' => 'GoldenBet_EUR',
     * ));
     * </code>
     *
     * @param array $params
     *
     * @return array
     */
    public function listGames($params)
    {
        Helper::optionalParam($params, 'BankGroupId', ParamType::STRING);

        return $this->execute('Game.List', $params);
    }

    /**
     * Creates or updates a bank group (aka "upsert").
     *
     * Available params:
     *  - Id (string, required)
     *  - Currency (string, required)
     *  - SettingsPatch (string, optional)
     *
     * Example:
     * <code>
     * $client->setBankGroup(array(
     *     'Id' => 'GoldenBet_EUR',
     *     'Currency' => 'EUR',
     *     'SettingsPatch' => 'my_custom_patch',
     * ));
     * </code>
     *
     * @param array $bankGroup
     *
     * @return array
     */
    public function setBankGroup($bankGroup)
    {
        Helper::requiredParam($bankGroup, 'Id', ParamType::STRING);
        Helper::requiredParam($bankGroup, 'Currency', ParamType::STRING);
        Helper::optionalParam($bankGroup, 'SettingsPatch', ParamType::STRING);

        return $this->execute('BankGroup.Set', $bankGroup);
    }

    /**
     * Creates or updates a player (aka "upsert").
     *
     * Available params:
     *  - Id (string, required)
     *  - BankGroupId (string, required)
     *  - Nick (string, optional)
     *
     * Example:
     * <code>
     * $client->setPlayer(array(
     *     'Id' => 'LuckyPlayer11_EUR',
     *     'BankGroupId' => 'GoldenBet_EUR',
     *     'Nick' => 'LuckyPlayer11',
     * ));
     * </code>
     *
     * @param array $player
     *
     * @return array
     */
    public function setPlayer($player)
    {
        Helper::requiredParam($player, 'Id', ParamType::STRING);
        Helper::requiredParam($player, 'BankGroupId', ParamType::STRING);
        Helper::optionalParam($player, 'Nick', ParamType::STRING);

        return $this->execute('Player.Set', $player);
    }

    /**
     * Registers a bonus.
     *
     * Available params:
     *  - Id (string, required)
     *
     * Example:
     * <code>
     * $client->setBonus(array(
     *     'Id' => 'WelcomeBonusSpainPlayersJan2024',
     * ));
     * </code>
     *
     * @param array $bonus
     *
     * @return array
     */
    public function setBonus($bonus)
    {
        Helper::requiredParam($bonus, 'Id', ParamType::STRING);

        return $this->execute('Bonus.Set', $bonus);
    }

    /**
     * Get a list of unprocessed `collectBonusReward` transactions that are less than 24 hours old.
     *
     * @return array
     */
    public function getPendingBonusTransactions()
    {
        return $this->execute('Bonus.GetPendingBonusTransactions');
    }

    /**
     * Creates a game session.
     *
     * Available params:
     *  - PlayerId (string, required)
     *  - GameId (string, required)
     *  - BonusId (string, optional)
     *  - RestorePolicy (string, optional)
     *      Allowed values:
     *          - Restore
     *          - Create
     *  - StaticHost (string, optional)
     *  - AlternativeId (string, optional)
     *  - PlayerIp (string, optional, valid IPv4/IPv6)
     *  - BaseHost (string, optional)
     *
     * Example:
     * <code>
     * $client->createSession(array(
     *     'PlayerId' => 'LuckyPlayer11_EUR',
     *     'GameId' => 'riot',
     *     'PlayerIp' => '127.0.0.1',
     * ));
     * </code>
     *
     * @param array $session
     *
     * @return array
     */
    public function createSession($session)
    {
        Helper::requiredParam($session, 'PlayerId', ParamType::STRING);
        Helper::requiredParam($session, 'GameId', ParamType::STRING);
        Helper::optionalParam($session, 'BonusId', ParamType::STRING);
        Helper::optionalParam($session, 'RestorePolicy', ParamType::STRING, function ($params, $key, $type) {
            Helper::strictValues($params, $key, array('Restore', 'Create'));
        });
        Helper::optionalParam($session, 'StaticHost', ParamType::STRING);
        Helper::optionalParam($session, 'AlternativeId', ParamType::STRING);
        Helper::optionalParam($session, 'PlayerIp', ParamType::IP_ADDRESS);
        Helper::optionalParam($session, 'BaseHost', ParamType::STRING);

        return $this->execute('Session.Create', $session);
    }

    /**
     * Creates a demo session.
     *
     * Available params:
     *  - GameId (string, required)
     *  - BankGroupId (string, required)
     *  - StartBalance (integer, optional)
     *  - StaticHost (string, optional)
     *  - PlayerIp (string, optional, valid IPv4/IPv6)
     *  - BaseHost (string, optional)
     *
     * Example:
     * <code>
     * $client->createDemoSession(array(
     *     'GameId' => 'riot',
     *     'BankGroupId' => 'GoldenBet_EUR',
     *     'StartBalance' => 100000,
     *     'PlayerIp' => '127.0.0.1',
     * ));
     * </code>
     *
     * @param array $demoSession
     *
     * @return array
     */
    public function createDemoSession($demoSession)
    {
        Helper::requiredParam($demoSession, 'GameId', ParamType::STRING);
        Helper::requiredParam($demoSession, 'BankGroupId', ParamType::STRING);
        Helper::optionalParam($demoSession, 'StartBalance', ParamType::INTEGER);
        Helper::optionalParam($demoSession, 'StaticHost', ParamType::STRING);
        Helper::optionalParam($demoSession, 'PlayerIp', ParamType::IP_ADDRESS);
        Helper::optionalParam($demoSession, 'BaseHost', ParamType::STRING);

        return $this->execute('Session.CreateDemo', $demoSession);
    }

    /**
     * Closes a specified session.
     *
     * Available params:
     *  - SessionId (string, required)
     *
     * Example:
     * <code>
     * $client->closeSession(array(
     *     'SessionId' => 'xxxxxxxxxxxxxxx',
     * ));
     * </code>
     *
     * @param array $session
     *
     * @return array
     */
    public function closeSession($session)
    {
        Helper::requiredParam($session, 'SessionId', ParamType::STRING);

        return $this->execute('Session.Close', $session);
    }

    /**
     * Returns token to access Game History interface.
     *
     * Available params:
     *  - SessionId (string, required)
     *  - ExpiryInSeconds (integer, required)
     *
     * Example:
     * <code>
     * $client->getHistoryToken(array(
     *     'SessionId' => 'xxxxxxxxxxxxxxx',
     *     'ExpiryInSeconds' => 300,
     * ));
     * </code>
     *
     * @param array $params
     *
     * @return array
     */
    public function getHistoryToken($params)
    {
        Helper::requiredParam($params, 'SessionId', ParamType::STRING);
        Helper::requiredParam($params, 'ExpiryInSeconds', ParamType::INTEGER);

        return $this->execute('History.GetToken', $params);
    }
}
