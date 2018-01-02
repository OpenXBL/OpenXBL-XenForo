<?php
/**
 * leaderboards.php
 *
 * sets cron to update leaderboard stats
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

namespace OpenXBL\Cron;

class Leaderboards
{
	public static function run()
	{
		\XF::app()->jobManager()
			->enqueueUnique('openxbl', '\OpenXBL:Leaderboards', [], false);
	}
}