<?php
/**
 * hubphp
 *
 * this file helps build hub/showcase calls
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
class OpenXBL_Helper_Hub 
{

	public static function buildGameDVR()
	{
		$sHelper = new OpenXBL_Helper_OpenXBL();

		$build = array();

		$data = json_decode($sHelper->getGameClipsDVR(), true);

		$clips = $data['data'][0]['gameClips'];

		foreach($clips as $clip)
		{

			$isShared = false;

			if( OpenXBL_Helper_Hub::isShared($clip['gameClipId']) )
			{
				$isShared = true;
			}

			$build[] = array(
				'id' => $clip['gameClipId'],
				'date' => strtotime($clip['dateRecorded']) - XenForo_Locale::getTimeZoneOffset(),
				'caption' => $clip['userCaption'],
				'durationSeconds' => $clip['durationInSeconds'],
				'titleId' => $clip['titleId'],
				'thumbnail' => $clip['thumbnails'][0]['uri'],
				'url' => $clip['gameClipUris'][0]['uri'],
				'name' => $clip['clipName'],
				'gameTitle' => $clip['titleName'],
				'type' => 'video',
				'shared' => $isShared
			);
		}

		return $build;
	}

	public static function buildScreenshotsDVR()
	{
		$sHelper = new OpenXBL_Helper_OpenXBL();

		$build = array();

		$data = json_decode($sHelper->getScreenshotsDVR(), true);

		$screenshots = $data['data'][0]['screenshots'];

		foreach($screenshots as $screenshot)
		{
			$build[] = array(
				'id' => $screenshot['screenshotId'],
				'date' => strtotime($screenshot['dateTaken']) - XenForo_Locale::getTimeZoneOffset(),
				'caption' => $screenshot['userCaption'],
				'titleId' => $screenshot['titleId'],
				'thumbnail' => $screenshot['thumbnails'][0]['uri'],
				'url' => $screenshot['screenshotUris'][0]['uri'],
				'name' => $screenshot['screenshotName'],
				'gameTitle' => $screenshot['titleName'],
				'type' => 'screenshot'
			);
		}

		return $build;

	}


	/*
	 * Share user DVR item to Showcase
	 */
	public static function shareDvrItem($data)
	{

		// store thumbnail / media content locally
		OpenXBL_Helper_Hub::downloadMedia($data);

		// get the current user
		$visitor = XenForo_Visitor::getInstance()->toArray();

		// get addon options
		$options = XenForo_Application::get('options');

		// Write the item to the database
	    $dw = XenForo_DataWriter::create('OpenXBL_DataWriter_DVR');

	    $dw->set('media_id', $data['media_id']);

	    $dw->set('type', $data['type']);

	    $dw->set('game', $data['game']);

	    $dw->set('duration', $data['duration']);

	    $dw->set('date', $data['date']);
	    
	    $dw->set('user_id', $visitor['user_id']);
	 
	    $dw->save();

	    if($options->openxblBlastActivity)
	    {
	    	// Let other users know about it!
	    	OpenXBL_Helper_Hub::shareItemToShoutbox($data);
		}
	}

	/*
	 * Share user DVR item to Showcase
	 */
	public static function deleteDvrItem($data)
	{

		// Write the item to the database
	    $dw = XenForo_DataWriter::create('OpenXBL_DataWriter_DVR');

	    $dw->setExistingData($data);

	    $dw->delete();
	}

	/*
	 * Make a post in the shoutbox
	 */
	public static function shareItemToShoutbox($data)
	{
		$options = XenForo_Application::get('options');

		$visitor = XenForo_Visitor::getInstance()->toArray();

		$media_type = ( $data['type'] == 'video' ) ? new XenForo_Phrase('openxbl_recording') : new XenForo_Phrase('openxbl_screenshot');

		$dw = XenForo_DataWriter::create('Dark_TaigaChat_DataWriter_Message');

		$dw->setOption(Dark_TaigaChat_DataWriter_Message::OPTION_IS_AUTOMATED, true);

		$dw->set('user_id', $visitor['user_id']);

		$dw->set('username', $visitor['user_id'] > 0 ? $visitor['username'] : new XenForo_Phrase('guest'));

		$link = $options->boardUrl . "/index.php?openxbl/showcase&media=" . $data['media_id'];

		$dw->set('message', new XenForo_Phrase('openxbl_shared', array('link' => $link, 'type' => $media_type), false ) );

		$dw->set('activity', 1);

		$dw->save();	
	}

	public static function isShared($media_id)
	{
		$model = XenForo_Model::create('OpenXBL_Model_DVR');

		return $model->getDvrById($media_id);
	}

	public static function getSharedDvr($type)
	{
		$db = XenForo_Application::get('db');
		return $db->fetchAll("SELECT U.gamertag, O.media_id, O.type, O.thumbnail, O.url, O.game, O.duration FROM xf_openxbl_dvr O JOIN xf_user_openxbl U ON O.user_id = U.user_id WHERE type = \"".$type."\"");
	}

	public static function getDvrItem($id)
	{
		$db = XenForo_Application::get('db');
		return $db->fetchAll("SELECT U.gamertag, O.media_id, O.type, O.thumbnail, O.url, O.game, O.duration FROM xf_openxbl_dvr O JOIN xf_user_openxbl U ON O.user_id = U.user_id WHERE media_id = \"" . $id . "\"")[0];
	}

	/*
	 * Download DVR media to our local server
	 */
	public static function downloadMedia($data)
	{

		// We won't download anything unless from a verified address
		if( !OpenXBL_Helper_Hub::isAuthorizedSource($data['url']) || !OpenXBL_Helper_Hub::isAuthorizedSource($data['thumbnail']) )
		{
			return false;
		}

		// Save media source
		$media_source = file_get_contents($data['url']);

		$extension = ($data['type'] == 'video') ? '.mp4' : '.png';

		$fp = fopen(realpath(dirname(basename(__DIR__))) . "/openxbl/media/" . $data['media_id'] . $extension, "w");

		fwrite($fp, $media_source);

		fclose($fp);


		// Save media thumbnail
		$media_thumbnail = file_get_contents($data['thumbnail']);

		$fp = fopen(realpath(dirname(basename(__DIR__))) . "/openxbl/media/thumbnail/" . $data['media_id'] . ".png", "w");

		fwrite($fp, $media_thumbnail);

		fclose($fp);

	}

	public static function isAuthorizedSource($url)
	{

	    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {

	        $url = "https://" . $url;

	    }

	    $domain = implode('.', array_slice(explode('.', parse_url($url, PHP_URL_HOST)), -2));

	    if ($domain == 'xboxlive.com') 
	    {

	        return true;

	    } 
	    else 
	    {

	        return false;

	    }

	}

}