<?php
/**
 * openxbl.php
 *
 * XSTS Passthrough. Given the nature of the authentication procedure OpenXBL
 * needs this file to pass the claims code to XenForo to validate the incoming
 * users request. 
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
try
{
	// Make sure the board url is correct in Home > Options > Board Information
	//
	require( 'library/XenForo/Autoloader.php' );
	XenForo_Autoloader::getInstance()->setupAutoloader( 'library' );    
	XenForo_Application::initialize('library', '/');

	$options = XenForo_Application::get('options');

	header('Location: ' . $options->boardUrl . '/index.php?register/openxbl&code='.@$_GET['code'].'&xerr='.@$_GET['xerr']);
}
catch(\Exception $e)
{
	echo "Missing board url or failed to initialize.";
	var_dump($e);
}