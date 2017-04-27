<?php
/**
 * find.php
 *
 * Autocomplete gamertag input field
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
class OpenXBL_ViewPublic_Member_Find extends XenForo_ViewPublic_Base
{

	public function renderJson()
	{

		$results = array();

		foreach ($this->_params['users'] AS $user)
		{

	        $db = XenForo_Application::get('db');
	        
	        $lookup = $db->fetchRow("SELECT gamertag 
	                                FROM xf_user_openxbl
	                                WHERE user_id = '" . $user['user_id'] ."';");

	        if( !empty( $lookup['gamertag'] ) )
	        {

				$results[$lookup['gamertag']] = array(

					'avatar' => XenForo_Template_Helper_Core::callHelper('avatar', array($user, 's')),

					'username' => htmlspecialchars($user['username'])

				);

	        }

		}

		return array(

			'results' => $results

		);

	}

}