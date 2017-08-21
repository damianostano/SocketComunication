<?php

/**
 * Created by PhpStorm.
 * User: Client
 * Date: 10/01/2017
 * Time: 11:11
 */
include_once('KeepAliveSingle.php');

class Master{
    public $state = 0;

    /**
     * @return int
     */
    public function getSec(): float{
        return $this->state/5;
    }


    public $log = null;
    public $keepalive = null;
    //proprieta private
    public $indirizzo = "";
    public $porta = "";
    public $sock = "";

    public $user = array();
    public $dispositivi = array();
    public $checkCons = array();
    public $nTry = array();     //x ogni client segna il numero di volte in cui si è provato l'handshake ed è fallito

    public $cmd = array("help"=>"help", "quit"=>"quit");

    public function __toString(){
        return "Master: ".$this->indirizzo.":".$this->porta."\nDispositivi: ".print_r($this->dispositivi, true)." Sock: ".print_r($this->sock, true)." CheckCons: ".print_r($this->checkCons, true)." N_Try: ".print_r($this->nTry, true)." Stato: ".$this->getSec()."\n";
    }

    //costruttore
    public function __construct($indirizzo, $porta){
        if(!isset($this->log)){
            $this->log = Logger::getLogger("monitor.trace");
        }
        $this->indirizzo = $indirizzo;
        $this->porta = $porta;
        $this->log->info("avvio server in corso...");

        set_time_limit(0);
        ob_implicit_flush(); //Implicit flushing will result in a flush operation after every output call (echo, print, ecc...)
        $this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("socket_create() fallito: motivo: " . socket_strerror(socket_last_error($this->sock))."\n");
        $ret = socket_bind($this->sock, $this->indirizzo, $this->porta) or die("socket_bind() fallito: motivo: " . socket_strerror($ret) . "\n");
        $ret = socket_listen($this->sock, MAX_QUEUE) or die("socket_listen() fallito: motivo: " . socket_strerror($ret) . "\n");
        $ret = socket_set_nonblock($this->sock) or die("socket_set_nonblock() fallito: motivo: " . socket_strerror($ret) . "\n");
        $this->avvia();
    }

    //distruttore
    public function __destruct(){
        $this->log->debug("-----function: __destruct()");
        @socket_close($this->sock);
        $this->log->debug("__destruct: socket_close eseguita");
        foreach($this->dispositivi as $key=>$value) {
            $this->disconnetti($key);
        }
    }

    //metodi privati

    //ciclo principale
    public function avvia(){
        $this->log->info("server ".$this->indirizzo.":".$this->porta." avviato");
        $write = null;
        $except = null;
        while(true) {
            //controllo cambiamenti
            $read = array_merge(array($this->sock), $this->user);
            $n = socket_select($read, $write, $except, 0);
            $this->log->trace("socket_select eseguita");
            $this->log->trace("read: " . print_r($read, true));
            $this->log->trace($this->__toString());
            //aggiunta client
            if (($key = array_search($this->sock, $read)) !== false) {
                unset($read[$key]);
                $this->aggiungi();
            }
            //chat
            if ($read !== false) {
                $this->receiveCmd($read);
            }
            if ($this->state % (FRAZ_SEC*BROADCAST_SEC_INTERVAL) === 0) {
                gc_collect_cycles();
                $this->keep_alive_connections();
            }
            usleep(SLEEP);
            $this->state++;
            if ($this->state%(FRAZ_SEC*5) === 0) {
                $this->log->debug("ciclo " . $this->getSec());
            }
            if($this->state%FRAZ_SEC===0){
//                $this->log->debug("ciclo2 " . $this->getSec());
                $this->log->trace($this->__toString());
            }
        }
    }

    public function decode_connection_msg($msg){// "login*1234" "login*user&&root"
        $msg_array = explode("*", trim($msg));
        if($msg_array[0]==="login"){
            $response = array("type"=>null, "val"=>null);

            $user_disp = explode("&&", trim($msg_array[1]));
            if(      count($user_disp)==1){ //login dispositivo
                if(preg_match("/^\d{4}$/", $user_disp[0])){
                    $response["type"] = DISP;
                    $response["val"] = $user_disp[0];
                }else{
                    $response["type"] = ERR;
                    $response["val"] = "Tentativo di login dispositivo non riuscito. ID dispositivo non conforme: ".$user_disp[0];
                }
            }else if(count($user_disp)>1){  //login utente
                if($user_disp[0]==="user"){
                    $response["type"] = USER;
                    $response["val"] = $user_disp[1];
                }else{
                    $response["type"] = ERR;
                    $response["val"] = "Tentativo di login non conforme allo standard: ".$msg;
                }
            }else{
                $response["type"] = ERR;
                $response["val"] = "Attenzione! Tentativo di login non conforme allo standard: ".$msg;
            }
        }else{
            $response["type"] = ERR;
            $response["val"] = "Rifiutata tentata connessione. Messaggio di registrazione non conforme!";
        }
        return $response;
    }

