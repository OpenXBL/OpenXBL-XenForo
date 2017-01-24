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

	protected function _getWrapper($selectedGroup, $selectedLink, XenForo_ControllerResponse_View $subView)
	{
		return $this->getHelper('Account')->getWrapper($selectedGroup, $selectedLink, $subView);
	}

    public static function getSessionActivityDetailsForList(array $activities)
    {
        return new XenForo_Phrase('xbx_viewing_openxbl');
    }

}