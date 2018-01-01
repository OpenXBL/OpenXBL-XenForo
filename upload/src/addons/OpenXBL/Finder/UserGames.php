<?php
/**
 * usergames.php
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

class UserGames extends Finder
{

	public function forUser(\XF\Entity\User $user, $forList = true)
	{
		$this->where('user_id', $user->user_id);

		if ($forList)
		{
			$this->forList($user);
		}

		return $this;

	}

	public function forList(\XF\Entity\User $user)
	{

		$this->with('Games');

		return $this;

	}

	public function orderForUser(\XF\Entity\User $user, $orderBy, $orderDir = 'desc')
	{

		$this->order($orderBy, $orderDir);

		return $this;

	}

}