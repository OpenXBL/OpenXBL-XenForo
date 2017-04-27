<?php
/**
 * hub.php
 *
 * Extend hub to widget framework
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
class OpenXBL_Widget_Hub extends WidgetFramework_WidgetRenderer
{

	protected function _getConfiguration()
	{

		return array(

			'name' => 'OpenXBL Hub (Full)',

			'useCache' => false,

			'useWrapper' => false,

		);

	}

	protected function _getOptionsTemplate() 
	{

		return false;

	}

	protected function _render(array $widget, $positionCode, array $params, XenForo_Template_Abstract $renderTemplateObject)
	{

		$params = $renderTemplateObject->getParams();

		$model = XenForo_Model::create('OpenXBL_Model_DVR');

		$params['items'] = $model->getRecentShares();

		$renderTemplateObject->setParams($params);

		return $renderTemplateObject->render();

	}

	protected function _getRenderTemplate(array $widget, $positionCode, array $params)
	{

		return 'openxbl_hub';

	}

}    