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
 * @version    1.0
 * @link       https:/xbl.io
 * @see        https://github.com/OpenXBL
 * @since      File available since Release 1.0
 */
header('Location: https://YOUR-XENFORO-URL.COM/index.php?register/openxbl&code='.$_GET['code']);