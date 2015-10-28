<?php
function txtmsg($aprs, $msg, $dest, $from = FALSE, $path = FALSE)
{
    $msg = new BCWNS_APRS_Message($msg, $dest);
//  $msg->setCallsign(MYCALL);
    if ($from == FALSE)
        $msg->setCallsign(MYCALL);
    else
        $msg->setCallsign($from);
    if ($path != FALSE) {
        $msg->setPath($path);
    }
    $aprs->sendPacket($msg);
}

function aprs2dec($ap)
{
    $dir = substr($ap, -1, 1);
    $sec = intval(substr($ap, -3, 2));
    $min = intval(substr($ap, strpos($ap, ".") - 2, 2));
    $hr = intval(substr($ap, 0, strpos($ap, ".") - 2));

    $latd = $hr;
    $latm = $min + $sec / 60;

    $z = $latd + ($latm / 60);

    if (strlen($ap) == 9 && $dir == "W") {
        return ("-" . round($z, 5));
    }
    return (round($z, 5));
}