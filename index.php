<?php
/**
 * Created by PhpStorm.
 * User: Client
 * Date: 10/01/2017
 * Time: 10:04
 */
error_reporting(E_ALL);
include_once('include.php');

$map = new MapDispoLogic($map_cmd);
$master = new Master("192.168.1.197","6080", $map);

//$cl = new CompactLogic();
//print $cl->cmd("||jw")->buildStringCmd("3");

//cd ..\..\xampp\htdocs\PhpStormProject\ServerChat
//C:\xampp\PHP\php.exe -f "C:\xampp\htdocs\PhpStormProject\ServerChat\server.php"
//(telnet) open 185.20.66.134 6080

//192.168.1.60

