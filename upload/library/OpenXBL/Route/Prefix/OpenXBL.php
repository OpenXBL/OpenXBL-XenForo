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
 * @version    1.0
 * @link       https:/xbl.io
 * @see        https://github.com/OpenXBL
 * @since      File available since Release 1.0
 */
class OpenXBL_Route_Prefix_XboxLive implements XenForo_Route_Interface
{

    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {

		$action = $router->resolveActionWithIntegerParam($routePath, $request, 'openxbl_id');

        return $router->getRouteMatch('OpenXBL_ControllerPublic_OpenXBL', $action, 'openxbl');

    }

    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {

        $actions = explode('/', $action);

        switch ($actions[0])
        {

            case 'callback':        $intParams = 'callback_id';        $strParams = '';            break;

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