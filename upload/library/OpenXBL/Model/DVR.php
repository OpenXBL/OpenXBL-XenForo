<?php
/**
 * openxbl.php
 *
 * A model interacts with the database. Any SQL gets done here.
 * These statements only are designed to GET database information
 * Data Writer will insert, update, and delete on your behalf
 *
 * @category   xbl.io
 * @package    OpenXBL
 * @author     David Regimbal
 * @copyright  2017 David Regimbal
 * @license    MIT
 * @version    1.0
 * @link       https:/xbl.io
 * @see        https://github.com/OpenXBL
 * @since      File available since Release 1.0
 *
 */
class OpenXBL_Model_DVR extends XenForo_Model
{
	/*
	 * This gets a DVR item by Media ID
	 * return array
	 */
	public function getDvrById($id)
	{
	    return $this->_getDb()->fetchRow('
	        SELECT U.gamertag, O.* FROM xf_openxbl_dvr O JOIN xf_user_openxbl U ON O.user_id = U.user_id WHERE media_id = ?', $id);
	} 

	/*
	 * This gets DVR items but with filter items for pagination
	 * @param { type < string 'video', string 'screenshot' > , array < int page, int perPage >}
	 * @return array<data, total>
	 */
	public function filterDvrByType($type, $options = array('page' => 0, 'perPage' => 0))
	{

		$limitOptions = $this->prepareLimitFetchOptions($options);

	    $sql = 'SELECT U.gamertag, O.* FROM xf_openxbl_dvr O JOIN xf_user_openxbl U ON O.user_id = U.user_id WHERE type = ?';

		$sql = $this->limitQueryResults($sql, $limitOptions['limit'], $limitOptions['offset']);

		$total = $this->_getDb()->fetchRow('SELECT COUNT(*) AS total FROM xf_openxbl_dvr WHERE type = ?', $type);

	    return array('data' => $this->_getDb()->fetchAll($sql, $type), 'total' => $total['total']);

	} 

	/*
	 * This gets DVR items by type
	 * @param < string 'video' , string 'screenshot' >
	 * @return array
	 */
	public function getDvrByType($type)
	{
		return $this->_getDb()->fetchAll('
	        SELECT U.gamertag, O.* FROM xf_openxbl_dvr O JOIN xf_user_openxbl U ON O.user_id = U.user_id WHERE type = ?', $type);
	}

	/*
	 * This gets recent DVR items (used for the hub)
	 * @return array
	 */
	public function getRecentShares()
	{
		return $this->_getDb()->fetchAll('
	        SELECT U.gamertag, O.* FROM xf_openxbl_dvr O JOIN xf_user_openxbl U ON O.user_id = U.user_id LIMIT 15');
	}
}