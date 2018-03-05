# SandS Logger 
## Synopsis
A generic plugin to manage logs. 

## Code Example
Minimum call 
```php
$sandsLogger = new SandsLogger ();
$sandsLogger->error ( 'The messagge you want to log.', plugin_basename ( __FILE__ ));
```
Send the log a mail (in addition) 
```php
$sandsLogger = new SandsLogger ();
$sandsLogger->error ( 'The messagge you want to log.', plugin_basename ( __FILE__ ), array (
		'sendMail' => 'true'
) );
```
Send to mail and store into a custom field 
```php
$sandsLogger = new SandsLogger ();
$sandsLogger->error ( 'The messagge you want to log.', plugin_basename ( __FILE__ ), array (
		'sendMail' => 'true',
		'intoField' => array('entity' => $order->id, 'field' => 'field_57s90asd19481fq')
) );
```

## Motivation
This plugin is useful because make it easy to handle logs in different ways. You can decide if a message have to be sent via mail or if have to go on a centralize place (for ex. cloudwatch); you can even save the log in a custom field. 

### File structure
```
sands-logger/
├── images/
│   ├── icon.png
├── includes/
│   ├── backend.php
│   └── options.tpl.php
└── sands-logger.php
└── README.md
```

## Installation
1. **Important** Exclude the *.log files from being served. Append the following in the .htaccess:
```apache
<Files ~ ".log$">
Order allow,deny
Deny from all
</Files> 
```
2. Enable the plugin as usual
3. Change the configuration under: /wp-admin/admin.php?page=sands-logger%2Fincludes%2Fbackend.php 

## Default behavior
The Logger creates by default a folder called `sands_logger` in the root of the wordpress. Inside this folder it creates a file whose name is the current date, like: `11.8.2016.log` (see code in sands-logger.php from line 163)
The latest log messages can be tailed using 
```bash 
tail -f sands_logger_current
``` 
`sands_logger_current` is a symbolic link that always points to the latest log file. 
The logger store also whatever comes as message in the normal log file `calling error_log("the message")` so we never loose anything. (see code in sands-logger.php at line 184)

## TODO 
- Create the logentries dispatcher (see function `_sendLogViaLogentries()`) and the relative settings
- Create cloudwatch dispatcher (see function `_sendLogViaCloudwatch()`) and the relative settings
- Create a set of options to force sending to a specific dispatcher (something like: send always to cloudwatch); for now you can eventually do it only within each log call using the options, like: `'sendMail' => 'true', 'cloudwatch' => 'true'` 
- Think about this http://php.net/manual/en/function.set-error-handler.php is it the case to take all inside this plugin? Maybe really not...

## Team
[![Beppe](https://avatars2.githubusercontent.com/u/485458?v=3&s=130)](https://github.com/giuseppeminnella) | [![Robert](https://avatars2.githubusercontent.com/u/365843?v=3&s=130)](https://github.com/rmunsky)

## License
This is a plugin developed for the internal use and it's property of [Software & Support Media GmbH](http://sandsmedia.com)