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
 * @version    1.0
 * @link       https:/xbl.io
 * @see        https://github.com/OpenXBL
 * @since      File available since Release 1.0
 */
class OpenXBL_ControllerPublic_OpenXBL extends XenForo_ControllerPublic_Abstract
{

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

	protected function _getWrapper($selectedGroup, $selectedLink, XenForo_ControllerResponse_View $subView)
	{
		return $this->getHelper('Account')->getWrapper($selectedGroup, $selectedLink, $subView);
	}

    public static function getSessionActivityDetailsForList(array $activities)
    {
        return new XenForo_Phrase('xbx_viewing_openxbl');
    }

}