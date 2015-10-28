<?php
function aprsbot_handleposition($hdr,$line)
{

    $dat=$hdr['aprsdat'];
    $lat = substr($dat,0,7);
    $long = substr($dat,9,9);
    $symbol = substr($dat,25,1);
    $symtable = substr($dat,6,1);

}
