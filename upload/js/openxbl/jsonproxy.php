<?php
/**
 * Grabs JSON data for OpenXBL
 *
 *
 * Originally written by Nico Bergemann for SteamProfiles <barracuda415@yahoo.de>
 * Copyright 2011 Nico Bergemann
 *
 * Code ported by David Regimbal for OpenXBL
 * Copyright 2017 David Regimbal.
 * Website: https://xbl.io
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *      
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *      
 */
header('content-type: application/json; charset: utf-8'); 


$startTime = microtime(true);
$fileDir = '../../';
require($fileDir . '/library/XenForo/Autoloader.php');
XenForo_Autoloader::getInstance()->setupAutoloader($fileDir . '/library');

XenForo_Application::initialize($fileDir . '/library', $fileDir);
XenForo_Application::set('page_start_time', $startTime);
XenForo_Application::disablePhpErrorHandler();
XenForo_Application::setDebugMode(false);

XenForo_Application::$externalDataPath = $fileDir . '/data';
XenForo_Application::$externalDataUrl = $fileDir . '/data';
XenForo_Application::$javaScriptUrl = $fileDir . '/js';

restore_error_handler();
restore_exception_handler();

$options = XenForo_Application::get('options');


if (!empty($_GET['openxblids'])) {
    
    $sHelper = new OpenXBL_Helper_OpenXBL();
    $openxblProfileAPI = $sHelper->getOpenXBLProfileAPI($_GET['openxblids']);

    if (isset($_GET['fullprofile'])) {
        $fullProfile = $_GET['fullprofile'];
    } else {
        $fullProfile = 0;
    }
    
    $options = array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'X-Authorization' => $options->openxblKey,
            'X-App' => 100
        )
    );
    $contentJson = $sHelper->call('GET',$openxblProfileAPI, $options);
    
    $contentDecoded = json_decode($contentJson);


    if (function_exists('gzcompress') && (!ini_get('zlib.output_compression'))) {
        ob_start('ob_gzhandler');
    } else {
        ob_start();
    }

    echo json_encode($contentDecoded->data[0]->people);

    ob_end_flush();
}
?>