<?php
/**
 * Simple interface to CBW relay boxes
 * 
 * (c) 2016 David Ponevac (david at davidus dot sk) www.davidus.sk
 */

// app base path
$basePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
$lockPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR;

// include classes
include $basePath . 'class' . DIRECTORY_SEPARATOR . 'CBW.php';
include $basePath . 'class' . DIRECTORY_SEPARATOR . 'Email.php';

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
		$lockFileHigh = $lockPath . md5($sensor . '_high');
		$lockFileLow = $lockPath . md5($sensor . '_low');

		// do we have a value
		if (isset($data['value'])) {
			echo "{$sensor}> Name: {$data['name']}, Value: {$data['value']} {$data['units']}\r\n";

			// did we cross the lower threshold?
			if (isset($data['thresholdLow']) && ($data['thresholdLow'] !== false) && ($data['value'] <= $data['thresholdLow'])) {
				echo "{$sensor}> Alarm for low threshold of {$data['thresholdLow']} {$data['units']}\r\n";
				
				if (!file_exists($lockFileLow)) {
					if (!empty($data['notify']) && is_array($data['notify'])) {
						$email = new Email();

						if ($email->ready) {
							$email->message("Notification for {$data['name']}", "Current value of {$data['value']} {$data['units']} is below your low threshold of {$data['thresholdLow']} {$data['units']}");

							foreach ($data['notify'] as $address) {
								$email->addAddress($address);
							}

							if ($email->send()) {
								echo "{$sensor}> Notification email sent!\r\n";
							}
						}
					}//if
				}//if
				
				// create lock file
				touch($lockFileLow);
			} else {
				// cleanup if necessary
				if (file_exists($lockFileLow)) {
					unlink($lockFileLow);
					clearstatcache();
				}
			}
			
			// did we cross the upper treshold?
			if (isset($data['thresholdHigh']) && ($data['thresholdHigh'] !== false) && ($data['value'] >= $data['thresholdHigh'])) {
				echo "{$sensor}> Alarm for high threshold of {$data['thresholdHigh']} {$data['units']}\r\n";
				
				if (!file_exists($lockFileHigh)) {
					if (!empty($data['notify']) && is_array($data['notify'])) {
						$email = new Email();

						if ($email->ready) {
							$email->message("Notification for {$data['name']}", "Current value of {$data['value']} {$data['units']} is above your high threshold of {$data['thresholdHigh']} {$data['units']}");

							foreach ($data['notify'] as $address) {
								$email->addAddress($address);
							}

							if ($email->send()) {
								echo "{$sensor}> Notification email sent!\r\n";
							}
						}
					}//if
				}//if

				// create lock file
				touch($lockFileHigh);
			} else {
				// cleanup if necessary
				if (file_exists($lockFileHigh)) {
					unlink($lockFileHigh);
					clearstatcache();
				}
			}//if	
		}
		// no value returned from XML call
		else {
			echo "No value found!\r\n";
		}
	}// foreach
} else {
	die("No data was acquired from the sensors!\r\n");
}