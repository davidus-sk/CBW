<?php
/**
 * Simple interface to CBW relay boxes
 * 
 * (c) 2016 David Ponevac (david at davidus dot sk) www.davidus.sk
 */

// app base path
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

// few sanity checks
if ($result && !empty($sensorData)) {
	foreach ($sensorData as $sensor => $data) {
		echo "{$sensor}> ";

		// do we have a value
		if (isset($data['value'])) {
			echo "Name: {$data['name']}, Value: {$data['value']} {$data['units']}\r\n";
		
			// did we cross the lower threshold?
			if (isset($data['thresholdLow']) && ($data['thresholdLow'] !== false) && ($data['value'] <= $data['thresholdLow'])) {
				echo "\tAlarm for low threshold of {$data['thresholdLow']} {$data['units']}";
			}
			
			// did we cross the upper treshold?
			if (isset($data['thresholdHigh']) && ($data['thresholdHigh'] !== false) && ($data['value'] >= $data['thresholdHigh'])) {
				echo "\tAlarm for high threshold of {$data['thresholdHold']} {$data['units']}";
			}	
		}
		// no value returned from XML call
		else {
			echo "No value found!\r\n";
		}
	}// foreach
} else {
	die("No data was acquired from the sensors!\r\n");
}