    private function decodeCmd($string_cmd){//es:  "example_cmd@@id_disp" il comando deve essere impartito al dispositivo id_disp
        $msg_array = explode("&&", trim($string_cmd));
//        $ret_array[0] = $msg_array[0];
//        $ret_array[1] = explode(",", $msg_array[1]);

        return $msg_array;
    }

    //aggiunta client
    private function aggiungi(){
        $this->log->trace("-----function: aggiungi()");
        if($buf = @socket_accept($this->sock)){
//            $sso = socket_set_option($buf, SOL_SOCKET, SO_SNDTIMEO, array('sec'=>TO_HANDSHAKE,'usec'=>100));
            $sso = socket_set_option($buf, SOL_SOCKET, SO_RCVTIMEO, array('sec'=>TO_HANDSHAKE,'usec'=>100));
            socket_set_nonblock($buf);
            $this->log->debug("riferimento al buffer: ".print_r($buf, true));
            $login_msg = $this->decode_connection_msg($this->leggi($buf));
            if($login_msg["type"]===ERR){
                $this->log->warn($login_msg["val"]);
                return false;
            }elseif($login_msg["type"]===USER){
                $user = $login_msg["val"];
                $this->user[$user] = $buf;
                if($this->scrivi_a_user($user, "Benvenuto")===false){
                    $this->log->error("Errore in scrittura di benvenuto allo User: ".$user);
                    return false;
                }
                $this->log->info("User $user aggiunto");
            }elseif($login_msg["type"]===DISP){
                $id_dispositivo = $login_msg["val"];
                $this->dispositivi[$id_dispositivo] = $buf;
                $this->log->info("Dispositivo $id_dispositivo aggiunto");
            }
            return true;
        }
    }

    //richiamo metodi per chat
    private function receiveCmd($read){
        $this->log->trace("-----function: receiveCmd(read)");
        //lettura client
        foreach($read as $key=>$value){
            //key corrispondente nell'array user
            $key_u = array_search($value, $this->user);
            $ret = $this->leggi_da_user($key_u);
            $string_cmd = trim($ret);
            if($string_cmd){//se c'è una stringa
                //bisogna controllare se è un comando valido
                $cmd = $this->decodeCmd($string_cmd);
                print_r($cmd, true)."\n";
                if(array_key_exists($cmd[0], $this->cmd)){
                    //TODO: da implementare con design pattern apposito
                    $command = $this->cmd[$cmd[0]];
                    $id_disp = $cmd[1];
                    switch ($this->cmd[$cmd[0]]){
                        case "help":
                            print "inoltrato comando help\n";
                            if(array_key_exists($id_disp, $this->dispositivi)){
                                $this->scrivi_a($id_disp, $command);
                                $r = new Request($this->dispositivi[$id_disp], $id_disp, $command);
                            }else{
                                $this->log->warn("Id dispositivo: ".$id_disp." non connesso.");
                            }
                            break;

                        case "quit":$this->log->debug("!segnale 'quit' per disconnessione ricevuto");
                            if($this->disconnetti_user($key_u)){
                                $this->log->debug("situazione master:\n".$this->__toString());
                            }else{
                                $this->log->warn("Tentata disconnessione dello User $key per segnale 'quit' fallita. situazione master:\n".$this->__toString());
                            }
                            break;

                        default: $this->log->warn("Comando dello user:".$this->cmd[$cmd]." sconosciuto!");
                    }


                }

            }
        }
        return true;
    }

    //lettura
    private function leggi($sock){
        return socket_read($sock, MAX_CHARS_READ, PHP_NORMAL_READ);
    }
    protected function leggi_da($key){
        $this->log->trace("-----function: leggi_da($key)");
        $msg = socket_read($this->dispositivi[$key], MAX_CHARS_READ, PHP_NORMAL_READ);
        $this->log->info("Ho letto dal dispositivo $key: $msg");
        return $msg;
    }
    protected function leggi_da_dispositivo($key){
        $this->log->trace("-----function: leggi_da_dispositivo($key)");
        $msg = socket_read($this->dispositivi[$key], MAX_CHARS_READ);
        $this->log->info("Ho letto dal dispositivo $key: $msg");
        return $msg;
    }
    protected function leggi_da_user($key){
        $this->log->trace("-----function: leggi_da_user($key)");
        $msg = socket_read($this->user[$key], MAX_CHARS_READ, PHP_NORMAL_READ);
        $this->log->info("Ho letto dallo user $key: $msg");
        return $msg;
    }

