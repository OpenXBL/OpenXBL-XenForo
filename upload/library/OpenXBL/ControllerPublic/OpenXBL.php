<?php
/**
 * openxbl.php
 *
 * Used to handle OpenXBL views and routes
 *
 * @category   xbl.io
 * @package    OpenXBL
 * @author     David Regimbal
 * @copyright  2017 David Regimbal
 * @license    MIT
 * @version    1.5
 * @link       https:/xbl.io
 * @see        https://github.com/OpenXBL
 * @since      File available since Release 1.0
 */
class OpenXBL_ControllerPublic_OpenXBL extends XenForo_ControllerPublic_Abstract
{

	public function actionIndex()
	{
        return $this->responseRedirect(
                    XenForo_ControllerResponse_Redirect::SUCCESS,
                    "?openxbl/showcase"
                );			
	}
    /**
    * Autocomplete Gamertag field input via username
    */
	public function actionFind()
	{
		$q = ltrim($this->_input->filterSingle('q', XenForo_Input::STRING, array('noTrim' => true)));

		if ($q !== '' && utf8_strlen($q) >= 2)
		{
			$users = $this->_getUserModel()->getUsers(
				array(
					'username' => array($q , 'r'),
					'user_state' => 'valid',
					'is_banned' => 0,
					'active_recently' => true
				),
				array('limit' => 10)
			);

		}
		else
		{
			$users = array();
		}

		$viewParams = array(
			'users' => $users
		);

		return $this->responseView(
			'OpenXBL_ViewPublic_Member_Find',
			'gamertag_autocomplete',
			$viewParams
		);
	}

    /**
    * Display user Xbox DVR :: Game Clips page
    */
	public function actionDvrClips()
	{


		$permissions = XenForo_Visitor::getInstance()->getPermissions();

		if(!$permissions['OpenXBL']['viewDVR'])
		{
			throw $this->getNoPermissionResponseException();
		}

		$helper = new OpenXBL_Helper_Hub();
		
		return $this->responseView('OpenXBL_ViewPublic_DvrClips', 'openxbl_dvr_clips', array("items" => $helper->buildGameDVR() ));

	}

    /**
    * Display user Xbox DVR :: Screenshots page
    */
	public function actionDvrScreenshots()
	{
		$visitor = XenForo_Visitor::getInstance();

		$visitorPerms = $visitor->getPermissions();

		if(!$visitorPerms['OpenXBL']['viewHub'])
		{
			return $this->responseError("You do not have permission to view this resource.");
		}

		$sHelper = new OpenXBL_Helper_Hub();
		
		return $this->responseView('OpenXBL_ViewPublic_DvrScreenshots', 'openxbl_dvr_screenshots', array("items" => $sHelper->buildScreenshotsDVR() ));

	}

    /**
    * Display showcase index
    */
	public function actionShowcase()
	{


		$manage['isAnyone'] = false;

		$manage['isSelf'] = false;

		$model = $this->_getDVRModel();

		$visitor = XenForo_Visitor::getInstance();

		$permissions = $visitor->getPermissions();

		if($permissions['OpenXBL']['deleteMediaAnyone'])
		{

			$manage['isAnyone'] = true;

		}

		$media_id = $this->_input->filterSingle('openxbl_id', XenForo_Input::STRING);

		if( $media_id )
		{

			$item = $model->getDvrById($media_id);

			$manage['isSelf'] = ($item['user_id'] == $visitor['user_id'] && $permissions['OpenXBL']['deleteMediaSelf']) ? true : false;

			$viewParams = array(

				'item' => $item,

				'manage' => $manage

			);

			return $this->responseView('OpenXBL_ViewPublic_Showcase', 'openxbl_showcase_item', $viewParams);
		}

		$viewParams = array(

			'gameclips' => $this->_getDVRModel()->getDvrByType('video'),

			'screenshots' => $this->_getDVRModel()->getDvrByType('screenshot')

		);

		return $this->responseView('OpenXBL_ViewPublic_Showcase', 'openxbl_showcase', $viewParams);
	}

    /**
    * Display showcase game clips page
    */
	public function actionShowcaseClips()
	{

		$model = $this->_getDVRModel();

		$page = $this->_input->filterSingle('page', XenForo_Input::UINT);

		$perPage = XenForo_Application::get('options')->openxblMaxItems;

		$items = $model->filterDvrByType('video', array('page' => $page, 'perPage' => $perPage) );

		$viewParams = array(

			'items' => $items['data'],

			'total' => $items['total'],

			'page' => $page,

			'perPage' => $perPage,

			'screenshots' => $model->getDvrByType('screenshot')

		);

        return $this->responseView('OpenXBL_ViewPublic_ShowcaseClips', 'openxbl_showcase_clips', $viewParams);

	}

    /**
    * Display showcase screenshots page
    */
	public function actionShowcaseScreenshots()
	{
		$model = $this->_getDVRModel();

		$page = $this->_input->filterSingle('page', XenForo_Input::UINT);

		$perPage = XenForo_Application::get('options')->openxblMaxItems;

		$items = $model->filterDvrByType('screenshot', array('page' => $page, 'perPage' => $perPage) );

		$viewParams = array(

			'items' => $items['data'],

			'total' => $items['total'],

			'page' => $page,

			'perPage' => $perPage,

			'video' => $model->getDvrByType('video')

		);

        return $this->responseView('OpenXBL_ViewPublic_ShowcaseScreenshots', 'openxbl_showcase_screenshots', $viewParams);
	}

