#PHPAPRS
This is basically a fork of the phpAPRS library by Matthew Asham.
All I did was some refactoring to get to know the code.
The original readme, credits and license can be found in /doc.

##Installation
run `composer require dbursem/phpaprs`. Check http://getcomposer.org for more info on composer.

##Usage

There is an example APRS bot in the example directory. Your best chance to get things working is using this example and
changing it as you like. 
* Copy the example directory to your install directory
* Copy the aprsbot.cfg.php file to local.aprsbot.cfg.php and edit it with your personal settings. 
* Set the filter to a valid APRS filter.
* Call the example aprsbot from the commandline with `php aprsbot.php`.


### Minimal steps:
* Copy aprsbot.cfg.php to local.aprsbot.cfg.php and edit it with your required settings
* Make an aprs object
```php
$aprs = new dbursem\phpaprs\APRS();
```
* Connect to the APRS host
```php
if ($aprs->connect(HOST, PORT, MYCALL, PASSCODE, $filter) == FALSE) 
{
    die( "Connect failed\n");
}
```
* Create a loop to handle the input/output
```php
while(1){
	$aprs->ioloop(5);
	// do stuff
	sleep(1);
}
```