    //scrittura
    private function scrivi($sock, $mex){
        return @socket_write($sock, $mex, strlen ($mex));
    }
    protected function scrivi_a($key, $msg){
        $msg.="\r";
        $this->log->debug("scrivi_a($key, $msg) ".strlen($msg));
        $ret = $this->scrivi($this->dispositivi[$key], $msg);
        if($ret===false){
            if($this->disconnetti($key)){
                $this->log->error("Errore con il dispositivo $key (in scrittura). Situazione master:\n".$this->__toString());
            }else{
                $this->log->debug("Situazione master:\n".$this->__toString());
            }
            return false;
        }
        $this->log->info("Ho scritto al dispositivo $key: $msg");
        return $ret;
    }
    protected function scrivi_a_user($key, $msg){
        $msg.="\r";
        $this->log->debug("scrivi_a($key, $msg) ".strlen($msg));
        $ret = $this->scrivi($this->user[$key], $msg);
        if($ret===false){
            if($this->disconnetti($key)){
                $this->log->error("Errore con il dispositivo $key (in scrittura). Situazione master:\n".$this->__toString());
            }else{
                $this->log->debug("Situazione master:\n".$this->__toString());
            }
            return false;
        }
        $this->log->info("Ho scritto al dispositivo $key: $msg");
        return $ret;
    }


    //disconnessione cliente
    private function disconnetti($key){
        $this->log->debug("disconnetti($key)");
        if(@socket_close($this->dispositivi[$key])===false) {
            $this->log->error("Il dispositivo non è stato disconnesso correttamente...problema in fase di socket_close per dispositivo $key:\n".socket_strerror(socket_last_error($this->sock)));
            return false;
        }
        unset($this->dispositivi[$key]);
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

    public function keep_alive_connections(){
        $this->log->trace("-----function: check_disconnect()");
        $this->log->trace($this->__toString());
        if(count($this->checkCons)>0){
            foreach($this->checkCons as $key=>$ka){
                $handshake=false;
                if(!$ka->isRunning()) {//se ha finito il tempo di attesa
                    if ($ka->isHandshaked()) {//ed ha concluso true l'handshaking
                        //l'handshake ha funzionato, devo togliere il ka da $this->checkCons

                        $handshake=true;
                        unset($this->nTry[$key]);
                        $this->log->debug(" ----- handshake [" . $this->checkCons[$key]->key . "] OK");
                    } else {
                        //l'handshake x questa volta è fallito, devo cmq togliere il ka da $this->checkCons
                        if (!isset($this->nTry[$key])) {
                            $this->nTry[$key]=1;  print "+++++++ this->nTry[$key]=>".$this->nTry[$key]."\n";
                        } else {
                            $this->nTry[$key]++;  print "+++++++ this->nTry[$key]=>".$this->nTry[$key]."\n";
                        }
                        $this->log->info(" ----- " . $this->nTry[$key] . "°handshake [$key] FALLITO");
                    }
                }else{
                    if (!isset($this->nTry[$key])) {
                        $this->nTry[$key]=1;  print "+++++++ this->nTry[$key]=>".$this->nTry[$key]."\n";
                    } else {
                        $this->nTry[$key]++;  print "+++++++ this->nTry[$key]=>".$this->nTry[$key]."\n";
                    }
                    $this->log->info(" ----- " . $this->nTry[$key] . "°handshake [$key] FALLITO per attesa...");
                }
                if(!$handshake){
                    if ($this->nTry[$key] >= N_TRY) {// E se è il N_TRY-iesimo handshake sconnettere il cient ed azzerare i contatori tryAppend e nTry
                        $this->disconnetti($key);
                        unset($this->nTry[$key]);       print "++++ unset nTry[$key]\n";
                        unset($this->checkCons[$key]);  print "++++ unset checkCons[$key]\n";
                    }
                }
            }
        }
        if(count($this->dispositivi)>0){
            foreach($this->dispositivi as $key=>$dispositivo){
                $response="";
                $r = socket_write($this->sock, ".\r", 2);
                $response = socket_read($dispositivo, 2);
                print " -- response[".$response."]\n";
                $response = substr($response, 0, 2);
                if($response==="-\r"){
//                    $this->handshake = true;
                    $this->checkCons[$key] = time();
                }else{
//                    $this->handshake = false;

                }
            }
        }

    }


}


