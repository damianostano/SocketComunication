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

class Master
{

    protected $timeOutUser = true;

    public $logic = null;
    public $state = 0;

    /**
     * @return int
     */
    public function getSec(): float
    {
        return $this->state / FRAZ_SEC;
    }

    private $decode = null;

    public function getDecode(): Decode
    {
        return $this->decode;
    }

    public function setDecode(Decode $decode)
    {
        return $this->decode = $decode;
    }

    public $log = null;
    public $indirizzo = "";
    public $porta = "";
    public $sock = "";

    public $user = array(); //lista di connessioni di utenti umani che possono inviare comandi
    public $dispositivi = array();//lista di connessioni dei dispositivi che possono ricevere comandi

    public $codaCmd = array(); //per ogni dispositivo la lista dei comandi ancora inevasi
    public $execCmd = array(); //L'handle a tutti i comandi in esecuzione
    public $codaResponse = array(); //per ogni utente la lista dei comandi eseguiti con relativa response

    public $lastPing = array(); //per ogni dispositivo l'ultima volta che gli ho inviato qualcosa
    public $lastPong = array(); //per ogni dispositivo l'ultima volta che ha risposto
    public $lastPongUser = array();//per ogni utente l'ultima volta che ha risposto
    public $lastLogin = array(); //per ogni dispositivo l'ultima volta che ha effettuato il login (in caso di riconnessione e di cmd keepAlive in sospeso non devo rimandare indietro il lastPing oltre questo valore)
    public $lastLogout = array(); //per ogni dispositivo l'ultima volta che ho rilevato la disconnessione
    public $semaforoDispo = array("server" => true); //per ogni dispositivo indica true o false a seconda se è possibile inviare comandi o no

    private $sequence_cmd = 1;

    public function getSequenceCmd(): string
    {
        $ret = (string)($this->sequence_cmd % 9999);
        $this->sequence_cmd++;
        $ret = str_pad($ret, 4, "0", STR_PAD_LEFT);
        return $ret;
    }


//    public $cmd = array("help"=>"help", "quit"=>"quit", CMD_KEEP_ALIVE);//i comandi accettati

    public function __toString()
    {
        return "Master: " . $this->indirizzo . ":" . $this->porta . "\nDispositivi: " . print_r($this->dispositivi, true) . " Sock: " . print_r($this->sock, true) .
            " codaCmd: " . print_r($this->codaCmd, true) .
            " execCmd: " . print_r($this->execCmd, true) .
            " codaResponse: " . print_r($this->codaResponse, true) .
            " Stato: " . $this->getSec() . "\n";
    }

    public function __statoCmd()
    {
        return " codaCmd: " . print_r($this->codaCmd, true) .
            " execCmd: " . print_r($this->execCmd, true) .
            " codaResponse: " . print_r($this->codaResponse, true);
    }

    public function __statoDispoUser()
    {
        return " Dispositivi: " . print_r($this->dispositivi, true) .
            " User: " . print_r($this->user, true);
    }

    //costruttore
    public function __construct($indirizzo, $porta, MapDispoLogic $mapLogic)
    {
        if (!isset($this->log)) {
            $this->log = Logger::getLogger("monitor.trace");
        }
        $this->decode = new DecodeSimple();
        $this->logic = $mapLogic;//new CompactLogic();                  //      ! ! ! !
        $this->indirizzo = $indirizzo;
        $this->porta = $porta;
        $this->log->info("*****************************************\nAvvio server in corso...");

        set_time_limit(0);
        ob_implicit_flush(); //Implicit flushing will result in a flush operation after every output call (echo, print, ecc...)
        $this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("socket_create() fallito: motivo: " . socket_strerror(socket_last_error($this->sock)) . "\n");
        $ret = socket_bind($this->sock, $this->indirizzo, $this->porta) or die("socket_bind() fallito: motivo: " . socket_strerror($ret) . "\n");
        $ret = socket_listen($this->sock, MAX_QUEUE) or die("socket_listen() fallito: motivo: " . socket_strerror($ret) . "\n");
        $ret = socket_set_nonblock($this->sock) or die("socket_set_nonblock() fallito: motivo: " . socket_strerror($ret) . "\n");
        $this->user[Cmd::$SERVER] = $this->sock;
        $this->avvia();
    }

    //distruttore
    public function __destruct()
    {
        $this->log->debug("-----function: __destruct()");
        @socket_close($this->sock);
        $this->log->info("__destruct: socket_close eseguita");
        $count = count($this->dispositivi);
        foreach ($this->dispositivi as $key => $value) {
            $this->disconnetti($key);
        }
        $this->log->info("__destruct: disconnessi $count dispositivi");
        $count = count($this->user);
        foreach ($this->user as $key => $value) {
            $this->disconnetti_user($key);
        }
        $this->log->info("__destruct: disconnessi $count utenti");
    }


