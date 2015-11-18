<?php

/*
 * APRS_Message.php : phpAPRS Message Packet
 * Matthew Asham, VE7UDP <matthewa@bcwireless.net>
 * 
 * This file is part of phpAPRS.
 * 
 * phpAPRS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * 
 * phpAPRS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with phpAPRS.  If not, see <http://www.gnu.org/licenses/>.
 * 
 */

namespace dbursem\phpaprs\packets;
use dbursem\phpaprs;

class APRS_Message extends phpaprs\APRS_BasePacket
{
    private $_msg;
    private $_dest;

    function __construct($msg, $dest = "NOBODY")
    {
        parent::__construct();
        $this->_msg = $msg;
        $this->_dest = $dest;
        $this->setCode(":");
        $this->setMaximumTransmissions(5);
        $this->setRetryInterval(40);
        $this->setAckCode(rand(0, 999));

    }

    function constructPacket()
    {
///    $this->_ack = rand(0,999);
        $msg = $this->_msg;
//    $msg=str_replace(":","-",$this->_msg);
        $msg = str_replace("|", " ", $msg);
        $msg = str_replace("~", "-", $msg);
        $ret = sprintf("%-9s", $this->_dest);
        $ret .= ":" . $msg;//."{".$this->_ack;
//    $msg.="{".$this->_ack;
        return ($ret);
    }

    static function parsePacket($header)
    {
        $dest = trim(substr($header['aprsdat'], 0, strpos($header['aprsdat'], ":")));
        $msg = trim(substr($header['aprsdat'], strpos($header['aprsdat'], ":") + 1));

        if (strpos($msg, "{") !== false) {
//      $this->debug( "Ack code is $ack");
            $msg = substr($msg, 0, strpos($msg, "{"));
            $ack = substr($msg, strpos($msg, "{") + 1);

            return array(
                "frame" => $header,
                "txtdest" => $dest,
                "ack" => $ack,
                "msg" => $msg,
            );
        }
        return array(
            "frame" => $header,
            "txtdest" => $dest,
            "msg" => $msg,
        );
    }

}