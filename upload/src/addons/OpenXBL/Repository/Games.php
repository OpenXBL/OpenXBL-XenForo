<?php
/**
 * games.php
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

class Games extends Repository
{

	public function findUserGames(\XF\Entity\User $user, $forList = true)
	{

		/** @var \OpenXBL\Finder\UserGames $finder */
		$finder = $this->finder('OpenXBL:UserGames');

		$finder->forUser($user, $forList)

			->setDefaultOrder('last_played', 'desc');

		return $finder;

	}

}