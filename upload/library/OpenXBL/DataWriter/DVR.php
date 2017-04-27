<?php
/**
 * dvr.php
 *
 * Data Writer for xf_openxbl_dvr
 *
 * @category   xbl.io
 * @package    OpenXBL
 * @author     David Regimbal
 * @copyright  2017 David Regimbal
 * @license    MIT
 * @version    1.5
 * @link       https:/xbl.io
 * @see        https://github.com/OpenXBL
 * @since      File available since Release 1.0
 */
class OpenXBL_DataWriter_DVR extends XenForo_DataWriter
{

    protected function _getFields() 
    {
        return array(
            'xf_openxbl_dvr' => array(
                'media_id'  => array(
                    'type'             => self::TYPE_STRING,
                    'required'         => true
                ),
                'user_id'    => array(
                    'type'             => self::TYPE_UINT, 
                    'required'         => true
                ),
                'type'    => array(
                    'type'            => self::TYPE_STRING,
                    'required'        => true
                ),
                'game'    => array(
                    'type'            => self::TYPE_STRING,
                    'required'        => true
                ),
                'duration'    => array(
                    'type'            => self::TYPE_UINT,
                    'required'        => false
                ),
                'date'    => array(
                    'type'            => self::TYPE_UINT,
                    'required'        => true,
                    'default'        => XenForo_Application::$time
                )
            )
        );
    }

    protected function _getExistingData($data)
    {
        if (!$id = $this->_getExistingPrimaryKey($data, 'media_id'))
        {
            return false;
        }
     
        return array('xf_openxbl_dvr' => $this->_getDVRModel()->getDvrById($id));
    }
 
    protected function _getUpdateCondition($tableName)
    {
        return 'media_id = ' . $this->_db->quote($this->getExisting('media_id'));
    }
 
    
    protected function _getDVRModel()
    {
        return $this->getModelFromCache ( 'OpenXBL_Model_DVR' );
    }
 
}