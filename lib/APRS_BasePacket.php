<?php

/* Matthew Asham, VE7UDP <matthewa@bcwireless.net>
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
 */

namespace dbursem\phpaprs;

class APRS_BasePacket
{
    protected $_code;
    protected $_callsign;
    protected $_latitude;
    protected $_longitude;
    protected $_maxt;    // maximum transmissions
    protected $_ack;
    protected $_acked;
    protected $_symbol;
    protected $_path;
    protected $_retryInterval;

    function __construct()
    {
        // parent::__construct() needs to be called from any sub classes prior to setting any paramters,
        // lest this constructor overwrite any transission settings

        $this->setMaximumTransmissions(1);    // by default we only send the packet once
        $this->setRetryInterval(40);        // retry unack'd packets after 40 seconds
    }

    function setAcked()
    {
        $this->_acked = TRUE;
    }

    function isAcked()
    {
        if ($this->_acked == TRUE)
            return (TRUE);
        return (FALSE);
    }

    function getAckCode()
    {
        return ($this->_ack);
    }

    function setAckCode($code)
    {
        $this->_ack = $code;
    }

    function getMaximumTransmissions()
    {
        return ($this->_maxt);
    }

    function setMaximumTransmissions($maxt)
    {
        $this->_maxt = $maxt;
    }

    function setRetryInterval($int)
    {
        $this->_retryInterval = $int;

    }

    function getRetryInterval()
    {
        return ($this->_retryInterval);
    }


    function getCode()
    {
        return ($this->_code);
    }

    function getCallsign()
    {
        return ($this->_callsign);
    }

    function setCode($c)
    {
        $this->_code = $c;
    }

    function setCallsign($call)
    {
        $this->_callsign = strtoupper($call);
    }


    function getLatitude()
    {
        return ($this->_latitude);
    }

    function setLatitude($lat)
    {
        $this->_latitude = $lat;
    }


    function getLongitude()
    {
        return ($this->_longitude);
    }

    function setLongitude($long)
    {
        $this->_longitude = $long;
    }


    function setDecimalCoordinates($lat, $long)
    {
        $this->_latitude = $this->_dec2aprs($lat) . "N";
        $this->_longitude = $this->_dec2aprs($long) . "W";
        echo $this->_latitude . "N " . $this->_longitude;
    }

    function _dec2aprs($dec)
    {
        $dec = str_replace("-", "", $dec);
        $vars = explode(".", $dec);
        $deg = $vars[0];
        $tempma = "0." . $vars[1];
        $tempma = $tempma * 3600;
        $min = floor($tempma / 60);
        $sec = 100 / (60 / ($tempma - ($min * 60)));

        $ret = sprintf("%d%02d.%02d", $deg, $min, $sec);
        return ($ret);

    }

    function getIcon()
    {
        return (substr($this->_symbol, 1));
    }

    function getSymbolTable()
    {
        return (substr($this->_symbol, 0, 1));
    }

    function setSymbol($table, $icon)
    {
        $this->_symbol = $table . $icon;
    }

    function getTimeDHM($time = 0)
    {
        if ($time == 0) {
            $time = time();
        }

        return (date('dHi', $time));
    }

    function getTimeHMS($time = 0)
    {
        if ($time == 0) {
            $time = time();
        }

        return (date('His', $time));
    }

    function getTimeMDHM($time = 0)
    {
        if ($time == 0) {
            $time = time();
//      return(date('
        }

        return (date('mdHi', $time));
    }

    function _getDateTimeZ()
    {
        return date_create("now", new DateTimeZone("GMT"));
    }

    function debug($str)
    {
        echo "pkt debug: [" . $this->getCode() . "] : " . $str . "\n";
        //$this->constructPacket()."\n";
    }

    function getZuluTime()
    {
        return (time() - date('Z'));
    }

    // zulu
    function generateDHMTimeStampZulu()
    {
        $time = $this->getZuluTime();
        $ret = date("dHi", $time) . "z";
        return ($ret);

    }

    function generateDHMTimeStampLocal()
    {
        $ret = date("dHi", time()) . "/";
        return ($ret);

    }

    function setPath($path)
    {
        $this->_path = $path;
    }

    function getPath()
    {
        return ($this->_path);
    }
}