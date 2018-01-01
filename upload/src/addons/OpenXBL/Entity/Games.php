<?php
/**
 * games.php
 *
 * database entity for xf_openxbl_games
 *
 * @category   xbl.io
 * @package    OpenXBL
 * @author     David Regimbal
 * @copyright  2017 David Regimbal
 * @license    MIT
 * @version    0.1
 * @link       https:/xbl.io
 * @see        https://github.com/OpenXBL
 * @since      File available since Release
 */

namespace OpenXBL\Entity;

use XF\Mvc\Entity\Entity;

use XF\Mvc\Entity\Structure;

class Games extends Entity
{
	public static function getStructure(Structure $structure)
	{
		$structure->table = 'xf_openxbl_games';

		$structure->shortName = 'OpenXBL:Games';

		$structure->primaryKey = 'title_id';

		$structure->columns = [

			'title_id' => ['type' => self::STR],

			'title' => ['type' => self::STR],

			'image' => ['type' => self::STR],

			'gamerscore' => ['type' => self::UINT],

			'achievements' => ['type' => self::UINT]

		];

		$structure->relations = [
			'UserGames' => [

				'entity' => 'OpenXBL:UserGames',

				'type' => self::TO_MANY,

				'conditions' => 'title_id',

				'key' => 'id'
			]
		];

		return $structure;
	}
}