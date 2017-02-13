<?php
/**
 * openxbl.php
 *
 * This helper file is critical and used throughout this project.
 * Think of it as the file that talks to the database and OpenXBL.
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
class OpenXBL_Helper_OpenXBL 
{

    // All incoming access tokens get encrypted
    private $cipher = "AES256";

	public function getUserInfo($openxbl_id) 
    {
		
        $db = XenForo_Application::get('db');
        $result = $db->fetchRow("SELECT gamertag 
                                FROM xf_user_openxbl
                                WHERE xuid = '" . $openxbl_id ."';");


		if(!empty($result)) {
			return array(
    	        'username' => $result['gamertag'],
                'gamertag' => $result['gamertag'],
                'xuid' => $openxbl_id,
                'avatar' => '',
	            'icon' => '',
                //'state' => $openxbl_id

			);
        } else {
			return array(
				'username' => "Unknown Xbox Live Account",
				'avatar' => "Uh-oh!"
			);
		}
	}

    public function getOpenXBLUsers() {
		$rVal = array();
		$db = XenForo_Application::get('db');
		$results = $db->fetchAll("SELECT u.provider_key, p.user_id, p.username 
                                FROM xf_user_external_auth u, xf_user p 
                                WHERE u.user_id = p.user_id 
                                AND u.provider = 'openxbl' 
                                ORDER BY p.username;");
		foreach($results as $row) {
			$rVal[] = array(
				'id' => OpenXBL_Helper_OpenXBL::convertIdToString($row['provider_key']),
				'id64' => $row['provider_key'],
				'username' => $row['username'],
				'user_id' => $row['user_id']
			);
		}
		return $rVal;
	}

    public function deleteOpenXBLData($user_id) {
        $db = XenForo_Application::get('db');
        $db->query("DELETE FROM xf_user_openxbl
                    WHERE user_id = $user_id");
    }

    public function getOpenXBLProfileAPI($openxblids)
    {
        
        $OpenXBLAPIDomain = $this->getOpenXBLAPIBase();
        
        if (!empty($openxblids)) {
            $options = XenForo_Application::get('options');
            $OpenXBLAPIkey = $options->openxblKey;
            if(empty($OpenXBLAPIkey)) {
                return $this->responseError('Missing API Key for OpenXBL. Please contact the forum administrator with this error.');
            }
            
            $OpenXBLAPI = $OpenXBLAPIDomain
                        .'/player/summary/?xuids='
                        .$openxblids;
        }

        return $OpenXBLAPI;
    }

    public function checkElgibility()
    {

        $user = XenForo_Visitor::getInstance()->toArray();

        if( $user['user_id'] == 0 )
        {
            return false;
        }

        if (!isset($user['externalAuth']))
        {
            $user['externalAuth'] = !empty($user['external_auth']) ? @unserialize($user['external_auth']) : array();
        }    
        
        $db = XenForo_Application::get('db');

        $token = $db->fetchRow('SELECT * FROM xf_user_openxbl WHERE user_id = ' . $user['user_id']);

        if( $this->decrypt($token['access_token']) )
        {
            return true;
        }    

    }

    public function getOpenXBLAPIBase()
    {
        $options = XenForo_Application::get('options');
        $OpenXBLAPIcase = $options->openxblAPILink;
        switch ($OpenXBLAPIcase) {
            case 0:
                $openxblAPIDomain = 'https://xbl.io/api/v1';
                break;
            default:
                $openxblAPIDomain = 'https://xbl.io/api/v1';
                break;
        }
        
        return $openxblAPIDomain;
    }
    
    public function getFriendsList()
    {
        $user = XenForo_Visitor::getInstance()->toArray();

        $token = $this->getAccessToken($user['user_id']);

        $options = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-Authorization' => $token,
                'X-Contract' => 100
            )
        );

        return $this->call('GET', $this->getOpenXBLAPIBase() . '/friends', $options);
    }

    public function friendSorter(&$array, $key, $direction)
    {

        switch ($direction) {
            case 'up':
                $temp = array($key => $array[$key]);
                unset($array[$key]);
                $array = $temp + $array;
                break;
            
            case 'down':
                $value = $array[$key];
                unset($array[$key]);
                $array[$key] = $value;
                break;
        }

        return $array;

    }

    public function call($method, $url, $options = array(), $json = false)
    {
        $crl = curl_init($url);
        $headr = array();

        if( !empty( $options['headers'] ) )
        {
        	foreach( $options['headers'] as $header => $value )
        	{
        		$headr[] = $header . ':' . $value;
        	}
        }

        curl_setopt($crl, CURLOPT_HTTPHEADER,$headr);
        curl_setopt($crl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($crl, CURLOPT_FOLLOWLOCATION, TRUE);
        if($method == 'POST')
        {
            if( !empty( $options['payload'] ) )
            {
                curl_setopt( $crl, CURLOPT_POSTFIELDS, json_encode( $options['payload'] ) );
            }
        	
        	curl_setopt($crl, CURLOPT_POST,true);       	
        }
        else if( $method == 'GET' )
        {
        	curl_setopt($crl, CURLOPT_POST,false);     
        }
        else
        {
            curl_setopt($crl, CURLOPT_CUSTOMREQUEST, $method);
        }

        $result = curl_exec($crl);

        curl_close($crl);

        if($json)
        {
            return json_decode($result, true);
        }

        return $result;
    }

    public function getAccessToken($user_id)
    {
        
        $db = XenForo_Application::get('db');
        $result = $db->fetchRow("SELECT access_token, refresh_token, updated_at 
                                FROM xf_user_openxbl
                                WHERE user_id = " . $user_id .";");

        if(!empty($result)) {

            if( time() > strtotime($result['updated_at']) )
            {
                // token expired. Request authority for new access token.
                
                // not built yet
            }

            return $this->decrypt($result['access_token']);  
        }
        else
        {
            return false;
        }


    }

    private function salt()
    {
        
        return 'dMH|pQR3Ye)Bfyn}(&V<*A8imN]O^Jg Vss@o-{SqZU`-6m#4hP?4025gnk}~35F';
        
    }
    
    public function encrypt($string) 
    {


		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

        return base64_encode($iv.openssl_encrypt($string, $this->cipher, $this->salt(), 0, $iv));
    }
    
    public function decrypt($string) 
    {
        if (0 === strpos($string, 'XBL')) {
            return $string;
        }

        $string = base64_decode($string);

		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		$iv = substr($string, 0, $iv_size);

        return openssl_decrypt(substr($string, $iv_size), $this->cipher, $this->salt(), 0, $iv);

    }

}