<?php
/**
 * listener.php
 *
 * XenForo events listener
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
class OpenXBL_Listener 
{

	public static function loadClassController($class, array &$extend) 
	{

		switch($class) {

			case 'XenForo_ControllerPublic_Register':

				$extend[] = 'OpenXBL_ControllerPublic_Register';

				break;

			case 'XenForo_ControllerPublic_Account':

				$extend[] = 'OpenXBL_ControllerPublic_Account';

				break;

			case 'XenForo_ControllerAdmin_User':

				$extend[] = 'OpenXBL_ControllerAdmin_User';

				break;

		}

	}

	/*
	 * Copyright notice. You can remove this if you wish
	 * but please give credit somewhere or donate to
	 * show appreciation. Thank you!
	 */
    public static function copyrightNotice(array $matches)
    {

        return $matches[0] . '<xen:set var="$openxblCopyrightShown">1</xen:set><br/>' .

            '<div id="openxblCopyrightNotice">' .

            '<a href="https://xbl.io" target="_blank">OpenXBL</a> for XenForo <span>&copy; '.date("Y").'</div>';

    } 

}
?>