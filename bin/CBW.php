<?php

$basePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;

// include classes
include $basePath . 'class' . DIRECTORY_SEPARATOR . 'CBW.php';

// read in config file
$configFile = $basePath . 'conf' . DIRECTORY_SEPARATOR . 'config.json';
$sensorData = array();

if (file_exists($configFile) && is_readable($configFile)) {
	$jsonConfig = file_get_contents($configFile);
	$sensorData = json_decode($jsonConfig, true);
	
	if (empty($sensorData)) {
		die("No valid data found in '{$configFile}'!\r\n");
	}
} else {
	die("Config file '{$configFile}' is not accessible!\r\n");
}

// process the file and get sensor values
$cbw = new CBW();
$result = $cbw->getValues($sensorData);

if ($result) {
	var_dump($sensorData);
}