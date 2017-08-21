<?php

/**
 * Created by PhpStorm.
 * User: Client
 * Date: 10/01/2017
 * Time: 11:11
 */
//include_once('KeepAliveSingle.php');
//include_once('Cmd.php');

//define ('CMD', 0);
//define ('ID_DISPO', 1);
//
//define ('ID_MSG', 0);
//define ('RESPONSE', 1);

class User{

    public $logic = null;


    public $log = null;
    public $indirizzo = "";
    public $porta = "";
    public $sock = "";



    //costruttore
    public function __construct($indirizzo, $porta){
        if(!isset($this->log)){
            $this->log = Logger::getLogger("monitor.trace");
        }
        $this->decode = new DecodeSimple();
        $this->logic = new CompactLogic();
        $this->indirizzo = $indirizzo;
        $this->porta = $porta;
        $this->log->info("*****************************************\nAvvio server in corso...");

        set_time_limit(0);
        ob_implicit_flush(); //Implicit flushing will result in a flush operation after every output call (echo, print, ecc...)
        $this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("socket_create() fallito: motivo: " . socket_strerror(socket_last_error($this->sock))."\n");
        $ret = socket_bind($this->sock, $this->indirizzo, $this->porta) or die("socket_bind() fallito: motivo: " . socket_strerror($ret) . "\n");
        $ret = socket_listen($this->sock, MAX_QUEUE) or die("socket_listen() fallito: motivo: " . socket_strerror($ret) . "\n");
        $ret = socket_set_nonblock($this->sock) or die("socket_set_nonblock() fallito: motivo: " . socket_strerror($ret) . "\n");
        $this->user[Cmd::$SERVER] = $this->sock;
        $this->avvia();
    }

    //distruttore
    public function __destruct(){
        $this->log->debug("-----function: __destruct()");
        @socket_close($this->sock);
        $this->log->info("__destruct: socket_close eseguita");
        $count = count($this->dispositivi);
        foreach($this->dispositivi as $key=>$value) {
            $this->disconnetti($key);
        }
        $this->log->info("__destruct: disconnessi $count dispositivi");
        $count = count($this->user);
        foreach($this->user as $key=>$value) {
            $this->disconnetti_user($key);
        }
        $this->log->info("__destruct: disconnessi $count utenti");
    }


    //ciclo principale
    public function avvia(){
        $this->log->info("server ".$this->indirizzo.":".$this->porta." avviato");
        $write = null;
        $except = null;
        while(isset($this->sock)) {
            //controllo cambiamenti
            $read = array_merge(array(), $this->user);
            $n = socket_select($read, $write, $except, 0);//controlla se i sock negli array dati hanno cambiamenti, toglie tutti quelli che non li hanno avuti
            $this->log->trace("socket_select eseguita");
            $this->log->trace("read: " . print_r($read, true));
//            $this->log->trace($this->__toString());
            //se ci sono da aggiungere dispositivi o user
//            print(" count(Read): ".count($read));
            if (($key = array_search($this->sock, $read)) !== false) {//se $this->sock è ancora presente ha avuto richieste
                unset($read[$key]);//quindi dato che le tratterò lo devo togliere a mano dall'array
                $this->aggiungi(); //aggiungo chi ha fatto richiesta, l'array read adesso contiene solo le richieste degli user
            }
            //ricevere comandi degli user
            if (count($read)>0) {
                print("ricevuto comando da user\n");
                $this->receiveCmd($read);
            }
//    $this->__statoDispoUser();
            $this->handlerCmd();
            usleep(SLEEP);
            $this->state++;
            if ($this->state%(FRAZ_SEC*5) === 0) {
                $this->log->debug("from start " . $this->getSec()." sec");
            }
            if($this->state%FRAZ_SEC===0){
//                $this->log->trace($this->__toString());
                if(count($this->execCmd)>SOGLIA_CMD_EXEC){
                    $this->log->warn("Attenzione! Ci sono + di ".SOGLIA_CMD_EXEC." comandi in esecuzione!");
                }
            }

        }
        $this->log->info("Server arrestato");
        $this->__destruct();
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
    private function leggi($sock){
        return socket_read($sock, MAX_CHARS_READ, PHP_NORMAL_READ);
    }

    //scrittura
    private function scrivi($sock, $mex){
        return @socket_write($sock, $mex, strlen ($mex));
    }


    //disconnessione cliente
    private function disconnetti($key){
        $this->log->debug("disconnetti($key)");
        if(@socket_close($this->dispositivi[$key])===false) {
            $this->log->error("Il dispositivo non è stato disconnesso correttamente...problema in fase di socket_close per dispositivo $key:\n".socket_strerror(socket_last_error($this->sock)));
            return false;
        }
        unset($this->dispositivi[$key]);
        unset($this->lastPing[$key]);
        unset($this->lastPong[$key]);
        $this->log->info("Dispositivo $key disconnesso");
        return true;
    }

    private function disconnetti_user($key){
        $this->log->debug("disconnetti_user($key)");
        if(@socket_close($this->user[$key])===false) {
            $this->log->error("Lo user non è stato disconnesso correttamente...problema in fase di socket_close per User $key:\n".socket_strerror(socket_last_error($this->sock)));
            return false;
        }
        unset($this->user[$key]);
        $this->log->info("User $key disconnesso");
        return true;
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



