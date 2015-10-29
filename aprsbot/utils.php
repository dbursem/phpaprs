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
    $min_frac = intval(substr($ap, -3, 2)); // APRS uses [hours][minutes].[hundreths_of_minutes], not seconds.
    $min = intval(substr($ap, strpos($ap, ".") - 2, 2));
    $deg = intval(substr($ap, 0, strpos($ap, ".") - 2)); // Angles are in degrees, not hours

    $latd = $deg;
    $latm = $min + $min_frac / 100; // APRS uses [hours][minutes].[hundreths_of_minutes], not seconds.

    $z = $latd + ($latm / 60);

    if (strlen($ap) == 9 && $dir == "W") {
        return ("-" . round($z, 5));
    }
    return (round($z, 5));
}