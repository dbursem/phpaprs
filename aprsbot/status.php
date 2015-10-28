<?php

function aprsbot_handlestatus($hdr,$line)
{
	$dat=$hdr['aprsdat'];
 	$time = substr($dat,1,7);
 	$lat = substr($dat,7,8);
 	$long = substr($dat,16,9);
 	$symbol = substr($dat,26,1);

	$lat = aprs2dec($lat);
	$long = aprs2dec($long);	

	// 
}
