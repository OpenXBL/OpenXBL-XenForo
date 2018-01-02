<?php
/**
 * member.php
 *
 * controller for user profile page
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

namespace OpenXBL\Pub\Controller;

use OpenXBL\Api;

use XF\Mvc\ParameterBag;

class Member extends \XF\Pub\Controller\Member
{

	/**
	 * OpenXBL API
	 */
	protected $xbox;

	public function actionGamesPlayed(ParameterBag $params)
	{

		if(!$params->user_id)
		{
			return $this->error('Visit a member profile to view their played games.');
		}

		$visitor = $this->assertViewableUser($params->user_id, [], true);

		$page = $this->filterPage($params->page);

		$perPage = 5;

		$gamesRepo = $this->getGamesRepo();

		$gamesFinder = $gamesRepo->findUserGames($visitor)

			->limitByPage($page, $perPage);

		$totalGames = $gamesFinder->total();

		$this->assertValidPage($page, $perPage, $totalGames, 'games');

		$games = $gamesFinder->fetch();

		$viewParams = [

			'user' => $visitor,

			'games' => $games,

			'page' => $page,

			'perPage' => $perPage,

			'total' => $totalGames

		];

		return $this->view('OpenXBL:Member\GamesPlayed', 'openxbl_member_games', $viewParams);
	}

	protected function preDispatchController($action, \XF\Mvc\ParameterBag $params)
	{
		$access_token = \XF::visitor()->ConnectedAccounts['openxbl']->getValue('extra_data')['token'];

		$this->xbox = new Api($access_token);

	}

	/**
	 * @return \OpenXBL\Repository\Games
	 */
	protected function getGamesRepo()
	{
		return $this->repository('OpenXBL:Games');
	}

	/**
	 * @param int $userId
	 * @param array $extraWith
	 * @param bool $basicProfileOnly
	 *
	 * @return \XF\Entity\User
	 *
	 * @throws \XF\Mvc\Reply\Exception
	 */
	protected function assertViewableUser($userId, array $extraWith = [], $basicProfileOnly = false)
	{
		$extraWith[] = 'Option';
		$extraWith[] = 'Privacy';
		$extraWith[] = 'Profile';
		array_unique($extraWith);

		/** @var \XF\Entity\User $user */
		$user = $this->em()->find('XF:User', $userId, $extraWith);
		if (!$user)
		{
			throw $this->exception($this->notFound(\XF::phrase('requested_user_not_found')));
		}

		$canView = $basicProfileOnly ? $user->canViewBasicProfile($error) : $user->canViewFullProfile($error);
		if (!$canView)
		{
			throw $this->exception($this->noPermission($error));
		}

		return $user;
	}

}