<?php
/**
 * openxbl.php
 *
 * this file creates provider data for openxbl
 *
 * @category   xbl.io
 * @package    OpenXBL
 * @author     David Regimbal
 * @copyright  2018 David Regimbal
 * @license    MIT
 * @version    0.2
 * @link       https:/xbl.io
 * @see        https://github.com/OpenXBL
 * @since      File available since Release
 */

namespace OpenXBL\ConnectedAccount\ProviderData;

use XF\ConnectedAccount\ProviderData\AbstractProviderData;

class OpenXBL extends AbstractProviderData
{

	public function getDefaultEndpoint()
	{

		return 'account';

	}

	public function getExtraParams()
	{

		$storageState = $this->storageState;

		$token = $storageState->getProviderToken();

		return (object) $token->getExtraParams();

	}

	public function getExtraData()
	{

		$storageState = $this->storageState;

		$token = $storageState->getProviderToken();

		$provider = $storageState->getProvider();

		$handler = $provider->handler;

		$params = (object) $token->getExtraParams();

		$extraData = [

			'token' => $params->token,

			'gamertag' => $params->gamertag,

			'xuid' => $params->xuid,

			'avatar' => $params->avatar,

			'gamerscore' => $params->gamerscore

		];

		return $extraData;

	}

	public function getProviderKey()
	{

		$storageState = $this->storageState;

		$token = $storageState->getProviderToken();

		return $token->getAccessToken();

	}

	public function getGamertag()
	{
		if(isset($this->getExtraParams()->gamertag))
		{
			return $this->getExtraParams()->gamertag;
		}
		
		return '';

	}

	public function getXUID()
	{
		if(isset($this->getExtraParams()->xuid))
		{
			return $this->getExtraParams()->xuid;
		}
		
		return 0;
	}

	public function getAvatar()
	{
		if(isset($this->getExtraParams()->avatar))
		{
			return $this->getExtraParams()->avatar;
		}

		return '';
	}

	public function getGamerscore()
	{
		if(isset($this->getExtraParams()->gamerscore))
		{
			return $this->getExtraParams()->gamerscore;
		}

		return 0;

	}

}