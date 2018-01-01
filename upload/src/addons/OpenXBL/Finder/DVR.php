<?php
/**
 * dvr.php
 *
 * this file extends the entities finder
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

namespace OpenXBL\Finder;

use XF\Mvc\Entity\Finder;

class DVR extends Finder
{

	public function forUser(\XF\Entity\User $user)
	{

		$this->where('user_id', $user->user_id);

		return $this;

	}

	public function getMediaById($media_id)
	{
		$this->where('media_id', $media_id);

		return $this;
	}

	public function getGameClips()
	{

		$this->where('type', 'video');

		return $this;

	}

	public function getScreenshots()
	{

		$this->where('type', 'image');

		return $this;

	}

	public function orderForUser(\XF\Entity\User $user, $orderBy, $orderDir = 'desc')
	{

		$this->order($orderBy, $orderDir);


		return $this;

	}

}