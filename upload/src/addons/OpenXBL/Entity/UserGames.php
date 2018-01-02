<?php
/**
 * usergames.php
 *
 * database entity for xf_openxbl_users_games
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

namespace OpenXBL\Entity;

use XF\Mvc\Entity\Entity;

use XF\Mvc\Entity\Structure;

class UserGames extends Entity
{
	public static function getStructure(Structure $structure)
	{
		$structure->table = 'xf_openxbl_users_games';

		$structure->shortName = 'OpenXBL:UserGames';

		$structure->primaryKey = ['user_id', 'title_id'];

		$structure->columns = [

			'title_id' => ['type' => self::STR],

			'user_id' => ['type' => self::UINT],

			'gamerscore' => ['type' => self::UINT],

			'achievements' => ['type' => self::UINT],

			'progress' => ['type' => self::UINT],

			'last_played' => ['type' => self::STR]

		];

		$structure->relations = [
			'Games' => [

				'entity' => 'OpenXBL:Games',

				'type' => self::TO_ONE,

				'conditions' => 'title_id',

				'primary' => true

			]
		];

		$structure->defaultWith = ['Games'];

		return $structure;
	}
}