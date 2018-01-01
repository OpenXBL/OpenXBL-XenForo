<?php
/**
 * dvr.php
 *
 * this file interacts with entities and finders
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

namespace OpenXBL\Repository;

use XF\Mvc\Entity\Finder;

use XF\Mvc\Entity\Repository;

class DVR extends Repository
{

	/**
	 *	findUserShares.
	 *	returns media shared by user
	 *	@param \XF\Entity\User $user
	 */
	public function findUserShares(\XF\Entity\User $user)
	{

		/** @var \OpenXBL\Finder\DVR $finder */
		$finder = $this->finder('OpenXBL:DVR');

		$finder->forUser($user)

			->setDefaultOrder('date', 'desc');

		return $finder;

	}

	/**
	 *	findGameClips.
	 *	returns videos from database
	 */
	public function findGameClips()
	{

		/** @var \OpenXBL\Finder\DVR $finder */
		$finder = $this->finder('OpenXBL:DVR');

		$finder->getGameClips()

			->setDefaultOrder('date', 'desc');

		return $finder;

	}

	/**
	 *	findScreenshots.
	 *	returns images from database
	 */
	public function findScreenshots()
	{

		/** @var \OpenXBL\Finder\DVR $finder */
		$finder = $this->finder('OpenXBL:DVR');

		$finder->getScreenshots()

			->setDefaultOrder('date', 'desc');

		return $finder;

	}

	/**
	 *	findMediaById.
	 *	returns media from database
	 *	@param String $media_id
	 */
	public function findMediaById($media_id)
	{

		/** @var \OpenXBL\Finder\DVR $finder */
		$finder = $this->finder('OpenXBL:DVR');

		$finder->getMediaById($media_id);

		return $finder;

	}

	/**
	 *	findAllMedia.
	 *	returns images and videos from database
	 */
	public function findAllMedia()
	{

		/** @var \OpenXBL\Finder\DVR $finder */
		$finder = $this->finder('OpenXBL:DVR');

		$finder->setDefaultOrder('date', 'desc');

		return $finder;

	}

	/**
	 *	Share.
	 *  insert media into database
	 *  @param Array $item  
	 */
	public function share($item)
	{

		$visitor = \XF::visitor();

		$media = $this->em->create('OpenXBL:DVR');

		$this->download($item);

		$media->bulkSet([

			'media_id' => $item['media_id'],

			'user_id' => $visitor->user_id,

			'type' => $item['type'],

			'game' => $item['game'],

			'duration' => $item['duration'],

			'date' => $item['date']

		]);

		$media->save();


	}

	// TO-DO: Put this into a service
	public function checkPaths()
	{
		// root openxbl folder
		if (!file_exists(realpath(dirname(basename(__DIR__))) . "/openxbl")) {
			mkdir(realpath(dirname(basename(__DIR__))) . "/openxbl", 0777, true);

			// openxbl media folder
			if (!file_exists(realpath(dirname(basename(__DIR__))) . "/openxbl/media")) {
				mkdir(realpath(dirname(basename(__DIR__))) . "/openxbl/media", 0777, true);
			}

			if (!file_exists(realpath(dirname(basename(__DIR__))) . "/openxbl/media/thumbnail")) {
				mkdir(realpath(dirname(basename(__DIR__))) . "/openxbl/media/thumbnail", 0777, true);
			}

		}
	}
	
	public function download($item)
	{

		$this->checkPaths();

		$object = $item['_object'];

		$url = ($item['type'] == 'video') ? $object->gameClipUris[0]->uri : $object->screenshotUris[0]->uri;

		$thumbnail = $object->thumbnails[0]->uri;

		// Save media source
		$media_source = file_get_contents($url);

		$extension = ($item['type'] == 'video') ? '.mp4' : '.png';

		$fp = fopen(realpath(dirname(basename(__DIR__))) . "/openxbl/media/" . $item['media_id'] . $extension, "w");

		fwrite($fp, $media_source);

		fclose($fp);

		// Save media thumbnail
		$media_thumbnail = file_get_contents($thumbnail);

		$fp = fopen(realpath(dirname(basename(__DIR__))) . "/openxbl/media/thumbnail/" . $item['media_id'] . ".png", "w");

		fwrite($fp, $media_thumbnail);

		fclose($fp);
	}

}