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


/*

For reference only - not used

not complete either

define("APRSCODE_MICE",0x1c);
define("APRSCODE_MICE_OLD",0x1d);
define("APRSCODE_POSITION",'!');
define("APRSCODE_WX_PEETBROSUII",'#');
define("APRSCODE_GPS",'$');

define("APRSCODE_ITEM",')');
define("APRSCODE_TEST",',');
define("APRSCODE_POSITION_TS",'/');
define("APRSCODE_MESSAGE",":");
define("ARPSCODE_OBJECT",";");
define("APRSCODE_CAPABILITIES","<");
define("APRSCODE_POSITION_NOTS","=");
define("ARPSCODE_STATUS",">");
define("APRSCODE_QUERY",'?');
define("APRSCODE_TELEMETRY","T");
define("APRSCODE_USERDATA","{");
define("APRSCODE_THIRDPARTY","}");
*/


interface BCWNS_APRS_Packet {
    public function getCode();
    public function setCode($c);
    public function getCallsign();
    public function setCallsign($c);
    public function constructPacket();
}


require "class.bcwns.aprs.basepacket.php";
require "packets/class.bcwns.aprs.item.php";
require "packets/class.bcwns.aprs.message.php";
require "packets/class.bcwns.aprs.position.php";


class BCWNS_APRS {

    private $callsign;
    private $passcode;

    private $socket;
    private $_connected;	// true or false
    private $_conn_delay;	// connection try delays
    private $_lastconn_attempt;	// last connection attempt

    private $_timeout;

    private $_inputdat;
    private $_inputlen;

    private $_outbuffer;

    private $_version;
    public $_debug;

    private $_maxtransmit;
    private $server;
    private $port;
    private $callbacks;

    function __construct()
    {

        $this->_timeout = 5;
        $this->_codes = array(
            0x1c=>"APRSCODE_MICE",
            0x1d=>"APRSCODE_MICE_OLD",
            '!'=>"APRSCODE_POSITION",
            ':'=>"APRSCODE_MESSAGE",
            "/"=>"APRSCODE_STATUS",
        );

        $this->_version="0.01a_dbu";

        $this->_maxtransmit = 5;	// transmit a maximum of 5 times
        $this->_lastconn_attempt=1;
        $this->_conn_delay=5;
    }

    function connect($host,$port,$callsign,$passcode=FALSE,$filter = '')
    {
        if ($this->_debug)
        {
            echo "trying to connect...";
        }

        if( $this->_connected == true ){
            $this->debug("Already connected!");
            return false;
        }

        $conn_interval = time() - $this->_lastconn_attempt;

        if( $conn_interval < $this->_conn_delay){
            $this->debug("Last connection attempt was $conn_interval seconds ago, waiting..");
            return(FALSE);
        }

        $this->callsign = $callsign;

        if(empty($passcode))
        {
            $this->passcode = $this->MakePassCode($this->callsign);
            $this->debug('No passcode specified, using auto generated code: ' . $this->passcode);
        }
        else {
            $this->passcode = $passcode;
            $this->debug("Custom passcode used: " . $passcode);
        }

        $this->server = $host;
        $this->port = $port;

        $this->socket = socket_create(AF_INET,SOCK_STREAM,getprotobyname("tcp"));//stream_socket_client("tcp://$host:$port",$errno,$errstr,$this->_timeout);

        $this->_lastconn_attempt = time();
        $res=socket_connect($this->socket,$host,$port);

        if($res==FALSE){
            socket_close($this->socket);
            $this->debug( "Connection failed: $host: $port : " . socket_strerror(socket_last_error()));
            return(FALSE);
        }

        $this->_connected = true;
        if (!empty($filter)) {
            $filter = ' filter ' . $filter;
        }
        $this->_send("user ".$this->callsign." pass ".$this->passcode." vers BCWNS_APRS_Cli ".$this->_version . $filter."\n");

        return(TRUE);
    }

    // marks the connection as disconnected
    function _disconnect()
    {
        socket_shutdown($this->socket,2);
        socket_close($this->socket);
        $this->_connected = false;
    }

