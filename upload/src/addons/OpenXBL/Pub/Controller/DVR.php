<?php

namespace OpenXBL\Pub\Controller;

use OpenXBL\Api;

class DVR extends \XF\Pub\Controller\AbstractController
{

	protected $xbox;

	public function actionGameClips()
	{

		$media = $this->xbox->get('dvr/gameclips?maxItems=100&continuationToken=');

		$this->session()->set('openxbl.clips', $media);

		$viewParams = [
			'library' => $media
		];

		return $this->view('OpenXBL:DVR\GameClips', 'openxbl_dvr_gameclips', $viewParams);
	}

	public function actionScreenshots()
	{
		$media = $this->xbox->get('dvr/screenshots?maxItems=100&continuationToken=');

		$this->session()->set('openxbl.screenshots', $media);

		$viewParams = [
			'library' => $media
		];

		return $this->view('OpenXBL:DVR\Screenshots', 'openxbl_dvr_screenshots', $viewParams);
	}

	public function actionShowcase(\XF\Mvc\ParameterBag $params)
	{

		$page = $this->filterPage($params->page);

		$perPage = 5;

		$dvrRepo = $this->getDvrRepo();

		$dvrFinder = $dvrRepo->findAllMedia()

			->limitByPage($page, $perPage);

		$totalGameClips = $dvrFinder->total();

		$this->assertValidPage($page, $perPage, $totalGameClips, 'items');

		$items = $dvrFinder->fetch();


		$viewParams = [

			'items' => $items,

			'page' => $page,

			'perPage' => $perPage,

			'total' => $totalGameClips


		];

		return $this->view('OpenXBL:DVR\Showcase', 'openxbl_dvr_showcase', $viewParams);
	}

	public function actionShowcaseFilters(\XF\Mvc\ParameterBag $params)
	{

		$filters = $this->getResourceFilterInput();

		if ($this->filter('apply', 'bool'))
		{
			return $this->redirect($this->buildLink(
				'openxbl/dvr/showcase',
				$filters
			));
		}

		if (!empty($filters['creator_id']))
		{
			$creatorFilter = $this->em()->find('XF:User', $filters['creator_id']);
		}
		else
		{
			$creatorFilter = null;
		}

		$viewParams = [
			'filters' => $filters,
			'creatorFilter' => $creatorFilter,
			'showTypeFilters' => true
		];

		return $this->view('OpenXBL:DVR\Showcase\Filters', 'openxbl_filters', $viewParams);

	}

	public function getResourceFilterInput()
	{
		$filters = [];

		$input = $this->filter([
			'type' => 'str',
			'creator' => 'str',
			'creator_id' => 'uint',
			'order' => 'str',
			'direction' => 'str'
		]);

		if ($input['type'] && ($input['type'] == 'video' || $input['type'] == 'image'))
		{
			$filters['type'] = $input['type'];
		}

		if ($input['creator_id'])
		{
			$filters['creator_id'] = $input['creator_id'];
		}
		else if ($input['creator'])
		{
			$user = $this->em()->findOne('XF:User', ['username' => $input['creator']]);
			if ($user)
			{
				$filters['creator_id'] = $user->user_id;
			}
		}

		$sorts = $this->getAvailableResourceSorts();

		if ($input['order'] && isset($sorts[$input['order']]))
		{
			if (!in_array($input['direction'], ['asc', 'desc']))
			{
				$input['direction'] = 'desc';
			}

			$defaultOrder = 'date';
			if ($input['order'] != $defaultOrder || $input['direction'] != 'desc')
			{
				$filters['order'] = $input['order'];
				$filters['direction'] = $input['direction'];
			}
		}

		return $filters;
	}

	public function getAvailableResourceSorts()
	{
		// maps [name of sort] => field in/relative to ResourceItem entity
		return [
			'date' => 'date',
			'game' => 'game'
		];
	}

	public function actionShare(\XF\Mvc\ParameterBag $params)
	{
		try
		{

			$media = $this->getMediaById($params->media_id);

			$dvrRepo = $this->getDvrRepo();
			
			$dvrRepo->share([
				'media_id' => $params->media_id,
				'type' => $media['type'],
				'game' => $media['titleName'],
				'duration' => (isset($media['durationInSeconds'])) ? $media['durationInSeconds'] : 0,
				'date' => date("Y-m-d H:m:s", strtotime(isset($media['dateRecorded']) ? $media['dateRecorded'] : $media['dateTaken'])),
				'_object' => (object) $media
			]);
			

			return $this->redirect($this->buildLink('openxbl/dvr/'.$params->media_id.'/view'), 'Your media has been shared!');

		}
		catch(\Exception $e)
		{
			// TO-DO: Return a more meaningful error message
			return $this->error('There was an issue sharing this media. Try again. [' . $e->getMessage() . ']');
		}


	}

	public function actionView(\XF\Mvc\ParameterBag $params, $selectedMedia = array())
	{

		$media = $this->getMediaById($params->media_id);

		if($media)
		{
			$viewParams = [
				'media' => $this->getMediaById($params->media_id),
				'media_id' => $params->media_id
			];

			return $this->view('OpenXBL:DVR\View', 'openxbl_dvr_view', $viewParams);
		}


		return $this->error('We could not locate this media.');
	}

	protected function getMediaById($media_id, $selectedMedia = array())
	{

		// check database
		$dvrRepo = $this->getDvrRepo();

		$mediaFinder = $dvrRepo->findMediaById($media_id);

		$selectedMedia = $mediaFinder->fetchOne();

		if($selectedMedia)
		{
			$selectedMedia = $selectedMedia->toArray();
			$selectedMedia['shared'] = true;
		}

		// check session clips
		if(!$selectedMedia && $this->session()->get('openxbl.clips') )
		{
			foreach ($this->session()->get('openxbl.clips')->gameClips as $item) {
			   if ($item->gameClipId == $media_id) {
			       $selectedMedia = $item;
			       $selectedMedia->type = 'video';
			       break;
			   }
			}
		}

		// check session images
		if(!$selectedMedia && $this->session()->get('openxbl.screenshots') )
		{
			foreach ($this->session()->get('openxbl.screenshots')->screenshots as $item) {
			   if ($item->screenshotId == $media_id) {
			       $selectedMedia = $item;
			       $selectedMedia->type = 'image';
			       break;
			   }
			}			
		}

		return (array) $selectedMedia;
	}

	protected function preDispatchController($action, \XF\Mvc\ParameterBag $params)
	{
		$access_token = \XF::visitor()->ConnectedAccounts['openxbl']->getValue('extra_data')['token'];

		$this->xbox = new Api($access_token);
	}

	/**
	 * @return \OpenXBL\Repository\Games
	 */
	protected function getDvrRepo()
	{
		return $this->repository('OpenXBL:DVR');
	}


}