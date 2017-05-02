<?php
/**
 * openxbl.php
 *
 * This file routes views for the OpenXBL addon
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
class OpenXBL_Route_Prefix_OpenXBL implements XenForo_Route_Interface
{

    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {

		$action = $router->resolveActionWithIntegerParam($routePath, $request, 'openxbl_id');

		$actions = explode('/', $action);

		if( isset( $actions[1] ) )
		{
			if (!empty($actions[1]) && $actions[1] == 'showcase' || $actions[1] == 'conversations')
			{
				$action = $router->resolveActionWithStringParam($routePath, $request, 'openxbl_id');
			}			
		}


        return $router->getRouteMatch('OpenXBL_ControllerPublic_OpenXBL', $action, 'openxbl');

    }

    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {

        $actions = explode('/', $action);

       // print_r($actions);

        switch ($actions[0])
        {

            case 'callback':        $intParams = 'callback_id';        $strParams = '';            break;

            case 'showcase':        $intParams = '';        $strParams = 'media_id';            break;

            default:                $intParams = '';                   $strParams = '';            break;

        }


        $action = XenForo_Link::getPageNumberAsAction($action, $extraParams);

        if ($intParams)
        {
            return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, $intParams, $strParams);
        }
        else
        {
            return XenForo_Link::buildBasicLinkWithStringParam($outputPrefix, $action, $extension, $data, $strParams);
        }

    }

}
?>