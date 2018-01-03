<?php
/**
 * leaderboardService.php
 *
 * used for the cron mostly to store data to database
 *
 * @category   xbl.io
 * @package    OpenXBL
 * @author     David Regimbal
 * @copyright  2018 David Regimbal
 * @license    MIT
 * @version    0.2
 * @link       https:/xbl.io
 * @see        https://github.com/OpenXBL
 * @since      File available since Release
 */

namespace OpenXBL\Service;

use XF\Service\AbstractService;

class LeaderboardService extends AbstractService implements \ArrayAccess
{
	public function insert($type, $title)
	{

		$db = $this->db();

		switch($type)
		{
			case 'title':

				$db->insert('xf_openxbl_games', [

					'title_id' => $title->titleId,

					'title' => $title->name,

					'image' => $title->images[1]->url,

					'gamerscore' => $title->achievement->totalGamerscore,

					'achievements' => $title->achievement->totalAchievements

				], false);
				

				break;

			case 'game':

				$db->insert('xf_openxbl_users_games', [

					'user_id' => $title[1]->user_id,

					'title_id' => $title[0]->titleId,

					'gamerscore' => $title[0]->achievement->currentGamerscore,

					'achievements' => $title[0]->achievement->totalAchievements,

					'progress' => $title[0]->achievement->progressPercentage,

					'last_played' => $title[0]->titleHistory->lastTimePlayed

				], false);

				break;
		}
		


	}

	public function getRankedGamerscore()
	{

		$response = array();

		$users = \XF::app()->finder('XF:User')->fetch();

    	foreach($users as $user)
    	{
			if(isset($user->ConnectedAccounts['openxbl']))
			{
				if(isset($user->ConnectedAccounts['openxbl']->getValue('extra_data')['gamertag']))
				{
					$gamertag = $user->ConnectedAccounts['openxbl']->getValue('extra_data')['gamertag'];

					if(isset($user->ConnectedAccounts['openxbl']->getValue('extra_data')['gamerscore']))
					{
						$gamerscore = $user->ConnectedAccounts['openxbl']->getValue('extra_data')['gamerscore'];
					}

					if(isset($user->ConnectedAccounts['openxbl']->getValue('extra_data')['avatar']))
					{
						$avatar = $user->ConnectedAccounts['openxbl']->getValue('extra_data')['avatar'];
					}

					$response[] = array(
						'user' => $user, 
						'gamertag' => $gamertag, 
						'gamerscore' => $gamerscore ?: 0, 
						'avatar' => $avatar);

				}

			}

    	}

		usort($response, function($a, $b) {
		    return $a['gamerscore'] < $b['gamerscore'];
		});

    	return $response;

	}

	public function getRankedGames()
	{

		$db = \XF::db();
		$userGames = $db->fetchAll('SELECT DISTINCT user_id , COUNT( title_id ) AS games_played FROM xf_openxbl_users_games GROUP BY user_id ORDER BY games_played DESC');

		$userRepo = $this->repository('XF:User');

		$response = array();

		foreach($userGames as $rank)
		{
			$user = $userRepo->getVisitor($rank['user_id']);

			$response[] = array(
				'games_played' => $rank['games_played'],
				'gamertag' => $user->ConnectedAccounts['openxbl']->getValue('extra_data')['gamertag'], 
				'avatar' => $user->ConnectedAccounts['openxbl']->getValue('extra_data')['avatar'],
				'user' => $user
			);
		}

		return $response;
	}

	public function offsetExists($offset)
	{
		return $this->responseJson && isset($this->responseJson[$offset]);
	}

	public function offsetGet($offset)
	{
		return $this->responseJson[$offset];
	}

	public function offsetSet($offset, $value)
	{
		throw new \BadMethodCallException("Cannot set values on LicenseValidator");
	}

	public function offsetUnset($offset)
	{
		throw new \BadMethodCallException("Cannot unset values on LicenseValidator");
	}

	function __get($name)
	{
		return $this->offsetGet($name);
	}

	function __isset($name)
	{
		return $this->offsetExists($name);
	}

}