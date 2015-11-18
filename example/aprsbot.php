<?php
// example.php : Example phpAPRS bot

use dbursem\phpaprs;
use dbursem\phpaprs\packets;

$debug = true;

require "../vendor/autoload.php";

require "utils/message.php";
require "utils/position.php";
require "utils/status.php";
require "utils/utils.php";


if (file_exists("local.aprsbot.cfg.php"))
    require "local.aprsbot.cfg.php";
else
    require "aprsbot.cfg.php";



$aprs = new phpaprs\APRS();
$aprs->_debug = $debug;


$beacon = new packets\APRS_Item(BEACON_LATITUDE, BEACON_LONGITUDE, MYCALL, BEACON_SYMBOL, BEACON_STATUS);
$beacon->setCallsign(MYCALL);

$filter = FILTER;

if ($aprs->connect(HOST, PORT, MYCALL, PASSCODE, $filter) == FALSE)
{
    die( "Connect failed\n");
}

$lastbeacon = 1;

// Setup our callbacks to process incoming stuff
 $aprs->addCallback(APRSCODE_MESSAGE, "*", "handlemessage");
 $aprs->addCallback(APRSCODE_STATUS, "*", "handlestatus");
 $aprs->addCallback(APRSCODE_POSITION, "*", "handleposition");

while (1) {
    // Beacon every BEACON_INTERVAL seconds
    if (time() - $lastbeacon > BEACON_INTERVAL) {
        echo "Send beacon\n";
        $aprs->sendPacket($beacon);
        $lastbeacon = time();
    }
    $aprs->ioloop(5);    // handle I/O events
    sleep(1);    // sleep for a second to prevent cpu spinning
}
