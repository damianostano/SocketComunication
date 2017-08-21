<?php
/**
 * Created by PhpStorm.
 * User: Sisas
 * Date: 02/02/2017
 * Time: 11:40
 */
include_once('../Autoload.php');

$ip = "192.168.1.197";
$port = "6080";



$ka = Cmd::getKeepAlive("0101", "id_dispo");
print_r($ka);
$arr[$ka->getId()] = $ka;
print_r($arr);

die("\ninterruzione manuale!");

$mt = microtime(true);
sleep(1);
$ts = microtime(true);
$differenza = ($ts-$mt);
print $ts." - ".$mt." = ".$differenza."\n";
$differenza<2? print "<2: OK\n":print "<2: NO\n";
$differenza>2? print ">2: OK\n":print ">2: NO\n";


die("\ninterruzione manuale!");
print str_pad("12", 4, "0", STR_PAD_LEFT);


ob_implicit_flush(); //Implicit flushing will result in a flush operation after every output call (echo, print, ecc...)
$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("socket_create() fallito: motivo: " . socket_strerror(socket_last_error($this->sock))."\n");
$ret = socket_bind($sock, $ip, $port) or die("socket_bind() fallito: motivo: " . socket_strerror($ret) . "\n");
$ret = socket_listen($sock, 10) or die("socket_listen() fallito: motivo: " . socket_strerror($ret) . "\n");
$ret = socket_set_nonblock($sock) or die("socket_set_nonblock() fallito: motivo: " . socket_strerror($ret) . "\n");

$dispositivo;
$read=array($sock);
$write=array();
$except=array();



$result = socket_select($read, $write, $except, 10);
if($buf = socket_accept($sock)) {
    $sso = socket_set_option($buf, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 2, 'usec' => 100));
    socket_set_nonblock($buf);
    $dispositivo = $buf;
}
while (isset($sock)) {
    $msg = socketRead($dispositivo, 10);
    print_r($msg);
    socket_write($dispositivo, $msg["msg"], strlen($msg["msg"]));
    usleep(2000000);
}
function socketRead(&$sock){
    $ret = "";
    $to  = "ok";
    for($i=1; ;$i++){
        print $i."\n";

        if(false===($tmp = socket_read($sock, 1))){
            $to = "to";
            break;
        }elseif($tmp==="\r" || $tmp==="\n"){
            $to = "ok";
            break;
        }else{
            $ret .= $tmp;
        }
    }
    return array("msg"=>$ret, "state"=>$to);
}
//function socketRead(&$sock, $limit=4096){
//    $ret = "";
//    $to  = "ok";
//    for($i=1; ;$i++){
////        print $i."\n";
//        if(false===($tmp = socket_read($sock, 1))){
//            $to = "to";
//            break;
//        }elseif($tmp==="\r" || $tmp==="\n"){
//            $to = "ok";
//            break;
//        }elseif($i>=$limit){
//            $ret .= $tmp;
//            $to = "lt";
//            break;
//        }else{
//            $ret .= $tmp;
//        }
//    }
//    return array("msg"=>$ret, "state"=>$to);
//}

function aggiungi(){
    if($buf = @socket_accept($this->sock)){
        $sso = socket_set_option($buf, SOL_SOCKET, SO_RCVTIMEO, array('sec'=>TO_HANDSHAKE,'usec'=>100));
        socket_set_nonblock($buf);
        $login_msg = $this->decode_connection_msg($this->leggi($buf));
        if($login_msg["type"]===ERR){
            return false;
        }elseif($login_msg["type"]===USER){
            $user = $login_msg["val"];
            $this->user[$user] = $buf;
            if($this->scrivi_a_user($user, "Benvenuto")===false){
                return false;
            }
        }elseif($login_msg["type"]===DISP){
            $id_dispositivo = $login_msg["val"];
            $this->dispositivi[$id_dispositivo] = $buf;
        }
        return true;
    }
}