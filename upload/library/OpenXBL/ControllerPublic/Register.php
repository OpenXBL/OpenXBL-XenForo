<?php
/**
 * register.php
 *
 * The file that processes the login/register request
 *
 * @category   xbl.io
 * @package    OpenXBL
 * @author     David Regimbal
 * @copyright  2017 David Regimbal
 * @license    MIT
 * @version    1.0
 * @link       https:/xbl.io
 * @see        https://github.com/OpenXBL
 * @since      File available since Release 1.0
 */
class OpenXBL_ControllerPublic_Register extends XFCP_OpenXBL_ControllerPublic_Register 
{

    private $ch = null;

	public function actionOpenXBL() 
	{
		$assocUserId = $this->_input->filterSingle('assoc', XenForo_Input::UINT);
		$redirect = $this->_input->filterSingle('redirect', XenForo_Input::STRING);

		$session = XenForo_Application::get('session');

		if($this->_input->filterSingle('reg', XenForo_Input::UINT)) {
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				$this->_genAuthorizeUrl()
			);
		}

		$code = $this->_input->filterSingle('code', XenForo_Input::STRING);

		$xerr = $this->_input->filterSingle('xerr', XenForo_Input::STRING);

		if( !empty( $xerr ) )
		{
			// do something with the error codes.
			switch($xerr)
			{
				case '2148916233':
					return $this->responseView('XenForo_ViewPublic_Register', 'openxbl_no_xbox_profile');
				break;
			}
		}

	
		if( empty( $code ) )
		{
			return $this->responseError("Missing 'code' parameter. Please try again.");
		}

		$token = $this->_genTokenizeUrl( $code );

		if( !$token['app_key'] )
		{
			return $this->responseError("Missing 'App Key.' Invalid request. Please try again.");
		}
	
		$session->set('openxbl', $token);
		$userModel = $this->_getUserModel();
		$userExternalModel = $this->_getUserExternalModel();

		$stAssoc = $userExternalModel->getExternalAuthAssociation('openxbl', $token['xuid']);

