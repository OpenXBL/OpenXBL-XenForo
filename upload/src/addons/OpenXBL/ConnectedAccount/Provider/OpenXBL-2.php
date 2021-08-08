<?php
/**
 * openxbl.php
 *
 * this file adds openxbl as a connected provider
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

namespace OpenXBL\ConnectedAccount\Provider;

use XF\ConnectedAccount\Provider\AbstractProvider;

use XF\Entity\ConnectedAccountProvider;

use OAuth\OAuth2\Token\StdOAuth2Token;

use OAuth\Common\Http\Exception\TokenResponseException;

use XF\ConnectedAccount\Storage\StorageState;

use XF\Http\Request;

use XF\Mvc\Controller;

class OpenXBL extends AbstractProvider
{

	public $base_url = 'https://xbl.io/'; 

	public function getOAuthServiceName()
	{
		return 'OpenXBL';
	}

	public function getOAuthConfig(ConnectedAccountProvider $provider, $redirectUri = null)
	{

	return [

            'key' => $provider->options['app_key'],

            'secret' => '',

            'scopes' => [],

            'redirect' => $redirectUri ?: $this->getRedirectUri($provider)

        ];
}

	public function handleAuthorization(Controller $controller, ConnectedAccountProvider $provider, $returnUrl = null)
	{

		/** @var \XF\Session\Session $session */
		$session = \XF::app()['session.public'];

		$session->set('connectedAccountRequest', [

			'app_key' => $provider->options['app_key'],

			'provider' => $this->providerId,

			'returnUrl' => $returnUrl,

			'test' => $this->testMode

		]);

		$session->save();

		return $controller->redirect($this->base_url.'app/auth/'.$provider->options['app_key']);

	}

	public function requestProviderToken(StorageState $storageState, Request $request, &$error = null, $skipStoredToken = false)
	{

		if ($request->filter('xerr', 'str') == '2148916233')
		{

			$error = \XF::phraseDeferred('you_must_first_create_an_xbox_live_account');

			return false;

		}

		$code = $request->filter('code', 'str');

		$token = $this->requestAccessToken($storageState, $code);

		$storageState->storeToken($token);

		return $token;


	}

	public function requestAccessToken(StorageState $storageState, $code)
    	{
        $client = \XF::app()->http()->client();
        try {
            $response = $client->request('POST', $this->base_url . 'app/claim', [
                'json' => [
                    "code" => $code,
                    "app_key" => $storageState->getProvider()->options['app_key']
                ],
                'verify' => false, // cringe
                'timeout' => 30,
            ]);
            return $this->parseAccessTokenResponse($response->getBody()->getContents());
        } catch (\Exception $e) {
            return $this->parseAccessTokenResponse('');
        }
    }

    protected function parseAccessTokenResponse($responseBody)
    {
        $data = json_decode($responseBody, true);

        if (null === $data || !is_array($data)) {
            throw new TokenResponseException('Unable to parse response.');
        } elseif (isset($data['error'])) {
            throw new TokenResponseException('Error in retrieving token: "' . $data['error'] . '"');
        }

        $token = new StdOAuth2Token();
		$token->setAccessToken($data['xuid']);

        $token->setExtraParams(array(
			'token' => $data['app_key'],
        	'xuid' => $data['xuid'],
        	'gamertag' => $data['gamertag'],
        	'avatar' => $data['avatar'],
        	'gamerscore' => $data['gamerscore']
        ));

        unset($data['app_key']);
        unset($data['xuid']);
        unset($data['gamertag']);
        unset($data['avatar']);
        unset($data['gamerscore']);

        return $token;
    }

	public function getProviderDataClass()
	{
		return 'OpenXBL:ProviderData\\' . $this->getOAuthServiceName();
	}

	public function getDefaultOptions()
	{
		return [
			'app_key' => ''
		];
	}


}
