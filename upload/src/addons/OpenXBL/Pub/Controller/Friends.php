<?php

namespace OpenXBL\Pub\Controller;

use OpenXBL\Api;

class Friends extends \XF\Pub\Controller\AbstractController
{

	protected $xbox;

	public function actionIndex()
	{

		$viewParams = [
			'friends' => $this->xbox->get('friends')->people
		];

		return $this->view('OpenXBL:Friends\Friends', 'openxbl_friends', $viewParams);
	}

	protected function preDispatchController($action, \XF\Mvc\ParameterBag $params)
	{
		if(isset(\XF::visitor()->ConnectedAccounts['openxbl']))
		{
			$access_token = \XF::visitor()->ConnectedAccounts['openxbl']->getValue('extra_data')['token'];

			$this->xbox = new Api($access_token);
		}
	}


}