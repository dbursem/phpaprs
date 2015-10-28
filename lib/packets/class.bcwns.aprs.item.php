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

class BCWNS_APRS_Item extends BCWNS_APRS_BasePacket
{
    private $label;
    private $symbol;
    private $live;

    function __construct($lat, $long, $label, $symbol, $status)
    {
        parent::__construct();
        $this->setLatitude($lat);
        $this->setLongitude($long);
        $this->_label = $label;
        $this->_symbol = $symbol;
        $this->_status = $status;
        $this->setLive();
        $this->setCode(")");
    }

    function constructPacket()
    {
        $ret = substr($this->_label, 0, 9);
        if ($this->live == TRUE)
            $ret .= "!";
        else
            $ret .= "_";
        $ret .= $this->getLatitude() . substr($this->_symbol, 0, 1) . $this->getLongitude() . substr($this->_symbol, 1, 1);
        $ret .= $this->_status;
        return ($ret);
    }

    function setKilled()
    {
        $this->live = FALSE;
    }

    function setLive()
    {
        $this->live = TRUE;
    }
}