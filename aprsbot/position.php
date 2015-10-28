<?php
function aprsbot_handleposition($header,$line)
{


	$data=$header['aprsdat'];

	$time_h = substr($data,0,2);
	$time_m = substr($data,2,2);
	$time_s = substr($data,4,2);

    $datetime = new DateTime('now',new DateTimeZone(GMT));
    $datetime->setTime($time_h,$time_m,$time_s);

    $lat = substr($data,7,8);
	$long = substr($data,16,9);
	//$symbol = substr($data,25,1);
    //$symtable = substr($data,6,1);
    $offset = strpos($data,'/A=');
    $altitude = substr($data,$offset+3,6);

    $params = [
        $header['src'],
        $datetime->format('Y-m-d H:i:s'),
        $lat,
        $long,
        $altitude,
    ];

}