	public function actionShowcaseRemove()
	{

		// parameters
		$confirmed = $this->_input->filterSingle('_xfConfirm', XenForo_Input::UINT);

		$media_id = $this->_input->filterSingle('media_id', XenForo_Input::STRING);

		// confirm dialog
		if(!$confirmed)
		{
			return $this->responseView('OpenXBL_ViewPublic_ShowcaseRemove', 'openxbl_showcase_delete', array('media_id' => $media_id));
		}

		//
		$model = $this->_getDVRModel();

		$visitor = XenForo_Visitor::getInstance();

		$permissions = $visitor->getPermissions();

		$item = $model->getDvrById($media_id);

		if( $item['user_id'] == $visitor['user_id'] && $permissions['OpenXBL']['deleteMediaSelf'] || $permissions['OpenXBL']['deleteMediaAnyone'] )
		{
			$this->_routeMatch->setResponseType('json');

			$helper = new OpenXBL_Helper_Hub();

			$data = $this->_input->filter(array(

				'media_id' => XenForo_Input::STRING,

			));

			$helper->deleteDvrItem($data);

	        return $this->responseRedirect(
	            XenForo_ControllerResponse_Redirect::SUCCESS, 
	            $this->_buildLink('openxbl/showcase'),                   
	            new XenForo_Phrase('openxbl_delete_success')        
	        );
		}

		throw $this->getNoPermissionResponseException();

	}

    /**
    * Add media item to community showcase
    */
	public function actionDvrShare()
	{

		//$this->_routeMatch->setResponseType('json');

		$helper = new OpenXBL_Helper_Hub();

		$data = $this->_input->filter(array(

			'media_id' => XenForo_Input::STRING,

			'type' => XenForo_Input::STRING,

			'game' => XenForo_Input::STRING,

			'duration' => XenForo_Input::UINT,

			'date' => XenForo_Input::STRING,

			'thumbnail' => XenForo_Input::STRING,

			'url' => XenForo_Input::STRING

		));

		$helper->shareDvrItem($data);

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS, 
            $this->_buildLink('openxbl/showcase',$data['media_id']),                   
            "Your media has been shared!"     
        );

	}

	public function actionConversationsSend()
	{

		$permissions = XenForo_Visitor::getInstance()->getPermissions();

		if(!$permissions['OpenXBL']['viewConversations'])
		{
			throw $this->getNoPermissionResponseException();
		}


		if( !$this->_input->filterSingle('_xfConfirm', XenForo_Input::UINT) )
		{

			$to = $this->_input->filterSingle('openxbl_id', XenForo_Input::STRING);
			
			return $this->responseView('OpenXBL_ViewPublic_ConversationsSend', 'openxbl_conversations_send', array("to" => $to ));

		}


		$helper = new OpenXBL_Helper_OpenXBL();

		$data['recipients'] = preg_replace('/\s+/', '', $this->_input->filterSingle('recipients', XenForo_Input::STRING));

		$data['message'] = $this->_input->filterSingle('message', XenForo_Input::STRING);

		$response = json_decode($helper->sendConversation($data), true);

		if( !isset($response['data'][0]['results']) )
		{
			return $this->responseError(
				new XenForo_Phrase('openxbl_message_sent_error')
			);
		}

        return $this->responseMessage(              
            new XenForo_Phrase('openxbl_message_sent')
        );



	}

	public function actionFriends()
	{
		$visitor = XenForo_Visitor::getInstance();

		$visitorPerms = $visitor->getPermissions();

		if(!$visitorPerms['OpenXBL']['viewFriends'])
		{
			return $this->responseError("You do not have permission to view this resource.");
		}

		$sHelper = new OpenXBL_Helper_OpenXBL();

		$friends = json_decode($sHelper->getFriendsList(), true)['data'][0];

		$numOnline = 0;

		// Shuffle those 'Online' to the top of the array
        for($i = 0; $i < count($friends['people']); $i++)
        {
            if( $friends['people'][$i]['presenceState'] == 'Online' )
            {
                $friends['people'] = $sHelper->friendSorter($friends['people'],$i,'up');
                $numOnline++;
            }
            else
            {
            	$friends['people'] = $sHelper->friendSorter($friends['people'],$i,'down');
            }
        }

		return $this->responseView('OpenXBL_ViewPublic_Friends', 'openxbl_member_friends', 
			array(
				'user' => XenForo_Visitor::getInstance(),
				'friends' => $friends,
				'numFriends' => count($friends['people']),
				'numOnline' => $numOnline
			));
	}

    /**
    * Get the OpenXBL DVR Model
    *
    * @return OpenXBL_Model_DVR
    */
    protected function _getDVRModel()
    {
        return $this->getModelFromCache ( 'OpenXBL_Model_DVR' );
    }

	protected function _getWrapper($selectedGroup, $selectedLink, XenForo_ControllerResponse_View $subView)
	{
		return $this->getHelper('Account')->getWrapper($selectedGroup, $selectedLink, $subView);
	}

    public static function getSessionActivityDetailsForList(array $activities)
    {
        return new XenForo_Phrase('xbx_viewing_openxbl');
    }

	protected function _getUserModel()
	{
		return $this->getModelFromCache('XenForo_Model_User');
	}

	public function actionCallback()
	{

		$this->_routeMatch->setResponseType('json');

		$visitor = XenForo_Visitor::getInstance();

		$visitorPerms = $visitor->getPermissions();

	}

}