    //ciclo principale
    public function avvia()
    {
        $this->log->info("server " . $this->indirizzo . ":" . $this->porta . " avviato");
        $write = null;
        $except = null;
        while (isset($this->sock)) {
            //controllo cambiamenti
            $read = array_merge(array(), $this->user);
            $n = socket_select($read, $write, $except, 0);//controlla se i sock negli array dati hanno cambiamenti, toglie tutti quelli che non li hanno avuti
            $this->log->trace("socket_select eseguita, $n socket presenti");
            $this->log->trace("read: " . print_r($read, true));
//            $this->log->trace($this->__toString());
            //se ci sono da aggiungere dispositivi o user
//            print(" count(Read): ".count($read));
            if (($key = array_search($this->sock, $read)) !== false) {//se $this->sock è ancora presente ha avuto richieste
                unset($read[$key]);//quindi dato che le tratterò lo devo togliere a mano dall'array
                $this->aggiungi(); //aggiungo chi ha fatto richiesta, l'array read adesso contiene solo le richieste degli user
            }
            //ricevere comandi degli user
            if (count($read) > 0) {
                print("ricevuto comando da user\n");
                $this->receiveCmd($read);
            }
//    $this->__statoDispoUser();
            $this->handlerCmd();
            usleep(SLEEP);
            $this->state++;
            if ($this->state % (FRAZ_SEC * 5) === 0) {
                $this->log->debug("from start " . $this->getSec() . " sec");
            }
            if ($this->state % FRAZ_SEC === 0) {
//                $this->log->trace($this->__toString());
                if (count($this->execCmd) > SOGLIA_CMD_EXEC) {
                    $this->log->warn("Attenzione! Ci sono + di " . SOGLIA_CMD_EXEC . " comandi in esecuzione!");
                }
            }

        }
        $this->log->info("Server arrestato");
        $this->__destruct();
    }

