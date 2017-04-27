<?php
/**
 * renderjson.php
 *
 * This file does not serve much purpose yet. This will change a typical view 
 * from a controller to a valid JOSN response. It will be useful to take full
 * advantage of OpenXBL. Some examples are included throughout this project.
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
class OpenXBL_ViewPublic_RenderJson extends XenForo_ViewPublic_Base
{
    public function renderJson()
    {
        return json_encode($this->_params);
    }
}