    function _send($data)
    {
        $res=socket_send($this->socket,$data,strlen($data),0);
        if($res<=0)
        {
            $this->debug("socket send returned $res");
            //echo "Socket send returned $res\n";
            $this->_disconnect();
        }
        else
        {
            $this->debug("sent ($res): $data");
        }
        return($res);
    }

    function sendPacket($packetObject,$path="BCWNS",$do=TRUE)
    {
       if(!is_object($packetObject))
       {
            $this->debug("sendPacket: packetObject isn't an object");
            return(FALSE);
       }

        if(!$packetObject instanceof BCWNS_APRS_BasePacket)
        {
            $this->debug("sendPacket: packetObject is not an instance of BCWNS_APRS_BasePacket");
            return(FALSE);
        }
        $packet = $packetObject->constructPacket();
        if($packet=="")
        {
            $this->debug("sendPacket: packet construction returned nothing on packetObject");
            return(FALSE);
        }

        $code = $packetObject->getCode();

        if( $code == "" )
        {
            $this->debug("sendPacket: packetObject does not have an aprs code");
            return(FALSE);
        }

        $tpath = $packetObject->getPath();
        if($tpath != FALSE)
        {
            $path = $tpath;
        }

        $this->_outbuffer[] = array(
            "from"=>$packetObject->getCallsign(),
            "data"=>$packetObject->constructPacket(),
            "path"=>$path,
            "code"=>$code,
            "stime"=>time(),
            "send"=>$do,
            "maxt"=>$packetObject->getMaximumTransmissions(),
            "txcount"=>0,
            "retintval"=>$packetObject->getRetryInterval(),
            "txack"=>$packetObject->getAckCode(),
            "obj"=>&$packetObject
        );
        //$this->debug(print_r($this->_outbuffer,TRUE));
        return false;
    }


    function getOutQueueLen()
    {
        return count($this->_outbuffer);
    }

    function _processOut()
    {
        $this->debug("in processout");

        if(empty($this->_outbuffer)) {
            return (FALSE);
        }

        foreach($this->_outbuffer as $idx=>$arr)
        {
            if($arr['txcount'] >= $arr['maxt'] || $arr['obj']->isAcked() == TRUE)
            {
                // maximum transmissions exceeded, or packet was acknowledged
                $this->debug("Remove $idx from outbuffer");
                unset($this->_outbuffer[$idx]);
                continue;
            }

            if (!isset($arr['txtime'])){
                $arr['txtime'] = 0;
            }
            // if the interval time has elapsed, or if the packet has not yet been sent - send it.
            if( (time() - $arr['txtime']) > $arr['retintval'] || $arr['txtime']==0 )
            {
                $this->debug("Process send $idx");

                $pkt = $arr['from'] . ">". $arr['path'] . ":" . $arr['code'] . $arr['data'];

                if($arr['txack']!="")
                    $pkt .= "{" .$arr['txack'];

                $pkt.="\n";

                if($arr['send']==TRUE)
                {
                    $this->_send($pkt);
                }
                else
                {
                    $this->debug("Debug send (not sending): $pkt");
                }
                $this->_outbuffer[$idx]['txtime'] = time();
                $this->_outbuffer[$idx]['txcount']++;
            }
            else
            {
                $this->debug("ignore $idx\n");
            }
        }
    }

    function _processMessageAck($header)
    {
        // processes acks..
        $myHeader = BCWNS_APRS_Message::parsePacket($header);

//        if($header['src'] == 'VE7UDP-4') {
//            $this->debug("in processmessageack: " . print_r($myHeader, TRUE));
//        }

        if(empty($this->_outbuffer)) {
            return;
        }

        foreach($this->_outbuffer as $idx=>$arr)
        {
            if($arr['obj']->getCallsign() == $myHeader['txtdest']
                && $myHeader['ack'] == ""
                && $myHeader['msg']=="ack".$arr['obj']->getAckCode())
            {
                $this->debug("Msg $idx recv ack ");
                $arr['obj']->setAcked();
            }
        }
        return;
    }

