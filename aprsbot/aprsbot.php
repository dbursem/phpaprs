<?php
// aprsbot.php : Example phpAPRS bot

$debug = false;

require "../lib/class.bcwns.aprs.php";
require "position.php";
require "status.php";
require "message.php";
require "utils.php";
require "OgnPosition.php";


if (file_exists("local.aprsbot.cfg.php"))
    require "local.aprsbot.cfg.php";
else
    require "aprsbot.cfg.php";


$aprs = new BCWNS_APRS();
$aprs->_debug = $debug;
$ogn = new OgnPosition(DB_NAME,DB_USER,DB_PASS,'localhost', $debug);


$beacon = new BCWNS_APRS_Item(BEACON_LATITUDE, BEACON_LONGITUDE, MYCALL, BEACON_SYMBOL, BEACON_STATUS);
$beacon->setCallsign(MYCALL);

$filter = $ogn->getFilter();

if ($aprs->connect(HOST, PORT, MYCALL, PASSCODE, $filter) == FALSE) {
    echo "Connect failed\n";
    exit;
}

$lastbeacon = 1;

// Setup our callbacks to process incoming stuff
// $aprs->addCallback("APRSCODE_MESSAGE", "*", "aprsbot_handlemessage");
// $aprs->addCallback("APRSCODE_STATUS", "*", "aprsbot_handlestatus");
// $aprs->addCallback("APRSCODE_POSITION", "*", 'aprsbot_handleposition');

//setup callback for OGN messages
$aprs->addCallback('/','APRS','ognposition');

//wrapper function for callback
function ognposition($a,$b){
    global $ogn;
    $ogn->handlePosition($a,$b);
}

while (1) {

    // Beacon every BEACON_INTERVAL seconds
    if (time() - $lastbeacon > BEACON_INTERVAL) {
        echo "Send beacon\n";
        $aprs->sendPacket($beacon);
        $lastbeacon = time();
    }
    $aprs->ioloop(5);    // handle I/O events
    $ogn->savePositions(); // save records to database
    sleep(1);    // sleep for a second to prevent cpu spinning
}
