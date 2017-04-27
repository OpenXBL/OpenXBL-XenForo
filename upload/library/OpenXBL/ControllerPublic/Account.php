<?php
/**
 * account.php
 *
 * External Accounts page commands
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
class OpenXBL_ControllerPublic_Account extends XFCP_OpenXBL_ControllerPublic_Account 
{
    
    public function actionExternalAccounts()
	{
        $response = parent::actionExternalAccounts();
        
		$stUser = false;

		if (!empty($response->subView->params['external']['openxbl']))
		{
			if (!empty($response->subView->params['external']['openxbl']['extra_data']))
			{
                $sHelper = new OpenXBL_Helper_OpenXBL();
                $stUser = $sHelper->getUserInfo($response->subView->params['external']['openxbl']['provider_key']);
			}
		}
        
        $stParams = $response->subView->params;
        
        $stParams['xbUser'] = $stUser;
        
        $response->subView->params = $stParams;

		return $response;
	}

    public function actionExternalAccountsDisassociate()
    {
        $response = parent::actionExternalAccountsDisassociate();
        
        $input = $this->_input->filter(array(
			'disassociate' => XenForo_Input::STRING,
			'account' => XenForo_Input::STRING
		));
        
        $visitor = XenForo_Visitor::getInstance();
        
        if ($input['disassociate'] && $input['account'] == 'openxbl')
		{
			$sHelper = new OpenXBL_Helper_OpenXBL();
            $sHelper->deleteOpenXBLData($visitor['user_id']);
		}

		return $response;
	}

	public function actionOpenXBL()
	{
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL_PERMANENT,
			XenForo_Link::buildPublicLink('account/external-accounts')
		);
	}
}

?>