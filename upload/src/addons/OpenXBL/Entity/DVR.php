<?php
/**
 * dvr.php
 *
 * database entity for xf_openxbl_dvr
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

class DVR extends Entity
{

	public static function getStructure(Structure $structure)
	{
		$structure->table = 'xf_openxbl_dvr';

		$structure->shortName = 'OpenXBL:DVR';

		$structure->primaryKey = 'media_id';

		$structure->columns = [

			'media_id' => ['type' => self::STR],

			'user_id' => ['type' => self::UINT],

			'type' => ['type' => self::STR],

			'game' => ['type' => self::STR],

			'duration' => ['type' => self::STR],

			'date' => ['type' => self::STR]

		];

		return $structure;
	}
}