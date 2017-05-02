<?php
/**
 * manufacture.php
 *
 * XenForo install/uninstall methods for this addon. 
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
 */
class OpenXBL_Manufacture 
{

	private static $_instance;
	protected $_db;

	public static final function getInstance() 
	{
		if(!self::$_instance) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	protected function _getDb() 
	{
		if($this->_db === null) {
			$this->_db = XenForo_Application::get('db');
		}

		return $this->_db;
	}

	public static function build($existingAddOn, $addOnData) 
	{

		if (XenForo_Application::$versionId < 1050000) {
            throw new XenForo_Exception('This add-on requires XenForo 1.5.0 Beta 1 or higher.', true);
        }
		
		$startVersion = 1;
		$endVersion = $addOnData['version_id'];

		if($existingAddOn) {
			$startVersion = $existingAddOn['version_id'] +1;
		}

		$install = self::getInstance();

		for($i = $startVersion; $i <= $endVersion; $i++) {
			$method = "_installVersion$i";
			if(method_exists($install, $method) === false) {
				continue;
			}

			$install->$method();
		}
	}

    protected function _installVersion1() 
    {
		$db = $this->_getDb();

		$db->query("CREATE TABLE IF NOT EXISTS xf_user_openxbl (
			  xuid varchar(255) NOT NULL,
			  user_id int(11) NOT NULL,
			  gamertag varchar(25) NOT NULL,
			  avatar_url text NOT NULL,
			  access_token text NOT NULL,
			  created_at timestamp NULL DEFAULT NULL,
			  updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			  PRIMARY KEY (xuid)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;");
	}

    protected function _installVersion8() 
    {

		$db = $this->_getDb();

		self::addColumnIfNotExists('xf_user_openxbl', 'refresh_token', 'text', 'access_token');

	}

    protected function _installVersion12() 
    {

		$db = $this->_getDb();

		$db->query("CREATE TABLE IF NOT EXISTS xf_openxbl_dvr (
			  media_id varchar(255) NOT NULL,
			  user_id int(11) NOT NULL,
			  type varchar(255) NOT NULL,
			  game varchar(255) NOT NULL,
			  duration int(11) NULL,
			  date varchar(255) NULL, 
			  created_at timestamp NULL DEFAULT NULL,
			  updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			  PRIMARY KEY (media_id)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;");

		// due to custom gamer pics the length will exceed VARCHAR
		$db->query('ALTER TABLE xf_user_openxbl DROP COLUMN avatar_url;');
		$db->query('ALTER TABLE xf_user_openxbl ADD avatar_url text NULL;');

	}

	public static function destroy() 
	{
		$lastUninstallStep = 1;

		$uninstall = self::getInstance();

		for($i = 1; $i <= $lastUninstallStep; $i++) {
			$method = "_uninstallStep$i";
			if(method_exists($uninstall, $method) === false) {
				continue;
			}

			$uninstall->$method();
		}
	}

	protected function _uninstallStep1() 
	{
		$db = $this->_getDb();

		$db->query("DROP TABLE xf_user_openxbl");
	}
    
    public static function dropColumnIfExists($tableName, $fieldName)
    {
    	$db = XenForo_Application::get('db');
    
    	$exists = $db->fetchRow("
			SHOW COLUMNS
			FROM {$tableName}
			WHERE Field = ?
		", $fieldName);
    
    	if ($exists)
    	{
    		$db->query("
    				ALTER TABLE {$tableName} DROP {$fieldName}
    		");
    	}
    }
	
    public static function addColumnIfNotExists($tableName, $fieldName, $fieldDef, $after)
    {
    	$db = XenForo_Application::get('db');
    
    	$exists = $db->fetchRow("
			SHOW COLUMNS
			FROM {$tableName}
			WHERE Field = ?
		", $fieldName);
    
    	if (!$exists)
    	{
    		$db->query("
    				ALTER TABLE {$tableName} ADD {$fieldName} {$fieldDef} AFTER {$after}
    		");
    	}
    }	

}
?>