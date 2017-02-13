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
class OpenXBL_Route_PrefixAdmin_OpenXBL implements XenForo_Route_Interface
{

	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
        $parts = explode('/', $routePath, 2);
		$action = $parts[0];

		if(isset($parts[1])) {
			switch($action) {
				case 'games':
					$action .= $router->resolveActionWithIntegerParam($parts[1], $request, 'game_id');
					break;
			}
		}

		return $router->getRouteMatch("OpenXBL_ControllerAdmin_OpenXBL", $action, 'openxbl');

	}
}