    //aggiunta client
    private function aggiungi()
    {
        $this->log->trace("-----function: aggiungi()");
        if ($buf = @socket_accept($this->sock)) {
//            $sso = socket_set_option($buf, SOL_SOCKET, SO_SNDTIMEO, array('sec'=>TO_HANDSHAKE,'usec'=>100));
//            $sso = socket_set_option($buf, SOL_SOCKET, SO_RCVTIMEO, array('sec'=>2,'usec'=>10000));//timeout impostato ad 1/100 di sec per ricevere il login
            socket_set_nonblock($buf);
            $this->log->debug("riferimento al buffer: " . print_r($buf, true));
            usleep(100000);//timeout impostato ad 1/10 di sec per ricevere il login
            $response = $this->socketRead($buf);
            $this->log->info("1° response: " . print_r($response, true));
            if ($response["state"] == "to") {
                usleep(3000000);//timeout impostato a 3 sec per ricevere il login
                $response = $this->socketRead($buf);   // prima di tentare di rileggere
                $this->log->info("2° response: " . print_r($response, true));
                //altrimenti la cosa è fallita, gestiamo ed andiamo avanti
                if ($response["state"] == "to") {
                    $this->log->error("2° tentativo di identificazione richiesta di collegamento fallito!");
                }
            } elseif ($response["state"] != "ok") {//non dovrebbe nemmeno succedere
                $this->log->error("Errore inatteso nell'aggiunta di un Utente o Dispositivo!");
            }
            //se tutto va bene
            if ($response["state"] == "ok") {
                $this->log->debug("state OK");
                /*$sso = */socket_set_option($buf, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 0, 'usec' => 1000));//timeout impostato ad 1/1000 di sec per ricevere i normali dati
                $login_msg = $this->getDecode()->decode_connection_msg($response["msg"]);
                if ($login_msg->isERR()) {
                    $this->log->error($login_msg->getVal());
                    return false;
                } elseif ($login_msg->isUSER()){
                    $user = $login_msg->getVal();
                    if (!key_exists($user, $this->user)) {//non devono esserci 2 utenti con lo stesso user
                        $this->user[$user] = $buf;
                        $this->lastPongUser[$user] = microtime(true);
                        if ($this->scrivi_a_user($user, "Benvenuto") === false) {
                            $this->log->error("Errore in scrittura di benvenuto allo User: " . $user);
                            return false;
                        }
                        $this->log->info("User $user aggiunto");
                    } else {//un utente è già loggato con questo nome
                        $this->user[$user] = $buf;//anche se è rientrato prendo il nuovo buffer
                        $this->lastPongUser[$user] = microtime(true);
                        if ($this->scrivi_a_user($user, "Sei rientrato") === false) {
                            $this->log->error("Errore in scrittura di benvenuto allo User: " . $user);
                            return false;
                        }
                        $this->log->warn("User $user già presente!");
                    }
                } elseif ($login_msg->isDISP()) {
                    $id_dispositivo = $login_msg->getVal();

                    //-------- FARSI DARE LA CONFIGURAZIONE ATTUALE PER SALVARSELA SUL DB
                    //TODO: ottenere il comando per richiedere la configurazione a seconda della tipologia di dispositivo
                    try {
                        $logic = $this->logic->getLogic($id_dispositivo);
                    } catch (NotMappedException $e) {
                        $this->logic->refreshDispositivi(); //reinterroga il DB per controllare se sono stati salvati altri dispositivi
                        try {
                            $logic = $this->logic->getLogic($id_dispositivo);
                        } catch (NotMappedException $e) {//ok non c'è sicuramente
                            $this->log->error("Errore in getLogic nella funzione aggiungi(): ". $e->getMessage());
                            return false;
                        }
                    }

//                    if(isset($this->lastLogout[$id_dispositivo]) && isset($this->lastPong[$id_dispositivo]))
                    $maxLastLogoutOrPong = $this->lastLogout[$id_dispositivo] > $this->lastPong[$id_dispositivo] ? $this->lastLogout[$id_dispositivo] : $this->lastPong[$id_dispositivo];
//                    else
//                        $maxLastLogoutOrPong = false;
                    $disconnTime = $maxLastLogoutOrPong ? microtime(true) - $maxLastLogoutOrPong : "prima connessione... 0";
                    if (!key_exists($id_dispositivo, $this->dispositivi)) {
//                        $this->log->info("Dispositivo $id_dispositivo aggiunto");
                        Logger::getLogger("monitor.disconnTime")->info($id_dispositivo . ") Dispositivo aggiunto. DisconnTime: $disconnTime sec. ", new Exception($id_dispositivo));
                    } else {//un dispositivo è già loggato con questo id
                        //TODO: restituire un messaggio (una mail?) per informare della cosa?
                        //Fabrizio dice che può succedere normalmente se va giù la connessione x motivi indipendenti da noi, è impossibile saperlo nel momento in cui accade
//                        $this->log->info("Dispositivo $id_dispositivo già presente!");
                        Logger::getLogger("monitor.disconnTime")->info($id_dispositivo . ") Dispositivo già presente! DisconnTime: $disconnTime sec. ", new Exception($id_dispositivo));
                    }

                    //Sia che il dispositivo lo senta già connesso o sia la 1° volta bisogna ricollegare il buffer
                    //(nel caso già lo senta potrebbe essere caduta la connessione e quindi essere ricollegato in un nuovo socket)
                    $this->dispositivi[$id_dispositivo] = $buf;
                    $this->lastPing[$id_dispositivo] = microtime(true);
                    $this->lastPong[$id_dispositivo] = microtime(true);
                    $this->lastLogin[$id_dispositivo] = microtime(true);
                    $this->semaforoDispo[$id_dispositivo] = true;
                    //bisognerebbe azzerare le code comandi in esecuzione perchè se si è riconnesso i vecchi cmd oramai sono persi, non riceverranno mai risposta
                    //ma non conviene perchè i comandi non sono divisi per dispositivo, meglio aspettare semplicemente che scadano
//                    $this->execCmd[$id_dispositivo] = array(); //non va

                    $cmd_get_conf = $logic->getCmdReadConfig(); //"**ur" per il compact
                    $command = new Cmd($this->getSequenceCmd(), $cmd_get_conf, $id_dispositivo, Cmd::$SERVER);
                    $this->codaCmd[$command->getIdDispo()][] = $command;
                }
            }
            return true;
        }
    }


    private function receiveCmd($read)
    {
        $this->log->trace("-----function: receiveCmd(read)");
        //lettura client
        foreach ($read as $key => $value) {
            //key corrispondente nell'array user
            $key_u = array_search($value, $this->user);
            if ($ret = $this->leggi_da_user($key_u)) {
                $string_cmd = trim($ret);
                if ($string_cmd) {//se c'è una stringa
                    //bisogna controllare se è un comando valido
                    try {
                        $cmd = $this->getDecode()->decodeCmd($string_cmd);
                        try {
                            $isCmd = $this->logic->getLogic($cmd->getIdDispo())->isCmd($cmd);
                        } catch (NotMappedException $e) {
                            $this->logic->refreshDispositivi(); //reinterroga il DB per controllare se sono stati salvati altri dispositivi
                            try {
                                $isCmd = $this->logic->getLogic($cmd->getIdDispo())->isCmd($cmd);
                            } catch (NotMappedException $e) {//ok non c'è sicuramente
                                $this->log->error("Errore in getLogic nella funzione receiveCmd(): ". $e->getMessage());
                                return false;
                            }
                        }
                        if ($cmd !== null && $isCmd) {
                            $id_cmd = $this->getSequenceCmd();
                            $command = new Cmd($id_cmd, $cmd->getCmd(), $cmd->getIdDispo(), $key_u);
                            //TODO:? controllo che non ci siano comandi ripetuti per lo stesso utente
                            $this->codaCmd[$command->getIdDispo()][] = $command;
                            $this->lastPongUser[$command->getIdUser()] = microtime(true);
                        } else {
                            $this->scrivi_a_user($key_u, CMD_INVALID);
                            $this->log->warn("Ricevuto comando non riconosciuto: " . $cmd->getCmd());
                        }
                    } catch (Exception $e) {
                        $this->log->error($e->getMessage());
                    }
                } elseif ($string_cmd === "") {
                    $this->disconnetti_user($key_u);
                }
            }
        }
        if (count($this->codaCmd) > 0 || count($this->execCmd) > 0 || count($this->codaResponse) > 0) {
            $this->log->debug("\nreceiveCmd:\n" . $this->__statoCmd());
        }
        return true;
    }

    private function handlerCmd()
    {
        $this->log->trace("-----function: handlerCmd()");

        if (count($this->codaCmd) > 0 || count($this->execCmd) > 0 || count($this->codaResponse) > 0) {
            $this->log->debug("\nhandlerCmd:\n" . $this->__statoCmd());
        }
        $n_dispo_connessi = count($this->dispositivi);
        $now = microtime(true);

        //giro di keep alive
        if ($n_dispo_connessi > 0) {
            $this->log->trace("handlerCmd: giro di keep alive");
            foreach ($this->dispositivi as $id_dispo => $sock_dispo) {
                $this->log->trace("handlerCmd: id_dispo(" . $id_dispo . ")");
                $interval = $now - $this->lastPing[$id_dispo];//se il tempo passato dall'ultima volta che ho mandato qualche cosa
                $this->log->trace("handlerCmd: interval(" . $interval . ")");
                if ($interval > KEEPALIVE_SEC && $this->semaforoDispo[$id_dispo]) {//è > del tempo definito per il mantenimento della connessione e il semaforo è verde
                    //mando il segnale di keep_alive
                    $cmd = Cmd::getKeepAlive($this->getSequenceCmd(), $id_dispo);
                    $this->log->trace("handlerCmd: creazione cmd di keepalive id(" . $cmd->getId() . ")");
                    $this->eseguiCmd($cmd);                 //eseguiCmd() riazzera il tempo di lastPing
                    Logger::getLogger("monitor.disconnTime")->info($id_dispo . ") handlerCmd: eseguito cmd di keepalive " . $cmd->getId() . " e messo in coda di esecuzione per il dispo: " . $id_dispo, new Exception($id_dispo));
                    //non passo per la normale coda dei comandi, il keepalive va diretto in esecuzione
                }
            }
        }

        //se ho dei comandi in coda per qualche dispositivo
        $countCodaCmd = count($this->codaCmd);
        if ($countCodaCmd > 0) {
            $this->log->debug("handlerCmd: ci sono " . $countCodaCmd . " comandi in coda per qualche dispositivo");
            foreach ($this->codaCmd as $id_dispo => $codaCmdDispo) {//per ogni dispositivo estraggo la sua coda
                if (count($this->codaCmd[$id_dispo]) > 0) {  //se ci sono effettivamente comandi
                    $cmd = array_shift($this->codaCmd[$id_dispo]);//estraggo il successivo comando di quel dispositivo
                    if ($this->semaforoDispo[$id_dispo]) { //e il semaforo è verde
                        if ($id_dispo === Cmd::$SERVER) {//se si vuole comunicare con il server invece che mandare un comando ad un dispositivo
                            $this->cmd4Server($cmd);
                        } elseif (isset($this->dispositivi[$id_dispo]) && $this->dispositivi[$id_dispo] != null) {//se c'è il dispositivo giusto per l'esecuzione
                            $this->eseguiCmd($cmd);                 //lo eseguo
                        } else {//ci sono comandi per dispositivi non presenti (teoricamente non dovrebbe succedere)

                            if ($cmd->getTsWait() === null) {

                                //TODO: bisognerebbe creare una risposta per l'utente che ha impartito il comando, il dispositivo potrebbe riconnettersi e quindi eseguire il comando
                                $this->log->warn("Il dispositivo " . $id_dispo . " non è attualmente connesso. Il comando: " . $cmd->getId() . " attenderà " . TIME_OUT_NORMAL . " sec, dopodichè sarà annullato.");
                                $cmd->setTsWait(microtime(true));
                                $this->codaCmd[$id_dispo][] = $cmd;
                            } else {
                                $interval = $now - $cmd->getTsWait();
                                if ($interval < TIME_OUT_NORMAL) {
                                    $this->codaCmd[$id_dispo][] = $cmd;//per verificarlo l'avevo tolto quindi dato che può aspettare ancora lo rimetto in coda
                                } else {
                                    $cmd->setResponse(RES_DELETE);//Setto la risposta per indicare la mancata risposta del dispositivo
                                    $this->codaResponse[$cmd->getIdUser()][] = $cmd;//lo metto nella coda di risposte allo user che aveva fatto la richiesta informandolo della mancata risposta del dispositivo
                                    Logger::getLogger("monitor.appendCmd")->warn("Comando " . $cmd->getId() . " annullato, non è potuto essere recapitato al dispositivo $id_dispo per più di " . TIME_OUT_NORMAL . " sec.\n" . print_r($cmd, true), new Exception($id_dispo));
                                }
                            }
                        }
                    } else {//il semaforo di quel dispositivo è rosso
                        $cmd->setResponse(RES_BUSY);
                        $this->execCmd[$cmd->getId()] = $cmd;
                    }
                } else {//se non ci sono comandi
                    unset($this->codaCmd[$id_dispo]);
                }
            }
        }

        //faccio un giro per vedere se qualche dispositivo ha scritto
        $countExecCmd = count($this->execCmd);
        if ($n_dispo_connessi > 0 /*&& $countExecCmd>0*/) {//se ci sono dei dispositivi connessi /*ed ho ancora dei comandi in esecuzione*/
            $this->log->trace("handlerCmd: controllo per vedere se qualche dispositivo ha scritto risposte o comnadi.");
            foreach ($this->dispositivi as $id_dispo => $sock_dispo) {  //per ogni dispositivo
                $response = $this->socketRead($sock_dispo);         //provo a vedere se c'è qualche cosa da leggere
                if ($response["state"] === "to") {                       //se va in time out non c'è nulla nel buffer
                    $interval = $now - $this->lastPong[$id_dispo];    //se il tempo passato dall'ultima volta che ho ricevuto qualche cosa da quel dispositivo
                    //è > del tempo definito per il mantenimento della connessione
                    if (($interval > TIME_OUT_NORMAL && $this->semaforoDispo[$id_dispo]) || //se il semaforo è verde c'è un tempo
                        ($interval > TIME_OUT_WAIT && !$this->semaforoDispo[$id_dispo])
                    ) { //se il dispo è in WAIT il tempo è più lungo
                        //stronca la connessione
                        if ($this->disconnetti($id_dispo)) {
                            Logger::getLogger("monitor.disconnTime")->warn($id_dispo . ") handlerCmd: TimeOut per il dispositivo $id_dispo superato! interval: " . $interval . "; now: " . $now . "; lastPong: " . $this->lastPong[$id_dispo] . "\n", new Exception($id_dispo));
                        }
                    }
                } elseif ($response["state"] === "max") {
                    //stronca la connessione perchè c'è qualche cosa che non va, sto leggendo a vuoto
                    if ($this->disconnetti($id_dispo)) {
                        Logger::getLogger("monitor.disconnTime")->warn($id_dispo . ") handlerCmd: Letto max caratteri da dispositivo $id_dispo quindi l'ho appena disconnesso. interval: " . $interval . "; now: " . $now . "; lastPong: " . $this->lastPong[$id_dispo] . "\n", new Exception($id_dispo));
                    }
                } elseif ($response["state"] === "err") {
                    //stronca la connessione perchè c'è qualche cosa che non va, sto leggendo a vuoto
                    if ($this->disconnetti($id_dispo)) {
                        Logger::getLogger("monitor.disconnTime")->warn($id_dispo . ") handlerCmd: Letto carattere vuoto da $id_dispo quindi l'ho appena disconnesso. interval: " . $interval . "; now: " . $now . "; lastPong: " . $this->lastPong[$id_dispo] . "\n", new Exception($id_dispo));
                    }
                } elseif ($response["state"] === "ok") {                 //se invece ho ricevuto effettivamente qualche cosa
                    try {
                        $msg_from_dispo = $response["msg"];
                        if ($this->getDecode()->isResponse($msg_from_dispo)) {//risposta ad un messaggio
                            $msg = $this->getDecode()->decodeResponse($msg_from_dispo); //splitto la risposta nel suo ID e nel messaggio
                            $cmd = $this->execCmd[$msg->getIdMsg()];    //prendo il comando da quelli in esecuzione
                            if ($cmd != null) {                                 //e se ho un comando in esecuzione che ha richiesto questa risposta
                                $cmd->setResponse($msg->getResponse());  //gli setto la risposta (che setta anche il tempo di ricezione)
                                $this->lastPong[$cmd->getIdDispo()] = microtime(true);//aggiorno l'ultima volta che il dispo mi ha risposto
                            } else { //ho una risposta ad un comando che non c'è più... la risposta cade nel vuoto
                                $this->log->warn("Risposta al comando " . $msg->getIdMsg() . " arrivata ma comando non più presente!");
                            }
                        } else {//comando di un dispositivo
                            try {//COMANDO{param1=val1;param2=val2;}*Sisas666@@server\n
                                $cmd = $this->getDecode()->decodeCmd($msg_from_dispo);//WAIT*Sisas666@@server\n   oppure keep alive del dispo-> .*Sisas666@@server\n
                                $isCmd = $this->logic->getLogic($cmd->getIdDispo())->isCmd($cmd);
                                if ($cmd !== null && $isCmd) {
                                    $id_cmd = $this->getSequenceCmd();
                                    //idDispo è "server" dato che è un dispositivo a mandare il comando, sul posto dell'idUser ci va quello del dispositivo che ha mandato il comando
                                    $idDispo = explode("*", trim($cmd->getCmd()))[1];
                                    $command = new Cmd($id_cmd, $cmd->getCmd(), $cmd->getIdDispo(), $idDispo);
                                    $this->codaCmd[$command->getIdDispo()][] = $command;
                                    $this->lastPong[$idDispo] = microtime(true);
                                } else {
//                                $this->scrivi_a_dispositivo($key_d, CMD_INVALID); //non ha senso ancora scrivere al dispositivo se non c'è la logica che elabora la risposta
                                    $this->log->warn("Ricevuto comando non riconosciuto dal dispo " . $idDispo . ": " . $cmd->getCmd(), new Exception($cmd->getIdDispo()));
                                }
                            } catch (Exception $e) {
                                $this->log->error($e->getMessage());
                            }
                        }
                    } catch (Exception $e) {
                        $this->log->error($e->getMessage());
                    }
                }
            }
        }

        //se ci sono comandi eseguiti in attesa di essere mandati al richiedente?
        if ($countExecCmd > 0) {
            $this->log->debug("handlerCmd: ci potrebbero essere fino a " . $countExecCmd . " comandi eseguiti in attesa");
            foreach ($this->execCmd as $id_msg => $cmd) {
                if ($cmd->getTsRicezione() != null) {//se il comando ha ricevuto una risposta
                    $this->codaResponse[$cmd->getIdUser()][] = $cmd;//lo metto nella coda di risposte allo user che aveva fatto la richiesta
                    unset($this->execCmd[$id_msg]); //e lo tolgo dall'insieme di quelli in esecuzione
                } else { //se ancora non ho avuto risposta al comando passato
                    $interval = $now - $cmd->getTsInvio();         //se il tempo passato da quando l'ho inviato
                    $CMD_TO = $cmd->isKeepAlive() ? TIME_OUT_NORMAL : CMD_TIME_OUT; //a seconda se è un comando di keep alive o normale ha un diverso tempo di attesa
                    if ($interval > $CMD_TO) { //è > del tempo di attesa massimo di risposta di un comando
                        $cmd->setResponse(RES_DELETE);//Setto la risposta per indicare la mancata risposta del dispositivo
                        $this->codaResponse[$cmd->getIdUser()][] = $cmd;//lo metto nella coda di risposte allo user che aveva fatto la richiesta informandolo della mancata risposta del dispositivo
                        Logger::getLogger("monitor.appendCmd")->warn("Non ho avuto risposta al comando $id_msg. Comando eliminato:\n" . print_r($this->execCmd[$id_msg], true), new Exception($cmd->getIdDispo()));
                        unset($this->execCmd[$id_msg]);//lo elimino
                    }
                }
            }
        }

        //invio le risposte presenti agli utenti che hanno fatto richiesta
        $countCodaResponse = count($this->codaResponse);
        if ($countCodaResponse > 0) {
            $this->log->debug("handlerCmd: ci sono " . $countCodaResponse . " risposte che attendono di essere inviate agli User richiedenti");
            foreach ($this->codaResponse as $id_user => $codaRespUser) {
                if (isset($this->user[$id_user])) {   //se l'utente a cui va mandata la risposta c'è
                    if (count($this->codaResponse[$id_user]) > 0) {     //se ci sono effettivamente response in coda
                        $cmd = array_shift($this->codaResponse[$id_user]);      //la estraggo
                        if ($cmd->getIdUser() === Cmd::$SERVER) {
                            $this->response4Server($cmd);

                        } else {
                            //logica di risposta
                            try {
                                $rispXuser = $this->logic->getLogic($cmd->getIdDispo())->elaboraRisposta($cmd);
                                $this->scrivi_a_user($id_user, $rispXuser);    //scrivo allo user la risposta del dispositivo
                            }catch(NotMappedException $nme){
                                $this->log->error($cmd->getIdDispo().") Errore durante l'invio delle risposte presenti agli utenti. In 'this->logic->getLogic(cmd->getIdDispo())'.", $nme);
                            }catch(Exception $e){
                                $this->log->error($cmd->getIdDispo().") Errore durante l'invio delle risposte presenti agli utenti. In '->elaboraRisposta(cmd)'.", $e);
                            }
                        }
                    } else {
                        unset($this->codaResponse[$id_user]);   //ho finito i messaggi a questo user quindi posso ripulire la sua coda
                    }
                } else { //l'utente richiedente non c'è più, forse è caduta la connessione o si è disconnesso
                    if (count($this->codaResponse[$id_user]) > 0) {     //lo user non c'è ma ci sono risposte appese per lui
                        foreach ($this->codaResponse[$id_user] as $cmd) {//le prendo e prima di eliminarle le loggo
                            Logger::getLogger("monitor.appendCmd")->warn("User $id_user disconnesso, risposta al messaggio " . $cmd->getId() . " eliminata.\n" . print_r($cmd, true));
                        }
                    }
                    unset($this->codaResponse[$id_user]);//quindi ripulisco la sua coda
                }
            }
        }

        //giro di disconnessione utenti appesi
        if ($this->timeOutUser && count($this->user) > 1) {
            $this->log->trace("handlerCmd: giro di controllo degli user appesi");
            foreach ($this->user as $id_user => $sock_user) {
                if ($id_user === Cmd::$SERVER)
                    continue;
                $interval = $now - $this->lastPongUser[$id_user];//se il tempo passato dall'ultima volta che lo user mi ha mandato qualche cosa
                if ($interval > TIME_OUT_USER) {       //è > del tempo definito per il mantenimento della connessione con gli user
                    //stronco user
                    $this->log->info("TIME_OUT_USER (" . $id_user . ") raggiunto.");
                    $this->disconnetti_user($id_user);
                }
            }
        }
    }

    private function response4Server(Cmd $cmd)
    {
        if ($cmd->isKeepAlive()) {//se è un comando di keep alive
            if ($cmd->getResponse() !== RES_KEEP_ALIVE) {//bisogna validare il response
                $pongPreCmd = ($cmd->getTsInvio() - KEEPALIVE_SEC);//se non è valido dovrei riportare indietro il pong time, come se non avessi ricevuto risposta
                $lastLogin = $this->lastLogin[$cmd->getIdDispo()];//ma non più sotto dell'ultima volta che il dispo si è loggato
                $lastPong = $this->lastPong[$cmd->getIdDispo()];//e non più sotto dell'ultima volta che il dispo ha risposto se c'è stata una comunicazione successiva andata a buon fine

                $max = max($pongPreCmd, $lastLogin, $lastPong);//in parole povere serve la MAX tra le 3
                $this->lastPong[$cmd->getIdDispo()] = $max;
                $this->log->warn("ATTENZIONE! Il dispositivo " . $cmd->getIdDispo() . " non ha risposto '-' al keep alive:\n" . print_r($cmd, true));//ma loggo la cosa
            } else {
                $this->log->info("Response al KeppAlive " . $cmd->getId() . " valido");
            }
        } else {//TODO: futuri possibili sviluppi

            $logic = $this->logic->getLogic($cmd->getIdDispo());
            if($cmd->getCmd() === $logic->getCmdReadConfig()){
                if($cmd->getResponse()!=RES_DELETE){
                    //TODO: salvare nel DB la configurazione
                    try {
                        $rispXuser = $logic->elaboraRisposta($cmd);
                        $config4bd = $logic->decodeConfigInDbForm($rispXuser);
                        $logic->updateConfig($config4bd, $this->logic->db);//$config4bd['citta']='B'
                    }catch(Exception $e){
                        $this->log->error($cmd->getIdDispo().") Errore in response4Server (".$e->getMessage().").", $e);
                    }
                }
                $this->log->info("---------------------------------Arrivata risposta dispositivo ".$cmd->getIdDispo().". Configurazione: ".$cmd->getResponse());
            }
        }
    }

    private function cmd4Server(Cmd $cmd)
    {
        $str_cmd = ServerLogic::getCmd($cmd);
        if(preg_match("/^.+\\{.+\\}$/", $str_cmd)){

            $cmd_array = explode("{", $str_cmd);

            $keyCmd = $cmd_array[0];
            $parCmd = substr($cmd_array[1], 0, -1);//tolgo l'ultimo carattere che so essere la parentesi }
            if(preg_match("/^(.+=.+;)+$/", $parCmd)){

                $parametri_str = explode(";", $parCmd);
                $parametri = null;
                foreach ($parametri_str as $param){
                    $par_k_v = explode("=", $param);
                    if($par_k_v[0]=="")
                        continue;
                    $key    = $par_k_v[0];
                    $value  = $par_k_v[1];
                    $parametri[$key] = $value;
                }
            }else{
                //se i parametri non sono scritti bene...
            }
        }else{
            $keyCmd = $cmd;
        }
        $valCmd = ServerLogic::getValue($cmd);
        if ($keyCmd === "quit") {
            $this->log->info("Ricevuto comando di spegnimento");
            unset($this->sock);
        } else {
            $cmd->setTsInvio();
            $cmd->setTsWait(null);

            if ($keyCmd === "list_dispo") {//richiesta di dispositivi connessi attualmente
                $this->log->info("Ricevuto comando di lista dispositivi");
                //reperire la lista dispo
                $list = $this->list_dispo($valCmd);
                //elaboro lista per scrivere messaggio di response
                $response = $list != null ? implode(";", $list) : ";";
                $cmd->setResponse($response);
                $this->execCmd[$cmd->getId()] = $cmd;   //lo metto nell'insieme di quelli lanciati identificandolo per id univoco

            } elseif ($keyCmd === "list_user") {//richiesta di utenti connessi attualmente
                $this->log->info("Ricevuto comando di lista utenti");
                //reperire la lista utenti
                $list = $this->list_user($valCmd);
                //elaboro lista per scrivere messaggio di response
                $response = $list != null ? implode(";", $list) : ";";
                $cmd->setResponse($response);
                $this->execCmd[$cmd->getId()] = $cmd;   //lo metto nell'insieme di quelli lanciati identificandolo per id univoco

            } elseif ($keyCmd === "logout_user") {//logout user
                $this->log->info("Ricevuto comando di logout User: " . $valCmd, new Exception($valCmd));
                $this->disconnetti_user($valCmd);
                $cmd->setResponse(CMD_ESEGUITO);
                $this->execCmd[$cmd->getId()] = $cmd;   //lo metto nell'insieme di quelli lanciati identificandolo per id univoco

            } elseif ($keyCmd === CMD_WAIT) {//richiesta di mettere in pausa l'invio di comandi al dispositivo
//                $this->log->info("Ricevuto comando WAIT da dispositivo: ".$valCmd);
                Logger::getLogger("monitor.disconnTime")->info($valCmd . ") Ricevuto comando WAIT da dispositivo: " . $valCmd, new Exception($valCmd));
//                unset($this->execCmd[$cmd->getId()]);
                $this->semaforoDispo[$valCmd] = false;

            } elseif ($keyCmd === CMD_READY) {//fine del periodo di sospensione di invio comandi
//                $this->log->info("Ricevuto comando READY da dispositivo: ".$valCmd);
                Logger::getLogger("monitor.disconnTime")->info($valCmd . ") Ricevuto comando READY da dispositivo: " . $valCmd, new Exception($valCmd));
//                unset($this->execCmd[$cmd->getId()]);
                $this->semaforoDispo[$valCmd] = true;

            } elseif ($keyCmd === CMD_KEEP_ALIVE) {//keepalive del dispositivo
//                $this->log->info("Ricevuto comando READY da dispositivo: ".$valCmd);
                Logger::getLogger("monitor.disconnTime")->info($valCmd . ") Ricevuto comando di KeepAlive da dispositivo: " . $valCmd, new Exception($valCmd));
                if ($this->semaforoDispo[$valCmd] === true) {
                    $rka = Cmd::getResponseKeepAlive($this->getSequenceCmd(), $valCmd);
                    $this->eseguiCmd($rka, false);
                    Logger::getLogger("monitor.disconnTime")->info($valCmd . ") Risposto con comando di ResponseKeepAlive id_cmd: " . $rka->getId(), new Exception($valCmd));
                } else {
                    Logger::getLogger("monitor.disconnTime")->info($valCmd . ") Non posso rispondere con ResponseKeepAlive. $valCmd in WAIT", new Exception($valCmd));
                }

            } elseif ($keyCmd === CMD_MAIL) {//un dispositivo richiede di mandare una mail
                //i parametri sono: to, Subject, Text
                $mail = new Mail();
                //ottenere mail dal dispositivo
                $logic = $this->logic->getLogic($valCmd);
                $mail_riferimento = $logic->getMailFromDispo($valCmd); //reinterroga il DB per controllare se sono stati salvati altri dispositivi
                $response = $mail->send($mail_riferimento, $parametri['Subject'], $parametri['Text']);
                if($response===true){
                    $this->log->info($valCmd.") Inviata mail per il dispositivo $valCmd. Parametri:".print_r($parametri, true));
                }else{
                    Logger::getLogger("monitor.disconnTime")->error($valCmd.") Errore invio mail per il dispositivo $valCmd. Errore: ".$response."; parametri: ".print_r($parametri, true));
                    $response = $mail->send($parametri['to'], $parametri['object'], $parametri['text']);
                    if($response===true){
                        $this->log->info($valCmd.") Inviata mail per il dispositivo $valCmd al 2° tentativo. Parametri:".print_r($parametri, true));
                    }else{
                        Logger::getLogger("monitor.disconnTime")->error($valCmd.") Errore invio mail per il dispositivo $valCmd anche al 2° tentativo. Errore: ".$response."; parametri: ".print_r($parametri, true));
                    }
                }
            }
        }

    }

    protected function list_dispo($istanza)
    {
        if ($istanza != null) {
            $list_dispo_istanza = $this->logic->mapIstanzeDispo[$istanza];
            $list_dispo = array_keys($this->dispositivi);
            $list_dispo_connessi = array_intersect($list_dispo, $list_dispo_istanza);
        } else {
            $list_dispo_connessi = array_keys($this->dispositivi);
        }
        return $list_dispo_connessi;
    }

    protected function list_user($istanza)
    {
        if ($istanza != null) {
//            $list_dispo_istanza = $this->logic->mapIstanzeDispo[$istanza];
//            $list_dispo = array_keys($this->dispositivi);
//            $list_dispo_connessi = array_intersect($list_dispo, $list_dispo_istanza);
            $list_user_connessi = array_keys($this->user);
        } else {
            $list_user_connessi = array_keys($this->user);
        }
        array_shift($list_user_connessi);
        return $list_user_connessi;
    }

    //lettura
    static function socketRead(&$sock)
    {
        $ret = "";
        $to = "ok";
        for ($i = 1; ; $i++) {
//            print $i."\n";
            if (false === ($tmp = socket_read($sock, 1))) {
                $to = "to";
                break;
            } elseif ($tmp === "\r") {
                $to = "ok";
                break;
            } elseif ($tmp === "\n") {
                $ret .= $tmp;
            } elseif ($tmp === "") {
                $to = "err";
                Logger::getLogger("monitor.disconnTime")->warn("socketRead ha letto carattere vuoto!... ret: " . $ret . "; to: " . $to);
                break;
            } else {
                Logger::getLogger("monitor.trace")->trace("====> entrato in }else{ letto: " . $tmp);
                $ret .= $tmp;
                if ($i > MAX_CHARS_READ) {
                    if ($ret === '') {
                        $to = "max";
                    } else {
                        $to = "to";
                    }
                    Logger::getLogger("monitor.disconnTime")->warn("socketRead ha letto più di " . MAX_CHARS_READ . " caratteri... ret: " . $ret . "; to: " . $to);
                    break;
                }
            }
        }
        Logger::getLogger("monitor.trace")->trace("====> ret: $ret; to: $to");
        return array("msg" => $ret, "state" => $to);
    }

    /**
     * Non usata
     * @param $key
     * @return string
     */
    protected function leggi_da_dispositivo($key){
        $this->log->trace("-----function: leggi_da_dispositivo($key)");
        $msg = socket_read($this->dispositivi[$key], MAX_CHARS_READ);
        $this->log->info("Ho letto dal dispositivo $key: $msg");
        return $msg;
    }

    protected function leggi_da_user($key)
    {
        $this->log->trace("-----function: leggi_da_user($key)");
        $msg = socket_read($this->user[$key], MAX_CHARS_READ, PHP_NORMAL_READ);
        $this->log->info("Ho letto dallo user $key: $msg");
        return $msg;
    }

    //scrittura
    private function scrivi($sock, $mex)
    {
        return @socket_write($sock, $mex, strlen($mex));
    }

    protected function scrivi_a_dispositivo($key, $msg)
    {
        $msg .= "\r";
        $this->log->debug("scrivi_a_dispositivo($key, $msg) " . strlen($msg));
        $ret = $this->scrivi($this->dispositivi[$key], $msg);
        if ($ret === false) {
            if ($this->disconnetti($key)) {
                $this->log->error("Errore con il dispositivo $key (durante la disconnessione, in scrittura). Situazione master:\n" . $this->__toString());
            } else {
                $this->log->debug("Situazione master:\n" . $this->__toString());
            }
            return false;
        } else {
            $this->log->info("Ho scritto al dispositivo $key: $msg");
        }
        return $ret;
    }

    protected function eseguiCmd(Cmd &$cmd, bool $mettiInCodaEsecuzione = true)
    {
        $idDispo = $cmd->getIdDispo();
        $interval = microtime(true) - $this->lastPing[$idDispo];
        if ($interval < 0.2) {
            usleep(SECONDO / 5);//1/5 sec.
        }
        $this->scrivi_a_dispositivo($idDispo, $cmd->buildCmd());
        $cmd->setTsInvio();
        $cmd->setTsWait(null);
        $this->lastPing[$idDispo] = microtime(true);
        if ($mettiInCodaEsecuzione) {
            $this->execCmd[$cmd->getId()] = $cmd;   //lo metto nell'insieme di quelli lanciati identificandolo per id univoco
        }
    }

    protected function scrivi_a_user($key, $msg)
    {
        $this->log->debug("scrivi_a_user($key, $msg) " . strlen($msg));
        $msg .= "\r";
        $ret = $this->scrivi($this->user[$key], $msg);
        if ($ret === false) {
            if ($this->disconnetti_user($key)) {
                $this->log->error("Errore con il dispositivo $key (in scrittura). Situazione master:\n" . $this->__toString());
            } else {
                $this->log->debug("Situazione master:\n" . $this->__toString());
            }
            return false;
        }
        $this->log->info("Ho scritto allo User $key: $msg");
        return $ret;
    }


    //disconnessione
    private function disconnetti($key)
    {
        $this->log->debug("disconnetti($key)");
        try{
            @socket_close($this->dispositivi[$key]);
        }catch (Exception $e){
            $this->log->error("$key) Il dispositivo non è stato disconnesso correttamente...problema in fase di socket_close:\n" . socket_strerror(socket_last_error($this->sock)));
            return false;
        }
        unset($this->dispositivi[$key]);
        $this->lastLogout[$key] = microtime(true);
//        $this->log->info("Dispositivo $key disconnesso");
        Logger::getLogger("monitor.disconnTime")->info("$key) disconnesso");
        return true;
    }

    private function disconnetti_user($key)
    {
        $this->log->debug("disconnetti_user($key)");
        try{
            @socket_close($this->user[$key]);
        }catch (Exception $e){
            $this->log->error("Lo user non è stato disconnesso correttamente...problema in fase di socket_close per User $key:\n" . socket_strerror(socket_last_error($this->sock)));
            return false;
        }
        unset($this->user[$key]);
        $this->log->info("User $key disconnesso");
//        Logger::getLogger("monitor.disconnTime")->info("User $key disconnesso");
        return true;
    }


}





