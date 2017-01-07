<?php
/**
 * Simple interface to CBW relay boxes
 * 
 * (c) 2016 David Ponevac (david at davidus dot sk) www.davidus.sk
 */
class CBW
{
	/**
	 * Class constructor
	 * @param string $host
	 */
	public function __construct()
	{
		;
	}
	
	/**
	 * Class destructor
	 */
	public function __destruct() {
		;
	}
	
	/**
	 * Retrieve XML data from host
	 * @return boolean|SimpleXMLElement
	 */
	private function getXml($host)
	{
		$context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
		$xmlString = file_get_contents('http://' . $host . '/state.xml', FALSE, $context);

		if (!empty($xmlString)) {
			return simplexml_load_string($xmlString);
		}

		return false;
	}
	
	/**
	 * Get sensor's numerical values
	 * @param array &$config
	 * @return bool
	 */
	public function getValues(&$config = array())
	{
		$status = false;

		if (!empty($config)) {
			// loop over sensors specified in config file
			foreach ($config as $fields) {
				
				// get XML data from remote host
				$xml = $this->getXml($fields['host']);
				
				// key is the sensor name
				$key = $fields['sensor'];
				
				if ($xml && isset($xml->{$key})) {
					// we got a value on the first try
					if (filter_var($xml->{$key}, FILTER_SANITIZE_NUMBER_FLOAT)) {
						$config[$key]['value'] = (double)filter_var($xml->{$key}, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_THOUSAND | FILTER_FLAG_ALLOW_FRACTION);
					}
					// some of these inputs tend to flip-flop, let's try this couple of times
					else {	
						for ($j = 0; $j < 4; $j++) {
							$xml = $this->getXml($fields['host']);

							if ($xml && filter_var($xml->{$key}, FILTER_SANITIZE_NUMBER_FLOAT)) {
								$config[$key]['value'] = (double)filter_var($xml->{$key}, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_THOUSAND | FILTER_FLAG_ALLOW_FRACTION);
								break;
							}//if

							usleep(50000);
						}//for
					}//if

					$status = true;
				}//if
			}//foreach
		}//if

		return $status;
	}
}
