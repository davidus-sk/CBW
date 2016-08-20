# ControlByWeb PHP App

This simple PHP based application will let you monitor and graph sensor inputs on the CBW device and send out email alerts when preset thresholds are crossed. Checkout this project, edit the `config.json` and `email.json` files in the `config` directory and schedule `bin\CBW.php` to run as after as you like.

## Configuration

Application behavior is controlled via two JSON configuration files. You can edit those at any point in time as they are continously read by the scripts. The `config.json` file contains an array of sensor objects coming from one or more CBW devices. Tthe `email.json` file cotains email server related settings.

``json
[
	{
		"sensor":"sensor1",
		"host":"192.168.42.21",
		"type":"temperature",
		"name":"Outside temperature",
		"units":"F",
		"thresholdLow":0,
		"thresholdHigh":50,
		"notify":[
			"me@domain.com"
		]
	}
]
``

`sensor` Name of the CBW's variable you ant to monitor. You can add one or more into the object.
`host` IP or domain name of the CBW device. You can add variables from multie CBW devices.
`type` This is not used right now, but it should name the physical attribute being monitored [temperature, humidity, voltage, etc.].
`name` Name the sensor. This is free text, you can put in whatever you want.
`units` Self explanatory - F, C, %RH, ...
`thresholdLow` An alert will be generated when current value dips below this value. To disable put in false.
`thresholdHigh` An alert will be generated when current value rises above this value. To disable put in false.
`notify` An array of email addresses that should be notified. To disable leave array blank.

``json
{
	"host":"pod51009.outlook.com",
	"protocol":"tls",
	"port":587,
	"username":"me@domain.com",
	"password":"XXXX",
	"fromAddress":"me@domain.com"
}
``

## How to deploy

The easiest way to get this application going is to schedule it via cron or Task scheduler. You can also wrap the execution of `CBW.php` inside a while(true) loop and run it more frequently than once a minute.

``
* * * * * root php /path/to/bin/CBW.php
``