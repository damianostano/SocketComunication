<?php

/**
 * Created by PhpStorm.
 * User: Sisas
 * Date: 08/02/2017
 * Time: 10:12
 */
class Cmd {
    public static $SERVER="server";

    private $id;
    private $cmd=CMD_KEEP_ALIVE;
    private $id_dispo="";
    private $id_user="";
    private $ts_creazione=null;
    private $ts_invio=null;
    private $ts_ricezione=null;
    private $ts_wait=null;

    private $keep_alive=false;
    /**
     * @return bool
     */
    public function isKeepAlive(): bool{
        return $this->keep_alive;
    }

    private $response="";

    public function buildCmd(){
        $cmd = $this->id."@@".$this->cmd;
        return $cmd;
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

    /**
     * Cmd constructor.
     * @param $id
     * @param string $cmd
     * @param string $id_dispo
     * @param string $id_user
     */
    public function __construct($id, string $cmd, string $id_dispo, string $id_user, bool $keep_alive=false){
        $this->id = $id;
        $this->cmd = $cmd;
        $this->id_dispo = $id_dispo;
        $this->id_user = $id_user;
        $this->ts_creazione = microtime(true);
        $this->keep_alive = $keep_alive;
    }

    public static function getKeepAlive($id, string $id_dispo) :Cmd {
        return new Cmd($id, CMD_KEEP_ALIVE, $id_dispo, Cmd::$SERVER, true);
    }
    public static function getResponseKeepAlive($id, string $id_dispo) :Cmd {
        return new Cmd($id, RES_KEEP_ALIVE, $id_dispo, Cmd::$SERVER, false);
    }

    /**
     * @return int
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCmd(): string
    {
        return $this->cmd;
    }

    /**
     * @return string
     */
    public function getIdDispo(): string
    {
        return $this->id_dispo;
    }

    /**
     * @return string
     */
    public function getIdUser(): string
    {
        return $this->id_user;
    }

    /**
     * @return float
     */
    public function getTsCreazione(): float{
        return $this->ts_creazione;
    }
    /**
     * @return mixed
     */
    public function getTsRicezione()
    {
        return $this->ts_ricezione;
    }
    /**
     * @param mixed $ts_evasione
     */
    public function setTsRicezione()
    {
        $this->ts_ricezione = microtime(true);
    }
    /**
     * @return null
     */
    public function getTsInvio()
    {
        return $this->ts_invio;
    }
    /**
     * @param null $ts_invio
     */
    public function setTsInvio()
    {
        $this->ts_invio = microtime(true);
    }

    /**
     * @return string
     */
    public function getResponse(): string
    {
        return $this->response;
    }

    /**
     * @param string $response
     */
    public function setResponse(string $response){
        $this->response = $response;
        $this->setTsRicezione();
    }

    /**
     * @return null
     */
    public function getTsWait(){
        return $this->ts_wait;
    }

    /**
     * @param null $ts_wait
     */
    public function setTsWait($ts_wait)
    {
        $this->ts_wait = $ts_wait;
    }



}