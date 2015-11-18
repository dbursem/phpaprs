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

namespace dbursem\phpaprs\packets;
use dbursem\phpaprs;

class APRS_Position extends phpaprs\APRS_BasePacket implements phpaprs\APRS_Packet {

  private $_msg;
  
  function __construct($lat="",$long="",$msg="")
  {
    parent::__construct();
    $this->setLatitude($lat);
    $this->setLongitude($long);
    $this->_msg = $msg;
    
  }

  function constructPacket()
  {
    $ret="";
    switch($this->_code){
      case('=');
      case('!');
        $ret=$this->getLatitude().$this->getSymbolTable().$this->getLongitude().$this->getIcon();
        if($this->_msg!="")
          $ret.=" ".$this->_msg;
      break;
      
      case('/');

      $ret=$this->generateDHMTimeStampLocal().$this->getLatitude().$this->getSymbolTable().$this->getLongitude().$this->getIcon();
        if($this->_msg!="")
          $ret.=" ".$this->_msg;
     break;
        
      case('@');


      $ret=$this->generateDHMTimeStampZulu().$this->getLatitude().$this->getSymbolTable().$this->getLongitude().$this->getIcon();
        if($this->_msg!="")
          $ret.=" ".$this->_msg;
      break;
    }
    return($ret);
  }
  
}