		if($stAssoc && $userModel->getUserById($stAssoc['user_id'])) {

			$this->_updateUserInfo($token, $stAssoc['user_id']);
			
			/** @var XenForo_ControllerHelper_Login $loginHelper */
			$loginHelper = $this->getHelper('Login');
			$loginHelper->tfaRedirectIfRequiredPublic($stAssoc['user_id'], $redirect, true);
            
			/* Cookies */
			XenForo_Visitor::setup($stAssoc['user_id']);
            XenForo_Application::get('session')->changeUserId($stAssoc['user_id']);

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				$this->getDynamicRedirect(false, false)
			);
		}		


		$existingUser = false;
		if(XenForo_Visitor::getUserId()) {
			$existingUser = XenForo_Visitor::getInstance();
		} else if($assocUserId) {
			$existingUser = $userModel->getUserById($assocUserId);
		}

		if($existingUser) {
			// must associate: matching user
			$this->_updateUserInfo($token, $existingUser['user_id']);

			return $this->responseView('XenForo_ViewPublic_Register_OpenXBL', 'register_openxbl', array(
				'associateOnly'	=> true,
				'existingUser'	=> $existingUser,
				'redirect'		=> $redirect
			));
		}

		if(!XenForo_Application::get('options')->get('registrationSetup', 'enabled')) {
			$this->_assertRegistrationActive();
		}


		$data = array(
			'redirect'		=> $redirect,
			'customFields'	=> $this->_getFieldModel()->prepareUserFields(
				$this->_getFieldModel()->getUserFields(array('registration' => true, 'gamertag' => $token['gamertag'])),
				true
			),
			'timeZones'		=> XenForo_Helper_TimeZone::getTimeZones(),
			'tosUrl'		=> XenForo_Dependencies_Public::getTosUrl(),
		);

		
		return $this->responseView('XenForo_ViewPublic_Register_OpenXBL', 'register_openxbl', $data, $this->_getRegistrationContainerParams());			


	}

	public function actionOpenXBLRegister() 
	{

		$this->_assertPostOnly();

		$session = XenForo_Application::get('session');

		if(!$session->get('openxbl')) 
		{

			return $this->responseError('Session expired. Please try again.');

		}

		// Get User Profile Data
		$access = $session->get('openxbl');

		$userModel = $this->_getUserModel();
		$userExternalModel = $this->_getUserExternalModel();

		$doAssoc = ($this->_input->filterSingle('associate', XenForo_Input::STRING) || $this->_input->filterSingle('force_assoc', XenForo_Input::UINT));

		if($doAssoc) 
		{
        
			$associate = $this->_input->filter(array(
				'associate_login' => XenForo_Input::STRING,
				'associate_password' => XenForo_Input::STRING
			));

			$loginModel = $this->_getLoginModel();

			$userId = $userModel->validateAuthentication($associate['associate_login'], $associate['associate_password'], $error);
			if (!$userId)
			{
				$loginModel->logLoginAttempt($associate['associate_login']);
				return $this->responseError($error);
			}

			XenForo_Application::getSession()->changeUserId($userId);
			XenForo_Visitor::setup($userId);


            $userId2 = $this->_associateExternalAccount();

			$userExternalModel->updateExternalAuthAssociation('openxbl', $access['xuid'], $userId);	
			XenForo_Helper_Cookie::setCookie('openxbl', $access['xuid'], 14 * 86400);		

			$this->_updateUserInfo($access, $userId);


			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				$this->getDynamicRedirect(false, false)
			);

		}

		$data = $this->_input->filter(array(
			'username'	=> XenForo_Input::STRING,
			'timezone'	=> XenForo_Input::STRING,
			'email'		=> XenForo_Input::STRING,
			'gender'	=> XenForo_Input::STRING,
			'location'	=> XenForo_Input::STRING,
			'dob_day'	=> XenForo_Input::UINT,
			'dob_month'	=> XenForo_Input::UINT,
			'dob_year'	=> XenForo_Input::UINT
		));

		if(XenForo_Dependencies_Public::getTosUrl() && !$this->_input->filterSingle('agree', XenForo_Input::UINT)) {
			return $this->responseError(new XenForo_Phrase('you_must_agree_to_terms_of_service'));
		}

		$options = XenForo_Application::get('options');

		$writer = XenForo_DataWriter::create('XenForo_DataWriter_User');
		if($options->registrationDefaults) {
			$writer->bulkSet($options->registrationDefaults, array('ignoreInvalidFields' => true));
		}
		$writer->bulkSet($data);

		$auth = XenForo_Authentication_Abstract::create('XenForo_Authentication_NoPassword');
		$writer->set('scheme_class', $auth->getClassName());
		$writer->set('data', $auth->generate(''), 'xf_user_authenticate');

		$writer->set('user_group_id', XenForo_Model_User::$defaultRegisteredGroupId);
		$writer->set('language_id', XenForo_Visitor::getInstance()->get('language_id'));

		$customFields = $this->_input->filterSingle('custom_fields', XenForo_Input::ARRAY_SIMPLE);
		$customFieldsShown = $this->_input->filterSingle('custom_fields_shown', XenForo_Input::STRING, array('array' => true));
		$writer->setCustomFields($customFields, $customFieldsShown);

		$writer->advanceRegistrationUserState(false);
		$writer->preSave();

		if($options->get('registrationSetup', 'requireDob')) {
			// dob required
			if(!$data['dob_day'] || !$data['dob_month'] || !$data['dob_year']) {
				$writer->error(new XenForo_Phrase('please_enter_valid_date_of_birth'), 'dob');
			} else {
				$userAge = $this->_getUserProfileModel()->getUserAge($writer->getMergedData(), true);
				if($userAge < 1) {

				} else if($userAge < intval($options->get('registrationSetup', 'minimumAge'))) {

					$writer->error(new XenForo_Phrase('sorry_you_too_young_to_create_an_account'));
				}
			}
		}

		$writer->save();
		$user = $writer->getMergedData();

		if(!$options->steamAvatarReg) {
            unset($avatar);
        }
        
        if(!empty($avatar)) {
			$avatarFile = tempnam(XenForo_Helper_File::getTempDir(), 'xf');

			$httpClient = XenForo_Helper_Http::getClient(preg_replace('/\s+/', '%20', $avatar));
			$response = $httpClient->request('GET');
			if($response->isSuccessful()) {
				file_put_contents($avatarFile, $response->getBody());
			}
			// Apply Avatar
			try {  
				$user = array_merge($user, $this->getModelFromCache('XenForo_Model_Avatar')->applyAvatar($user['user_id'], $avatarFile));
			} catch (XenForo_Exception $e) {}

			@unlink($avatarFile);
		}
		
		if(!isset($access['XErr']))
		{
			$userExternalModel->updateExternalAuthAssociation('openxbl', $access['xuid'], $user['user_id']);
		}
		else
		{
			$userExternalModel->updateExternalAuthAssociation('openxbl', $user['user_id'], $user['user_id']);
		}

		XenForo_Model_Ip::log($user['user_id'], 'user', $user['user_id'], 'register');
		
		/* Cookies */
		$userModel->setUserRememberCookie($user['user_id']);
		
		$session->changeUserId($user['user_id']);
		XenForo_Visitor::setup($user['user_id']);


		if(!isset($access['XErr']))
		{
			$this->_updateUserInfo($access, $user['user_id']);
		}
		

		$redirect = $this->_input->filterSingle('redirect', XenForo_Input::STRING);

		$viewParams = array(
			'user'		=> $user,
			'redirect'	=> ($redirect ? XenForo_Link::convertUriToAbsoluteUri($redirect) : ''),
			'openxbl' => true
		);

		return $this->responseView(
			'XenForo_ViewPublic_Register_Process',
			'register_process',
			$viewParams,
			$this->_getRegistrationContainerParams()
		);
	}

	private function _genAuthorizeUrl() {

        $options = XenForo_Application::get('options');

		return 'https://xbl.io/app/auth/' . $options->openxblKey;
	}

	private function _genTokenizeUrl( $code ) {

		$options = XenForo_Application::get('options');

	    ##
	    # Create the payload for this request
	    $payload = array(
	        "code" => $code,
	        "app_key" => $options->openxblKey
	    );

	    ##
	    # Get the users token via claims request
	    $crl = curl_init("https://xbl.io/app/claim");
	    $header = array();
	    $header[] = 'X-Contract: 2';
	    $header[] = 'Content-Type: application/json';
	    $header[] = 'Content-Length: ' . strlen(json_encode($payload));
	    curl_setopt($crl, CURLOPT_HTTPHEADER,$header);

	    curl_setopt($crl, CURLOPT_SSL_VERIFYPEER, false);
    	curl_setopt($crl, CURLOPT_SSL_VERIFYHOST, false);

	    curl_setopt( $crl, CURLOPT_POSTFIELDS, json_encode($payload) );
	    curl_setopt($crl, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($crl, CURLOPT_POST,true);
	    $result = curl_exec($crl);
	    curl_close($crl); 	
	    $result = json_decode( $result, true );

	    ##
	    # Save to user session
	    return array(
	    	'app_key' => $result['data'][0]['app_key'], 
	    	'xuid' => $result['data'][0]['xuid'],
	    	'gamertag' => $result['data'][0]['gamertag'],
	    	'level' => $result['data'][0]['level']
	    );

	}

	private function _updateUserInfo($token, $userid)
	{

		$sHelper = new OpenXBL_Helper_OpenXBL();

        $hash = $sHelper->encrypt($token['app_key']);

		$db = XenForo_Application::get('db');

		$db->beginTransaction();

		$db->query('INSERT INTO xf_user_openxbl (
			xuid, user_id, gamertag, avatar_url, access_token
		) VALUES (' 
			. $token['xuid'] . ', ' 
			. $userid . ', "' 
			. $token['gamertag'] . '","'
			. '' . '","'
			. $hash . '") ON DUPLICATE KEY UPDATE gamertag=VALUES(gamertag), avatar_url=VALUES(avatar_url), access_token=VALUES(access_token)');
	}

}
?>