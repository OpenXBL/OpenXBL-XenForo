<?php

namespace OpenXBL\Pub\Controller;

use OpenXBL\Api;

class Leaderboards extends \XF\Pub\Controller\AbstractController
{

	protected $service;

	public function actionGamerscore()
	{
		$viewParams = [
			'scores' => $this->service->getRankedGamerscore(),
			'active' => 'ranked_gamerscore'
		];

		return $this->view('OpenXBL:Leaderboards\Leaderboards', 'openxbl_leaderboards_gamerscore', $viewParams);
	}


	public function actionGames()
	{

		$viewParams = [
			'ranks' => $this->service->getRankedGames(),
			'active' => 'ranked_games_played'
		];

		return $this->view('OpenXBL:Leaderboards\Leaderboards', 'openxbl_leaderboards_games', $viewParams);
	}

	protected function preDispatchController($action, \XF\Mvc\ParameterBag $params)
	{
        /** @var \OpenXBL\LeaderboardService $service */
        $this->service = \XF::service('OpenXBL:LeaderboardService');
	}


}