<?php
/**
 * user.php
 *
 * Shows user details in admin panel
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
class OpenXBL_ControllerAdmin_User extends XFCP_OpenXBL_ControllerAdmin_User 
{
    
    public function actionExtra() 
    {
        $response = parent::actionExtra();
        
		$stUser = false;

		if (!empty($response->params['external']['openxbl']))
		{

			if (!empty($response->params['external']['openxbl']['extra_data']))
			{
                $sHelper = new OpenXBL_Helper_OpenXBL();
                $stUser = $sHelper->getUserInfo($response->params['external']['openxbl']['provider_key']);

			}
		}
        
        $stParams = $response->params;
        
        $stParams['stUser'] = $stUser;
        
        $response->params = $stParams;

		return $response;
    }
}
?>