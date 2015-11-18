#PHPAPRS
This is basically a fork of the phpAPRS library by Matthew Asham.
All I did was some refactoring to get to know the code.
The original readme, credits and license can be found in /doc.

##Installation
clone this repo and run `composer update` in the root directory, to activate the composer autoloader. 

I'll see if I can make this my first project on packagist soon!

##Usage

There is an example APRS bot in the example directory. Your best chance to get things working is using this example and
changing it as you like. 

### Minimal steps:
1. Copy aprsbot.cfg.php to local.aprsbot.cfg.php and edit it with your required settings
2. Make an aprs object
```php
$aprs = new dbursem\phpaprs\APRS();
```
3. Connect to the APRS host
```php
if ($aprs->connect(HOST, PORT, MYCALL, PASSCODE, $filter) == FALSE) 
{
    die( "Connect failed\n");
}
```
4. Create a loop to handle the input/output
```php
while(1){
	$aprs->ioloop(5);
	// do stuff
	sleep(1);
}
```
