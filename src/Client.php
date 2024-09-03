<?php
namespace mascotgaming\mascot\api\client;

class Client
{
	/**
	 * @var \JsonRPC\Client
	 */
	private $_client;

	/**
	 * Client constructor.
	 *
	 * Config array:
	 *  - url              string JSON-RPC 2.0 Server.
	 *  - debug            boolean Debug mode.
	 *  - ssl_verification boolean Certificate verification of HTTP connection over TLS.
	 *
	 * @param array $config
	 * @throws Exception
	 */
	public function __construct($config)
	{
		if(!array_key_exists('url', $config))
		{
			throw new Exception('You must specify url for API');
		}

		$http = new \JsonRPC\HttpClient($config['url']);

		if(array_key_exists('debug', $config) && $config['debug'] === true)
		{
			$http->withDebug();
		}

		if(array_key_exists('ssl_verification', $config) && $config['ssl_verification'] === false)
		{
			$http->withoutSslVerification();
		}

		$http->withSslLocalCert($config['sslKeyPath']);
		$this->_client = new \JsonRPC\Client(null, false, $http);
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
	 * @param array $params
	 * @return array
	 */
	private function execute($method, $params = array())
	{
		return $this->getClient()->execute($method, $params);
	}

	/**
	 * Returns a list of available games.
	 *
	 * @param array $params
	 * @return array
	 */
	public function listGames($params)
	{
		Helper::optionalParam($params, 'BankGroupId', ParamType::STRING);

		return $this->execute('Game.List');
	}

	/**
	 * Creates or updates a bank group (aka "upsert").
	 *
	 * @param array $bankGroup
	 * @return array
	 */
	public function setBankGroup($bankGroup)
	{
		Helper::requiredParam($bankGroup, 'Id', ParamType::STRING);
		Helper::requiredParam($bankGroup, 'Currency', ParamType::STRING);
		Helper::optionalParam($bankGroup, 'SettingsPatch', ParamType::INTEGER);

		return $this->execute('BankGroup.Set', $bankGroup);
	}

	/**
	 * Creates or updates a player (aka "upsert").
	 *
	 * @param array $player
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
	 * @param $bonus
	 * @return array
	 */
	public function setBonus($bonus)
	{
		Helper::requiredParam($bonus, 'Id', ParamType::STRING);

		return $this->execute('Bonus.Set', $bonus);
	}

	/**
	 * Creates a game session.
	 *
	 * @param array $session
	 * @return array
	 */
	public function createSession($session)
	{
		Helper::requiredParam($session, 'PlayerId', ParamType::STRING);
		Helper::requiredParam($session, 'GameId', ParamType::STRING);
		Helper::optionalParam($session, 'BonusId', ParamType::STRING);
		Helper::optionalParam($session, 'RestorePolicy', ParamType::STRING, function($params, $key, $type) {
			Helper::strictValues($params, $key, array('Restore', 'Create'));
		});
		Helper::optionalParam($session, 'StaticHost', ParamType::STRING);
		Helper::optionalParam($session, 'AlternativeId', ParamType::STRING);

		return $this->execute('Session.Create', $session);
	}

	/**
	 * Creates a demo session.
	 *
	 * @param array $demoSession
	 * @return array
	 */
	public function createDemoSession($demoSession)
	{
		Helper::requiredParam($demoSession, 'GameId', ParamType::STRING);
		Helper::requiredParam($demoSession, 'BankGroupId', ParamType::STRING);
		Helper::optionalParam($demoSession, 'StartBalance', ParamType::INTEGER);
		Helper::optionalParam($demoSession, 'StaticHost', ParamType::STRING);

		return $this->execute('Session.CreateDemo', $demoSession);
	}

	/**
	 * Closes a specified session.
	 *
	 * @param array $session
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
	 * @param $params
	 * @return array
	 */
	public function getHistoryToken($params)
	{
		Helper::requiredParam($params, 'SessionId', ParamType::STRING);
		Helper::requiredParam($params, 'ExpiryInSeconds', ParamType::INTEGER);

		return $this->execute('History.GetToken', $params);
	}
}
