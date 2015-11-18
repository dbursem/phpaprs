<?php

use dbursem\phpaprs\packets;

    function handlemessage($hdr, $line)
    {
        global $aprs;

        // XXX:  This stuff should be handled in the packet class not here.
        $dest = trim(substr($hdr['aprsdat'], 0, strpos($hdr['aprsdat'], ":")));
        $msg = trim(substr($hdr['aprsdat'], strpos($hdr['aprsdat'], ":") + 1));

        if (strpos($msg, "{") !== FALSE)
        {
            $ackcode = substr($msg, strpos($msg, "{") + 1);
            //decho ("Ack code is $ackcode");
            $msg = substr($msg, 0, strpos($msg, "{"));
            $m = new packets\APRS_Message("ack" . $ackcode, $hdr['src']);//$line);
            $m->setCallsign(MYCALL);
            $m->setAckCode("");   // don't tag an ack as requiring an ack.
            $m->setMaximumTransmissions(1);
            $aprs->sendPacket($m);


        }
        if (substr($msg, 0, 3) == "ack" && strpos($msg, " ") == FALSE)
        {
//    echo ("Ignore ack: $msg");
            return;
        }
        // end of stuff that doesnt belong here.

        echo "dest is $dest\n";
        // Ignore messages that aren't intended for me
        if (strtoupper($dest) != MYCALL)
        {
            return;
        }

        $argv = explode(" ", $msg);
        echo $hdr['src'] . ">" . $hdr['path_full'] . " $msg\n";

        switch (strtolower($argv[0]))
        {
            case('help');
                txtmsg($aprs, "i am beyond help", $hdr['src']);
                break;

            case('date');
            case('time');
                txtmsg($aprs, "the current date is: " . date('r'), $hdr['src']);
                break;

            case('path');
                txtmsg($aprs, "your path was: " . $hdr['path_full'], $hdr['src']);
                break;

            case('ping');
                txtmsg($aprs, "pong", $hdr['src']);
                break;

            default;
                txtmsg($aprs, "i know not what you mean", $hdr['src']);
        }

        return TRUE;
    }