    function ioloop()
    {
        $read[] = $this->socket;

        if($this->_connected==false)
        {
            $this->debug("Connection closed, trying to re-connect...");

            if($this->connect($this->server,$this->port,$this->callsign,$this->passcode)==FALSE)
            {
                $this->debug("Re-connection attempt failed");
                return(FALSE);
            };
            $this->debug("Reconnected!");
        }

        $this->debug("before select");

        $w = null;
        $e = null;
        $res = socket_select($read, $w, $e,0);
        if( $res === FALSE )
        {
            $this->debug( "select error: " . socket_strerror(socket_last_error()));
            return(FALSE);
        }
        elseif($res===0){
            // no messages
            $this->debug("no messages");
            $this->_processOut();
            return(FALSE);
        }

        $res = socket_recv($this->socket,$buf,8096,0);
        if( $res === FALSE )
        {
            $this->debug( "Receive error: " . socket_strerror(socket_last_error()));
            return(FALSE);
        }
        elseif($res===0){
            $this->_disconnect();
            $this->debug( "Read 0 after select");
            return true;
        }

        $this->_inputlen +=$res;
        $this->_inputdat.=$buf;
        $this->process();
        $this->_processOut();

        return true;
    }

    function process()
    {
        $offset = strrpos($this->_inputdat,"\n");

        $segments=substr($this->_inputdat,0,$offset);

        $this->_inputdat = substr($this->_inputdat,$offset+1);

        $this->_inputlen -= strlen($segments);

        $segments_array=explode("\n",$segments);

        foreach($segments_array as $line)
        {
            // do something about $Line
            $this->debug("Process: $line\n");

            if($line[0]=='#'){
                $this->debug( "Server comment: $line");
                continue;
            }

            $header = $this->parseHeader($line);
            //$this->debug(print_r($header));
            if( $header == FALSE )
            {
                $this->debug( "Header decode fail");
                continue;
            }

//            if( $header['src'] == 'VE7UDP-4' )
//            {
//                $this->debug("Inbound message code: ".$header['code']." from ".$header['src']);
//            }
            if($header['code'] == ':')
            {
                $this->_processMessageAck($header);
            }

            //execute callback functions for this path
            if(isset($this->callbacks[$header['code']][$header['path'][0]]))
            {
                $func = $this->callbacks[$header['code']][$header['path'][0]];
                if($func != "")
                {
                    $this->debug('going to call '. $func);
                    $res = $func($header,$line);
                    if ($res != FALSE)
                    {
                        // sent it
                    }
                }
            }
            //execute generic callback functions
            if(isset($this->callbacks[$header['code']]['*']))
            {
                $func = $this->callbacks[$header['code']]['*'];
                if($func != "")
                {
                    $this->debug('going to call '. $func);
                    $res = $func($header,$line);
                    if ($res != FALSE)
                    {
                        // sent it
                    }
                }
            }
        }

        return;
    }

    function parseHeader($data)
    {
        if($data[0]=='#'){
            return(FALSE);
        }
        $header['src']= substr($data,0,strpos($data,">"));
        $header['path_full']=substr($data,strpos($data,">")+1,strpos($data,":")-strlen($header['src'])-1);
        $header['path']=explode(",",$header['path_full']);
        $header['code'] = substr($data,strpos($data,":")+1,1);
        $header['aprsdat'] = substr($data,strpos($data,":")+2);
        return($header);

    }

    function addCallback($code,$dest,$func)
    {
        if(strlen($code)>1)
        {
            foreach($this->_codes as $c=>$name)
            {
                if($name==$code)
                    $code=$c;
            }
        }
        if(strlen($code)>1)
        {
            $this->debug( "Unknown named code: $code");
            return(FALSE);
        }
        $this->callbacks[$code][$dest]=$func;
    }

    function debug($str)
    {
        if($this->_debug==TRUE) {
            echo "debug: $str\n";
        }
    }


    function MakePassCode($callsign)
    {
        if(strpos($callsign,'-')!==FALSE) {
            $localCallsign = strtoupper(substr($callsign,0,strpos($callsign,'-')));
        }
        else {
            $localCallsign = strtoupper($callsign);
        }

        $len = strlen($localCallsign);
        $i2=0;
        $hash = 0x73e2;
        while($i2<$len)
        {
            $hash ^= ord($localCallsign[$i2++]) << 8;
            $hash ^= ord($localCallsign[$i2++]);
        }
        return($hash &  0x7fff);
    }
}