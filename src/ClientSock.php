<?php


class ClientSock{

    public $log = null;
    public $indirizzo = null;
    public $porta = null;
    public $sock = null;
    public $user = null;

    public function __construct($indirizzo, $porta, MapDispoLogic $mapLogic, String $user){
        if(!isset($this->log)){
            $this->log = Logger::getLogger("monitor.trace");
        }
//        $this->decode = new DecodeSimple();
        $this->logic = $mapLogic;//new CompactLogic();                  //      ! ! ! !
        $this->indirizzo = $indirizzo;
        $this->porta = $porta;
        $this->user = $user;
    }

    function login(){
        set_time_limit(0);
        ob_implicit_flush(); //Implicit flushing will result in a flush operation after every output call (echo, print, ecc...)
        $this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if($this->sock === false){
            throw new Exception("socket_create() fallito... motivo: ".socket_strerror(socket_last_error($this->sock))."\n");
//            die("socket_create() fallito... motivo: " . socket_strerror(socket_last_error($this->sock))."\n");
        }
        $result = socket_connect($this->sock, $this->indirizzo, $this->porta);
        if($result === false){
            throw new Exception("socket_connect() fallito... motivo: ".socket_strerror(socket_last_error($this->sock))."\n");
//            die("socket_connect() fallito: motivo: " . socket_strerror(socket_last_error($this->sock))."\n");
        }
//        $ret = socket_set_nonblock($this->sock) or die("socket_set_nonblock() fallito: motivo: " . socket_strerror($ret) . "\n");

        $this->scrivi("login*user&&".$this->user."\r");
        return $this->leggi();
    }
    function logout(){
        socket_close($this->sock);
    }

    function leggi(){
        $msg = $this::socketRead($this->sock);
        if($msg['state']!="ok"){
            throw new Exception('socketRead() è andata in stato TO!');
        }
        return $msg['msg'];
    }
    function scrivi($mex){
        return @socket_write($this->sock, $mex, strlen($mex));
    }

    //lettura
    static function socketRead(&$sock){
        $ret = "";
        $to  = "ok";
        for($i=1; ;$i++){
//            print $i."\n";
            if(false===($tmp = socket_read($sock, 1))){
                $to = "to";
                break;
            }elseif($tmp==="\r"){
                $to = "ok";
                break;
//            }elseif($i>=$limit){
//                $ret .= $tmp;
//                $to = "lt";
//                break;
            }elseif($tmp==="\n"){
                $ret .= $tmp;
            }else{
                $ret .= $tmp;
            }
        }
        return array("msg"=>$ret, "state"=>$to);
    }
}

/*
 * login*user&&root#013
 * quit@@server#013
 * help@@1234#013 -> 0001@@help#013
 *
 * login*1234#013
 * 0001@@resp-help#013
 * 0003@@-#013
 */



