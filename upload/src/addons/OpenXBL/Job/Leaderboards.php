<?php
/**
 * leaderboards.php
 *
 * the job the cron runs to update stats
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

namespace OpenXBL\Job;

use XF\Job\AbstractJob;
use OpenXBL\Api;

class Leaderboards extends AbstractJob
{

	public function run($maxRunTime)
	{

		$startTime = microtime(true);

		$users = \XF::app()->finder('XF:User')->fetch();

    	foreach($users as $user)
    	{
    		$this->updateUser($user);

			if (microtime(true) - $startTime >= $maxRunTime)
			{
				return $this->resume();
			}

    	}

    	return $this->complete();

    }

    public function updateUser(\XF\Entity\User $user)
    {

        /** @var \OpenXBL\LeaderboardService $service */
        $service = \XF::service('OpenXBL:LeaderboardService');

		if(!isset($user->ConnectedAccounts['openxbl']))
		{
			return false;
		}

    	$access_token = $user->ConnectedAccounts['openxbl']->getValue('extra_data')['token'];

		if(!$access_token)
		{
			return;
		}

		$xbox = new Api($access_token);

		$games = $xbox->get('achievements');

			foreach($games->titles as $title)
			{

				$gameFinder = \XF::finder('OpenXBL:Games');

				$userGameFinder = \XF::finder('OpenXBL:UserGames');

				// For xf_openxbl_games

				if( ! $gameFinder->where('title_id', '=', $title->titleId)->fetchOne() )
				{

					\XF::app()->error()->logError( $gameFinder->where('title_id', '=', $title->titleId)->getQuery() );

					$service->insert('title', $title);

				}

				// For xf_user_openxbl_games

				if( ! $userGameFinder->where(['title_id', '=', $title->titleId, 'user_id', '=', $user->user_id])->fetchOne() )
				{
					$service->insert('game', [$title, $user]);
				}

			}


    }

	public function getStatusMessage()
	{
		$actionPhrase = \XF::phrase('rebuilding');
		$typePhrase = 'Rebuilding Xbox Live Leaderboards';

		return sprintf('%s... %s (%s)', $actionPhrase, $typePhrase, $this->data['start']);
	}

	public function canCancel()
	{
		return false;
	}

	public function canTriggerByChoice()
	{
		return true;
	}

	public function logMessage($msg)
	{
		$logFile = fopen("src/addons/OpenXBL/Cron/leaderboard.log", "w+");

		fwrite($logFile, $msg . "\n");

		fclose($logFile);	
	}

}